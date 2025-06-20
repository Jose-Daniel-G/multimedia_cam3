@extends('adminlte::page')

@section('title', 'Dashboard')
@section('css')

@stop

@section('content_header')
    {{-- <h1>Sistema de reservas </h1> --}}
@stop

@section('content')
    <div class="container-fluid mt-4 bg-white shadow p-4 rounded border">



        @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
        <div class="d-flex justify-content-between align-items-center bg-light p-3 rounded border">
            <!-- Avatar -->
            <div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center text-white fs-4 fw-bold"
                style="width: 60px; height: 60px;">Ad</div>

            <!-- Mensaje de bienvenida -->
            <div>
                <p class="fs-5">Bienvenido, iniciaste sesión como: <span class="fw-bold">Admin</span></p>
            </div>

            <!-- Información adicional -->
            <div class="d-flex bg-white p-3 rounded border align-items-center">
                <p class="fw-semibold me-2">Último registro: </p>
                <input value="{{ optional(auth()->user()->lastSuccessfulLoginAt())->format('Y-m-d') }}" type="date"
                    class="form-control me-3" style="width: 150px;" disabled>

                <p class="fw-semibold me-2">Último proceso:</p>
                <input value="{{ optional(auth()->user()->lastSuccessfulLoginAt())->format('Y-m-d') }}" type="date"
                    class="form-control me-3" style="width: 150px;" disabled>

                <p class="fw-semibold me-2">Otro proceso:</p>
                <input type="date" class="form-control" style="width: 150px;" disabled>
            </div>

        </div>
        <main class="mt-4">
            <div class="row">
                {{-- <div class="col-lg-3 col-6">
                    <div class="small-box bg-warning">
                        <div class="inner">
                            <h3></h3>
                            <p>Crear nuevo usuario</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-users mr-2"></i>
                        </div>
                        <a href="" class="small-box-footer">Mas info <i class="fas fa-arrow-circle-right"></i></a>
                    </div>
                </div>

                <div class="col-lg-3 col-6">
                    <div class="small-box bg-danger">
                        <div class="inner">
                            <h3></h3>
                            <p>Cambiar rol a usuario</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-book"></i>
                        </div>
                        <a href="" class="small-box-footer">Mas info <i class="fas fa-arrow-circle-right"></i></a>
                    </div>
                </div> --}}

                <div class="col-lg-3 col-6">
                    <div class="small-box bg-danger">
                        <div class="inner">
                            <h3></h3>
                            <p>Realizar publicación masiva</p>
                        </div>
                        <div class="icon">
                            <i class="ion fas bi bi-person-lines-fill"></i>
                        </div>
                        <a href="{{ route('main.index') }}" class="small-box-footer">Mas info <i
                                class="fas fa-arrow-circle-right"></i></a>
                    </div>
                </div>

                <div class="col-lg-3 col-6">
                    <div class="small-box bg-secondary">
                        <div class="inner">
                            <h3></h3>
                            <p>Administrar Usuarios</p>
                        </div>
                        <div class="icon">
                            <i class="ion fas bi bi-calendar2-week"></i>
                        </div>
                        <a href="{{ route('admin.users.index') }}" class="small-box-footer">Mas info <i
                                class="fas fa-arrow-circle-right"></i></a>
                    </div>
                </div>
                {{-- <div class="col-lg-3 col-6">
                    <div class="small-box bg-success">
                        <div class="inner">
                            <h3></h3>
                            <p>Consultar últimos procesos</p>
                        </div>
                        <div class="icon">
                            <i class="fa-regular fa-circle-check"></i>
                        </div>
                        <a href="" class="small-box-footer">Mas info <i class="fas fa-arrow-circle-right"></i></a>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-warning">
                        <div class="inner">
                            <h3></h3>
                            <p>Gestionar lista de usuarios</p>
                        </div>
                        <div class="icon">
                            <i class="ion fas bi bi-person-lines-fill"></i>
                        </div>
                        <a href="" class="small-box-footer">Mas info <i class="fas fa-arrow-circle-right"></i></a>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-success">
                        <div class="inner">
                            <h3></h3>
                            <p>Realizar una publicación</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-laptop"></i>
                        </div>
                        <a href="   " class="small-box-footer">Mas info <i class="fas fa-arrow-circle-right"></i></a>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-primary">
                        <div class="inner">
                            <h3></h3>
                            <p>Administrar Bases de datos</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-laptop"></i>
                        </div>
                        <a href="" class="small-box-footer">Mas info <i class="fas fa-arrow-circle-right"></i></a>
                    </div>
                </div> --}}

            </div>
        </main>


    </div>


@stop

@section('js')
    @if (session('error'))
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: `{!! addslashes(session('error')) !!}`,
            });
        </script>
    @endif

    @if ($errors->any())
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Errores de validación',
                html: `{!! implode('<br>', $errors->all()) !!}`,
            });
        </script>
    @endif
@endsection


