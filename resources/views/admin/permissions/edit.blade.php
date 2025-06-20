@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
    <h1>Sistema de reservas</h1>
@stop

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="h4 mb-0">Permissions / Editar</h2>
    <a href="{{ route('admin.permissions.index') }}" class="btn btn-dark btn-sm">Volver</a>
</div>

<div class="container-fluid">
    <div class="card">
        <div class="card-body">
            <form action="{{ route('admin.permissions.update', $permission->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label for="name" class="form-label">Nombre</label>
                    <input
                        type="text"
                        name="name"
                        id="name"
                        class="form-control @error('name') is-invalid @enderror"
                        value="{{ old('name', $permission->name) }}"
                    >
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <button type="submit" class="btn btn-primary">Actualizar</button>
            </form>
        </div>
    </div>
</div>
@stop

@section('css')
{{-- Estilos personalizados si los necesitas --}}
@stop

@section('js')
{{-- Scripts personalizados si los necesitas --}}
@stop
