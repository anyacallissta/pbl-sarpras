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
        Schema::table('m_item', function (Blueprint $table) {
            $table->unsignedBigInteger('ruang_id')->after('item_id')->nullable(); 
            $table->foreign('ruang_id')
                  ->references('ruang_id')
                  ->on('m_ruang')
                  ->onDelete('cascade'); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('m_item', function (Blueprint $table) {
            $table->dropForeign(['ruang_id']);
            $table->dropColumn('ruang_id');
        });
    }
};
