<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Lugar extends Model
{
    use HasFactory;

    protected $table = 'lugares';

    protected $fillable = [
        'nombre',
        'codigo_lugar',
        'isla_id',
    ];

    public function isla(): BelongsTo
    {
        return $this->belongsTo(Isla::class);
    }

    public function populationStats(): HasMany
    {
        return $this->hasMany(PopulationStat::class);
    }
}
