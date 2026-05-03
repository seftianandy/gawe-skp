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
        Schema::create('laporan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('periode');
            $table->unsignedTinyInteger('bulan');
            $table->unsignedSmallInteger('tahun');
            $table->enum('status', ['draft', 'submit', 'final'])->default('draft');
            $table->string('file_pdf')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['user_id', 'bulan', 'tahun']);
            $table->index(['user_id', 'periode']);
            $table->index(['status', 'tahun', 'bulan']);
        });

        Schema::create('hasil_kerja', function (Blueprint $table) {
            $table->id();
            $table->foreignId('laporan_id')->constrained('laporan')->cascadeOnDelete();
            $table->string('judul');
            $table->timestamps();
        });

        Schema::create('indikator_kinerja', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hasil_kerja_id')->constrained('hasil_kerja')->cascadeOnDelete();
            $table->text('deskripsi');
            $table->string('satuan');
            $table->string('target');
            $table->enum('kategori', ['kualitas', 'kuantitas']);
            $table->timestamps();

            $table->index('kategori');
        });

        Schema::create('rencana_aksi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('indikator_id')->constrained('indikator_kinerja')->cascadeOnDelete();
            $table->text('deskripsi');
            $table->timestamps();
        });

        Schema::create('realisasi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('indikator_id')->constrained('indikator_kinerja')->cascadeOnDelete();
            $table->date('tanggal');
            $table->string('output');
            $table->text('keterangan');
            $table->timestamps();

            $table->index(['indikator_id', 'tanggal']);
        });

        Schema::create('bukti_foto', function (Blueprint $table) {
            $table->id();
            $table->foreignId('realisasi_id')->constrained('realisasi')->cascadeOnDelete();
            $table->string('file_path');
            $table->timestamps();
        });

        Schema::create('perilaku_kerja', function (Blueprint $table) {
            $table->id();
            $table->foreignId('laporan_id')->constrained('laporan')->cascadeOnDelete();
            $table->string('nama');
            $table->text('deskripsi');
            $table->timestamps();

            $table->index('nama');
        });

        Schema::create('bukti_perilaku', function (Blueprint $table) {
            $table->id();
            $table->foreignId('perilaku_id')->constrained('perilaku_kerja')->cascadeOnDelete();
            $table->string('file_path');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bukti_perilaku');
        Schema::dropIfExists('perilaku_kerja');
        Schema::dropIfExists('bukti_foto');
        Schema::dropIfExists('realisasi');
        Schema::dropIfExists('rencana_aksi');
        Schema::dropIfExists('indikator_kinerja');
        Schema::dropIfExists('hasil_kerja');
        Schema::dropIfExists('laporan');
    }
};
