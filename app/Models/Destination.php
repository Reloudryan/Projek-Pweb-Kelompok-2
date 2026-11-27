<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Model minimal supaya relasi modul Kuota Harian & Pemesanan bisa jalan.
 * Modul Paket/Destinasi dipegang Fathoni: kalau dia sudah punya Destination.php,
 * pakai milik dia saja, asalkan relasi dailyQuotas() dan bookings() tetap ada.
 */
class Destination extends Model
{
    protected $fillable = [
        'nama_destinasi',
        'deskripsi',
        'lokasi',
        'harga_tiket',
        'kuota_default',
        'gambar',
    ];

    public function dailyQuotas()
    {
        return $this->hasMany(DailyQuota::class);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }
}
