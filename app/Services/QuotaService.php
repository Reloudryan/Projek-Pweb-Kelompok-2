<?php

namespace App\Services;

use App\Models\DailyQuota;
use App\Models\Destination;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

/**
 * Algoritma validasi kuota tiket harian (laporan bab 3.4).
 * Semua method ini dipanggil DI DALAM DB::transaction() oleh BookingController.
 */
class QuotaService
{
    /**
     * Ambil baris kuota paket + tanggal dan kunci barisnya (lockForUpdate)
     * supaya dua pemesanan bersamaan tidak sama-sama lolos.
     * Kalau admin belum mengatur kuota tanggal itu, dipakai kuota_default paket.
     */
    public function lockForDate(Destination $destination, $tanggal): DailyQuota
    {
        $tanggal = Carbon::parse($tanggal)->toDateString();

        $quota = DailyQuota::where('destination_id', $destination->id)
            ->whereDate('tanggal', $tanggal)
            ->lockForUpdate()
            ->first();

        if (! $quota) {
            $quota = DailyQuota::create([
                'destination_id' => $destination->id,
                'tanggal' => $tanggal,
                'kuota_maksimal' => $destination->kuota_default,
                'kuota_terpakai' => 0,
            ]);
        }

        return $quota;
    }

    /** Langkah 4-7: cek sisa kuota, kalau cukup tambah kuota_terpakai. */
    public function reserve(DailyQuota $quota, int $jumlah): void
    {
        if ($jumlah > $quota->sisa_kuota) {
            throw ValidationException::withMessages([
                'jumlah_tiket' => sprintf(
                    'Kuota tidak mencukupi. Sisa kuota tanggal %s tinggal %d tiket.',
                    $quota->tanggal->format('d/m/Y'),
                    $quota->sisa_kuota
                ),
            ]);
        }

        $quota->increment('kuota_terpakai', $jumlah);
    }

    /** Langkah 8-9: kembalikan kuota (pesanan dibatalkan / diubah). */
    public function release(DailyQuota $quota, int $jumlah): void
    {
        $quota->decrement('kuota_terpakai', min($jumlah, $quota->kuota_terpakai));
    }
}
