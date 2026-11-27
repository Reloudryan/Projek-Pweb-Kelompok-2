<?php

namespace App\Http\Requests;

use App\Models\DailyQuota;
use Illuminate\Foundation\Http\FormRequest;

class DailyQuotaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // akses sudah dibatasi middleware auth + role:admin di route
    }

    public function rules(): array
    {
        $quota = $this->route('daily_quota'); // null saat tambah, model saat ubah

        return [
            'destination_id' => ['required', 'integer', 'exists:destinations,id'],
            'tanggal' => array_filter([
                'bail',
                'required',
                'date',
                $this->isMethod('post') ? 'after_or_equal:today' : null,
                function ($attribute, $value, $fail) use ($quota) {
                    $sudahAda = DailyQuota::where('destination_id', $this->input('destination_id'))
                        ->whereDate('tanggal', $value)
                        ->when($quota, fn ($q) => $q->where('id', '!=', $quota->id))
                        ->exists();

                    if ($sudahAda) {
                        $fail('Kuota untuk paket dan tanggal ini sudah ada. Ubah data yang sudah ada.');
                    }
                },
            ]),
            'kuota_maksimal' => ['required', 'integer', 'min:1', 'max:100000'],
        ];
    }

    public function messages(): array
    {
        return [
            'destination_id.required' => 'Paket wisata wajib dipilih.',
            'destination_id.exists' => 'Paket wisata tidak ditemukan.',
            'tanggal.required' => 'Tanggal wajib diisi.',
            'tanggal.date' => 'Format tanggal tidak valid.',
            'tanggal.after_or_equal' => 'Tanggal tidak boleh sebelum hari ini.',
            'kuota_maksimal.required' => 'Kuota maksimal wajib diisi.',
            'kuota_maksimal.integer' => 'Kuota maksimal harus berupa angka bulat.',
            'kuota_maksimal.min' => 'Kuota maksimal minimal 1 tiket.',
            'kuota_maksimal.max' => 'Kuota maksimal terlalu besar (maksimal 100.000).',
        ];
    }
}
