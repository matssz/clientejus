<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LegalCase extends Model
{
    use HasFactory;

    public const STATUS_NOVO_ATENDIMENTO = 'novo_atendimento';
    public const STATUS_DOCUMENTOS_PENDENTES = 'documentos_pendentes';
    public const STATUS_EM_ANALISE = 'em_analise';
    public const STATUS_PRONTO_PARA_PROTOCOLO = 'pronto_para_protocolo';
    public const STATUS_PROTOCOLADO = 'protocolado';
    public const STATUS_AGUARDANDO_RETORNO = 'aguardando_retorno';
    public const STATUS_FINALIZADO = 'finalizado';

    protected $fillable = [
        'user_id',
        'client_id',
        'case_type_id',
        'title',
        'description',
        'status',
        'opened_at',
        'closed_at',
    ];

    protected function casts(): array
    {
        return [
            'opened_at' => 'date',
            'closed_at' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function caseType(): BelongsTo
    {
        return $this->belongsTo(CaseType::class);
    }

    public function checklistItems(): HasMany
    {
        return $this->hasMany(ChecklistItem::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(CaseDocument::class);
    }
}
