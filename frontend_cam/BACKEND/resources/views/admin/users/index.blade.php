@extends('adminlte::page')

@section('title', 'JDeveloper')
@section('css')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.5.2/css/bootstrap.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/3.0.3/css/responsive.bootstrap4.css">
@stop
@section('content_header')
    <h1>Lista de usuarios</h1>
@stop
@section('content')
    <div class="card card-outline card-primary">
        <div class="card-header">
            <h3 class="card-title">Usuarios registrados</h3>
            <div class="card-tools">
                <a href="{{ route('admin.users.create') }}" class="btn btn-primary">Registrar
                    {{-- <i class="fa-solid fa-plus"></i> --}}
                </a>
            </div>
        </div>
        @if ($users->count())
            <div class="card-body">
                <table id="usuarios" class="table table-striped table-bordered table-hover table-sm">

                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Email</th>
                            <th></th>
                            <th>
                                Activo/Inactivo
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($users as $user)
                            <tr>
                                <td>{{ $user->id }}</td>
                                <td>{{ $user->name }}</td>
                                <td>{{ $user->email }}</td>
                                <td width="10px"><a href="{{ route('admin.users.edit', $user) }}"
                                        class="btn btn-primary"><i class="fas fa-edit"></i></a>
                                </td>
                                <td width="10px" class="text-center">
                                    <form action="{{ route('users.toggleStatus', $user->id) }}" method="POST">
                                        @csrf
                                        @method('PATCH') <!-- Laravel permite cambios parciales con PATCH -->
                                        <button type="submit"
                                            class="btn {{ $user->status ? 'btn-danger' : 'btn-success' }}">
                                            {!! $user->status ? '<i class="fa-solid fa-circle-xmark"></i>' : '<i class="fa-solid fa-square-check"></i>' !!}
                                        </button>

                                    </form>

                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="card-body"><strong> No hay registros</strong></div>
        @endif

    </div>
@stop
@section('css')
    {{-- <link rel="stylesheet" href="/css/admin_custom.css"> --}}
@stop
@section('js')
    <script>
        new DataTable('#usuarios', {
            responsive: true,
            autoWidth: false, //no le vi la funcionalidad
            dom: 'Bfrtip', // Añade el contenedor de botones
            buttons: [
                'copy', 'csv', 'excel', 'pdf', 'print', 'colvis' // Botones que aparecen en la imagen
            ],
            "language": {
                "decimal": "",
                "emptyTable": "No hay datos disponibles en la tabla",
                "info": "Mostrando _START_ a _END_ de _TOTAL_ entradas",
                "infoEmpty": "Mostrando 0 a 0 de 0 entradas",
                "infoFiltered": "(filtrado de _MAX_ entradas totales)",
                "infoPostFix": "",
                "thousands": ",",
                "lengthMenu": "Mostrar _MENU_ entradas",
                "loadingRecords": "Cargando...",
                "processing": "",
                "search": "Buscar:",
                "zeroRecords": "No se encontraron registros coincidentes",
                "paginate": {
                    "first": "Primero",
                    "last": "Último",
                    "next": "Siguiente",
                    "previous": "Anterior"
                },
                "aria": {
                    "orderable": "Ordenar por esta columna",
                    "orderableReverse": "Invertir el orden de esta columna"
                }
            }

        });

        // @if (session('info') && session('icono'))
        //     Swal.fire({
        //         title: "{{ session('title') }}!",
        //         text: "{{ session('info') }}",
        //         icon: "{{ session('icono') }}"
        //     });
        // @endif
    </script>
@stop
