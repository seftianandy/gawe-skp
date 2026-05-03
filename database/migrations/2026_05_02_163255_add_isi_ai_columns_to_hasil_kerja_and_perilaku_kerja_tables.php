<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hasil_kerja', function (Blueprint $table) {
            if (! Schema::hasColumn('hasil_kerja', 'isi_ai')) {
                $table->longText('isi_ai')->nullable()->after('judul');
            }
        });

        Schema::table('perilaku_kerja', function (Blueprint $table) {
            if (! Schema::hasColumn('perilaku_kerja', 'isi_ai')) {
                $table->longText('isi_ai')->nullable()->after('deskripsi');
            }
        });
    }

    public function down(): void
    {
        Schema::table('hasil_kerja', function (Blueprint $table) {
            if (Schema::hasColumn('hasil_kerja', 'isi_ai')) {
                $table->dropColumn('isi_ai');
            }
        });

        Schema::table('perilaku_kerja', function (Blueprint $table) {
            if (Schema::hasColumn('perilaku_kerja', 'isi_ai')) {
                $table->dropColumn('isi_ai');
            }
        });
    }
};
