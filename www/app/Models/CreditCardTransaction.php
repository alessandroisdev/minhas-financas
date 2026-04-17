<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CreditCardTransaction extends Model
{
    protected $fillable = [
        'credit_card_id',
        'description',
        'amount',
        'date',
        'installments',
        'current_installment',
        'installment_group_id',
        'category_id'
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function creditCard()
    {
        return $this->belongsTo(CreditCard::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
