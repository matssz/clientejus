<?php

namespace App\Http\Controllers;

use App\Models\ChecklistItem;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user();

        return view('dashboard', [
            'clientCount' => $user->clients()->count(),
            'recentClients' => $user->clients()->latest()->limit(5)->get(),
            'caseCount' => $user->legalCases()->count(),
            'recentCases' => $user->legalCases()->with('client')->latest()->limit(5)->get(),
            'pendingDocumentCount' => ChecklistItem::query()
                ->whereHas('legalCase', fn ($query) => $query->where('user_id', $user->id))
                ->where('is_required', true)
                ->where('is_completed', false)
                ->count(),
        ]);
    }
}
