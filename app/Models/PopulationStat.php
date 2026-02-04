<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
}
