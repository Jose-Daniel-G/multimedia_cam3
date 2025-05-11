@extends('adminlte::page')

@section('title', 'Cargar PDF')

@section('content_header')
    <h1>Procesar documentos</h1>
@stop

@section('content')
    <div class="container mt-4">
        <h2 class="text-center"> {{ $organismo->depe_nomb }} </h2>
        <h2 class="text-center">📂 Verificar PDF en CSV y Subir </h2>
        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger">
                {!! nl2br(e(session('error'))) !!}
            </div>
        @endif


        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="card shadow mt-3">
            <form method="POST" action="{{ route('main.store') }}" enctype="multipart/form-data">
                <select name="id_tipo_plantilla" id="id_tipo_plantilla" class="form-control">
                    <option value="">Seleccione una plantilla</option>
                    @foreach ($tipo_plantilla as $plantilla)
                        <option value="{{ $plantilla->id_tipo_plantilla }}">{{ $plantilla->nombre_plantilla }}</option>
                    @endforeach
                </select>
                {{-- @if (session('sheet'))
                    <p>{{ session('sheet') }}</p>
                @endif --}}

                <div id="verify-content" style="display:none">
                    <div class="form-group">
                        <div class="card-body text-center">
                            @csrf
                            <button type="submit" class="btn btn-primary">🔍 Procesar</button>

                        </div>
                    </div>
                </div>
            </form>
           </div>
        @if (session('info') == 2 || session('info') == 1)
            @include('admin.import.message.response_coactivo_persuasivo')
        @elseif(session('info') == 3)
            @include('admin.import.message.response_liquidaciones')
        @endif
        @if ($sheet == 1)
            @include('admin.import.message.records')
        @endif

    </div>
@stop

@section('css')
    {{-- <link rel="stylesheet" href="/css/admin_custom.css"> --}}
@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    @if (session('error'))
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: `{!! addslashes(session('error')) !!}`,
            });
        </script>
    @endif
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let tipoPlantilla = document.getElementById('id_tipo_plantilla');
            let verifyContent = document.getElementById('verify-content');

            if (tipoPlantilla) {
                tipoPlantilla.addEventListener('change', function() {
                    verifyContent.style.display = this.value ? 'block' : 'none';
                });
            }
        });
    </script>
@stop
