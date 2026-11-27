@extends('layouts.app')
@section('title', 'Kuota Harian - SIMATA')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4>Kelola Kuota Harian</h4>
    <a href="{{ route('admin.daily-quotas.create') }}" class="btn btn-primary">+ Tambah Kuota</a>
</div>

<div class="card shadow-sm border-0 mb-3">
    <div class="card-body">
        <form action="{{ route('admin.daily-quotas.index') }}" method="GET" class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label mb-1">Paket Wisata</label>
                <select name="destination_id" class="form-select">
                    <option value="">Semua paket</option>
                    @foreach($destinations as $d)
                        <option value="{{ $d->id }}" {{ request('destination_id') == $d->id ? 'selected' : '' }}>{{ $d->nama_destinasi }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label mb-1">Dari Tanggal</label>
                <input type="date" name="dari" value="{{ request('dari') }}" class="form-control">
            </div>
            <div class="col-md-3">
                <label class="form-label mb-1">Sampai Tanggal</label>
                <input type="date" name="sampai" value="{{ request('sampai') }}" class="form-control">
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-outline-primary w-100">Filter</button>
                <a href="{{ route('admin.daily-quotas.index') }}" class="btn btn-outline-secondary">Reset</a>
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
                        <th width="5%">No</th>
                        <th>Paket Wisata</th>
                        <th>Tanggal</th>
                        <th class="text-end">Kuota Total</th>
                        <th class="text-end">Terpakai</th>
                        <th class="text-end">Sisa</th>
                        <th width="18%">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($quotas as $i => $quota)
                    <tr>
                        <td>{{ $quotas->firstItem() + $i }}</td>
                        <td>{{ $quota->destination->nama_destinasi ?? '-' }}</td>
                        <td>{{ $quota->tanggal->format('d/m/Y') }}</td>
                        <td class="text-end">{{ number_format($quota->kuota_maksimal, 0, ',', '.') }}</td>
                        <td class="text-end">{{ number_format($quota->kuota_terpakai, 0, ',', '.') }}</td>
                        <td class="text-end">
                            <span class="badge {{ $quota->sisa_kuota > 0 ? 'bg-success' : 'bg-danger' }}">
                                {{ $quota->sisa_kuota > 0 ? number_format($quota->sisa_kuota, 0, ',', '.') : 'Penuh' }}
                            </span>
                        </td>
                        <td>
                            <a href="{{ route('admin.daily-quotas.edit', $quota) }}" class="btn btn-sm btn-warning">Edit</a>
                            <form action="{{ route('admin.daily-quotas.destroy', $quota) }}" method="POST" class="d-inline"
                                  onsubmit="return confirm('Yakin ingin menghapus kuota ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">Belum ada data kuota harian.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $quotas->links('pagination::bootstrap-5') }}
    </div>
</div>
@endsection
