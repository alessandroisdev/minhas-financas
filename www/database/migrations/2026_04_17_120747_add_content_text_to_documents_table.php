<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->longText('content_text')->nullable();
        });

        // Configurar FULLTEXT Index usando instrução SQL raw (evita possíveis restrições nativas do builder em algumas versões do Blueprint)
        DB::statement('ALTER TABLE documents ADD FULLTEXT content_index (title, content_text)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropIndex('content_index');
            $table->dropColumn('content_text');
        });
    }
};
