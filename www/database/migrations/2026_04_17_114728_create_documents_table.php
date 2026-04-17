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
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            
            // Metadados básicos essenciais
            $table->string('title');
            $table->string('file_path');
            $table->string('file_type', 50)->nullable();
            $table->unsignedBigInteger('file_size')->default(0); 
            
            // Tipologia Documental (Arquivologia)
            $table->enum('typology', ['invoice', 'receipt', 'statement', 'contract', 'declaration', 'other'])->default('other');
            
            // Contexto Temporal / Proveniência Financeira
            $table->date('reference_date')->nullable();
            $table->foreignId('transaction_id')->nullable()->constrained('transactions')->nullOnDelete();
            
            // Flexibilidade de Tags (E.g. #Carro #Nubank) e Segredo (Cofre)
            $table->json('tags')->nullable();
            $table->boolean('is_secured')->default(false);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
