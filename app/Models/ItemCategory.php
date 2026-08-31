<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ItemCategory extends Model
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
        return $this->hasMany(InventoryItem::class, 'category_id');
    }

    /**
     * Helper to get the next sequential category code (e.g. "001", "002").
     */
    public static function generateNextCode(): string
    {
        $lastCategory = self::orderByRaw('CAST(code AS UNSIGNED) DESC')->first();

        if (!$lastCategory || !is_numeric($lastCategory->code)) {
            return '001';
        }

        $nextNumber = ((int) $lastCategory->code) + 1;
        return str_pad((string) $nextNumber, 3, '0', STR_PAD_LEFT);
    }
}
