<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bank_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bank_import_id')->constrained()->onDelete('cascade');
            $table->foreignId('transaction_id')->nullable()->constrained('transactions')->nullOnDelete();
            
            $table->string('fitid')->unique()->nullable(); // ID da transação no OFX
            $table->string('description');
            $table->decimal('amount', 15, 2);
            $table->date('date');
            
            $table->enum('status', ['pending', 'matched', 'manual'])->default('pending');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bank_transactions');
    }
};
