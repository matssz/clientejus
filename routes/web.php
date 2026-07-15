<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\CaseChecklistController;
use App\Http\Controllers\CaseDocumentController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\ContractController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LegalCaseController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
});

Route::middleware('guest')->group(function () {
    Route::get('/cadastro', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('/cadastro', [RegisteredUserController::class, 'store']);

    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store']);
});

Route::middleware('auth')->group(function () {
    Route::get('/painel', DashboardController::class)->name('dashboard');
    Route::resource('clientes', ClientController::class);
    Route::get('/casos/{caso}/whatsapp', [LegalCaseController::class, 'whatsapp'])->name('casos.whatsapp');
    Route::post('/casos/{caso}/checklist/padrao', [CaseChecklistController::class, 'generate'])->name('casos.checklist.generate');
    Route::post('/casos/{caso}/checklist', [CaseChecklistController::class, 'store'])->name('casos.checklist.store');
    Route::patch('/casos/{caso}/checklist/{item}', [CaseChecklistController::class, 'update'])->name('casos.checklist.update');
    Route::delete('/casos/{caso}/checklist/{item}', [CaseChecklistController::class, 'destroy'])->name('casos.checklist.destroy');
    Route::get('/casos/{caso}/documentos/whatsapp', [CaseChecklistController::class, 'whatsapp'])->name('casos.documents.whatsapp');
    Route::post('/casos/{caso}/documentos', [CaseDocumentController::class, 'store'])->name('casos.documents.store');
    Route::get('/casos/{caso}/documentos/{documento}/download', [CaseDocumentController::class, 'download'])->name('casos.documents.download');
    Route::delete('/casos/{caso}/documentos/{documento}', [CaseDocumentController::class, 'destroy'])->name('casos.documents.destroy');
    Route::resource('casos', LegalCaseController::class);
    Route::get('/contratos/{contrato}/documento', [ContractController::class, 'download'])->name('contratos.download');
    Route::resource('contratos', ContractController::class);
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
});
