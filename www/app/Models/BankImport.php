<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BankImport extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'filename', 'status', 'total_items', 'processed_items'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function bankTransactions()
    {
        return $this->hasMany(BankTransaction::class);
    }
}
