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
        Schema::create('lampiran_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hasil_kerja_id')->nullable()->constrained('hasil_kerja')->cascadeOnDelete();
            $table->foreignId('perilaku_kerja_id')->nullable()->constrained('perilaku_kerja')->cascadeOnDelete();
            $table->string('nama_file');
            $table->string('file_path');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lampiran_files');
    }
};
