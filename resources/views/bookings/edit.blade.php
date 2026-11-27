@extends('layouts.app')
@section('title', 'Ubah Pesanan - SIMATA')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Ubah Pesanan {{ $booking->kode_booking }}</h5>
            </div>
            <div class="card-body">
                @include('bookings._form', [
                    'action' => route('bookings.update', $booking),
                    'method' => 'PUT',
                    'tombol' => 'Simpan Perubahan',
                ])
            </div>
        </div>
    </div>
</div>
@endsection
