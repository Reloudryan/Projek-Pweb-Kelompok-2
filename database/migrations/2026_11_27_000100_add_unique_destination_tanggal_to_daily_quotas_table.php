<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Menjamin satu paket wisata hanya punya satu baris kuota per tanggal
// (sesuai ERD / struktur tabel di laporan: indeks unik destination_id + tanggal).
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('daily_quotas', function (Blueprint $table) {
            $table->unique(['destination_id', 'tanggal'], 'daily_quotas_destination_tanggal_unique');
        });
    }

    public function down(): void
    {
        Schema::table('daily_quotas', function (Blueprint $table) {
            $table->dropUnique('daily_quotas_destination_tanggal_unique');
        });
    }
};
