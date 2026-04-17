<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CreditCard extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'limit',
        'closing_day',
        'due_day',
        'brand',
        'color'
    ];

    public function transactions()
    {
        return $this->hasMany(CreditCardTransaction::class);
    }
}
