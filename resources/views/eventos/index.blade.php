@extends('adminlte::page')

@section('title', 'DeveloTech')

@section('content_header')
    <h1>Calendario</h1>
@stop

@section('content')
    <div class="card-header">
        <a class="btn btn-secondary" data-toggle="modal" data-target="#claseModal">Agregar Curso</a>
    </div>
    <div id="calendar"></div> <!-- Contenedor para el calendario -->
    {{-- @include('evento.modal.modal') --}}
    {{-- MODAL --}}
    <div class="modal fade" id="claseModal" tabindex="-1" aria-labelledby="claseModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="claseModalLabel">Fechas</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form action="" autocomplete="off" id="eventoForm" method="POST">
                        @csrf
                        <div class="form-group d-none">
                            <label for="id">ID</label>
                            <input type="text" class="form-control" name="id" id="id"
                                aria-describedby="helpId" placeholder="">
                            <small id="helpId" class="form-text text-muted">Help text</small>
                        </div>
                        <div class="form-group">
                            <label for="title">Clase</label>
                            <input type="text" class="form-control" name="title" id="title"
                                placeholder="Agenda la clase">
                        </div>
                        <div class="form-group">
                            <label for="descripcion">Descripción del evento</label>
                            <textarea class="form-control" name="descripcion" id="descripcion"></textarea>
                        </div>
                        <div class="form-group d-none">
                            <label for="start">Inicio</label>
                            <input type="date" class="form-control" name="start" id="start">
                        </div>
                        <div class="form-group d-none">
                            <label for="end">Fin</label>
                            <input type="date" class="form-control" name="end" id="end">
                        </div>


                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-success me-2" id="btnGuardar">Guardar</button>
                    <button type="button" class="btn btn-warning me-2" id="btnUpdate">Modificar</button>
                    <button type="button" class="btn btn-danger me-2" id="btnEliminar">Eliminar</button>
                    <button type="button" class="btn btn-secondary me-2" data-dismiss="modal">cerrar</button>
                </div>
            </div>
        </div>
    </div>

@stop

@section('css')
    {{-- FullCalendar CSS --}}
    {{-- <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.css" rel="stylesheet"> --}}
@stop

@section('js')
    {{-- Axios JS --}}
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>

    {{-- FullCalendar JS --}}
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>

    {{-- Script para inicializar el calendario --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            //let formulario = document.querySelector('#eventoForm');
            let form = document.getElementById('eventoForm');
            var calendarEl = document.getElementById('calendar');
            if (calendarEl) {
                var calendar = new FullCalendar.Calendar(calendarEl, {
                    initialView: 'dayGridMonth',
                    locale: 'es',
                    displayEventTime: false,
                    headerToolbar: {
                        left: 'prev,next today',
                        center: 'title',
                        right: 'dayGridMonth,timeGridWeek,listWeek' // Botones de vistas
                    },
                    events: "{{ route('admin.eventos.show') }}",
                    dateClick: function(info) {
                        form.reset();
                        form.start.value = info.dateStr;
                        form.end.value = info.dateStr;
                        $("#claseModal").modal("show");
                    },
                    eventClick: function(info) {
                        var evento = info.event;
                        var id = info.event.id;

                        // Construye la URL con el ID del evento
                        //const url = `http://laravel9.test/eventos/edit/${id}`;
                        const url = "{{ route('admin.eventos.edit', ':id') }}".replace(':id', id);
                        axios.post(url)
                            .then((response) => {
                                //console.log(response.data); // Verifica qué está devolviendo el servidor
                                let formulario = document.getElementById('eventoForm');

                                const data = response.data;

                                formulario.id.value = data.id;
                                formulario.title.value = data.title;
                                formulario.descripcion.value = data.descripcion;
                                formulario.start.value = formatDateForInput(evento.start);
                                formulario.end.value = formatDateForInput(evento.end);

                                $("#claseModal").modal("show");
                            })
                    }
                });
                calendar.render();

                document.getElementById("btnGuardar").addEventListener("click", function() {
                    let formulario = document.getElementById('eventoForm');
                    const datos = {
                        id: formulario.id.value,
                        title: formulario.title.value,
                        descripcion: formulario.descripcion.value,
                        start: formulario.start.value,
                        end: formulario.end.value
                    };
                    const url = "{{ route('admin.eventos.store') }}";
                    sendData(url, datos);
                });

                document.getElementById("btnEliminar").addEventListener("click", function() {
                    var id = form.id.value;
                    const url = "{{ route('admin.eventos.destroy', ':id') }}".replace(':id',
                        id); // Ruta para eliminar
                    sendData(url, {
                        _method: 'DELETE',
                        id: id
                    });
                });

                document.getElementById("btnUpdate").addEventListener("click", function() {
                    let formulario = document.getElementById('eventoForm');
                    const datos = {
                        _method: 'PUT', // Laravel espera una solicitud PUT para actualizar
                        title: formulario.title.value,
                        descripcion: formulario.descripcion.value,
                        start: formulario.start.value,
                        end: formulario.end.value
                    };

                    const url = "{{ route('admin.eventos.update', ':id') }}".replace(':id', formulario.id
                        .value);

                    sendData(url, datos);
                });

                function sendData(url, datos) {
                    axios.post(url, datos, {
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                                'content') // CSRF token para Laravel
                        }
                    }).then((respuesta) => {
                        calendar.refetchEvents(); // Recargar eventos del calendario
                        $("#claseModal").modal("hide"); // Cerrar el modal
                    }).catch(error => {
                        if (error.response) {
                            console.log(error.response.data); // Manejo de errores
                        }
                    });
                }

            }
        });
        // Función de formato de fecha
        function formatDateForInput(dateTime) {
            const date = new Date(dateTime);
            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const day = String(date.getDate()).padStart(2, '0');
            return `${year}-${month}-${day}`;
        }
    </script>
    {{-- Script para inicializar el calendario --}}
@stop
