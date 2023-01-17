<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Settlement extends Model
{
    use HasFactory;

    /**
     * Get the settlement type that owns the zip code.
     */
    public function settlementType()
    {
        return $this->belongsTo(SettlementType::class);
    }
}
