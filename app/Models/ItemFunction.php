<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ItemFunction extends Model
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
        'code',
        'name',
        'description',
    ];

    /**
     * Relasi ke Inventaris Barang
     */
    public function inventoryItems(): HasMany
    {
        return $this->hasMany(InventoryItem::class, 'function_id');
    }

    /**
     * Helper to get the next sequential 2-digit function code (e.g. "01", "02").
     */
    public static function generateNextCode(): string
    {
        $lastFunction = self::orderByRaw('CAST(code AS UNSIGNED) DESC')->first();

        if (!$lastFunction || !is_numeric($lastFunction->code)) {
            return '01';
        }

        $nextNumber = ((int) $lastFunction->code) + 1;
        return str_pad((string) $nextNumber, 2, '0', STR_PAD_LEFT);
    }
}
