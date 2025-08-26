@extends('layouts.app')

@section('title', 'Alterar Senha')

@section('content')
<div class="text-center mb-4">
    <h5>Olá, {{ $email ?? 'Usuário' }}</h5>
    <p class="text-muted">Digite sua nova senha abaixo</p>
</div>
<form method="POST" action="{{ route('password.change') }}">
    @csrf
    <div class="mb-3">
        <label for="new_password" class="form-label">Nova Senha</label>
        <input type="password" class="form-control" id="new_password" name="new_password" required placeholder="Mínimo 8 caracteres" minlength="8">
    </div>
    <div class="mb-3">
        <label for="new_password_confirmation" class="form-label">Confirmar Nova Senha</label>
        <input type="password" class="form-control" id="new_password_confirmation" name="new_password_confirmation" required placeholder="Digite a senha novamente" minlength="8">
    </div>
    <button type="submit" class="btn btn-primary w-100 mb-3">
        <i class="bi bi-key"></i> Alterar Senha
    </button>
</form>
<form method="POST" action="{{ route('logout') }}" class="text-center">
    @csrf
    <button type="submit" class="btn btn-link text-decoration-none text-danger">
        <i class="bi bi-box-arrow-right"></i> Sair
    </button>
</form>
@endsection
