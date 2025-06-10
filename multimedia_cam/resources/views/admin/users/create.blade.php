@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
    <h1>Sistema de reservas </h1>
@stop

@section('content')
    <div class="container-fluid">

        <div class="row">
            <h1>Registro de nuevo usuario</h1>

        </div>
        <div class="row">
            <div class="col-md-10">
                <div class="card card-outline card-primary">
                    <div class="card-header">
                        <h3 class="card-title">Datos</h3>
                        <form action="{{ route('admin.users.store') }}" method="POST" autocomplete="off">
                            @csrf
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="name">Nombre del usuario </label><b>*</b>
                                        <input type="text" class="form-control" name="name" value="{{ old('name')}}" required>
                                        @error('name')
                                            <small class="bg-danger text-white p-1">{{ $message }}</small>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="email">Email</label><b>*</b>
                                        <input type="email" class="form-control" name="email"  value="{{ old('email')}}" required>
                                    </div>
                                    @error('email')
                                        <small class="bg-danger text-white p-1">{{ $message }}</small>
                                    @enderror
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="organismo_id">Dependencias <b>*</b></label>
                                        <select name="organismo_id" id="organismo_id" class="form-control" required>
                                            <option value="" disabled selected>Seleccione una dependencia</option>
                                            @foreach ($organismos as $organism)
                                                <option value="{{ $organism->id }}" 
                                                    {{ old('organismo_id', $post->organismo_id ?? '') == $organism->id ? 'selected' : '' }}>
                                                    {{ $organism->depe_nomb }} {{-- Corregido el nombre de la columna --}}
                                                </option>
                                            @endforeach
                                        </select>
                                        
                                        @error('organismo_id')
                                            <small class="bg-danger text-white p-1">{{ $message }}</small>
                                        @enderror
                                    </div>
                                    
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="password">Contrasena</label><b>*</b>
                                        <input type="password" class="form-control" name="password"  value="{{ old('password')}}" required>
                                    </div>
                                    @error('password')
                                        <small class="bg-danger text-white p-1">{{ $message }}</small>
                                    @enderror
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="password_confirmation">Verificacion de contrasena</label><b>*</b>
                                        <input type="password" class="form-control" name="password_confirmation"  value="{{ old('password_confirmation')}}" required>
                                    </div>
                                    @error('password_confirmation')
                                        <small class="bg-danger text-white p-1">{{ $message }}</small>
                                    @enderror
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">
                                            Cancelar
                                            {{-- <i class="fa-solid fa-plus"></i> --}}
                                        </a>
                                        <button type="submit" class="btn btn-primary">Registrar usuario</button>

                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

            </div>
        </div>
    </div>
    </div>
@stop

@section('css')

@stop

@section('js')

@stop
