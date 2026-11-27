<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // akses dibatasi middleware auth + role:pengunjung, kepemilikan dicek di controller
    }

    public function rules(): array
    {
        return [
            'destination_id' => ['required', 'integer', 'exists:destinations,id'],
            'tanggal_kunjungan' => ['required', 'date', 'after_or_equal:today'],
            'jumlah_tiket' => ['required', 'integer', 'min:1', 'max:50'],
        ];
    }

    public function messages(): array
    {
        return [
            'destination_id.required' => 'Paket wisata wajib dipilih.',
            'destination_id.exists' => 'Paket wisata tidak ditemukan.',
            'tanggal_kunjungan.required' => 'Tanggal kunjungan wajib diisi.',
            'tanggal_kunjungan.date' => 'Format tanggal kunjungan tidak valid.',
            'tanggal_kunjungan.after_or_equal' => 'Tanggal kunjungan tidak boleh sebelum hari ini.',
            'jumlah_tiket.required' => 'Jumlah tiket wajib diisi.',
            'jumlah_tiket.integer' => 'Jumlah tiket harus berupa angka bulat.',
            'jumlah_tiket.min' => 'Pesan minimal 1 tiket.',
            'jumlah_tiket.max' => 'Satu pesanan maksimal 50 tiket.',
        ];
    }
}
