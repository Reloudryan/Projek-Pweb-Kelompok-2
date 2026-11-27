<form action="{{ $action }}" method="POST">
    @csrf
    @if($method !== 'POST')
        @method($method)
    @endif

    <div class="mb-3">
        <label class="form-label">Paket Wisata</label>
        <select name="destination_id" id="destination_id" class="form-select @error('destination_id') is-invalid @enderror" required>
            <option value="">-- Pilih paket --</option>
            @foreach($destinations as $d)
                <option value="{{ $d->id }}" data-harga="{{ (int) $d->harga_tiket }}"
                    {{ old('destination_id', $booking->destination_id ?? $selectedDestination ?? '') == $d->id ? 'selected' : '' }}>
                    {{ $d->nama_destinasi }} - Rp {{ number_format($d->harga_tiket, 0, ',', '.') }}
                </option>
            @endforeach
        </select>
        @error('destination_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="mb-3">
        <label class="form-label">Tanggal Kunjungan</label>
        <input type="date" name="tanggal_kunjungan" id="tanggal_kunjungan" min="{{ now()->format('Y-m-d') }}"
               value="{{ old('tanggal_kunjungan', isset($booking) ? $booking->tanggal_kunjungan->format('Y-m-d') : '') }}"
               class="form-control @error('tanggal_kunjungan') is-invalid @enderror" required>
        @error('tanggal_kunjungan') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div id="info-kuota" class="alert d-none py-2"></div>

    <div class="mb-3">
        <label class="form-label">Jumlah Tiket</label>
        <input type="number" name="jumlah_tiket" id="jumlah_tiket" min="1" max="50"
               value="{{ old('jumlah_tiket', $booking->jumlah_tiket ?? 1) }}"
               class="form-control @error('jumlah_tiket') is-invalid @enderror" required>
        @error('jumlah_tiket') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="mb-4 p-3 bg-light rounded d-flex justify-content-between align-items-center">
        <span class="text-muted">Total harga</span>
        <strong class="fs-5" id="total-harga">Rp 0</strong>
    </div>

    <div class="d-flex justify-content-between">
        <a href="{{ isset($booking) ? route('bookings.show', $booking) : route('bookings.index') }}" class="btn btn-secondary">Kembali</a>
        <button type="submit" class="btn btn-primary">{{ $tombol ?? 'Simpan' }}</button>
    </div>
</form>

@push('scripts')
<script>
$(function () {
    const urlCekKuota = @json(route('bookings.cek-kuota'));
    const bookingId = @json(isset($booking) ? $booking->id : null);

    function rupiah(angka) {
        return 'Rp ' + Number(angka).toLocaleString('id-ID');
    }

    function hitungTotal() {
        const harga = Number($('#destination_id option:selected').data('harga')) || 0;
        const jumlah = parseInt($('#jumlah_tiket').val(), 10) || 0;
        $('#total-harga').text(rupiah(harga * jumlah));
    }

    function cekKuota() {
        const destinasi = $('#destination_id').val();
        const tanggal = $('#tanggal_kunjungan').val();
        const $info = $('#info-kuota');

        if (!destinasi || !tanggal) {
            $info.addClass('d-none');
            return;
        }

        $.getJSON(urlCekKuota, { destination_id: destinasi, tanggal: tanggal, booking_id: bookingId })
            .done(function (res) {
                $info.removeClass('d-none alert-success alert-danger')
                     .addClass(res.sisa > 0 ? 'alert-success' : 'alert-danger')
                     .text(res.sisa > 0
                        ? 'Sisa kuota pada tanggal ini: ' + res.sisa + ' dari ' + res.maksimal + ' tiket.'
                        : 'Kuota pada tanggal ini sudah penuh. Silakan pilih tanggal lain.');
            })
            .fail(function () {
                $info.addClass('d-none');
            });
    }

    $('#destination_id').on('change', function () { hitungTotal(); cekKuota(); });
    $('#tanggal_kunjungan').on('change', cekKuota);
    $('#jumlah_tiket').on('input', hitungTotal);

    hitungTotal();
    cekKuota();
});
</script>
@endpush
