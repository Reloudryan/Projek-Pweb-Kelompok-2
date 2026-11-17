@extends('layouts.app')
@section('title', 'Login - SIMATA')

@section('content')
<div class="row justify-content-center mt-5">
    <div class="col-md-5">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-primary text-white text-center py-3">
                <h5 class="mb-0">Login SIMATA</h5>
            </div>
            <div class="card-body p-4">
                <form action="{{ route('login.post') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" value="{{ old('email') }}" class="form-control @error('email') is-invalid @enderror" required>
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <div class="input-group">
                            <input type="password" name="password" id="password" class="form-control @error('password') is-invalid @enderror" required>
                            <button type="button" class="btn btn-outline-secondary" id="togglePassword">Lihat</button>
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">Masuk</button>
                </form>
                <div class="text-center mt-3">
                    <small>Belum punya akun? <a href="{{ route('register') }}">Daftar di sini</a></small>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        $('#togglePassword').on('click', function() {
            const input = $('#password');
            const isPassword = input.attr('type') === 'password';
            input.attr('type', isPassword ? 'text' : 'password');
            $(this).text(isPassword ? 'Sembunyikan' : 'Lihat');
        });
    });
</script>
@endpush