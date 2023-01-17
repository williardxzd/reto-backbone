<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ZipCode extends Model
{
    use HasFactory;

    /**
     * Get the federal entity that owns the zip code.
     */
    public function federalEntity()
    {
        return $this->belongsTo(FederalEntity::class);
    }

    /**
     * Get the municipality that owns the zip code.
     */
    public function municipality()
    {
        return $this->belongsTo(Municipality::class);
    }

    /**
     * The settlements that belong to the zip code.
     */
    public function settlements()
    {
        return $this->belongsToMany(Settlement::class);
    }
}
