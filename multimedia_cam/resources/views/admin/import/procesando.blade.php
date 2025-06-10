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
                @if (!empty($excelFiles))
                <label class="badge bg-success">Total Archivos Excel: {{ count($excelFiles) }}</label>

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
                            @php $hayEnCola = false; @endphp
                            @foreach ($excelFiles as $index => $row)
                                @if ($row['estado'] != 'Publicado')
                                    @php $hayEnCola = true; @endphp
                                    <tr>
                                        <td>{{ $row['id_plantilla'] }}</td> <!-- Mostrar el id_plantilla -->
                                        <td>{{ $row['file'] }}</td>
                                        <td>{{ $row['n_registros'] }}</td>
                                        <td>{{ $row['n_pdfs'] }}</td>
                                        <td>
                                            {{-- {{  $index }}% --}}
                                            <div class="progress">
                                                <div class="progress-bar" role="progressbar"
                                                    style="width:100%; border-radius: 0.5rem;" aria-valuenow="100%"
                                                    aria-valuemin="0" aria-valuemax="100">
                                                    {{ $row['estado'] }}
                                                </div>
                                            </div>
                                        </td>
                                        <td> {{ $row['porcentaje'] }}</td>
                                        <td> {{ date(now()) }} </td>
                                        <td> <i class="fa-solid fa-trash text-danger" id="delete" aria-hidden="true"></i>
                                        </td>
                                    </tr>
                                @endif
                            @endforeach
                            @if (!$hayEnCola)
                                <tr>
                                    <td colspan="8" class="text-center">No hay procesos en cola</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                @else
                    <p class="text-center">No hay procesos en cola</p>
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
