@props(['status', 'label'])

@php
    $classes = [
        'novo_atendimento' => 'text-bg-secondary',
        'documentos_pendentes' => 'text-bg-warning',
        'em_analise' => 'text-bg-info',
        'pronto_para_protocolo' => 'text-bg-primary',
        'protocolado' => 'text-bg-success',
        'aguardando_retorno' => 'text-bg-dark',
        'finalizado' => 'text-bg-light border text-secondary',
    ];
@endphp

<span {{ $attributes->class(['badge', $classes[$status] ?? 'text-bg-secondary']) }}>{{ $label }}</span>
