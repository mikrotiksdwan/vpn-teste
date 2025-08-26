@extends('layouts.app')

@section('title', 'Admin Dashboard')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Gerenciamento de Usuários</h4>
    <a href="{{ route('admin.users.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-circle"></i> Convidar Novo Usuário
    </a>
</div>

<div class="table-responsive">
    <table class="table table-striped table-hover">
        <thead class="table-dark">
            <tr>
                <th scope="col">#</th>
                <th scope="col">Usuário</th>
                <th scope="col">Email</th>
                <th scope="col">Admin</th>
                <th scope="col" class="text-end">Ações</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($users as $user)
                <tr>
                    <th scope="row">{{ $user->id }}</th>
                    <td>{{ $user->username }}</td>
                    <td>{{ $user->email }}</td>
                    <td>
                        @if ($user->is_admin)
                            <span class="badge bg-success">Sim</span>
                        @else
                            <span class="badge bg-secondary">Não</span>
                        @endif
                    </td>
                    <td class="text-end">
                        <div class="d-flex justify-content-end gap-2">
                            @if (!$user->is_admin)
                                <form action="{{ route('admin.users.promote', $user->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-success">Promover</button>
                                </form>
                            @endif
                            <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" onsubmit="return confirm('Tem certeza que deseja excluir este usuário? Esta ação não pode ser desfeita.');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger">Excluir</button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center">Nenhum usuário encontrado.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<form method="POST" action="{{ route('logout') }}" class="text-center mt-4">
    @csrf
    <button type="submit" class="btn btn-link text-decoration-none text-danger">
        <i class="bi bi-box-arrow-right"></i> Sair da conta de Admin
    </button>
</form>
@endsection
