<?php

namespace App\Models\Diy;

use Illuminate\Database\Eloquent\Model;

class Bases extends Model
{
    protected $fillable = [
        'nom',
        'ratio',
        'quantite',
        'prix',
        'stock',
        'description',
        'image'
    ];
}
