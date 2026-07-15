@extends('layouts.app')

@section('title', 'Painel | ClienteJus')

@section('content')
    <div class="mb-4">
        <div>
            <p class="text-uppercase text-primary fw-semibold small mb-2">Visão geral</p>
            <h1 class="h3 mb-1">Olá, {{ auth()->user()->name }}</h1>
            <p class="text-secondary mb-0">Acompanhe os dados principais do seu escritório.</p>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-12 col-md-4">
            <div class="card stat-card stat-card-primary h-100">
                <div class="card-body">
                    <p class="text-secondary mb-2">Clientes cadastrados</p>
                    <p class="display-6 fw-semibold mb-0">{{ $clientCount }}</p>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="card stat-card stat-card-success h-100">
                <div class="card-body">
                    <p class="text-secondary mb-2">Casos cadastrados</p>
                    <p class="display-6 fw-semibold mb-0">{{ $caseCount }}</p>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="card stat-card stat-card-warning h-100">
                <div class="card-body">
                    <p class="text-secondary mb-2">Documentos pendentes</p>
                    <p class="display-6 fw-semibold mb-0">{{ $pendingDocumentCount }}</p>
                </div>
            </div>
        </div>
    </div>

@endsection
