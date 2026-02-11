<div class="modal fade" id="deleteModalCategoria" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form id="deleteForm">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Eliminar Categoria</h5>
                    <button type="button" class="close" data-bs-dismiss="modal">&times;</button>
                </div>
                <div class="d-none" id="deleteMessage">
                </div>
                <div class="modal-body" id="contenedor_delete">
                    <input type="hidden" id="delete_id" name="id">
                    <p>¿Estás seguro de que deseas eliminar esta categoria?</p>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-danger">Eliminar</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script src="../../presentation/categorias/ui/categorias.min.js"></script>