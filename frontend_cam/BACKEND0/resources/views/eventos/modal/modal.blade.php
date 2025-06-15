<div class="modal fade" id="claseModal" tabindex="-1" aria-labelledby="claseModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="claseModalLabel">Crear nuevo Curso</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="createCursoForm" action="{{ route('admin.cursos.store') }}" autocomplete="off" method="POST">
                    @csrf
                    <div class="form-group">
                        <label for="id">ID</label>
                        <input type="text" class="form-control" name="id" id="id"
                            aria-describedby="helpId" placeholder="">
                        <small id="helpId" class="form-text text-muted">Help text</small>
                    </div>
                    <div class="form-group">
                        <label for="title">Clase</label>
                        <input type="text" class="form-control" name="title" id="title"
                            aria-describedby="helpId" placeholder="Agenda la clase">
                        <small id="helpId" class="form-text text-muted">Help text</small>
                    </div>
                    <div class="form-group">
                        <label for="descripcion">Descripcion del evento</label>
                        <textarea class="form-control" name="descripcion" id="descripcion"></textarea>
                        <small id="helpId" class="form-text text-muted">Help text</small>
                    </div>
                    <div class="form-group">
                        <label for="">Start</label>
                        <input type="text" class="form-control" name="" id=""
                            aria-describedby="helpId" placeholder="">
                        <small id="helpId" class="form-text text-muted">Help text</small>
                    </div>
                    <div class="form-group">
                        <label for="end">End</label>
                        <input type="text" class="form-control" name="end" id="end"
                            aria-describedby="helpId" placeholder="">
                        <small id="helpId" class="form-text text-muted">Help text</small>
                    </div>
                    <button type="button" class="btn btn-success me-2" id="btnGuardar">Guardar</button>
                    <button type="button" class="btn btn-warning me-2" id="btnModificar">Modificar</button>
                    <button type="button" class="btn btn-danger me-2" id="btnEliminar">Eliminar</button>
                    <button type="button" class="btn btn-secondary me-2" data-dismiss="modal">cerrar</button>
                </form>
            </div>
        </div>
    </div>
</div>
