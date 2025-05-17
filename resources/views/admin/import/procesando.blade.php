@extends('adminlte::page')

@section('title', 'Cargar PDF')

@section('content_header')
    <h1>En proceso</h1>
@stop

@section('content')
    <div class="container mt-4">
        <h2 class="text-center"> {{ $organismo->depe_nomb }} </h2>
        <h2 class="text-center">📂En proceso</h2>

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
                                <th>Progreso</th>
                                <th>Estado</th>
                                <th>Observaciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($excelFiles as $index => $row)
                                <tr>
                                    <td>{{ $row['id_plantilla'] }}</td> <!-- Mostrar el id_plantilla -->
                                    <td>{{ $row['file'] }}</td> <!-- Mostrar el nombre del archivo sin extensión -->
                                    <td>{{ $row['n_registros'] }}</td> <!-- Mostrar el nombre del archivo sin extensión -->
                                    <td>{{ $row['n_pdfs'] }}</td> <!-- Mostrar el nombre del archivo sin extensión -->
                                    <td>
                                        {{-- {{  $index }}% --}}

                                            <div class="progress" role="progressbar" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100">
                                                <div class="progress-bar" style="width: {{$index+1}}0%; border-radius: 0.5rem;"> {{$index}}%</div>
                                            </div>
                                    </td> <!-- Mostrar el nombre del archivo sin extensión -->
                                    <td> Publicado(cuando el archivo procesado con 0 errores) </td> <!-- Mostrar el nombre del archivo sin extensión -->
                                    <td> {{ date(now()) }} </td> <!-- Mostrar el nombre del archivo sin extensión -->
                                </tr>
                            @endforeach
                            <tr>
                                <td>{{ $row['id_plantilla'] }}</td> <!-- Mostrar el id_plantilla -->
                                <td>{{ $row['file'] }}</td> <!-- Mostrar el nombre del archivo sin extensión -->
                                <td>{{ $row['n_registros'] }}</td> <!-- Mostrar el nombre del archivo sin extensión -->
                                <td>{{ $row['n_pdfs'] }}</td> <!-- Mostrar el nombre del archivo sin extensión -->
                                <td>
                                    {{-- {{  $index }}% --}}
                                    <div class="progress" role="progressbar" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100">
                                        <div class="progress-bar" style="width: 100%;border-radius: 0.5rem;"> 100%</div>
                                    </div>

                                </td> <!-- Mostrar el nombre del archivo sin extensión -->
                                <td> Publicado(cuando el archivo procesado con 0 errores) </td>
                                <!-- Mostrar el nombre del archivo sin extensión -->
                                <td> {{ date(now()) }} </td> <!-- Mostrar el nombre del archivo sin extensión -->
                            </tr>
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

    <script>
        $(document).ready(function() {
            $('#delete').on('click', function(e) {
                e.preventDefault();

                Swal.fire({
                    title: '¿Estás seguro?',
                    text: "¡No podrás revertir esto!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Sí, eliminarlo',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        Swal.fire(
                            'Eliminado',
                            'El archivo ha sido eliminado.',
                            'success'
                        );
                        // Aquí puedes enviar una solicitud o redirigir a una ruta de eliminación
                        // window.location.href = $(this).data('url');
                    }
                });
            });
        });
    </script>
@stop

