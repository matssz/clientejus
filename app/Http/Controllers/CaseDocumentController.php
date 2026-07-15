<?php

namespace App\Http\Controllers;

use App\Http\Requests\CaseDocumentRequest;
use App\Models\CaseDocument;
use App\Models\LegalCase;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CaseDocumentController extends Controller
{
    public function store(CaseDocumentRequest $request, int $caso): RedirectResponse
    {
        $case = $this->caseForUser($request, $caso);
        $file = $request->file('document');
        $path = $file->store("case-documents/{$case->id}", 'local');

        if ($path === false) {
            return back()->withErrors([
                'document' => 'Não foi possível armazenar o documento.',
            ]);
        }

        $document = $case->documents()->create([
            'checklist_item_id' => $request->validated('checklist_item_id'),
            'original_name' => $file->getClientOriginalName(),
            'file_path' => $path,
            'mime_type' => $file->getMimeType(),
            'file_size' => $file->getSize(),
            'uploaded_at' => now(),
        ]);

        if ($document->checklistItem !== null) {
            $document->checklistItem->update([
                'is_completed' => true,
                'completed_at' => now(),
            ]);
        }

        return $this->backToCase($case)->with('status', 'Documento enviado com sucesso.');
    }

    public function download(Request $request, int $caso, int $documento): StreamedResponse
    {
        $case = $this->caseForUser($request, $caso);
        $document = $this->documentForCase($case, $documento);

        abort_unless(Storage::disk('local')->exists($document->file_path), 404);

        return Storage::disk('local')->download($document->file_path, $document->original_name);
    }

    public function destroy(Request $request, int $caso, int $documento): RedirectResponse
    {
        $case = $this->caseForUser($request, $caso);
        $document = $this->documentForCase($case, $documento);
        $checklistItem = $document->checklistItem;

        Storage::disk('local')->delete($document->file_path);
        $document->delete();

        if ($checklistItem !== null && ! $checklistItem->documents()->exists()) {
            $checklistItem->update([
                'is_completed' => false,
                'completed_at' => null,
            ]);
        }

        return $this->backToCase($case)->with('status', 'Documento excluído.');
    }

    private function caseForUser(Request $request, int $caseId): LegalCase
    {
        return $request->user()->legalCases()->findOrFail($caseId);
    }

    private function documentForCase(LegalCase $case, int $documentId): CaseDocument
    {
        return $case->documents()->findOrFail($documentId);
    }

    private function backToCase(LegalCase $case): RedirectResponse
    {
        return redirect()->to(route('casos.show', $case).'#documentos');
    }
}
