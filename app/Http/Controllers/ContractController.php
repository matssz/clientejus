<?php

namespace App\Http\Controllers;

use App\Http\Requests\ContractRequest;
use App\Models\Contract;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ContractController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search'));
        $status = (string) $request->query('status');

        $contracts = Contract::query()
            ->whereHas('legalCase', fn ($query) => $query->where('user_id', $request->user()->id))
            ->with(['legalCase.client'])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query
                        ->where('title', 'like', "%{$search}%")
                        ->orWhereHas('legalCase', fn ($query) => $query->where('title', 'like', "%{$search}%"))
                        ->orWhereHas('legalCase.client', fn ($query) => $query->where('name', 'like', "%{$search}%"));
                });
            })
            ->when(array_key_exists($status, Contract::statuses()), fn ($query) => $query->where('status', $status))
            ->orderByRaw('expires_at is null, expires_at asc')
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        return view('contracts.index', [
            'contracts' => $contracts,
            'search' => $search,
            'selectedStatus' => $status,
            'statuses' => Contract::statuses(),
        ]);
    }

    public function create(Request $request): View|RedirectResponse
    {
        $cases = $this->casesForUser($request);

        if ($cases->isEmpty()) {
            return redirect()
                ->route('casos.create')
                ->with('warning', 'Cadastre um caso antes de criar um contrato.');
        }

        return view('contracts.create', [
            'cases' => $cases,
            'statuses' => Contract::statuses(),
            'selectedCaseId' => (int) $request->query('case_id'),
        ]);
    }

    public function store(ContractRequest $request): RedirectResponse
    {
        $data = Arr::except($request->validated(), 'original_document');
        $case = $request->user()->legalCases()->findOrFail($data['legal_case_id']);
        $contract = $case->contracts()->create($data);

        if ($request->hasFile('original_document')) {
            $this->storeOriginalDocument($contract, $request);
        }

        return redirect()
            ->route('contratos.show', $contract)
            ->with('status', 'Contrato cadastrado com sucesso.');
    }

    public function show(Request $request, int $contrato): View
    {
        return view('contracts.show', [
            'contract' => $this->contractForUser($request, $contrato)->load(['legalCase.client', 'legalCase.caseType']),
        ]);
    }

    public function edit(Request $request, int $contrato): View
    {
        return view('contracts.edit', [
            'contract' => $this->contractForUser($request, $contrato),
            'cases' => $this->casesForUser($request),
            'statuses' => Contract::statuses(),
        ]);
    }

    public function update(ContractRequest $request, int $contrato): RedirectResponse
    {
        $contract = $this->contractForUser($request, $contrato);
        $data = Arr::except($request->validated(), 'original_document');
        $oldPath = $contract->original_document_path;

        $contract->update($data);

        if ($request->hasFile('original_document')) {
            $this->storeOriginalDocument($contract, $request);

            if ($oldPath !== null) {
                Storage::disk('local')->delete($oldPath);
            }
        }

        return redirect()
            ->route('contratos.show', $contract)
            ->with('status', 'Contrato atualizado com sucesso.');
    }

    public function destroy(Request $request, int $contrato): RedirectResponse
    {
        $contract = $this->contractForUser($request, $contrato);

        if ($contract->original_document_path !== null) {
            Storage::disk('local')->delete($contract->original_document_path);
        }

        $contract->delete();

        return redirect()
            ->route('contratos.index')
            ->with('status', 'Contrato excluído com sucesso.');
    }

    public function download(Request $request, int $contrato): StreamedResponse
    {
        $contract = $this->contractForUser($request, $contrato);

        abort_if($contract->original_document_path === null, 404);
        abort_unless(Storage::disk('local')->exists($contract->original_document_path), 404);

        return Storage::disk('local')->download(
            $contract->original_document_path,
            $contract->original_document_name ?? 'contrato',
        );
    }

    private function storeOriginalDocument(Contract $contract, ContractRequest $request): void
    {
        $file = $request->file('original_document');
        $path = $file->store("contracts/{$contract->id}", 'local');

        if ($path === false) {
            throw ValidationException::withMessages([
                'original_document' => 'Não foi possível armazenar o documento.',
            ]);
        }

        $contract->update([
            'original_document_path' => $path,
            'original_document_name' => $file->getClientOriginalName(),
            'original_document_mime_type' => $file->getMimeType(),
            'original_document_size' => $file->getSize(),
        ]);
    }

    private function contractForUser(Request $request, int $contractId): Contract
    {
        return Contract::query()
            ->whereHas('legalCase', fn ($query) => $query->where('user_id', $request->user()->id))
            ->findOrFail($contractId);
    }

    private function casesForUser(Request $request): Collection
    {
        return $request->user()
            ->legalCases()
            ->with('client')
            ->latest()
            ->get();
    }
}
