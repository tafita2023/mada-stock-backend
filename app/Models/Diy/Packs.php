<?php

namespace App\Models\Diy;

use Illuminate\Database\Eloquent\Model;

class Packs extends Model
{
    protected $fillable = [
        'nom',
        'marque',
        'quantite',
        'nicotine',
        'prix',
        'stock',
        'description',
        'image'
    ];
}
