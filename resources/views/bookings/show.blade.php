@extends('layouts.app')
@section('title', 'Detail Pesanan - SIMATA')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card shadow-sm border-0 mb-3">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Pesanan {{ $booking->kode_booking }}</h5>
                <span class="badge {{ $booking->status_class }}">{{ $booking->status_label }}</span>
            </div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-4">Paket Wisata</dt>
                    <dd class="col-sm-8">{{ $booking->destination->nama_destinasi ?? '-' }}</dd>

                    <dt class="col-sm-4">Lokasi</dt>
                    <dd class="col-sm-8">{{ $booking->destination->lokasi ?? '-' }}</dd>

                    <dt class="col-sm-4">Tanggal Kunjungan</dt>
                    <dd class="col-sm-8">{{ $booking->tanggal_kunjungan->format('d/m/Y') }}</dd>

                    <dt class="col-sm-4">Jumlah Tiket</dt>
                    <dd class="col-sm-8">{{ $booking->jumlah_tiket }} tiket</dd>

                    <dt class="col-sm-4">Total Harga</dt>
                    <dd class="col-sm-8 fw-bold">Rp {{ number_format($booking->total_harga, 0, ',', '.') }}</dd>

                    <dt class="col-sm-4">Dibuat</dt>
                    <dd class="col-sm-8">{{ $booking->created_at->format('d/m/Y H:i') }}</dd>
                </dl>
            </div>
            <div class="card-footer bg-white d-flex justify-content-between">
                <a href="{{ route('bookings.index') }}" class="btn btn-secondary">Kembali</a>
                @if($booking->bisaDiubah())
                    <div class="d-flex gap-2">
                        <a href="{{ route('bookings.edit', $booking) }}" class="btn btn-warning">Ubah Pesanan</a>
                        <form action="{{ route('bookings.cancel', $booking) }}" method="POST"
                              onsubmit="return confirm('Yakin ingin membatalkan pesanan ini?')">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn btn-danger">Batalkan</button>
                        </form>
                    </div>
                @endif
            </div>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-header bg-white"><h6 class="mb-0">Bukti Pembayaran</h6></div>
            <div class="card-body">
                @if($booking->bukti_bayar)
                    @php $ekstensi = strtolower(pathinfo($booking->bukti_bayar, PATHINFO_EXTENSION)); @endphp
                    <div class="mb-3">
                        @if($ekstensi === 'pdf')
                            <a href="{{ asset('storage/'.$booking->bukti_bayar) }}" target="_blank" class="btn btn-outline-primary btn-sm">Lihat file PDF</a>
                        @else
                            <a href="{{ asset('storage/'.$booking->bukti_bayar) }}" target="_blank">
                                <img src="{{ asset('storage/'.$booking->bukti_bayar) }}" alt="Bukti bayar" class="img-thumbnail" style="max-height: 220px;">
                            </a>
                        @endif
                    </div>
                @else
                    <p class="text-muted">Belum ada bukti pembayaran yang diunggah.</p>
                @endif

                @if($booking->bisaDiubah())
                    <form action="{{ route('bookings.bukti', $booking) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-2">
                            <label class="form-label">{{ $booking->bukti_bayar ? 'Ganti bukti pembayaran' : 'Unggah bukti pembayaran' }} (JPG, PNG, PDF, maks. 2 MB)</label>
                            <input type="file" name="bukti_bayar" class="form-control @error('bukti_bayar') is-invalid @enderror" accept=".jpg,.jpeg,.png,.pdf" required>
                            @error('bukti_bayar') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <button type="submit" class="btn btn-success">Unggah</button>
                    </form>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
