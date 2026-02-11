<!-- Modal de Registro de Demo -->
<div class="modal fade" id="demoModal" tabindex="-1" aria-labelledby="demoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="demoModalLabel">
                    <i class="bi bi-gift-fill me-2"></i>
                    Solicita tu Demo Gratuita por 15 Días
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info">
                    <i class="bi bi-info-circle-fill me-2"></i>
                    <strong>¡15 días gratis!</strong> Prueba completa sin tarjeta de crédito. Si te gusta, elige un plan después.
                </div>

                <form id="demoForm" class="needs-validation" novalidate>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="nombre" class="form-label">Tu Nombre *</label>
                            <input type="text" class="form-control" id="nombre" name="nombre" 
                                   placeholder="Ej: Juan" required>
                            <div class="invalid-feedback">
                                Por favor ingresa tu nombre.
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label for="apellido" class="form-label">Apellido *</label>
                            <input type="text" class="form-control" id="apellido" name="apellido" 
                                   placeholder="Ej: Pérez" required>
                            <div class="invalid-feedback">
                                Por favor ingresa tu apellido.
                            </div>
                        </div>

                        <div class="col-md-12">
                            <label for="empresa" class="form-label">Nombre de tu Restaurante *</label>
                            <input type="text" class="form-control" id="empresa" name="empresa" 
                                   placeholder='Ej: Restaurante "La Cocina"' required>
                            <div class="invalid-feedback">
                                Por favor ingresa el nombre de tu restaurante.
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label for="email" class="form-label">Email *</label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   placeholder="tu@email.com" required>
                            <div class="invalid-feedback">
                                Por favor ingresa un email válido.
                            </div>
                            <small class="text-muted">Te enviaremos tus credenciales aquí</small>
                        </div>

                        <div class="col-md-6">
                            <label for="telefono" class="form-label">Teléfono de Contacto *</label>
                            <input type="tel" class="form-control" id="telefono" name="telefono" 
                                   placeholder="Ej: 3312345678" required>
                            <div class="invalid-feedback">
                                Por favor ingresa tu teléfono.
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label for="giro" class="form-label">Tipo de Cocina</label>
                            <select class="form-select" id="giro" name="giro">
                                <option value="">Selecciona una opción</option>
                                <option value="Mexicana">Comida Mexicana</option>
                                <option value="Italiana">Comida Italiana</option>
                                <option value="Japonesa">Comida Japonesa / Sushi</option>
                                <option value="China">Comida China</option>
                                <option value="Fast Food">Fast Food / Comida Rápida</option>
                                <option value="Mariscos">Mariscos / Pescados</option>
                                <option value="Parrilla">Parrilla / Carnes</option>
                                <option value="Tacos">Tacos / Antojitos</option>
                                <option value="Cafetería">Cafetería / Café</option>
                                <option value="Pizzería">Pizzería</option>
                                <option value="Hamburguesería">Hamburguesería</option>
                                <option value="Vegetariana">Vegetariana / Vegana</option>
                                <option value="Otro">Otro</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label for="empleados" class="form-label">¿Cuántos empleados tienes?</label>
                            <select class="form-select" id="empleados" name="empleados">
                                <option value="">Selecciona una opción</option>
                                <option value="Solo yo">Solo yo</option>
                                <option value="2-5">2-5 empleados</option>
                                <option value="6-10">6-10 empleados</option>
                                <option value="11-20">11-20 empleados</option>
                                <option value="21+">Más de 20 empleados</option>
                            </select>
                        </div>
                    </div>

                    <div class="mt-4">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="terminos" required>
                            <label class="form-check-label" for="terminos">
                                Acepto los <a href="#" class="text-primary">términos y condiciones</a> y la política de privacidad *
                            </label>
                            <div class="invalid-feedback">
                                Debes aceptar los términos y condiciones.
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" form="demoForm" class="btn btn-primary">
                    <i class="bi bi-check-circle-fill me-2"></i>
                    Solicitar Demo Gratuita
                </button>
            </div>
        </div>
    </div>
</div>


