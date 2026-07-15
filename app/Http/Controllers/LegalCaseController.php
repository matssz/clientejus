<?php

namespace App\Http\Controllers;

use App\Http\Requests\LegalCaseRequest;
use App\Models\CaseType;
use App\Models\LegalCase;
use App\Services\CaseChecklistService;
use App\Services\WhatsAppLink;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LegalCaseController extends Controller
{
    public function __construct(
        private readonly CaseChecklistService $checklistService,
        private readonly WhatsAppLink $whatsAppLink,
    ) {}

    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search'));
        $status = (string) $request->query('status');

        $cases = $request->user()
            ->legalCases()
            ->with(['client', 'caseType'])
            ->withCount([
                'checklistItems as required_document_count' => fn ($query) => $query->where('is_required', true),
                'checklistItems as completed_document_count' => fn ($query) => $query
                    ->where('is_required', true)
                    ->where('is_completed', true),
            ])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query
                        ->where('title', 'like', "%{$search}%")
                        ->orWhereHas('client', fn ($query) => $query->where('name', 'like', "%{$search}%"));
                });
            })
            ->when(array_key_exists($status, LegalCase::statuses()), fn ($query) => $query->where('status', $status))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('cases.index', [
            'cases' => $cases,
            'search' => $search,
            'selectedStatus' => $status,
            'statuses' => LegalCase::statuses(),
        ]);
    }

    public function create(Request $request): View|RedirectResponse
    {
        $clients = $request->user()->clients()->orderBy('name')->get();

        if ($clients->isEmpty()) {
            return redirect()
                ->route('clientes.create')
                ->with('warning', 'Cadastre um cliente antes de criar um caso.');
        }

        return view('cases.create', [
            'clients' => $clients,
            'caseTypes' => CaseType::query()->orderBy('name')->get(),
            'statuses' => LegalCase::statuses(),
            'selectedClientId' => (int) $request->query('client_id'),
        ]);
    }

    public function store(LegalCaseRequest $request): RedirectResponse
    {
        $case = $request->user()
            ->legalCases()
            ->create($this->normalizedData($request->validated(), true));

        $this->checklistService->generateDefaults($case);

        return redirect()
            ->route('casos.show', $case)
            ->with('status', 'Caso cadastrado com sucesso.');
    }

    public function show(Request $request, int $caso): View
    {
        $case = $this->caseForUser($request, $caso)->load([
            'client',
            'caseType',
            'checklistItems.documents',
            'documents.checklistItem',
        ]);
        $requiredItems = $case->checklistItems->where('is_required', true);
        $completedItems = $requiredItems->where('is_completed', true)->count();
        $progress = $requiredItems->isEmpty()
            ? 0
            : (int) round(($completedItems / $requiredItems->count()) * 100);

        return view('cases.show', [
            'case' => $case,
            'requiredItemCount' => $requiredItems->count(),
            'completedItemCount' => $completedItems,
            'documentProgress' => $progress,
        ]);
    }

    public function edit(Request $request, int $caso): View
    {
        return view('cases.edit', [
            'case' => $this->caseForUser($request, $caso),
            'clients' => $request->user()->clients()->orderBy('name')->get(),
            'caseTypes' => CaseType::query()->orderBy('name')->get(),
            'statuses' => LegalCase::statuses(),
        ]);
    }

    public function update(LegalCaseRequest $request, int $caso): RedirectResponse
    {
        $case = $this->caseForUser($request, $caso);
        $case->update($this->normalizedData($request->validated()));

        return redirect()
            ->route('casos.show', $case)
            ->with('status', 'Caso atualizado com sucesso.');
    }

    public function destroy(Request $request, int $caso): RedirectResponse
    {
        $case = $this->caseForUser($request, $caso);

        if ($case->documents()->exists()) {
            return back()->withErrors([
                'delete' => 'Este caso possui documentos e não pode ser excluído.',
            ]);
        }

        $case->delete();

        return redirect()
            ->route('casos.index')
            ->with('status', 'Caso excluído com sucesso.');
    }

    public function whatsapp(Request $request, int $caso): RedirectResponse
    {
        $case = $this->caseForUser($request, $caso)->load('client');

        $message = sprintf(
            'Olá, %s. Aqui é %s. Sobre o caso "%s", o status atual é: %s. Em caso de dúvidas, estou à disposição.',
            $case->client->name,
            $request->user()->name,
            $case->title,
            $case->statusLabel(),
        );

        $url = $this->whatsAppLink->make($case->client->phone, $message);

        if ($url === null) {
            return back()->withErrors([
                'whatsapp' => 'Cadastre o telefone do cliente antes de abrir o WhatsApp.',
            ]);
        }

        return redirect()->away($url);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function normalizedData(array $data, bool $creating = false): array
    {
        if ($creating && $data['opened_at'] === null) {
            $data['opened_at'] = now()->toDateString();
        }

        if ($data['status'] === LegalCase::STATUS_FINALIZADO) {
            $data['closed_at'] ??= now()->toDateString();
        } else {
            $data['closed_at'] = null;
        }

        return $data;
    }

    private function caseForUser(Request $request, int $caseId): LegalCase
    {
        return $request->user()
            ->legalCases()
            ->findOrFail($caseId);
    }
}
