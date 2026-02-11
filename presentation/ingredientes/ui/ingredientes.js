class IngredientesUI {
    constructor() {
        this.isInitialized = false;
        this.ingredienteCRUD = new IngredienteCRUD();
        this.currentPage = 1;
        this.recordsPerPage = 10;
        this.totalRecords = 0;
        this.allIngredientes = [];
        this.filteredIngredientes = [];
        this.currentFilter = '';
        this.currentStateFilter = '';
        this.chart = null;
        this.stockChart = null;
        this.quantityChart = null;
    }

    init() {
        if (this.isInitialized) {
            return;
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => {
                this.initializeComponents();
            });
        } else {
            this.initializeComponents();
        }
    }

    async initializeComponents() {
        this.isInitialized = true;

        this.initializeForm();

        this.createFilterInterface();
        this.createTableInterface();
        this.createPaginationInterface();
        this.initializeChart();

        await this.loadIngredientes();

        console.log('IngredientesUI inicializado correctamente');
    }

    initializeForm() {
        const form = document.querySelector('.needs-validation');
        if (!form) return;

        const newForm = form.cloneNode(true);
        form.parentNode.replaceChild(newForm, form);

        newForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            e.stopPropagation();

            if (newForm.checkValidity()) {
                await this.handleFormSubmit(newForm);
            }

            newForm.classList.add('was-validated');
        });
    }

    async handleFormSubmit(form) {
        const formData = new FormData(form);
        const ingredienteData = {
            nombre: formData.get('nombre'),
            estado: formData.get('estado'),
            cantidad: parseInt(formData.get('cantidad')),
            unidadMedida: formData.get('unidadMedida') || 'Unidades'
        };

        const result = await this.ingredienteCRUD.create(ingredienteData);

        if (result.success) {
            form.reset();
            form.classList.remove('was-validated');
            await this.loadIngredientes();
            this.updateChart();
        }
    }

    createFilterInterface() {
        const container = document.getElementById('ingrediente-list-container');
        if (!container) return;

        const existingFilter = container.querySelector('.row.mb-3');
        if (existingFilter) {
            existingFilter.remove();
        }

        const filterHTML = `
            <div class="row mb-3">
                <div class="col-md-4 mb-2 mb-md-0">
                    <label for="searchFilter" class="form-label">Buscar por nombre:</label>
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="fas fa-search"></i>
                        </span>
                        <input type="text" id="searchFilter" class="form-control" placeholder="Buscar ingredientes...">
                    </div>
                </div>
                <div class="col-md-3 mb-2 mb-md-0">
                    <label for="stateFilter" class="form-label">Filtrar por estado:</label>
                    <select id="stateFilter" class="form-control">
                        <option value="">Todos los estados</option>
                        <option value="Disponible">Disponible</option>
                        <option value="Agotado">Agotado</option>
                    </select>
                </div>
                <div class="col-md-3 mb-2 mb-md-0">
                    <label for="recordsPerPage" class="form-label">Registros por página:</label>
                    <select id="recordsPerPage" class="form-control">
                        <option value="5">5 registros</option>
                        <option value="10" selected>10 registros</option>
                        <option value="25">25 registros</option>
                        <option value="50">50 registros</option>
                    </select>
                </div>

                <div class="col-md-3 mb-2 mb-md-0 mt-md-2">
                    <label for="unidadMedidaFilter" class="form-label">Filtrar por unidad de medida:</label>
                    <select id="unidadMedidaFilter" class="form-control">
                        <option value="">Todas las unidades</option>
                        <option value="Gramos">Gramos</option>
                        <option value="Litros">Litros</option>
                        <option value="Unidades">Unidades</option>
                        <option value="Kilogramos">Kilogramos</option>
                    </select>
                </div>

                <div class="col-md-2 mt-md-2">
                    <label class="form-label">Acciones:</label>
                    <button id="refreshData" class="btn btn-outline-primary w-100">
                        <i class="fas fa-sync-alt"></i> Actualizar
                    </button>
                </div>
            </div>
        `;

        container.insertAdjacentHTML('beforeend', filterHTML);
        this.attachFilterEvents();
    }

    attachFilterEvents() {
        const searchFilter = document.getElementById('searchFilter');
        if (searchFilter) {
            searchFilter.addEventListener('input', (e) => {
                this.currentFilter = e.target.value.toLowerCase();
                this.applyFilters();
            });
        }

        const stateFilter = document.getElementById('stateFilter');
        if (stateFilter) {
            stateFilter.addEventListener('change', (e) => {
                this.currentStateFilter = e.target.value;
                this.applyFilters();
            });
        }

        const recordsPerPageSelect = document.getElementById('recordsPerPage');
        if (recordsPerPageSelect) {
            recordsPerPageSelect.addEventListener('change', (e) => {
                this.recordsPerPage = parseInt(e.target.value);
                this.currentPage = 1;
                this.renderTable();
            });
        }

        const unidadMedidaFilter = document.getElementById('unidadMedidaFilter');
        if (unidadMedidaFilter) {
            unidadMedidaFilter.addEventListener('change', (e) => {
                this.currentUnidadMedidaFilter = e.target.value;
                this.applyFilters();
            });
        }

        const refreshBtn = document.getElementById('refreshData');
        if (refreshBtn) {
            refreshBtn.addEventListener('click', () => {
                this.loadIngredientes();
            });
        }
    }

    createTableInterface() {
        const container = document.getElementById('ingrediente-list-container');
        if (!container) return;

        const existingTable = container.querySelector('.table-responsive');
        if (existingTable) {
            existingTable.remove();
        }

        const tableHTML = `
            <div class="row mt-4">
                <div class="col-12">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th><i class="fas fa-hashtag"></i> ID</th>
                                    <th><i class="fas fa-tag"></i> Nombre</th>
                                    <th><i class="fas fa-info-circle"></i> Estado</th>
                                    <th><i class="fas fa-cubes"></i> Cantidad</th>
                                    <th><i class="fas fa-balance-scale"></i> Unidad de Medida</th>
                                    <th><i class="fas fa-cogs"></i> Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="ingredientesTableBody">
                                <tr>
                                    <td colspan="5" class="text-center">
                                        <div class="spinner-border text-primary" role="status">
                                            <span class="visually-hidden">Cargando...</span>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        `;

        container.insertAdjacentHTML('beforeend', tableHTML);
    }

    createPaginationInterface() {
        const container = document.getElementById('ingrediente-list-container');
        if (!container) return;

        const existingPagination = container.querySelector('.row.mt-3');
        if (existingPagination) {
            existingPagination.remove();
        }

        const paginationHTML = `
            <div class="row mt-3">
                <div class="col-md-6">
                    <div id="recordsInfo" class="text-muted"></div>
                </div>
                <div class="col-md-6">
                    <nav aria-label="Paginación de ingredientes">
                        <ul id="paginationList" class="pagination justify-content-end"></ul>
                    </nav>
                </div>
            </div>
        `;

        container.insertAdjacentHTML('beforeend', paginationHTML);
    }

    async loadIngredientes() {
        try {
            this.showLoadingState();
            this.allIngredientes = await this.ingredienteCRUD.findAll();
            this.applyFilters();
            this.updateChart();
        } catch (error) {
            console.error('Error cargando ingredientes:', error);
            this.showErrorState();
        }
    }

    applyFilters() {
        this.filteredIngredientes = this.allIngredientes.filter(ingrediente => {
            const matchesSearch = !this.currentFilter ||
                ingrediente.nombre.toLowerCase().includes(this.currentFilter);

            const matchesState = !this.currentStateFilter ||
                ingrediente.estado === this.currentStateFilter;

            const matchesUnidadMedida = !this.currentUnidadMedidaFilter ||
                ingrediente.unidad === this.currentUnidadMedidaFilter;

            return matchesSearch && matchesState && matchesUnidadMedida;
        });

        this.totalRecords = this.filteredIngredientes.length;
        this.currentPage = 1;
        this.renderTable();
    }

    renderTable() {
        const tbody = document.getElementById('ingredientesTableBody');
        if (!tbody) return;

        const startIndex = (this.currentPage - 1) * this.recordsPerPage;
        const endIndex = startIndex + this.recordsPerPage;
        const paginatedData = this.filteredIngredientes.slice(startIndex, endIndex);

        if (paginatedData.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="6" class="text-center py-4">
                        <div class="text-muted">
                            <i class="fas fa-box-open fa-3x mb-3"></i>
                            <p class="mb-0">No se encontraron ingredientes</p>
                        </div>
                    </td>
                </tr>
            `;
            this.renderPagination();
            return;
        }

        const self = this;
        const rows = paginatedData.map(ingrediente => {
            const estadoClass = ingrediente.estado === 'Disponible' ? 'success' : 'danger';
            const estadoIcon = ingrediente.estado === 'Disponible' ? 'fa-check-circle' : 'fa-times-circle';
            const cantidadColor = ingrediente.cantidad > 10 ? 'text-success' :
                ingrediente.cantidad > 5 ? 'text-warning' : 'text-danger';
            if (!ingrediente.unidad) {
                ingrediente.unidad = 'Unidades';
            }
            const iconoUnidad = ingrediente.unidad === 'Gramos' ? 'fa-weight-hanging' :
                ingrediente.unidad === 'Litros' ? 'fa-tint' :
                    ingrediente.unidad === 'Unidades' ? 'fa-cubes' :
                        ingrediente.unidad === 'Kilogramos' ? 'fa-weight' : 'fa-cube';

            return `
                <tr>
                    <td><strong>#${ingrediente.id}</strong></td>
                    <td>
                        <i class="fas fa-leaf text-success me-2"></i>
                        ${ingrediente.nombre}
                    </td>
                    <td>
                        <span class="badge bg-${estadoClass}">
                            <i class="fas ${estadoIcon}"></i> ${ingrediente.estado}
                        </span>
                    </td>
                    <td>
                        <span class="fw-bold ${cantidadColor}">
                            <i class="fas ${iconoUnidad}"></i> ${ingrediente.cantidad}
                        </span>
                    </td>
                    <td>
                        <span class="fw-bold">${ingrediente.unidad}</span>
                    </td>
                    <td>
                        <div class="btn-group" role="group">
                            <button class="btn btn-sm btn-outline-primary edit-btn" 
                                    data-id="${ingrediente.id}"
                                    title="Editar">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-danger delete-btn" 
                                    data-id="${ingrediente.id}"
                                    title="Eliminar">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            `;
        }).join('');

        tbody.innerHTML = rows;

        tbody.querySelectorAll('.edit-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const id = parseInt(e.currentTarget.dataset.id);
                self.editIngrediente(id);
            });
        });

        tbody.querySelectorAll('.delete-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const id = parseInt(e.currentTarget.dataset.id);
                self.deleteIngrediente(id);
            });
        });

        this.renderPagination();
        this.updateRecordsInfo();
    }

    renderPagination() {
        const paginationList = document.getElementById('paginationList');
        if (!paginationList) return;

        const totalPages = Math.ceil(this.totalRecords / this.recordsPerPage);

        if (totalPages <= 1) {
            paginationList.innerHTML = '';
            return;
        }

        const self = this;
        let paginationHTML = '';

        paginationHTML += `
            <li class="page-item ${this.currentPage === 1 ? 'disabled' : ''}">
                <a class="page-link prev-btn" href="#" data-page="${this.currentPage - 1}">
                    <i class="fas fa-chevron-left"></i>
                </a>
            </li>
        `;

        const startPage = Math.max(1, this.currentPage - 2);
        const endPage = Math.min(totalPages, this.currentPage + 2);

        for (let i = startPage; i <= endPage; i++) {
            paginationHTML += `
                <li class="page-item ${this.currentPage === i ? 'active' : ''}">
                    <a class="page-link page-num-btn" href="#" data-page="${i}">${i}</a>
                </li>
            `;
        }

        paginationHTML += `
            <li class="page-item ${this.currentPage === totalPages ? 'disabled' : ''}">
                <a class="page-link next-btn" href="#" data-page="${this.currentPage + 1}">
                    <i class="fas fa-chevron-right"></i>
                </a>
            </li>
        `;

        paginationList.innerHTML = paginationHTML;

        paginationList.querySelectorAll('.page-link').forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                const newPage = parseInt(e.currentTarget.dataset.page);
                if (!isNaN(newPage)) {
                    self.changePage(newPage);
                }
            });
        });
    }

    changePage(newPage) {
        const totalPages = Math.ceil(this.totalRecords / this.recordsPerPage);

        if (newPage < 1 || newPage > totalPages) return;

        this.currentPage = newPage;
        this.renderTable();
    }

    updateRecordsInfo() {
        const recordsInfo = document.getElementById('recordsInfo');
        if (!recordsInfo) return;

        const startRecord = (this.currentPage - 1) * this.recordsPerPage + 1;
        const endRecord = Math.min(this.currentPage * this.recordsPerPage, this.totalRecords);

        recordsInfo.innerHTML = `
            <i class="fas fa-info-circle"></i>
            Mostrando ${startRecord} a ${endRecord} de ${this.totalRecords} registros
        `;
    }

    initializeChart() {
        const container = document.getElementById('inventory-chart');
        if (!container) return;

        const existingChart = container.querySelector('.row.mt-4');
        if (existingChart) {
            existingChart.remove();
        }

        const chartHTML = `
            <div class="row mt-4">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">
                                <i class="fas fa-chart-pie text-primary"></i>
                                Estado del Inventario
                            </h5>
                            <div id="stockChart"></div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">
                                <i class="fas fa-chart-bar text-info"></i>
                                Cantidad por Ingrediente
                            </h5>
                            <div id="quantityChart"></div>
                        </div>
                    </div>
                </div>
            </div>
        `;

        container.insertAdjacentHTML('beforeend', chartHTML);
    }

    updateChart() {
        if (this.allIngredientes.length === 0) return;

        setTimeout(() => {
            if (document.querySelector("#stockChart")) {
                this.renderStockChart();
            }
            if (document.querySelector("#quantityChart")) {
                this.renderQuantityChart();
            }
        }, 100);
    }

    renderStockChart() {
        const chartElement = document.querySelector("#stockChart");
        if (!chartElement) {
            console.warn('Elemento #stockChart no encontrado');
            return;
        }

        const disponibles = this.allIngredientes.filter(i => i.estado === 'Disponible').length;
        const agotados = this.allIngredientes.filter(i => i.estado === 'Agotado').length;

        const options = {
            chart: {
                type: 'pie',
                height: 300,
                animations: {
                    enabled: true,
                    easing: 'easeinout',
                    speed: 800
                }
            },
            series: [disponibles, agotados],
            labels: ['Disponibles', 'Agotados'],
            colors: ['#28a745', '#dc3545'],
            legend: {
                position: 'bottom'
            },
            plotOptions: {
                pie: {
                    donut: {
                        size: '45%'
                    }
                }
            },
            dataLabels: {
                enabled: true,
                formatter: function (val, opts) {
                    return opts.w.config.series[opts.seriesIndex]
                }
            },
            tooltip: {
                y: {
                    formatter: function (val) {
                        return val + " ingredientes"
                    }
                }
            }
        };

        if (this.stockChart) {
            this.stockChart.destroy();
        }

        this.stockChart = new ApexCharts(chartElement, options);
        this.stockChart.render();
    }

    renderQuantityChart() {
        const chartElement = document.querySelector("#quantityChart");
        if (!chartElement) {
            console.warn('Elemento #quantityChart no encontrado');
            return;
        }

        const ingredientes = this.allIngredientes.slice(0, 10);
        const nombres = ingredientes.map(i => i.nombre);
        const cantidades = ingredientes.map(i => i.cantidad);
        const unidades = ingredientes.map(i => i.unidad);

        const options = {
            chart: {
                type: 'bar',
                height: 300,
                animations: {
                    enabled: true,
                    easing: 'easeinout',
                    speed: 800
                }
            },
            series: [{
                name: 'Cantidad',
                data: cantidades
            }],
            xaxis: {
                categories: nombres,
                labels: {
                    rotate: -45
                }
            },
            yaxis: {
                title: {
                    text: 'Cantidad'
                }
            },
            plotOptions: {
                bar: {
                    horizontal: false,
                    columnWidth: '55%',
                    colors: {
                        ranges: [{
                            from: 0,
                            to: 5,
                            color: '#dc3545'
                        }, {
                            from: 6,
                            to: 10,
                            color: '#ffc107'
                        }, {
                            from: 11,
                            to: 1000,
                            color: '#28a745'
                        }]
                    }
                }
            },
            dataLabels: {
                enabled: true,
            },
            tooltip: {
                y: {
                    formatter: function (val) {
                        return val 
                    }
                }
            }
        };

        if (this.quantityChart) {
            this.quantityChart.destroy();
        }

        this.quantityChart = new ApexCharts(chartElement, options);
        this.quantityChart.render();
    }

    async editIngrediente(id) {
        const ingrediente = await this.ingredienteCRUD.findById(id);
        if (!ingrediente) return;

        const form = document.querySelector('.needs-validation');

        const newForm = form.cloneNode(true);
        form.parentNode.replaceChild(newForm, form);

        newForm.querySelector('#validationCustom01').value = ingrediente.nombre;

        const estadoSelect = newForm.querySelector('#validationCustom02');
        if (estadoSelect) {
            estadoSelect.value = ingrediente.estado.trim();
        } else {
            console.warn('Elemento #validationCustom02 no encontrado');
        }

        newForm.querySelector('#validationCustom03').value = ingrediente.cantidad;
        newForm.querySelector('#validationCustom04').value = ingrediente.unidad;

        const submitBtn = newForm.querySelector('button[type="submit"]');
        submitBtn.innerHTML = '<i class="fas fa-save me-1"></i> Actualizar Ingrediente';
        submitBtn.classList.remove('btn-primary');
        submitBtn.classList.add('btn-warning');

        if (!newForm.querySelector('.btn-cancel')) {
            const cancelBtn = document.createElement('button');
            cancelBtn.type = 'button';
            cancelBtn.className = 'btn btn-secondary ms-2 btn-cancel';
            cancelBtn.innerHTML = '<i class="fas fa-times me-1"></i> Cancelar';
            cancelBtn.addEventListener('click', () => this.cancelEdit());
            submitBtn.parentNode.appendChild(cancelBtn);
        }
        
        newForm.dataset.editId = id;
        newForm.addEventListener('submit', (e) => this.handleEditSubmit(e, id));
        newForm.scrollIntoView({ behavior: 'smooth' });
    }


    async handleEditSubmit(e, id) {
        e.preventDefault();
        e.stopPropagation();

        const form = e.target;
        if (!form.checkValidity()) {
            form.classList.add('was-validated');
            return;
        }

        const formData = new FormData(form);
        const ingredienteData = {
            nombre: formData.get('nombre'),
            estado: formData.get('estado'),
            cantidad: parseInt(formData.get('cantidad')),
            unidadMedida: formData.get('unidadMedida') || 'Unidades'
        };

        const result = await this.ingredienteCRUD.update(id, ingredienteData);

        if (result.success) {
            this.cancelEdit();
            await this.loadIngredientes();
            this.updateChart();
        }
    }

    cancelEdit() {
        const form = document.querySelector('.needs-validation');
        form.reset();
        form.classList.remove('was-validated');
        delete form.dataset.editId;

        const submitBtn = form.querySelector('button[type="submit"]');
        submitBtn.innerHTML = '<i class="fa-solid fa-plus me-1"></i> Agregar Ingrediente';
        submitBtn.classList.remove('btn-warning');
        submitBtn.classList.add('btn-primary');

        const cancelBtn = form.querySelector('.btn-cancel');
        if (cancelBtn) {
            cancelBtn.remove();
        }

        this.initializeForm();
    }

    async deleteIngrediente(id) {
        const ingrediente = this.allIngredientes.find(i => i.id === id);
        if (!ingrediente) return;

        const confirmed = await this.showConfirmDialog(
            '¿Eliminar ingrediente?',
            `¿Estás seguro de que deseas eliminar "${ingrediente.nombre}"?`,
            'Esta acción no se puede deshacer.'
        );

        if (confirmed) {
            const result = await this.ingredienteCRUD.delete(id);
            if (result.success) {
                await this.loadIngredientes();
                this.updateChart();
            }
        }
    }

    showConfirmDialog(title, message, subtitle) {
        return new Promise((resolve) => {
            const modal = document.createElement('div');
            modal.className = 'modal fade';
            modal.innerHTML = `
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">
                                <i class="fas fa-exclamation-triangle text-warning"></i>
                                ${title}
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <p class="mb-2">${message}</p>
                            <small class="text-muted">${subtitle}</small>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                <i class="fas fa-times"></i> Cancelar
                            </button>
                            <button type="button" class="btn btn-danger btn-confirm">
                                <i class="fas fa-trash"></i> Eliminar
                            </button>
                        </div>
                    </div>
                </div>
            `;

            document.body.appendChild(modal);
            const bsModal = new bootstrap.Modal(modal);
            bsModal.show();

            modal.querySelector('.btn-confirm').addEventListener('click', () => {
                bsModal.hide();
                resolve(true);
            });

            modal.addEventListener('hidden.bs.modal', () => {
                document.body.removeChild(modal);
                resolve(false);
            });
        });
    }

    showLoadingState() {
        const tbody = document.getElementById('ingredientesTableBody');
        if (tbody) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="5" class="text-center py-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Cargando...</span>
                        </div>
                        <p class="mt-2 text-muted">Cargando ingredientes...</p>
                    </td>
                </tr>
            `;
        }
    }

    showErrorState() {
        const tbody = document.getElementById('ingredientesTableBody');
        if (tbody) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="5" class="text-center py-4">
                        <div class="text-danger">
                            <i class="fas fa-exclamation-triangle fa-3x mb-3"></i>
                            <p class="mb-0">Error al cargar los datos</p>
                            <button class="btn btn-outline-primary mt-2" onclick="window.ingredientesUI.loadIngredientes()">
                                <i class="fas fa-sync-alt"></i> Reintentar
                            </button>
                        </div>
                    </td>
                </tr>
            `;
        }
    }
}