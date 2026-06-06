<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Materiel extends Model
{
    protected $fillable = [
    'nom',
    'type',
    'marque',
    'prix',
    'stock',
    'image'
    ];
}
