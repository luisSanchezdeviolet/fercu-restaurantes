<!-- Modal de Planes -->
<div class="modal fade" id="modal-plan" tabindex="-1" aria-labelledby="modalPlanLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modal-titulo">Inscripción al Plan</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="modal-content-wrapper">
                    <div class="text-center mb-4">
                        <h4 id="modal-precio" class="text-primary">Precio: $0.00</h4>
                        <p id="modal-ahorro" class="text-success fw-bold" style="display: none;"></p>
                        <p id="modal-descripcion" class="text-muted"></p>
                    </div>

                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        <strong>¿Ya tienes cuenta?</strong> Inicia sesión para contratar este plan.
                    </div>

                    <div class="d-grid gap-2">
                        <button type="button" class="btn btn-primary btn-lg" id="btn-login">
                            <i class="bi bi-box-arrow-in-right me-2"></i>
                            Ya tengo cuenta - Iniciar Sesión
                        </button>
                        <button type="button" class="btn btn-success btn-lg" id="btn-registrar">
                            <i class="bi bi-person-plus-fill me-2"></i>
                            Aún no tengo cuenta - Registrarme
                        </button>
                    </div>

                    <!-- Formulario de Login (inicialmente oculto) -->
                    <div id="form-login" style="display: none;" class="mt-4">
                        <hr>
                        <h5 class="text-center mb-3">Iniciar Sesión</h5>
                        <form id="loginForm">
                            <div class="mb-3">
                                <label for="loginEmail" class="form-label">Email</label>
                                <input type="email" class="form-control" id="loginEmail" required>
                            </div>
                            <div class="mb-3">
                                <label for="loginPassword" class="form-label">Contraseña</label>
                                <input type="password" class="form-control" id="loginPassword" required>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">
                                Iniciar Sesión y Contratar
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


