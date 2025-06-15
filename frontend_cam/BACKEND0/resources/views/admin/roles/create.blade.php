@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
    <h1>Sistema de reservas </h1>
@stop

@section('content')
    <div class="d-flex justify-content-between">
        <h2 class="font-semibold fs-xl text-gray-800 leading-tight">
            Roles / Crear
        </h2>
        <a href="{{ route('admin.roles.index') }}" class="btn btn-secondary btn-sm rounded">Volver</a>
    </div>
    <div class="py-3">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm rounded">
                <div class="p-3 text-gray-900">
                    <form action="{{ route('admin.roles.store') }}" method="POST">
                        {{-- @method('POST') --}}
                        @csrf
                        <label for="name" class="form-label fs-lg fw-medium">Nombre</label>
                        <div class="mb-3">
                            <input value="{{ old('name') }}" type="text" placeholder="Ingresa el nombre" name="name"
                                   id="name" class="form-control border-gray-300 shadow-sm rounded-lg">
                            @error('name')
                                <p class="text-danger fw-medium">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="row row-cols-4 g-2">
                            @if ($permissions->isNotEmpty())
                                @foreach ($permissions as $permission)
                                    <div class="mt-3">
                                        <div class="form-check">
                                            <input type="checkbox" name="permission[]" id="permission-{{ $permission->id }}"
                                                   class="form-check-input rounded" value="{{ $permission->name }}">
                                            <label class="form-check-label" for="permission-{{ $permission->id }}">{{ $permission->name }}</label>
                                        </div>
                                    </div>
                                @endforeach
                            @endif
                        </div>

                        <button class="btn btn-primary btn-sm rounded">Enviar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')

@stop

@section('js')

@stop