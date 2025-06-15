@extends('adminlte::page')

@section('title', 'JDeveloper')

@section('content_header')
    <h1>Editar Usuario</h1>
@stop

@section('content')
    @if (session('info'))
        <div class="alert alert-success">
            <strong>{{ session('info') }}</strong>
        </div>
    @endif

    <div class="card">
        <div class="card-body">
            <p class="h5">Nombre Completo:</p>
            <p class="form-control">{{ $user->name }}</p>

            <h2 class="h5">Listado de Roles</h2>

            <!-- Formulario para actualizar roles -->
            <form action="{{ route('admin.users.update', $user) }}" method="POST">
                @csrf
                @method('PUT')

                @foreach ($roles as $role)
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" name="roles[]" value="{{ $role->id }}"
                               id="role_{{ $role->id }}" {{ $user->roles->contains($role->id) ? 'checked' : '' }}>
                        <label class="form-check-label" for="role_{{ $role->id }}">
                            {{ $role->name }}
                        </label>
                    </div>
                @endforeach
                <button type="submit" class="btn btn-primary mt-3">Asignar rol</button>
            </form>
        </div>
    </div>
@stop
