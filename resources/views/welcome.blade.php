@extends('layouts.app')
@section('title', 'Beranda - SIMATA')

@section('content')
<div class="p-5 mb-4 bg-white rounded-3 shadow-sm">
    <div class="container-fluid py-3">
        <h2 class="fw-bold text-primary">Selamat Datang di SIMATA</h2>
        <p class="col-md-8 fs-5 text-muted">
            Sistem Informasi Pemesanan Tiket Wisata. Halaman katalog destinasi wisata akan ditampilkan di sini.
        </p>
        @auth
            <div class="alert alert-info mb-0">
                Anda login sebagai <strong>{{ auth()->user()->name }}</strong> (Role: <span class="text-uppercase fw-bold">{{ auth()->user()->role }}</span>).
            </div>
        @endauth
    </div>
</div>
@endsection