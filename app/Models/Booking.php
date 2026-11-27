<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    public const STATUS_MENUNGGU = 'menunggu_pembayaran';
    public const STATUS_LUNAS = 'lunas';
    public const STATUS_SELESAI = 'selesai';
    public const STATUS_BATAL = 'batal';

    protected $fillable = [
        'kode_booking',
        'user_id',
        'destination_id',
        'tanggal_kunjungan',
        'jumlah_tiket',
        'total_harga',
        'bukti_bayar',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_kunjungan' => 'date',
            'jumlah_tiket' => 'integer',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function destination()
    {
        return $this->belongsTo(Destination::class);
    }

    /** Pesanan hanya boleh diubah / dibatalkan selama belum lunas. */
    public function bisaDiubah(): bool
    {
        return $this->status === self::STATUS_MENUNGGU;
    }

    protected function statusLabel(): Attribute
    {
        return Attribute::get(fn () => match ($this->status) {
            self::STATUS_MENUNGGU => 'Menunggu Pembayaran',
            self::STATUS_LUNAS => 'Lunas',
            self::STATUS_SELESAI => 'Selesai',
            self::STATUS_BATAL => 'Dibatalkan',
            default => ucfirst($this->status),
        });
    }

    protected function statusClass(): Attribute
    {
        return Attribute::get(fn () => match ($this->status) {
            self::STATUS_MENUNGGU => 'bg-warning text-dark',
            self::STATUS_LUNAS => 'bg-success',
            self::STATUS_SELESAI => 'bg-primary',
            self::STATUS_BATAL => 'bg-secondary',
            default => 'bg-dark',
        });
    }

    /** Kode pesanan: TKT-{tanggal kunjungan}-{nomor urut}, contoh TKT-20261205-001. */
    public static function buatKode($tanggalKunjungan): string
    {
        $tanggal = Carbon::parse($tanggalKunjungan);
        $urut = static::whereDate('tanggal_kunjungan', $tanggal->toDateString())->count() + 1;

        do {
            $kode = sprintf('TKT-%s-%03d', $tanggal->format('Ymd'), $urut++);
        } while (static::where('kode_booking', $kode)->exists());

        return $kode;
    }
}
