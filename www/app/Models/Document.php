<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    protected $fillable = [
        'user_id', 'title', 'file_path', 'file_type', 'file_size',
        'typology', 'reference_date', 'transaction_id', 'tags', 'is_secured'
    ];

    protected $casts = [
        'reference_date' => 'date',
        'tags' => 'array',
        'is_secured' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }
}
