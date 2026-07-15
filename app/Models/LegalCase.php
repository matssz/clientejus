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

    /**
     * @return array<string, string>
     */
    public static function statuses(): array
    {
        return [
            self::STATUS_NOVO_ATENDIMENTO => 'Novo atendimento',
            self::STATUS_DOCUMENTOS_PENDENTES => 'Documentos pendentes',
            self::STATUS_EM_ANALISE => 'Em análise',
            self::STATUS_PRONTO_PARA_PROTOCOLO => 'Pronto para protocolo',
            self::STATUS_PROTOCOLADO => 'Protocolado',
            self::STATUS_AGUARDANDO_RETORNO => 'Aguardando retorno',
            self::STATUS_FINALIZADO => 'Finalizado',
        ];
    }

    public function statusLabel(): string
    {
        return self::statuses()[$this->status] ?? $this->status;
    }

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

    public function contracts(): HasMany
    {
        return $this->hasMany(Contract::class);
    }
}
