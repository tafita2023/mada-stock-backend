<?php

namespace App\Models\Diy;

use Illuminate\Database\Eloquent\Model;

class Divers extends Model
{
    protected $fillable = [
        'nom',
        'marque',
        'prix',
        'stock',
        'description',
        'image'
    ];

}
