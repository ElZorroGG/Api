<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Isla extends Model
{
    use HasFactory;

    protected $table = 'islas';

    protected $fillable = [
        'nombre',
        'codigo',
    ];
}
