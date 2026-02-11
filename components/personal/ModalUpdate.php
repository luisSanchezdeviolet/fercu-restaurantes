<div class="modal fade" id="editPersonalModal" tabindex="-1" role="dialog" aria-labelledby="editModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <form id="editForm">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Editar Empleado</h5>
          <button type="button" class="close" data-bs-dismiss="modal">&times;</button>
        </div>
        <div class="d-none" id="editMessage">
        </div>
        <div class="modal-body" id="contenedor_edit">
          <input type="hidden" id="edit_id" name="id">
          <div class="form-group">
            <label>Nombre</label>
            <input type="text" class="form-control" id="edit_name" name="name">
          </div>
          <div class="form-group">
            <label>Email</label>
            <input type="email" class="form-control" id="edit_email" name="email">
          </div>
          <div class="form-group">
            <label>Rol</label>
            <select class="form-control" id="edit_role" name="role">
              <option value="Administrador">Administrador</option>
              <option value="Mesero">Mesero</option>
            </select>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-success">Guardar Cambios</button>
        </div>
      </div>
    </form>
  </div>
</div>

<script src="../../presentation/personal/scripts/personal.min.js"></script>