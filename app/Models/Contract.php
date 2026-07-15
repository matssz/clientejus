<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Contract extends Model
{
    use HasFactory;

    public const STATUS_ACTIVE = 'active';

    public const STATUS_EXPIRED = 'expired';

    public const STATUS_TERMINATED = 'terminated';

    protected $fillable = [
        'legal_case_id',
        'title',
        'signed_at',
        'expires_at',
        'status',
        'original_document_path',
        'original_document_name',
        'original_document_mime_type',
        'original_document_size',
    ];

    protected function casts(): array
    {
        return [
            'signed_at' => 'date',
            'expires_at' => 'date',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function statuses(): array
    {
        return [
            self::STATUS_ACTIVE => 'Vigente',
            self::STATUS_EXPIRED => 'Expirado',
            self::STATUS_TERMINATED => 'Encerrado',
        ];
    }

    public function statusLabel(): string
    {
        return self::statuses()[$this->status] ?? $this->status;
    }

    public function isExpiringSoon(): bool
    {
        return $this->status === self::STATUS_ACTIVE
            && $this->expires_at !== null
            && $this->expires_at->betweenIncluded(today(), today()->addDays(30));
    }

    public function isPastDue(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isBefore(today());
    }

    public function legalCase(): BelongsTo
    {
        return $this->belongsTo(LegalCase::class);
    }
}
