<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryPeriod extends Model
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
        'name',
        'start_date',
        'cutoff_date',
        'is_active',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'start_date' => 'datetime',
        'cutoff_date' => 'datetime',
        'is_active' => 'boolean',
    ];

    /**
     * Helper to get currently active inventory period.
     */
    public static function getActivePeriod(): ?self
    {
        return self::where('is_active', true)->latest()->first();
    }

    /**
     * Check if the current period has passed its cutoff date.
     */
    public function isCutoffPassed(): bool
    {
        return Carbon::now()->greaterThanOrEqualTo($this->cutoff_date);
    }
}
