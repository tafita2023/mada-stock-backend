<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payements extends Model
{
    protected $table = 'payements';

    protected $fillable = [
        'reference',
        'amount',
        'client_name',
        'description',
        'provider',
        'payer_email',
        'payer_phone',
        'payment_link',
        'status',
    ];
}