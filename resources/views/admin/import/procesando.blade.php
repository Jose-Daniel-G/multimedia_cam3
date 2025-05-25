@extends('adminlte::page')

@section('title', 'Cargar PDF')

@section('content_header')
    <h1>En proceso</h1>
@stop

@section('content')
    <div class="container mt-4">
        <h2 class="text-center"> {{ $organismo->depe_nomb }} </h2>
        <h2 class="text-center">📂 En proceso</h2>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger">{!! nl2br(e(session('error'))) !!}</div>
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
                <label class="badge bg-success">Total Archivos Excel: <span
                        id="total-excel">{{ $excelCount }}</span></label>

                <table class="table table-bordered table-striped dataTable dtr-inline">
                    <thead>
                        <tr>
                            <th>Tipo plantilla</th>
                            <th>Nombre Archivo</th>
                            <th>Número de Registros</th>
                            <th>Número de PDFs</th>
                            <th>Progreso</th>
                            <th>Estado</th>
                            <th>Observaciones</th>
                            <th>Fecha</th>
                        </tr>
                    </thead>
                    <tbody id="tabla-progreso-body">
                        @php $hayEnCola = false; @endphp

                        @if (!$hayEnCola)
                            <tr>
                                <td colspan="8" class="text-center">No hay procesos en cola</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        $(document).ready(function() {
            // Función para actualizar la tabla
            function actualizarTabla() {
                $.ajax({
                    url: "{{ route('main.procesando.json') }}",
                    method: 'GET',
                    success: function(data) {
                        let html = '';

                        if (data.length === 0) {
                            html =
                                `<tr><td colspan="8" class="text-center">No hay procesos en cola</td></tr>`;
                        } else {
                            data.forEach(row => {
                                if (row.estado !== 'Publicado') {
                                    html += `
                                        <tr>
                                            <td>${row.id_plantilla}</td>
                                            <td>${row.archivo}</td>
                                            <td>${row.n_registros}</td>
                                            <td>${row.n_pdfs}</td>
                                            <td>
                                                <div class="progress">
                                                    <div class="progress-bar bg-info" role="progressbar"
                                                        style="width: ${row.progreso}%; border-radius: 0.5rem; min-width: 2rem;"
                                                        aria-valuenow="${row.progreso}" aria-valuemin="0" aria-valuemax="100">
                                                        ${row.progreso}%
                                                    </div>
                                                </div>
                                            </td>

                                            <td>${row.estado}</td>
                                            <td>${row.observaciones ?? ''}</td>
                                            <td>${row.fecha}</td>
                                        </tr>
                                    `;
                                }
                            });
                        }

                        $('#tabla-progreso-body').html(html);
                        $('#total-excel').text(data.length);
                    },
                    error: function(xhr, status, error) {
                        console.error('Error al actualizar tabla:', error);
                    }
                });
            }

            // Ejecutar cada 5 segundos
            setInterval(actualizarTabla, 2000);
        });
    </script>
@stop
