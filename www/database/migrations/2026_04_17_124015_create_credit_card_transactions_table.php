<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('credit_card_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('credit_card_id')->constrained()->onDelete('cascade');
            $table->string('description');
            $table->decimal('amount', 12, 2);
            $table->date('date');
            
            // Tratativa de Parcelamento Fixa Relacional (Time Shift Mode)
            $table->integer('installments')->default(1)->comment('Quantidade total de parcelas');
            $table->integer('current_installment')->default(1)->comment('O índice desta parcela atual');
            $table->string('installment_group_id')->nullable()->comment('UUID para agrupar o bloco de parcelas em caso de exclusões massivas');
            
            // Relacionamentos Secundários Opcionais
            $table->foreignId('category_id')->nullable()->constrained('categories')->onDelete('set null');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('credit_card_transactions');
    }
};
