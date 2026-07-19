<?php

namespace App\Models\Diy;

use Illuminate\Database\Eloquent\Model;

class Aromes extends Model
{
    protected $fillable = [
        'nom',
        'marque',
        'quantite',
        'categorie',
        'prix',
        'stock',
        'description',
        'image'
    ];

}
