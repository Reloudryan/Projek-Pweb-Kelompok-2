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
    Schema::create('bookings', function (Blueprint $table) {
        $table->id();
        $table->string('kode_booking')->unique();
        $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
        $table->foreignId('destination_id')->constrained('destinations')->onDelete('cascade');
        $table->date('tanggal_kunjungan');
        $table->integer('jumlah_tiket');
        $table->decimal('total_harga', 12, 2);
        $table->string('bukti_bayar')->nullable();
        $table->enum('status', ['menunggu_pembayaran', 'lunas', 'selesai', 'batal'])
              ->default('menunggu_pembayaran');
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
