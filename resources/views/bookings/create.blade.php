@extends('layouts.app')
@section('title', 'Pesan Tiket - SIMATA')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Pesan Tiket Wisata</h5>
            </div>
            <div class="card-body">
                @include('bookings._form', [
                    'action' => route('bookings.store'),
                    'method' => 'POST',
                    'tombol' => 'Pesan Sekarang',
                ])
            </div>
        </div>
    </div>
</div>
@endsection
