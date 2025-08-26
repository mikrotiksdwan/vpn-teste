@extends('layouts.app')

@section('title', 'Convidar Novo Usuário')

@section('content')
<h4 class="mb-4">Convidar Novo Usuário</h4>

<form method="POST" action="{{ route('admin.users.store') }}">
    @csrf
    <div class="mb-3">
        <label for="username" class="form-label">Usuário (para a VPN)</label>
        <input type="text" class="form-control" id="username" name="username" value="{{ old('username') }}" required>
        <div class="form-text">Este será o nome de usuário que a pessoa usará para se conectar à VPN.</div>
    </div>
    <div class="mb-3">
        <label for="email" class="form-label">Email</label>
        <input type="email" class="form-control" id="email" name="email" value="{{ old('email') }}" required>
        <div class="form-text">Um link para criar a senha será enviado para este email.</div>
    </div>
    <div class="mt-4">
        <button type="submit" class="btn btn-primary">Enviar Convite</button>
        <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary">Cancelar</a>
    </div>
</form>
@endsection
