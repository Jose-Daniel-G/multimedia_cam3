@extends('adminlte::page')

@section('title', 'Cargar PDF')

@section('content_header')
    <h1>Por procesar</h1>
@stop

@section('content')
    <div class="container mt-4">
        <h2 class="text-center"> {{ $organismo->depe_nomb }} </h2>
        <h2 class="text-center">📂Por procesar</h2>

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
        <div class="row">
            <div class="col-12">
                <label class="badge bg-success">Total Archivos Excel: {{ count($excelFiles) }}</label>
                @if (!empty($excelFiles))
                    <table class="table table-bordered table-striped dataTable dtr-inline">
                        <thead>
                            <tr>
                                <th>Tipo plantilla</th>
                                <th>Nombre Archivo</th>
                                <th>Numero de Registros</th>
                                <th>Numero de Pdf asociados</th>
                                <th>Seleccionar</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($excelFiles as $row)
                                <tr>
                                    <td>{{ $row['id_plantilla'] }}</td> <!-- Mostrar el id_plantilla -->
                                    <td>{{ $row['file'] }}</td> <!-- Mostrar el nombre del archivo sin extensión -->
                                    <td>{{ $row['n_registros'] }}</td> <!-- Mostrar el nombre del archivo sin extensión -->
                                    <td>{{ $row['n_pdfs'] }}</td> <!-- Mostrar el nombre del archivo sin extensión -->
                                    <td>
                                        <input data-filename="{{ $row['file'] }}" class="btn btn-primary process-btn"
                                            type="button" value="Procesar">
                                    </td>
                                </tr>
                            @endforeach

                        </tbody>
                    </table>
                @else
                    <p>No hay datos en los archivos CSV/XLSX.</p>
                @endif
            </div>
        </div>
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
            const buttons = document.querySelectorAll('.process-btn');
            buttons.forEach(button => {
                button.addEventListener('click', function() {
                    const fileName = this.getAttribute('data-filename');

                    fetch('{{ route('main.store') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({
                                file: fileName
                            })
                        })
                        .then(async response => {
                            const text = await response.text();
                            console.log("Respuesta cruda:", text);
                            if (!response.ok) {
                                throw new Error(text);
                            }
                            return JSON.parse(text); // parsear manualmente
                        })
                        .then(data => {
                            alert('Procesado correctamente');
                            console.log(data);
                        })
                        .catch(error => {
                            alert(error.message);
                            console.error(error);
                        });

                });
            });
        });
    </script>
@stop
