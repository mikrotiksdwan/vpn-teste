@extends('layouts.app')

@section('title', 'Login')

@section('content')
<form method="POST" action="{{ route('login') }}">
    @csrf
    <div class="mb-3">
        <label for="username" class="form-label">Usuário</label>
        <input type="text" class="form-control" id="username" name="username" value="{{ old('username') }}" required autofocus>
    </div>
    <div class="mb-3">
        <label for="password" class="form-label">Senha</label>
        <input type="password" class="form-control" id="password" name="password" required>
    </div>
    <button type="submit" class="btn btn-primary w-100 mb-3">Entrar</button>
    <div class="text-center">
        <a href="{{ route('password.request') }}" class="text-decoration-none">Esqueci minha senha</a>
    </div>
</form>
@endsection
