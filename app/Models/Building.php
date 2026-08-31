<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Building extends Model
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
        'total_floors',
        'description',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'total_floors' => 'integer',
    ];

    /**
     * Relasi ke Ruangan
     */
    public function rooms(): HasMany
    {
        return $this->hasMany(Room::class, 'building_id');
    }

    /**
     * Relasi ke Inventaris Barang
     */
    public function inventoryItems(): HasMany
    {
        return $this->hasMany(InventoryItem::class, 'building_id');
    }

    /**
     * Helper to get the next sequential building code (e.g. "001", "002").
     */
    public static function generateNextCode(): string
    {
        $lastBuilding = self::orderByRaw('CAST(code AS UNSIGNED) DESC')->first();

        if (!$lastBuilding || !is_numeric($lastBuilding->code)) {
            return '001';
        }

        $nextNumber = ((int) $lastBuilding->code) + 1;
        return str_pad((string) $nextNumber, 3, '0', STR_PAD_LEFT);
    }
}
