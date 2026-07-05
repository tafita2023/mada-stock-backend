<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Produit extends Model
{
    protected $fillable = [
        'marque',
        'nom',
        'description',
        'contenance',
        'nicotine',
        'prix',
        'saveur',
        'stock',
        'image',
    ];
}
