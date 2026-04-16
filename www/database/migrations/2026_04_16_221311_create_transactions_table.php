<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            
            $table->string('description');
            $table->decimal('amount', 15, 2);
            $table->enum('type', ['income', 'expense']);
            $table->date('date');
            $table->enum('status', ['pending', 'paid', 'reconciled'])->default('pending');
            
            // Recorrência
            $table->string('recurrence_type')->nullable(); // daily, weekly, monthly, yearly
            $table->integer('recurrence_interval')->nullable(); // ex: a cada 2 (meses)
            $table->date('recurrence_end_date')->nullable();
            $table->foreignId('parent_id')->nullable()->constrained('transactions')->onDelete('cascade');
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
