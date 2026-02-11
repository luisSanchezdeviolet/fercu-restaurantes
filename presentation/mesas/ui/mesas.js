class MesasUI {
    constructor() {
        this.mesaCRUD = new MesaCRUD();
        this.currentPage = 1;
        this.itemsPerPage = 10;
        this.currentFilter = 'todos';
        this.searchTerm = '';
        this.editingMesaId = null;
        
        this.insertStyles();
    }

    insertStyles() {
        const style = document.createElement('style');
        style.textContent = toastStyles;
        document.head.appendChild(style);
    }

    async init() {
        try {
            this.setupEventListeners();
            await this.loadMesas();
            this.renderFilters();
            await this.renderEstadisticas();
        } catch (error) {
            console.error('Error al inicializar MesasUI:', error);
            this.showNotification('Error al inicializar la interfaz', 'error');
        }
    }

    setupEventListeners() {
        const form = document.querySelector('.needs-validation');
        if (form) {
            form.addEventListener('submit', (e) => this.handleFormSubmit(e));
        }

        const inputs = document.querySelectorAll('#numero_mesa, #mesa_capacidad, #mesa_estado');
        inputs.forEach(input => {
            input.addEventListener('input', () => this.validateInput(input));
            input.addEventListener('blur', () => this.validateInput(input));
        });
    }

    validateInput(input) {
        const isValid = input.checkValidity();
        
        if (isValid) {
            input.classList.remove('is-invalid');
            input.classList.add('is-valid');
        } else {
            input.classList.remove('is-valid');
            input.classList.add('is-invalid');
        }
    }

    async handleFormSubmit(e) {
        e.preventDefault();
        
        const form = e.target;
        const isValid = form.checkValidity();
        
        form.classList.add('was-validated');
        
        if (!isValid) {
            this.showNotification('Por favor completa todos los campos correctamente', 'error');
            return;
        }

        const formData = this.getFormData();
        
        try {
            let result;
            if (this.editingMesaId) {
                result = await this.mesaCRUD.update(this.editingMesaId, formData);
            } else {
                result = await this.mesaCRUD.create(formData);
            }

            if (result.success) {
                this.resetForm();
                await this.loadMesas();
                await this.renderEstadisticas();
            }
        } catch (error) {
            console.error('Error al guardar mesa:', error);
            this.showNotification('Error al guardar la mesa', 'error');
        }
    }

    getFormData() {
        return {
            numero_mesa: parseInt(document.getElementById('numero_mesa').value),
            asientos: parseInt(document.getElementById('mesa_capacidad').value),
            estado: document.getElementById('mesa_estado').value
        };
    }

    resetForm() {
        const form = document.querySelector('.needs-validation');
        if (form) {
            form.reset();
            form.classList.remove('was-validated');
            
            const inputs = form.querySelectorAll('.form-control');
            inputs.forEach(input => {
                input.classList.remove('is-valid', 'is-invalid');
            });
        }

        this.editingMesaId = null;
        this.updateFormButton();
    }

    updateFormButton() {
        const button = document.querySelector('button[type="submit"]');
        if (button) {
            button.textContent = this.editingMesaId ? 'Actualizar Mesa' : 'Agregar Mesa';
            button.className = this.editingMesaId ? 'btn btn-warning' : 'btn btn-primary';
        }
    }

    renderFilters() {
        const filtrosContainer = document.getElementById('filtros');
        if (!filtrosContainer) return;

        filtrosContainer.innerHTML = `
            <div class="row mb-3">
                <div class="col-md-4">
                    <label for="filtro-estado">Filtrar por Estado:</label>
                    <select id="filtro-estado" class="form-control">
                        <option value="todos">Todos</option>
                        <option value="disponible">Disponible</option>
                        <option value="ocupada">Ocupada</option>
                        <option value="reservada">Reservada</option>
                    </select>
                </div>
                <div class="col-md-4 mt-3 mt-md-0">
                    <label for="buscar-mesa">Buscar Mesa:</label>
                    <input type="text" id="buscar-mesa" class="form-control" placeholder="Número de mesa o capacidad">
                </div>
                <div class="col-md-4">
                    <label>&nbsp;</label>
                    <div class="d-flex">
                        <button id="btn-limpiar-filtros" class="btn btn-secondary">Limpiar</button>
                    </div>
                </div>
            </div>
        `;

        document.getElementById('filtro-estado').addEventListener('change', (e) => {
            this.currentFilter = e.target.value;
            this.currentPage = 1;
            this.loadMesas();
        });

        document.getElementById('buscar-mesa').addEventListener('input', (e) => {
            this.searchTerm = e.target.value;
            this.currentPage = 1;
            this.loadMesas();
        });

        document.getElementById('btn-limpiar-filtros').addEventListener('click', () => {
            this.currentFilter = 'todos';
            this.searchTerm = '';
            this.currentPage = 1;
            document.getElementById('filtro-estado').value = 'todos';
            document.getElementById('buscar-mesa').value = '';
            this.loadMesas();
        });
    }

    async loadMesas() {
        try {
            let mesas = await this.mesaCRUD.findAll();
            
            if (this.currentFilter !== 'todos') {
                mesas = mesas.filter(mesa => mesa.estado.toLowerCase() === this.currentFilter);
            }

            if (this.searchTerm) {
                mesas = mesas.filter(mesa => 
                    mesa.numero_mesa.toString().includes(this.searchTerm) ||
                    mesa.asientos.toString().includes(this.searchTerm)
                );
            }

            this.renderMesas(mesas);
            this.renderPaginacion(mesas.length);
        } catch (error) {
            console.error('Error al cargar mesas:', error);
            this.showNotification('Error al cargar las mesas', 'error');
        }
    }

    renderMesas(mesas) {
        const container = document.getElementById('mesa-lista');
        if (!container) return;

        if (mesas.length === 0) {
            container.innerHTML = `
                <div class="alert alert-info">
                    <i class="fa fa-information"></i>
                    No se encontraron mesas con los criterios seleccionados.
                </div>
            `;
            return;
        }

        const startIndex = (this.currentPage - 1) * this.itemsPerPage;
        const endIndex = startIndex + this.itemsPerPage;
        const mesasPaginadas = mesas.slice(startIndex, endIndex);

        const tableHTML = `
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead class="thead-dark">
                        <tr>
                            <th>Número</th>
                            <th>Capacidad</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${mesasPaginadas.map(mesa => this.renderMesaRow(mesa)).join('')}
                    </tbody>
                </table>
            </div>
        `;

        container.innerHTML = tableHTML;
    }

    renderMesaRow(mesa) {
        const estadoClass = {
            'Disponible': 'success',
            'Ocupada': 'danger',
            'Reservada': 'warning'
        }[mesa.estado] || 'secondary';

        const estadoAcciones = this.getEstadoAcciones(mesa);

        return `
            <tr>
            <td><strong>Mesa ${mesa.numero_mesa}</strong></td>
            <td>${mesa.asientos} personas</td>
            <td>
                <span class="badge bg-${estadoClass}">
                    ${mesa.estado}
                </span>
            </td>
            <td>
                <div class="btn-group" role="group">
                <button class="btn btn-sm btn-info" onclick="mesasUI.editMesa(${mesa.id})" title="Editar">
                    <i class="fa fa-pencil-alt"></i>
                </button>
                ${estadoAcciones
                    .replace(/mdi mdi-account-multiple/g, 'fa fa-users')
                    .replace(/mdi mdi-clock/g, 'fa fa-clock')
                    .replace(/mdi mdi-check/g, 'fa fa-check')
                }
                <button class="btn btn-sm btn-danger" onclick="mesasUI.showDeleteModal(${mesa.id})" title="Eliminar">
                    <i class="fa fa-trash"></i>
                </button>
                </div>
            </td>
            </tr>
        `;
    }

    getEstadoAcciones(mesa) {
        switch (mesa.estado.toLowerCase()) {
            case 'disponible':
                return `
                    <button class="btn btn-sm btn-success" onclick="mesasUI.ocuparMesa(${mesa.id})" title="Ocupar">
                        <i class="fa fa-users"></i>
                    </button>
                    <button class="btn btn-sm btn-warning" onclick="mesasUI.reservarMesa(${mesa.id})" title="Reservar">
                        <i class="fa fa-clock"></i>
                    </button>
                `;
            case 'ocupada':
                return `
                    <button class="btn btn-sm btn-secondary" onclick="mesasUI.liberarMesa(${mesa.id})" title="Liberar">
                        <i class="fa fa-check"></i>
                    </button>
                `;
            case 'reservada':
                return `
                    <button class="btn btn-sm btn-success" onclick="mesasUI.ocuparMesa(${mesa.id})" title="Ocupar">
                        <i class="fa fa-users"></i>
                    </button>
                    <button class="btn btn-sm btn-secondary" onclick="mesasUI.liberarMesa(${mesa.id})" title="Liberar">
                        <i class="fa fa-check"></i>
                    </button>
                `;
            default:
                return '';
        }
    }

    renderPaginacion(totalItems) {
        const container = document.getElementById('paginacion');
        if (!container) return;

        const totalPages = Math.ceil(totalItems / this.itemsPerPage);
        
        if (totalPages <= 1) {
            container.innerHTML = '';
            return;
        }

        let paginationHTML = `
            <nav aria-label="Paginación de mesas">
                <ul class="pagination justify-content-center">
        `;

        paginationHTML += `
            <li class="page-item ${this.currentPage === 1 ? 'disabled' : ''}">
                <a class="page-link" href="#" onclick="mesasUI.changePage(${this.currentPage - 1})">
                    Anterior
                </a>
            </li>
        `;

        for (let i = 1; i <= totalPages; i++) {
            if (i === 1 || i === totalPages || (i >= this.currentPage - 2 && i <= this.currentPage + 2)) {
                paginationHTML += `
                    <li class="page-item ${i === this.currentPage ? 'active' : ''}">
                        <a class="page-link" href="#" onclick="mesasUI.changePage(${i})">${i}</a>
                    </li>
                `;
            } else if (i === this.currentPage - 3 || i === this.currentPage + 3) {
                paginationHTML += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
            }
        }

        paginationHTML += `
            <li class="page-item ${this.currentPage === totalPages ? 'disabled' : ''}">
                <a class="page-link" href="#" onclick="mesasUI.changePage(${this.currentPage + 1})">
                    Siguiente
                </a>
            </li>
        `;

        paginationHTML += `
                </ul>
            </nav>
            <div class="text-center mt-2">
                <small class="text-muted">
                    Mostrando ${((this.currentPage - 1) * this.itemsPerPage) + 1} - 
                    ${Math.min(this.currentPage * this.itemsPerPage, totalItems)} de ${totalItems} mesas
                </small>
            </div>
        `;

        container.innerHTML = paginationHTML;
    }

    async renderEstadisticas() {
        const container = document.getElementById('mesa-estadisticas');
        if (!container) return;

        try {
            const stats = await this.mesaCRUD.getEstadisticas();
            
            container.innerHTML = `
                <div class="mt-5 mt-md-0 mb-4">
                    <h3 class="text-center mb-4">Estadísticas de Mesas</h3>
                </div>
                <div class="row">
                    <div class="col-md-3">
                        <div class="card bg-primary text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h4 class="mb-0">${stats.total}</h4>
                                        <p class="mb-0">Total Mesas</p>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="fa fa-table h2 mb-0"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-success text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h4 class="mb-0">${stats.disponibles}</h4>
                                        <p class="mb-0">Disponibles</p>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="fa fa-check-circle h2 mb-0"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-danger text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h4 class="mb-0">${stats.ocupadas}</h4>
                                        <p class="mb-0">Ocupadas</p>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="fa fa-users h2 mb-0"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-warning text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h4 class="mb-0">${stats.reservadas}</h4>
                                        <p class="mb-0">Reservadas</p>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="fa fa-clock h2 mb-0"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">Ocupación del Restaurante</h5>
                                <div class="progress mb-2" style="height: 25px;">
                                    <div class="progress-bar bg-info" role="progressbar" style="width: ${stats.porcentajeOcupacion}%">
                                        ${stats.porcentajeOcupacion}%
                                    </div>
                                </div>
                                <small class="text-muted">
                                    ${stats.ocupadas + stats.reservadas} de ${stats.total} mesas en uso
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        } catch (error) {
            console.error('Error al renderizar estadísticas:', error);
            container.innerHTML = `
                <div class="alert alert-warning">
                    <i class="fa fa-exclamation-triangle"></i>
                    Error al cargar las estadísticas
                </div>
            `;
        }
    }

    changePage(page) {
        if (page < 1) return;
        this.currentPage = page;
        this.loadMesas();
    }

    async editMesa(id) {
        try {
            const mesa = await this.mesaCRUD.findById(id);
            if (mesa) {
                document.getElementById('numero_mesa').value = mesa.numero_mesa;
                document.getElementById('mesa_capacidad').value = mesa.asientos;
                document.getElementById('mesa_estado').value = mesa.estado;
                
                this.editingMesaId = id;
                this.updateFormButton();
                
                document.querySelector('.needs-validation').scrollIntoView({ behavior: 'smooth' });
            }
        } catch (error) {
            console.error('Error al editar mesa:', error);
            this.showNotification('Error al cargar los datos de la mesa', 'error');
        }
    }

    showDeleteModal(id) {
        let existingModal = document.getElementById('modalEliminarMesa');
        if (existingModal) existingModal.remove();

        const modalHTML = `
        <div class="modal fade" id="modalEliminarMesa" tabindex="-1" aria-labelledby="modalEliminarMesaLabel" aria-hidden="true">
          <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
              <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="modalEliminarMesaLabel"><i class="fa fa-trash"></i> Eliminar Mesa</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
              </div>
              <div class="modal-body">
                ¿Estás seguro de que deseas eliminar esta mesa?
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" id="btnConfirmarEliminarMesa">Eliminar</button>
              </div>
            </div>
          </div>
        </div>
        `;
        document.body.insertAdjacentHTML('beforeend', modalHTML);

        const modal = new bootstrap.Modal(document.getElementById('modalEliminarMesa'));
        modal.show();

        document.getElementById('btnConfirmarEliminarMesa').onclick = async () => {
            await this.deleteMesa(id);
            modal.hide();
        };
    }

    async deleteMesa(id) {
        try {
            const result = await this.mesaCRUD.delete(id);
            if (result.success) {
                await this.loadMesas();
                await this.renderEstadisticas();
            }
        } catch (error) {
            console.error('Error al eliminar mesa:', error);
            this.showNotification('Error al eliminar la mesa', 'error');
        }
        const modalElem = document.getElementById('modalEliminarMesa');
        if (modalElem) modalElem.remove();
    }

    async ocuparMesa(id) {
        try {
            const result = await this.mesaCRUD.ocuparMesa(id);
            if (result.success) {
                await this.loadMesas();
                await this.renderEstadisticas();
            }
        } catch (error) {
            console.error('Error al ocupar mesa:', error);
        }
    }

    async liberarMesa(id) {
        try {
            const result = await this.mesaCRUD.liberarMesa(id);
            if (result.success) {
                await this.loadMesas();
                await this.renderEstadisticas();
            }
        } catch (error) {
            console.error('Error al liberar mesa:', error);
        }
    }

    async reservarMesa(id) {
        try {
            const result = await this.mesaCRUD.reservarMesa(id);
            if (result.success) {
                await this.loadMesas();
                await this.renderEstadisticas();
            }
        } catch (error) {
            console.error('Error al reservar mesa:', error);
        }
    }

    showNotification(message, type = 'info') {
        let container = document.querySelector('.toast-container');
        if (!container) {
            container = document.createElement('div');
            container.className = 'toast-container';
            document.body.appendChild(container);
        }

        const toast = document.createElement('div');
        toast.className = `toast ${type}`;
        toast.innerHTML = `
            <div class="toast-header">
                <strong class="mr-auto">
                    <i class="fa fa-${type === 'success' ? 'check-circle' : 'exclamation-triangle'}"></i>
                    ${type === 'success' ? 'Éxito' : 'Error'}
                </strong>
                <button type="button" class="ml-2 mb-1 close" onclick="this.parentElement.parentElement.remove()">
                    <span>&times;</span>
                </button>
            </div>
            <div class="toast-body">
                ${message}
            </div>
        `;

        container.appendChild(toast);

        setTimeout(() => {
            if (toast.parentElement) {
                toast.remove();
            }
        }, 1000);
    }
}