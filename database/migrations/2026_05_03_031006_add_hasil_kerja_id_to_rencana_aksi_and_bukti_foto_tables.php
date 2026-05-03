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
        Schema::table('rencana_aksi', function (Blueprint $table) {
            if (! Schema::hasColumn('rencana_aksi', 'hasil_kerja_id')) {
                $table->foreignId('hasil_kerja_id')
                    ->nullable()
                    ->after('id')
                    ->constrained('hasil_kerja')
                    ->cascadeOnDelete();
            }
        });

        Schema::table('bukti_foto', function (Blueprint $table) {
            if (! Schema::hasColumn('bukti_foto', 'hasil_kerja_id')) {
                $table->foreignId('hasil_kerja_id')
                    ->nullable()
                    ->after('id')
                    ->constrained('hasil_kerja')
                    ->cascadeOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rencana_aksi', function (Blueprint $table) {
            if (Schema::hasColumn('rencana_aksi', 'hasil_kerja_id')) {
                $table->dropConstrainedForeignId('hasil_kerja_id');
            }
        });

        Schema::table('bukti_foto', function (Blueprint $table) {
            if (Schema::hasColumn('bukti_foto', 'hasil_kerja_id')) {
                $table->dropConstrainedForeignId('hasil_kerja_id');
            }
        });
    }
};
