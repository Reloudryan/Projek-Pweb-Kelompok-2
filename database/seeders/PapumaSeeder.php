<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\DailyQuota;
use App\Models\Destination;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Data contoh studi kasus Pantai Papuma (laporan bab 3.5).
 * Harga & kuota hanya data dummy, bukan tarif resmi pengelola.
 */
class PapumaSeeder extends Seeder
{
    public function run(): void
    {
        $lokasi = 'Desa Lojejer, Kec. Wuluhan, Kab. Jember';

        $reguler = Destination::updateOrCreate(
            ['nama_destinasi' => 'Tiket Masuk Reguler Pantai Papuma'],
            [
                'deskripsi' => 'Tiket masuk perorangan untuk menikmati pasir putih dan formasi batu karang Pantai Papuma.',
                'lokasi' => $lokasi,
                'harga_tiket' => 20000,
                'kuota_default' => 500,
            ]
        );

        Destination::updateOrCreate(
            ['nama_destinasi' => 'Paket Keluarga Papuma (4 orang)'],
            [
                'deskripsi' => 'Paket hemat untuk satu keluarga (4 orang).',
                'lokasi' => $lokasi,
                'harga_tiket' => 70000,
                'kuota_default' => 200,
            ]
        );

        Destination::updateOrCreate(
            ['nama_destinasi' => 'Paket Pelajar / Rombongan'],
            [
                'deskripsi' => 'Tiket khusus pelajar dan rombongan wisata sekolah.',
                'lokasi' => $lokasi,
                'harga_tiket' => 15000,
                'kuota_default' => 300,
            ]
        );

        // Kuota harian destinasi 1 (tabel 3.3). Kuota terpakai 5 Des diisi dari booking contoh di bawah.
        foreach ([
            ['2026-12-05', 500, 4],
            ['2026-12-06', 500, 0],
            ['2026-12-25', 800, 0],
        ] as [$tanggal, $maksimal, $terpakai]) {
            $sudahAda = DailyQuota::where('destination_id', $reguler->id)
                ->whereDate('tanggal', $tanggal)
                ->exists();

            if (! $sudahAda) {
                DailyQuota::create([
                    'destination_id' => $reguler->id,
                    'tanggal' => $tanggal,
                    'kuota_maksimal' => $maksimal,
                    'kuota_terpakai' => $terpakai,
                ]);
            }
        }

        // Pesanan contoh (tabel 3.4)
        $pengunjung = User::where('email', 'user@simata.test')->first();

        if ($pengunjung) {
            Booking::firstOrCreate(
                ['kode_booking' => 'TKT-20261205-001'],
                [
                    'user_id' => $pengunjung->id,
                    'destination_id' => $reguler->id,
                    'tanggal_kunjungan' => '2026-12-05',
                    'jumlah_tiket' => 4,
                    'total_harga' => 80000,
                    'status' => Booking::STATUS_MENUNGGU,
                ]
            );
        }
    }
}
