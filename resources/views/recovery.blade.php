@extends('layouts.app')

@section('title', 'Recuperar Senha')

@section('content')
<div class="text-center mb-4">
    <h5>Recuperar Senha</h5>
    <p class="text-muted">Digite seu email para receber o link de recuperação.</p>
</div>
<form method="POST" action="{{ route('password.request') }}">
    @csrf
    <div class="mb-3">
        <label for="email" class="form-label">Email</label>
        <input type="email" class="form-control" id="email" name="email" value="{{ old('email') }}" required autofocus>
    </div>
    <button type="submit" class="btn btn-primary w-100 mb-3">
        <i class="bi bi-envelope"></i> Enviar Link de Recuperação
    </button>
    <div class="text-center">
        <a href="{{ route('login') }}" class="text-decoration-none">Voltar ao Login</a>
    </div>
</form>
@endsection
