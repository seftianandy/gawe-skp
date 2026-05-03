<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hasil_kerja', function (Blueprint $table): void {
            if (Schema::hasColumn('hasil_kerja', 'judul')) {
                $table->dropColumn('judul');
            }
        });

        Schema::table('hasil_kerja', function (Blueprint $table): void {
            if (! Schema::hasColumn('hasil_kerja', 'indikator_kinerja_master_id')) {
                $table->foreignId('indikator_kinerja_master_id')
                    ->nullable()
                    ->constrained('indikator_kinerja_masters')
                    ->nullOnDelete();
            }
        });

        Schema::table('perilaku_kerja', function (Blueprint $table): void {
            if (Schema::hasColumn('perilaku_kerja', 'deskripsi')) {
                $table->text('deskripsi')->nullable()->change();
            }
        });
    }

    public function down(): void
    {
        Schema::table('perilaku_kerja', function (Blueprint $table): void {
            if (Schema::hasColumn('perilaku_kerja', 'deskripsi')) {
                $table->text('deskripsi')->nullable(false)->change();
            }
        });

        Schema::table('hasil_kerja', function (Blueprint $table): void {
            if (Schema::hasColumn('hasil_kerja', 'indikator_kinerja_master_id')) {
                $table->dropConstrainedForeignId('indikator_kinerja_master_id');
            }
        });

        Schema::table('hasil_kerja', function (Blueprint $table): void {
            if (! Schema::hasColumn('hasil_kerja', 'judul')) {
                $table->string('judul')->after('laporan_id');
            }
        });
    }
};
