<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class DailyQuota extends Model
{
    protected $fillable = [
        'destination_id',
        'tanggal',
        'kuota_maksimal',
        'kuota_terpakai',
    ];

    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
            'kuota_maksimal' => 'integer',
            'kuota_terpakai' => 'integer',
        ];
    }

    public function destination()
    {
        return $this->belongsTo(Destination::class);
    }

    /** Sisa kuota = kuota maksimal - kuota terpakai (tidak pernah negatif). */
    protected function sisaKuota(): Attribute
    {
        return Attribute::get(fn () => max(0, $this->kuota_maksimal - $this->kuota_terpakai));
    }
}
