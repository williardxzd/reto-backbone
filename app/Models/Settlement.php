<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Settlement extends Model
{
    use HasFactory;

    /**
     * The relations to eager load on every query.
     *
     * @var array
     */
    protected $with = ['settlementType'];

    /**
     * The attributes that should be visible in serialization.
     *
     * @var array<string>
     */
    protected $visible = [
        'key',
        'name',
        'zone_type'
    ];

    /**
     * Get the settlement type that owns the zip code.
     */
    public function settlementType()
    {
        return $this->belongsTo(SettlementType::class);
    }
}
