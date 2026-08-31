<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DataFinalization extends Model
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
        'inventory_period_id',
        'document_number',
        'total_items_recorded',
        'total_units_recorded',
        'total_good_condition',
        'total_damaged_condition',
        'statement_notes',
        'signed_at',
        'is_finalized',
        'pdf_path',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'signed_at' => 'datetime',
        'is_finalized' => 'boolean',
        'total_items_recorded' => 'integer',
        'total_units_recorded' => 'integer',
        'total_good_condition' => 'integer',
        'total_damaged_condition' => 'integer',
    ];

    /**
     * Relasi ke User / Anggota
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relasi ke Periode Inventaris
     */
    public function period(): BelongsTo
    {
        return $this->belongsTo(InventoryPeriod::class, 'inventory_period_id');
    }

    /**
     * Helper to generate document number e.g. BA-FIN/INV/2026/001
     */
    public static function generateDocumentNumber(): string
    {
        $count = self::count() + 1;
        $numStr = str_pad((string) $count, 3, '0', STR_PAD_LEFT);
        $year = date('Y');
        return "BA-FIN/INV/{$year}/{$numStr}";
    }
}
