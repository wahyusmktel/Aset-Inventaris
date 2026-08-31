<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IntegrityPact extends Model
{
    use HasFactory, HasUuids;

    /**
     * The "type" of the primary key ID.
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'school_id',
        'document_number',
        'is_agreed',
        'signed_at',
        'signer_ip',
        'digital_signature_hash',
        'pdf_path',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_agreed' => 'boolean',
        'signed_at' => 'datetime',
    ];

    /**
     * Relasi ke User / Anggota
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relasi ke Sekolah
     */
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    /**
     * Helper to generate document number e.g. 001/PI-INV/SMKTELKOM/2026
     */
    public static function generateDocumentNumber(): string
    {
        $count = self::count() + 1;
        $numStr = str_pad((string) $count, 3, '0', STR_PAD_LEFT);
        $year = date('Y');
        return "{$numStr}/PI-INV/SMKTELKOM/{$year}";
    }
}
