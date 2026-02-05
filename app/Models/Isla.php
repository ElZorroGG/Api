<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Isla extends Model
{
    use HasFactory;

    protected $table = 'islas';

    protected $fillable = [
        'nombre',
        'codigo',
    ];

    public function lugares(): HasMany
    {
        return $this->hasMany(Lugar::class);
    }

    public function populationStats(): HasMany
    {
        return $this->hasMany(PopulationStat::class);
    }
}
