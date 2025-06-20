@extends('adminlte::page')

@section('title', 'Editar Rol')

@section('content_header')
    <h1>Editar Rol</h1>
@stop

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="h4">Roles / Editar</h2>
        <a href="{{ route('admin.roles.index') }}" class="btn btn-secondary btn-sm">Volver</a>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger">
            <strong>¡Oops!</strong> Hubo algunos problemas con tus datos.<br><br>
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card">
        <div class="card-body">
            <form action="{{ route('admin.roles.update', $role->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label for="name" class="form-label">Nombre del rol</label>
                    <input value="{{ old('name', $role->name) }}" type="text" name="name" id="name"
                        class="form-control @error('name') is-invalid @enderror" placeholder="Nombre del rol">
                    @error('name')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                <label class="form-label">Permisos</label>
                <div class="row">
                    @foreach ($permissions as $permission)
                        <div class="col-md-3 mb-2">
                            <div class="form-check">
                                <input type="checkbox" name="permission[]"
                                    id="permission-{{ $permission->id }}"
                                    class="form-check-input"
                                    value="{{ $permission->name }}"
                                    {{ $hasPermissions->contains($permission->name) ? 'checked':'' }}>
                                <label class="form-check-label" for="permission-{{ $permission->id }}">
                                    {{ $permission->name }}
                                </label>
                            </div>
                        </div>
                    @endforeach
                </div>

                <button type="submit" class="btn btn-primary mt-3">Actualizar</button>
            </form>
        </div>
    </div>
@stop

@section('css')
@stop

@section('js')
@stop
    