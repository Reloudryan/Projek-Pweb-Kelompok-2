<?php

namespace App\Http\Controllers;

use App\Http\Requests\BookingRequest;
use App\Models\Booking;
use App\Models\DailyQuota;
use App\Models\Destination;
use App\Services\QuotaService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class BookingController extends Controller
{
    public function __construct(private QuotaService $quota)
    {
    }

    /** Riwayat pesanan milik pengunjung yang login. */
    public function index(Request $request)
    {
        $statuses = [
            Booking::STATUS_MENUNGGU => 'Menunggu Pembayaran',
            Booking::STATUS_LUNAS => 'Lunas',
            Booking::STATUS_SELESAI => 'Selesai',
            Booking::STATUS_BATAL => 'Dibatalkan',
        ];

        $bookings = auth()->user()->bookings()
            ->with('destination')
            ->when(
                array_key_exists((string) $request->status, $statuses),
                fn ($q) => $q->where('status', $request->status)
            )
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('bookings.index', compact('bookings', 'statuses'));
    }

    public function create(Request $request)
    {
        $destinations = Destination::orderBy('nama_destinasi')->get();
        $selectedDestination = $request->query('destination_id');

        return view('bookings.create', compact('destinations', 'selectedDestination'));
    }

    public function store(BookingRequest $request)
    {
        $data = $request->validated();
        $destination = Destination::findOrFail($data['destination_id']);

        // Cek kuota + simpan pesanan + tambah kuota_terpakai harus satu kesatuan.
        $booking = DB::transaction(function () use ($data, $destination) {
            $quota = $this->quota->lockForDate($destination, $data['tanggal_kunjungan']);
            $this->quota->reserve($quota, (int) $data['jumlah_tiket']);

            return Booking::create([
                'kode_booking' => Booking::buatKode($data['tanggal_kunjungan']),
                'user_id' => auth()->id(),
                'destination_id' => $destination->id,
                'tanggal_kunjungan' => $data['tanggal_kunjungan'],
                'jumlah_tiket' => $data['jumlah_tiket'],
                'total_harga' => $destination->harga_tiket * $data['jumlah_tiket'],
                'status' => Booking::STATUS_MENUNGGU,
            ]);
        });

        return redirect()->route('bookings.show', $booking)
            ->with('success', "Pesanan {$booking->kode_booking} berhasil dibuat. Silakan unggah bukti pembayaran.");
    }

    public function show(Booking $booking)
    {
        $this->milikSaya($booking);
        $booking->load('destination');

        return view('bookings.show', compact('booking'));
    }

    public function edit(Booking $booking)
    {
        $this->milikSaya($booking);

        if (! $booking->bisaDiubah()) {
            return redirect()->route('bookings.show', $booking)
                ->with('error', 'Pesanan yang sudah lunas atau dibatalkan tidak dapat diubah.');
        }

        $destinations = Destination::orderBy('nama_destinasi')->get();

        return view('bookings.edit', compact('booking', 'destinations'));
    }

    public function update(BookingRequest $request, Booking $booking)
    {
        $this->milikSaya($booking);
        $data = $request->validated();
        $destinationBaru = Destination::findOrFail($data['destination_id']);

        $berhasil = DB::transaction(function () use ($booking, $data, $destinationBaru) {
            $booking = Booking::lockForUpdate()->findOrFail($booking->id);

            if (! $booking->bisaDiubah()) {
                return false;
            }

            // 1) kembalikan kuota lama
            $kuotaLama = $this->quota->lockForDate($booking->destination, $booking->tanggal_kunjungan);
            $this->quota->release($kuotaLama, $booking->jumlah_tiket);

            // 2) cek & pakai kuota baru (kalau tidak cukup -> ValidationException, transaksi dibatalkan)
            $kuotaBaru = $this->quota->lockForDate($destinationBaru, $data['tanggal_kunjungan']);
            $this->quota->reserve($kuotaBaru, (int) $data['jumlah_tiket']);

            $booking->update([
                'destination_id' => $destinationBaru->id,
                'tanggal_kunjungan' => $data['tanggal_kunjungan'],
                'jumlah_tiket' => $data['jumlah_tiket'],
                'total_harga' => $destinationBaru->harga_tiket * $data['jumlah_tiket'],
            ]);

            return true;
        });

        if (! $berhasil) {
            return redirect()->route('bookings.show', $booking)
                ->with('error', 'Pesanan yang sudah lunas atau dibatalkan tidak dapat diubah.');
        }

        return redirect()->route('bookings.show', $booking)
            ->with('success', 'Pesanan berhasil diperbarui.');
    }

    /** Batalkan pesanan: status jadi batal, kuota dikembalikan. */
    public function cancel(Booking $booking)
    {
        $this->milikSaya($booking);

        $berhasil = DB::transaction(function () use ($booking) {
            $booking = Booking::lockForUpdate()->findOrFail($booking->id);

            if (! $booking->bisaDiubah()) {
                return false;
            }

            $kuota = $this->quota->lockForDate($booking->destination, $booking->tanggal_kunjungan);
            $this->quota->release($kuota, $booking->jumlah_tiket);
            $booking->update(['status' => Booking::STATUS_BATAL]);

            return true;
        });

        if (! $berhasil) {
            return back()->with('error', 'Pesanan yang sudah lunas atau dibatalkan tidak dapat dibatalkan lagi.');
        }

        return redirect()->route('bookings.index')
            ->with('success', "Pesanan {$booking->kode_booking} dibatalkan dan kuota dikembalikan.");
    }

    /** Unggah bukti pembayaran (admin memverifikasinya di modul Verifikasi Pembayaran). */
    public function uploadBukti(Request $request, Booking $booking)
    {
        $this->milikSaya($booking);

        if (! $booking->bisaDiubah()) {
            return back()->with('error', 'Bukti bayar hanya dapat diunggah untuk pesanan yang menunggu pembayaran.');
        }

        $request->validate([
            'bukti_bayar' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:2048'],
        ], [
            'bukti_bayar.required' => 'Pilih file bukti pembayaran terlebih dahulu.',
            'bukti_bayar.file' => 'Bukti pembayaran harus berupa file.',
            'bukti_bayar.mimes' => 'Format bukti pembayaran harus JPG, PNG, atau PDF.',
            'bukti_bayar.max' => 'Ukuran bukti pembayaran maksimal 2 MB.',
        ]);

        if ($booking->bukti_bayar) {
            Storage::disk('public')->delete($booking->bukti_bayar);
        }

        $path = $request->file('bukti_bayar')->store('bukti-bayar', 'public');
        $booking->update(['bukti_bayar' => $path]);

        return back()->with('success', 'Bukti pembayaran berhasil diunggah. Menunggu verifikasi admin.');
    }

    /** Endpoint AJAX (jQuery) untuk menampilkan sisa kuota di form pemesanan. */
    public function cekKuota(Request $request)
    {
        $data = $request->validate([
            'destination_id' => ['required', 'integer', 'exists:destinations,id'],
            'tanggal' => ['required', 'date'],
            'booking_id' => ['nullable', 'integer'],
        ]);

        $destination = Destination::findOrFail($data['destination_id']);
        $quota = DailyQuota::where('destination_id', $destination->id)
            ->whereDate('tanggal', $data['tanggal'])
            ->first();

        $maksimal = $quota?->kuota_maksimal ?? $destination->kuota_default;
        $sisa = max(0, $maksimal - ($quota?->kuota_terpakai ?? 0));

        // Saat ubah pesanan, tiket miliknya sendiri pada paket + tanggal yang sama dihitung kembali.
        if (! empty($data['booking_id'])) {
            $sendiri = Booking::where('id', $data['booking_id'])
                ->where('user_id', auth()->id())
                ->where('status', Booking::STATUS_MENUNGGU)
                ->first();

            if ($sendiri
                && (int) $sendiri->destination_id === (int) $destination->id
                && $sendiri->tanggal_kunjungan->isSameDay(Carbon::parse($data['tanggal']))) {
                $sisa += $sendiri->jumlah_tiket;
            }
        }

        return response()->json([
            'maksimal' => (int) $maksimal,
            'sisa' => (int) $sisa,
        ]);
    }

    private function milikSaya(Booking $booking): void
    {
        abort_unless((int) $booking->user_id === (int) auth()->id(), 403, 'Pesanan ini bukan milik Anda.');
    }
}
