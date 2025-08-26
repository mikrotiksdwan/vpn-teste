@extends('layouts.app')

@section('title', 'Redefinir Senha')

@section('content')
<div class="text-center mb-4">
    <h5>Redefinir Senha</h5>
    <p class="text-muted">Crie uma nova senha para sua conta.</p>
</div>
<form method="POST" action="{{ route('password.update') }}">
    @csrf
    <input type="hidden" name="token" value="{{ $token }}">
    <div class="mb-3">
        <label for="password" class="form-label">Nova Senha</label>
        <input type="password" class="form-control" id="password" name="password" required minlength="8">
    </div>
    <div class="mb-3">
        <label for="password_confirmation" class="form-label">Confirmar Nova Senha</label>
        <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required minlength="8">
    </div>
    <button type="submit" class="btn btn-primary w-100 mb-3">
        <i class="bi bi-key-fill"></i> Redefinir Senha
    </button>
</form>
@endsection
