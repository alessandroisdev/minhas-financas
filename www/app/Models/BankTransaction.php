<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BankTransaction extends Model
{
    use HasFactory;

    protected $fillable = ['bank_import_id', 'transaction_id', 'fitid', 'description', 'amount', 'date', 'status'];

    public function bankImport()
    {
        return $this->belongsTo(BankImport::class);
    }

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }
}
