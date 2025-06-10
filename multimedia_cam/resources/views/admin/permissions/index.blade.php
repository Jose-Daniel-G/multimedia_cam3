@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
    <h1>Sistema de reservas</h1>
@stop

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="h4 mb-0">{{ __('Permissions') }}</h2>
    @can('permissions.create')
        <a href="{{ route('admin.permissions.create') }}" class="btn btn-dark btn-sm">Crear</a>
    @endcan
</div>

<div class="container-fluid">
    <x-message></x-message>

    <div class="card">
        <div class="card-body p-0">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Nombre</th>
                        <th>Creación</th>
                        <th class="text-center">Acción</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($permissions as $permission)
                        <tr>
                            <td>{{ $permission->id }}</td>
                            <td>{{ $permission->name }}</td>
                            <td>{{ \Carbon\Carbon::parse($permission->created_at)->format('d M, Y') }}</td>
                            <td class="text-center">
                                @can('permissions.edit')
                                    <a href="{{ route('admin.permissions.edit', $permission->id) }}" class="btn btn-sm btn-outline-dark me-2">Editar</a>
                                @endcan
                                @can('permissions.delete')
                                    <form action="{{ route('admin.permissions.destroy', $permission->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger">Eliminar</button>
                                    </form>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center">No hay permisos registrados.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">
            {{ $permissions->links() }}
        </div>
    </div>
</div>
@stop

@section('css')
{{-- Puedes incluir estilos adicionales si lo deseas --}}
@stop

@section('js')
{{-- Puedes incluir scripts adicionales aquí --}}
@stop
