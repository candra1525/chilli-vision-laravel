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
        Schema::table('history_details', function (Blueprint $table) {
            $table->uuid('history_id')->nullable();
            $table->foreign('history_id')->references('id')->on('histories')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('history_details', function (Blueprint $table) {
            $table->dropForeign(['histroy_id']);
            $table->dropColumn('histroy_id');
        });
    }
};
