@extends('layouts.app')
@section('title', 'Tambah Kuota Harian - SIMATA')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Tambah Kuota Harian</h5>
            </div>
            <div class="card-body">
                @include('admin.daily-quotas._form', [
                    'action' => route('admin.daily-quotas.store'),
                    'method' => 'POST',
                ])
            </div>
        </div>
    </div>
</div>
@endsection
