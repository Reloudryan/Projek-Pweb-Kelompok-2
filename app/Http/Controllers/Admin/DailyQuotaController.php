<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\DailyQuotaRequest;
use App\Models\DailyQuota;
use App\Models\Destination;
use Illuminate\Http\Request;

class DailyQuotaController extends Controller
{
    public function index(Request $request)
    {
        $destinations = Destination::orderBy('nama_destinasi')->get();

        $quotas = DailyQuota::with('destination')
            ->when($request->filled('destination_id'), fn ($q) => $q->where('destination_id', $request->destination_id))
            ->when($request->filled('dari'), fn ($q) => $q->whereDate('tanggal', '>=', $request->dari))
            ->when($request->filled('sampai'), fn ($q) => $q->whereDate('tanggal', '<=', $request->sampai))
            ->orderBy('tanggal', 'desc')
            ->paginate(10)
            ->withQueryString();

        return view('admin.daily-quotas.index', compact('quotas', 'destinations'));
    }

    public function create()
    {
        $destinations = Destination::orderBy('nama_destinasi')->get();

        return view('admin.daily-quotas.create', compact('destinations'));
    }

    public function store(DailyQuotaRequest $request)
    {
        DailyQuota::create($request->validated() + ['kuota_terpakai' => 0]);

        return redirect()->route('admin.daily-quotas.index')
            ->with('success', 'Kuota harian berhasil ditambahkan.');
    }

    public function edit(DailyQuota $daily_quota)
    {
        $destinations = Destination::orderBy('nama_destinasi')->get();

        return view('admin.daily-quotas.edit', [
            'quota' => $daily_quota,
            'destinations' => $destinations,
        ]);
    }

    public function update(DailyQuotaRequest $request, DailyQuota $daily_quota)
    {
        $data = $request->validated();

        // Paket & tanggal dikunci kalau sudah ada tiket yang terpesan.
        $kunciBerubah = (int) $data['destination_id'] !== (int) $daily_quota->destination_id
            || $data['tanggal'] !== $daily_quota->tanggal->format('Y-m-d');

        if ($daily_quota->kuota_terpakai > 0 && $kunciBerubah) {
            return back()->withInput()->withErrors([
                'tanggal' => 'Paket dan tanggal tidak dapat diubah karena sudah ada tiket yang terpesan.',
            ]);
        }

        if ($data['kuota_maksimal'] < $daily_quota->kuota_terpakai) {
            return back()->withInput()->withErrors([
                'kuota_maksimal' => "Kuota maksimal tidak boleh kurang dari kuota terpakai ({$daily_quota->kuota_terpakai} tiket).",
            ]);
        }

        $daily_quota->update($data);

        return redirect()->route('admin.daily-quotas.index')
            ->with('success', 'Kuota harian berhasil diperbarui.');
    }

    public function destroy(DailyQuota $daily_quota)
    {
        if ($daily_quota->kuota_terpakai > 0) {
            return back()->with('error', 'Kuota tidak dapat dihapus karena sudah ada tiket yang terpesan.');
        }

        $daily_quota->delete();

        return redirect()->route('admin.daily-quotas.index')
            ->with('success', 'Kuota harian berhasil dihapus.');
    }
}
