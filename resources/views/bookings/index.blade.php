@extends('layouts.app')
@section('title', 'Pesanan Saya - SIMATA')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4>Riwayat Pesanan Saya</h4>
    <a href="{{ route('bookings.create') }}" class="btn btn-primary">+ Pesan Tiket</a>
</div>

<div class="card shadow-sm border-0 mb-3">
    <div class="card-body">
        <form action="{{ route('bookings.index') }}" method="GET" class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label mb-1">Status Pesanan</label>
                <select name="status" class="form-select">
                    <option value="">Semua status</option>
                    @foreach($statuses as $value => $label)
                        <option value="{{ $value }}" {{ request('status') === $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-outline-primary w-100">Filter</button>
                <a href="{{ route('bookings.index') }}" class="btn btn-outline-secondary">Reset</a>
            </div>
        </form>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Kode Pesanan</th>
                        <th>Paket Wisata</th>
                        <th>Tanggal Kunjungan</th>
                        <th class="text-end">Jumlah</th>
                        <th class="text-end">Total</th>
                        <th>Status</th>
                        <th width="10%">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($bookings as $booking)
                    <tr>
                        <td class="fw-semibold">{{ $booking->kode_booking }}</td>
                        <td>{{ $booking->destination->nama_destinasi ?? '-' }}</td>
                        <td>{{ $booking->tanggal_kunjungan->format('d/m/Y') }}</td>
                        <td class="text-end">{{ $booking->jumlah_tiket }}</td>
                        <td class="text-end">Rp {{ number_format($booking->total_harga, 0, ',', '.') }}</td>
                        <td><span class="badge {{ $booking->status_class }}">{{ $booking->status_label }}</span></td>
                        <td>
                            <a href="{{ route('bookings.show', $booking) }}" class="btn btn-sm btn-info text-white">Detail</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">Belum ada pesanan.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $bookings->links('pagination::bootstrap-5') }}
    </div>
</div>
@endsection
