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
        Schema::create('history_details', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name_disease'); // Nama Penyakit
            $table->string('another_name_disease'); // Nama Lain Penyakit
            $table->text('symptom'); // Gejala
            $table->text('reason'); // Penyebab
            $table->text('preventive_measure'); // Tindakan Pencegahan
            $table->string('source'); // Sumber
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('history_details');
    }
};
