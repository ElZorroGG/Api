<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PopulationStat extends Model
{
    use HasFactory;

    protected $table = 'population_stats';

    protected $fillable = [
        'lugar_id',
        'isla_id',
        'ano',
        'genero',
        'edad',
        'poblacion',
    ];

    protected $casts = [
        'ano' => 'integer',
        'poblacion' => 'integer',
    ];

    public function lugar(): BelongsTo
    {
        return $this->belongsTo(Lugar::class);
    }

    public function isla(): BelongsTo
    {
        return $this->belongsTo(Isla::class);
    }
}
