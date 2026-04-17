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
        Schema::table('documents', function (Blueprint $table) {
            $table->dropColumn('typology');
            
            $table->foreignId('folder_id')->nullable()->constrained('document_folders')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->enum('typology', ['invoice', 'receipt', 'statement', 'contract', 'declaration', 'other'])->default('other');
            $table->dropForeign(['folder_id']);
            $table->dropColumn('folder_id');
        });
    }
};
