<?php

namespace App\Http\Controllers;

use App\Models\LegalCase;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user();

        return view('dashboard', [
            'clientCount' => $user->clients()->count(),
            'caseCount' => $user->legalCases()->count(),
            'pendingDocumentCount' => $user->legalCases()
                ->where('status', LegalCase::STATUS_DOCUMENTOS_PENDENTES)
                ->count(),
        ]);
    }
}
