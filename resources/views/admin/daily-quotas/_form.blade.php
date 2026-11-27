<form action="{{ $action }}" method="POST">
    @csrf
    @if($method !== 'POST')
        @method($method)
    @endif

    <div class="mb-3">
        <label class="form-label">Paket Wisata</label>
        <select name="destination_id" class="form-select @error('destination_id') is-invalid @enderror" required>
            <option value="">-- Pilih paket --</option>
            @foreach($destinations as $d)
                <option value="{{ $d->id }}"
                    {{ old('destination_id', $quota->destination_id ?? '') == $d->id ? 'selected' : '' }}>
                    {{ $d->nama_destinasi }}
                </option>
            @endforeach
        </select>
        @error('destination_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="mb-3">
        <label class="form-label">Tanggal</label>
        <input type="date" name="tanggal"
               value="{{ old('tanggal', isset($quota) ? $quota->tanggal->format('Y-m-d') : '') }}"
               class="form-control @error('tanggal') is-invalid @enderror" required>
        @error('tanggal') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="mb-3">
        <label class="form-label">Kuota Maksimal (tiket)</label>
        <input type="number" name="kuota_maksimal" min="1"
               value="{{ old('kuota_maksimal', $quota->kuota_maksimal ?? '') }}"
               class="form-control @error('kuota_maksimal') is-invalid @enderror" required>
        @error('kuota_maksimal') <div class="invalid-feedback">{{ $message }}</div> @enderror
        @isset($quota)
            <div class="form-text">Kuota terpakai saat ini: {{ $quota->kuota_terpakai }} tiket.</div>
        @endisset
    </div>

    <div class="d-flex justify-content-between">
        <a href="{{ route('admin.daily-quotas.index') }}" class="btn btn-secondary">Kembali</a>
        <button type="submit" class="btn btn-primary">Simpan</button>
    </div>
</form>
