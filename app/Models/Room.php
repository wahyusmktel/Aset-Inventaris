<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Room extends Model
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
        'building_id',
        'code',
        'name',
        'floor',
        'capacity',
        'type',
        'description',
    ];

    /**
     * Get the building that the room belongs to.
     */
    public function building(): BelongsTo
    {
        return $this->belongsTo(Building::class);
    }

    /**
     * Relasi ke Inventaris Barang
     */
    public function inventoryItems(): HasMany
    {
        return $this->hasMany(InventoryItem::class, 'room_id');
    }

    /**
     * Helper to get the next sequential 4-digit room code (e.g. "0001", "0002").
     */
    public static function generateNextCode(): string
    {
        $lastRoom = self::orderByRaw('CAST(code AS UNSIGNED) DESC')->first();

        if (!$lastRoom || !is_numeric($lastRoom->code)) {
            return '0001';
        }

        $nextNumber = ((int) $lastRoom->code) + 1;
        return str_pad((string) $nextNumber, 4, '0', STR_PAD_LEFT);
    }
}
