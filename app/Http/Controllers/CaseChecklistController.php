<?php

namespace App\Http\Controllers;

use App\Http\Requests\ChecklistItemRequest;
use App\Models\ChecklistItem;
use App\Models\LegalCase;
use App\Services\CaseChecklistService;
use App\Services\WhatsAppLink;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CaseChecklistController extends Controller
{
    public function __construct(
        private readonly CaseChecklistService $checklistService,
        private readonly WhatsAppLink $whatsAppLink,
    ) {}

    public function generate(Request $request, int $caso): RedirectResponse
    {
        $case = $this->caseForUser($request, $caso);
        $createdItems = $this->checklistService->generateDefaults($case);

        $message = $createdItems > 0
            ? "{$createdItems} itens adicionados ao checklist."
            : 'O checklist padrão já estava atualizado.';

        return $this->backToCase($case)->with('status', $message);
    }

    public function store(ChecklistItemRequest $request, int $caso): RedirectResponse
    {
        $case = $this->caseForUser($request, $caso);

        $case->checklistItems()->create($request->validated());

        return $this->backToCase($case)->with('status', 'Pendência adicionada.');
    }

    public function update(Request $request, int $caso, int $item): RedirectResponse
    {
        $validated = $request->validate([
            'is_completed' => ['required', 'boolean'],
        ]);

        $case = $this->caseForUser($request, $caso);
        $checklistItem = $this->itemForCase($case, $item);
        $isCompleted = (bool) $validated['is_completed'];

        $checklistItem->update([
            'is_completed' => $isCompleted,
            'completed_at' => $isCompleted ? now() : null,
        ]);

        return $this->backToCase($case)->with('status', 'Checklist atualizado.');
    }

    public function destroy(Request $request, int $caso, int $item): RedirectResponse
    {
        $case = $this->caseForUser($request, $caso);
        $this->itemForCase($case, $item)->delete();

        return $this->backToCase($case)->with('status', 'Item removido do checklist.');
    }

    public function whatsapp(Request $request, int $caso): RedirectResponse
    {
        $case = $this->caseForUser($request, $caso)->load(['client', 'checklistItems']);
        $pendingItems = $case->checklistItems
            ->where('is_required', true)
            ->where('is_completed', false)
            ->pluck('name');

        if ($pendingItems->isEmpty()) {
            return $this->backToCase($case)->with('status', 'Não há documentos obrigatórios pendentes.');
        }

        $message = sprintf(
            "Olá, %s. Para continuarmos o caso \"%s\", ainda precisamos dos seguintes documentos:\n- %s\nPor favor, envie-os por este WhatsApp.",
            $case->client->name,
            $case->title,
            $pendingItems->implode("\n- "),
        );

        $url = $this->whatsAppLink->make($case->client->phone, $message);

        if ($url === null) {
            return back()->withErrors([
                'documents_whatsapp' => 'Cadastre o telefone do cliente antes de cobrar os documentos.',
            ]);
        }

        return redirect()->away($url);
    }

    private function caseForUser(Request $request, int $caseId): LegalCase
    {
        return $request->user()->legalCases()->findOrFail($caseId);
    }

    private function itemForCase(LegalCase $case, int $itemId): ChecklistItem
    {
        return $case->checklistItems()->findOrFail($itemId);
    }

    private function backToCase(LegalCase $case): RedirectResponse
    {
        return redirect()->to(route('casos.show', $case).'#documentos');
    }
}
