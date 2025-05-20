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
                    <table class="table table-bordered table-striped dataTable dtr-inline table-hover">
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
                                <tr id="fila-{{ Str::slug($row['file']) }}">
                                    <td>{{ $row['plantilla'] }}</td>
                                    <td>{{ $row['file'] }} </td>
                                    <td>{{ $row['n_registros'] }} </td>
                                    <td>{{ $row['n_pdfs'] }} </td>
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
    {{--
<link rel="stylesheet" href="/css/admin_custom.css"> --}}
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
                                try {
                                    const errorData = JSON.parse(text);
                                    console.log("Datos del error parseados:", errorData);
                                    let errorMessage = '';
                                    if (errorData.errors) {
                                        errorMessage = Object.keys(errorData.errors).map(
                                            key => key).join('<br>');
                                    }
                                    const errorTitle = errorData.title ||
                                        'Error'; // Usar el título del JSON o un título genérico
                                    return Promise.reject({
                                        title: errorTitle,
                                        message: errorMessage
                                    });
                                } catch (e) {
                                    console.error("Error al parsear JSON:", e);
                                    return Promise.reject({
                                        title: 'Error',
                                        message: text ||
                                            "Ocurrió un error inesperado."
                                    });
                                }
                            }
                            return JSON.parse(text);
                        })
                        .then(data => {
                            if (data.error) {
                                return Promise.reject({
                                    title: 'Error',
                                    message: data.error
                                });
                            }

                            // Si no hay error, mostrar éxito
                            Swal.fire({
                                title: 'Éxito',
                                text: data.success || 'Operación realizada con éxito',
                                icon: 'success'
                            });

                            // Aquí eliminas la fila o lo que necesites
                            const slug = fileName.toLowerCase().replace(/\s+/g, '-').replace(
                                /[^a-z0-9\-]/g, '');
                            const fila = document.getElementById('fila-' + slug);
                            if (fila) {
                                fila.remove();
                            }

                            console.log(data);
                        }).catch (error => {
                        Swal.fire({
                            title: error.title ||
                            'Error', // Usar el título del objeto de error o un título genérico
                            html: error.message,
                            icon: 'error'
                        });
                        console.error(error);
                    });
                });
            });
        });
    </script>
@stop
