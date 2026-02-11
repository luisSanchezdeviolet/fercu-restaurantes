class CategoriaUI {
    constructor() {
        this.categoriaCRUD = new CategoriaCRUD();
        this.isSubmitting = false;
        this.isInitialized = false;
        this.allCategorias = [];
        this.filteredCategorias = [];
        this.currentFilters = {
            nombre: '',
            estado: '',
            imagen: ''
        };
        this.pagination = {
            currentPage: 1,
            itemsPerPage: 5,
            totalItems: 0,
            totalPages: 0
        };
        this.deleteModalInitialized = false;
        this.deleteModalInstance = null;
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

    initializeComponents() {
        this.bindEvents();
        this.loadCategorias();
        this.imageFunction();
        this.initDeleteModal();
        this.isInitialized = true;
    }

    initDeleteModal() {
        if (this.deleteModalInitialized) {
            return;
        }

        const deleteModalElement = document.getElementById('deleteModalCategoria');
        if (!deleteModalElement) {
            console.warn('Delete modal element not found. Modal initialization will be deferred.');
            return;
        }

        this.waitForBootstrap().then(() => {
            const modalElement = document.getElementById('deleteModalCategoria');
            if (modalElement && window.bootstrap && window.bootstrap.Modal) {
                this.deleteModalInstance = new bootstrap.Modal(modalElement, {
                    backdrop: 'static',
                    keyboard: false
                });
            }

            const deleteForm = document.getElementById('deleteForm');
            if (deleteForm && !deleteForm.hasAttribute('data-event-bound')) {
                deleteForm.addEventListener('submit', (e) => this.handleDeleteSubmit(e));
                deleteForm.setAttribute('data-event-bound', 'true');
                this.deleteModalInitialized = true;
            }
        }).catch((error) => {
            console.error('Error initializing Bootstrap modal:', error);
        });
    }

    waitForBootstrap() {
        return new Promise((resolve, reject) => {
            let attempts = 0;
            const maxAttempts = 50;
            
            const checkBootstrap = () => {
                if (window.bootstrap && window.bootstrap.Modal) {
                    resolve();
                } else if (attempts < maxAttempts) {
                    attempts++;
                    setTimeout(checkBootstrap, 100);
                } else {
                    reject(new Error('Bootstrap not available after timeout'));
                }
            };
            
            checkBootstrap();
        });
    }

    bindEvents() {
        const form = document.querySelector('.needs-validation');
        if (form) {
            form.removeEventListener('submit', this.handleSubmitBound);
            this.handleSubmitBound = (e) => this.handleSubmit(e);
            form.addEventListener('submit', this.handleSubmitBound);
        }

        if (!document.body.hasAttribute('data-categoria-events-bound')) {
            document.addEventListener('click', (e) => {
                if (e.target.classList.contains('btn-edit-categoria')) {
                    document.getElementById('validationCustom01').value = '';
                    document.getElementById('validationCustom02').value = '1';
                    document.getElementById('imagen').value = '';
                    this.clearImagePreview();
                    this.handleEdit(e.target.dataset.id);
                }
                if (e.target.classList.contains('btn-delete-categoria')) {
                    document.getElementById('validationCustom01').value = '';
                    document.getElementById('validationCustom02').value = '1';
                    document.getElementById('imagen').value = '';
                    this.clearImagePreview();
                    this.showDeleteModal(e.target.dataset.id);
                }
                if (e.target.classList.contains('btn-cancel-edit')) {
                    this.cancelEdit();
                }
                if (e.target.classList.contains('btn-clear-filters') || e.target.closest('.btn-clear-filters')) {
                    this.clearAllFilters();
                }
                
                if (e.target.classList.contains('page-link') && !e.target.closest('.disabled')) {
                    e.preventDefault();
                    const page = parseInt(e.target.dataset.page);
                    if (page && page !== this.pagination.currentPage) {
                        this.goToPage(page);
                    }
                }
            });
            
            document.addEventListener('change', (e) => {
                if (e.target.id === 'items-per-page-select') {
                    const newItemsPerPage = parseInt(e.target.value);
                    this.changeItemsPerPage(newItemsPerPage);
                }
            });
            
            document.body.setAttribute('data-categoria-events-bound', 'true');
        }

        const imageInput = document.getElementById('imagen');
        if (imageInput && !imageInput.hasAttribute('data-change-listener')) {
            imageInput.addEventListener('change', (e) => this.handleImagePreview(e));
            imageInput.setAttribute('data-change-listener', 'true');
        }
    }

    showDeleteModal(id) {
        if (!this.deleteModalInitialized) {
            this.initDeleteModal();
            if (!this.deleteModalInitialized) {
                setTimeout(() => {
                    this.showDeleteModal(id);
                }, 100);
                return;
            }
        }

        const categoria = this.allCategorias.find(cat => cat.id.toString() === id.toString());
        
        const deleteIdInput = document.getElementById('delete_id');
        if (deleteIdInput) {
            deleteIdInput.value = id;
        }

        const modalBody = document.getElementById('contenedor_delete');
        if (modalBody && categoria) {
            modalBody.innerHTML = `
                <input type="hidden" id="delete_id" name="id" value="${id}">
                <div class="text-center mb-3">
                    <i class="fa-solid fa-triangle-exclamation text-warning" style="font-size: 3rem;"></i>
                </div>
                <div class="alert alert-warning">
                    <h6 class="alert-heading">¿Estás seguro de que deseas eliminar esta categoría?</h6>
                    <hr>
                    <div class="row">
                        <div class="col-sm-4"><strong>ID:</strong></div>
                        <div class="col-sm-8">#${categoria.id}</div>
                    </div>
                    <div class="row">
                        <div class="col-sm-4"><strong>Nombre:</strong></div>
                        <div class="col-sm-8">${categoria.nombre}</div>
                    </div>
                    <div class="row">
                        <div class="col-sm-4"><strong>Estado:</strong></div>
                        <div class="col-sm-8">
                            <span class="badge ${categoria.estado == 1 ? 'bg-success' : 'bg-danger'}">
                                ${categoria.estado == 1 ? 'Activo' : 'Inactivo'}
                            </span>
                        </div>
                    </div>
                    ${categoria.imagen ? `
                        <div class="row mt-2">
                            <div class="col-sm-4"><strong>Imagen:</strong></div>
                            <div class="col-sm-8">
                                <img src="${categoria.imagen}" alt="${categoria.nombre}" 
                                     style="width: 60px; height: 60px; object-fit: cover;" 
                                     class="img-thumbnail">
                            </div>
                        </div>
                    ` : ''}
                </div>
                <p class="text-muted mb-0">
                    <i class="fa-solid fa-info-circle me-1"></i>
                    Esta acción no se puede deshacer.
                </p>
            `;
        }

        const deleteMessage = document.getElementById('deleteMessage');
        if (deleteMessage) {
            deleteMessage.classList.add('d-none');
            deleteMessage.innerHTML = '';
        }

        this.displayDeleteModal();
    }

    displayDeleteModal() {
        const deleteModalElement = document.getElementById('deleteModalCategoria');
        
        if (!deleteModalElement) {
            console.error('Modal element not found');
            this.handleModalFallback();
            return;
        }

        try {
            if (this.deleteModalInstance) {
                this.deleteModalInstance.show();
                return;
            }

            if (window.bootstrap && window.bootstrap.Modal) {
                const modalInstance = new bootstrap.Modal(deleteModalElement, {
                    backdrop: 'static',
                    keyboard: false
                });
                modalInstance.show();
                this.deleteModalInstance = modalInstance;
                return;
            }

            if (window.$ && $.fn.modal) {
                $(deleteModalElement).modal('show');
                return;
            }

            this.showModalManually(deleteModalElement);

        } catch (error) {
            console.error('Error showing modal:', error);
            this.handleModalFallback();
        }
    }

    showModalManually(deleteModalElement) {
        deleteModalElement.classList.add('show');
        deleteModalElement.style.display = 'block';
        deleteModalElement.setAttribute('aria-hidden', 'false');
        
        const existingBackdrop = document.getElementById('manual-modal-backdrop');
        if (!existingBackdrop) {
            const backdrop = document.createElement('div');
            backdrop.className = 'modal-backdrop fade show';
            backdrop.id = 'manual-modal-backdrop';
            document.body.appendChild(backdrop);
        }
        document.body.classList.add('modal-open');

        const closeButtons = deleteModalElement.querySelectorAll('[data-bs-dismiss="modal"], .close');
        closeButtons.forEach(btn => {
            btn.addEventListener('click', () => this.hideDeleteModal(), { once: true });
        });
    }

    handleModalFallback() {
        const deleteIdInput = document.getElementById('delete_id');
        const id = deleteIdInput ? deleteIdInput.value : null;
        
        if (id) {
            const categoria = this.allCategorias.find(cat => 
                cat.id.toString() === id.toString()
            );
            
            if (categoria) {
                const confirmDelete = confirm(
                    `¿Estás seguro de que deseas eliminar la categoría "${categoria.nombre}"?\n\n` +
                    'Esta acción no se puede deshacer.'
                );
                
                if (confirmDelete) {
                    this.handleDeleteSubmit({
                        preventDefault: () => {},
                        target: { 
                            querySelector: () => ({ 
                                textContent: 'Eliminar', 
                                disabled: false,
                                innerHTML: ''
                            }) 
                        }
                    });
                }
            }
        }
    }

    hideDeleteModal() {
        const deleteModalElement = document.getElementById('deleteModalCategoria');
        
        if (!deleteModalElement) {
            return;
        }
        
        try {
            if (this.deleteModalInstance) {
                this.deleteModalInstance.hide();
            } else if (window.$ && $.fn.modal) {
                $(deleteModalElement).modal('hide');
            } else {
                deleteModalElement.classList.remove('show');
                deleteModalElement.style.display = 'none';
                deleteModalElement.setAttribute('aria-hidden', 'true');
                
                const backdrop = document.getElementById('manual-modal-backdrop');
                if (backdrop) {
                    backdrop.remove();
                }
                document.body.classList.remove('modal-open');
            }
        } catch (error) {
            console.error('Error hiding modal:', error);
        }
    }

    async handleDeleteSubmit(e) {
        e.preventDefault();
        
        const deleteIdInput = document.getElementById('delete_id');
        const id = deleteIdInput ? deleteIdInput.value : null;
        
        if (!id) {
            this.showDeleteError('Error: No se pudo obtener el ID de la categoría');
            return;
        }

        const submitBtn = e.target.querySelector('button[type="submit"]');
        const originalText = submitBtn ? submitBtn.textContent : 'Eliminar';
        
        try {
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-1"></i>Eliminando...';
            }
            
            const result = await this.categoriaCRUD.delete(id);
            
            if (result && result.success) {
                this.hideDeleteModal();
                
                await this.loadCategorias();
                
                if (this.categoriaCRUD && this.categoriaCRUD.showNotification) {
                    this.categoriaCRUD.showNotification('Categoría eliminada exitosamente', 'success');
                }
            } else {
                this.showDeleteError('Error al eliminar la categoría');
            }
        } catch (error) {
            console.error('Error al eliminar:', error);
            this.showDeleteError('Error al eliminar la categoría: ' + error.message);
        } finally {
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            }
        }
    }

    showDeleteError(message) {
        const deleteMessage = document.getElementById('deleteMessage');
        if (deleteMessage) {
            deleteMessage.className = 'alert alert-danger mx-3 mt-3';
            deleteMessage.innerHTML = `
                <i class="fa-solid fa-exclamation-triangle me-1"></i>
                ${message}
            `;
            deleteMessage.classList.remove('d-none');
        } else {
            this.showError(message);
        }
    }

    calculatePagination() {
        this.pagination.totalItems = this.filteredCategorias.length;
        this.pagination.totalPages = Math.ceil(this.pagination.totalItems / this.pagination.itemsPerPage);
        
        if (this.pagination.currentPage > this.pagination.totalPages && this.pagination.totalPages > 0) {
            this.pagination.currentPage = this.pagination.totalPages;
        }
        
        if (this.pagination.currentPage < 1) {
            this.pagination.currentPage = 1;
        }
    }

    getCurrentPageData() {
        const startIndex = (this.pagination.currentPage - 1) * this.pagination.itemsPerPage;
        const endIndex = startIndex + this.pagination.itemsPerPage;
        return this.filteredCategorias.slice(startIndex, endIndex);
    }

    goToPage(page) {
        if (page >= 1 && page <= this.pagination.totalPages) {
            this.pagination.currentPage = page;
            this.renderCategoriasTable();
        }
    }

    goToFirstPage() {
        this.goToPage(1);
    }

    goToLastPage() {
        this.goToPage(this.pagination.totalPages);
    }

    goToPreviousPage() {
        this.goToPage(this.pagination.currentPage - 1);
    }

    goToNextPage() {
        this.goToPage(this.pagination.currentPage + 1);
    }

    changeItemsPerPage(newItemsPerPage) {
        this.pagination.itemsPerPage = newItemsPerPage;
        this.pagination.currentPage = 1;
        this.calculatePagination();
        this.renderCategoriasTable();
    }

    createPaginationControls() {
        let paginationHTML = `
            <nav aria-label="Navegación de categorías">
                <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap">
                    <div class="d-flex align-items-center gap-2">
                        <label for="items-per-page-select" class="form-label mb-0 me-2">Mostrar:</label>
                        <select class="form-select form-select-sm" id="items-per-page-select" style="width: auto;">
                            <option value="5" ${this.pagination.itemsPerPage === 5 ? 'selected' : ''}>5</option>
                            <option value="10" ${this.pagination.itemsPerPage === 10 ? 'selected' : ''}>10</option>
                            <option value="25" ${this.pagination.itemsPerPage === 25 ? 'selected' : ''}>25</option>
                            <option value="50" ${this.pagination.itemsPerPage === 50 ? 'selected' : ''}>50</option>
                        </select>
                        <span class="text-muted">por página</span>
                    </div>
                    <div class="pagination-info ">
                        <small class="text-muted">
                            Mostrando ${((this.pagination.currentPage - 1) * this.pagination.itemsPerPage) + 1} - 
                            ${Math.min(this.pagination.currentPage * this.pagination.itemsPerPage, this.pagination.totalItems)} 
                            de ${this.pagination.totalItems} registros
                        </small>
                    </div>
                </div>`;
    
        if (this.pagination.totalPages > 1) {
            paginationHTML += `
                <ul class="pagination pagination-sm justify-content-center">
            `;
    
            paginationHTML += `
                <li class="page-item ${this.pagination.currentPage === 1 ? 'disabled' : ''}">
                    <a class="page-link" href="#" data-page="1" title="Primera página">
                        <i class="fa-solid fa-chevron-double-left"></i>
                    </a>
                </li>
            `;
    
            paginationHTML += `
                <li class="page-item ${this.pagination.currentPage === 1 ? 'disabled' : ''}">
                    <a class="page-link" href="#" data-page="${this.pagination.currentPage - 1}" title="Página anterior">
                        <i class="fa-solid fa-chevron-left"></i>
                    </a>
                </li>
            `;
    
            const maxVisiblePages = 5;
            let startPage = Math.max(1, this.pagination.currentPage - Math.floor(maxVisiblePages / 2));
            let endPage = Math.min(this.pagination.totalPages, startPage + maxVisiblePages - 1);
    
            if (endPage - startPage + 1 < maxVisiblePages) {
                startPage = Math.max(1, endPage - maxVisiblePages + 1);
            }
    
            if (startPage > 1) {
                paginationHTML += `
                    <li class="page-item">
                        <a class="page-link" href="#" data-page="1">1</a>
                    </li>
                `;
                if (startPage > 2) {
                    paginationHTML += `
                        <li class="page-item disabled">
                            <span class="page-link">...</span>
                        </li>
                    `;
                }
            }
    
            for (let i = startPage; i <= endPage; i++) {
                paginationHTML += `
                    <li class="page-item ${i === this.pagination.currentPage ? 'active' : ''}">
                        <a class="page-link" href="#" data-page="${i}">${i}</a>
                    </li>
                `;
            }
    
            if (endPage < this.pagination.totalPages) {
                if (endPage < this.pagination.totalPages - 1) {
                    paginationHTML += `
                        <li class="page-item disabled">
                            <span class="page-link">...</span>
                        </li>
                    `;
                }
                paginationHTML += `
                    <li class="page-item">
                        <a class="page-link" href="#" data-page="${this.pagination.totalPages}">${this.pagination.totalPages}</a>
                    </li>
                `;
            }
    
            paginationHTML += `
                <li class="page-item ${this.pagination.currentPage === this.pagination.totalPages ? 'disabled' : ''}">
                    <a class="page-link" href="#" data-page="${this.pagination.currentPage + 1}" title="Página siguiente">
                        <i class="fa-solid fa-chevron-right"></i>
                    </a>
                </li>
            `;
    
            paginationHTML += `
                <li class="page-item ${this.pagination.currentPage === this.pagination.totalPages ? 'disabled' : ''}">
                    <a class="page-link" href="#" data-page="${this.pagination.totalPages}" title="Última página">
                        <i class="fa-solid fa-chevron-double-right"></i>
                    </a>
                </li>
            `;
    
            paginationHTML += `
                    </ul>
            `;
        }
    
        paginationHTML += `
                </nav>
        `;
    
        return paginationHTML;
    }

    createFiltersSection() {
        const filtersHTML = `
            <div class="card mb-3" id="filters-section">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="mb-0">
                            <i class="fa-solid fa-filter-variant me-1"></i>
                            Filtros de Categorías
                        </h6>
                        <button type="button" class="btn btn-sm btn-outline-secondary btn-toggle-filters">
                            <i class="fa-solid fa-chevron-up"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body" id="filters-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="filter-nombre" class="form-label">Buscar por nombre</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fa-solid fa-magnifying-glass"></i>
                                </span>
                                <input type="text" class="form-control" id="filter-nombre" 
                                       placeholder="Escriba el nombre...">
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="filter-estado" class="form-label">Filtrar por estado</label>
                            <select class="form-control" id="filter-estado">
                                <option value="">Todos los estados</option>
                                <option value="1">Activo</option>
                                <option value="0">Inactivo</option>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="filter-imagen" class="form-label">Filtrar por imagen</label>
                            <select class="form-control" id="filter-imagen">
                                <option value="">Todas</option>
                                <option value="con-imagen">Con imagen</option>
                                <option value="sin-imagen">Sin imagen</option>
                            </select>
                        </div>
                        <div class="col-md-2 mb-3 d-flex align-items-end">
                            <button type="button" class="btn btn-outline-danger btn-clear-filters w-100">
                                <i class="fa-solid fa-filter-circle-xmark me-1"></i>
                                Limpiar
                            </button>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <div class="d-flex justify-content-between align-items-center">
                                <small class="text-muted" id="filter-results-count">
                                    Mostrando 0 de 0 categorías
                                </small>
                                <div class="btn-group btn-group-sm" role="group">
                                    <button type="button" class="btn btn-outline-primary btn-export-excel">
                                        <i class="fa-solid fa-file-excel me-1"></i>
                                        Excel
                                    </button>
                                    <button type="button" class="btn btn-outline-success btn-export-csv">
                                        <i class="fa-solid fa-file-csv me-1"></i>
                                        CSV
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
        return filtersHTML;
    }

    applyFilters() {
        const nombreFilter = this.currentFilters.nombre.toLowerCase();
        const estadoFilter = this.currentFilters.estado;
        const imagenFilter = this.currentFilters.imagen;

        this.filteredCategorias = this.allCategorias.filter(categoria => {
            const matchesNombre = !nombreFilter || 
                categoria.nombre.toLowerCase().includes(nombreFilter);

            const matchesEstado = !estadoFilter || 
                categoria.estado.toString() === estadoFilter;

            let matchesImagen = true;
            if (imagenFilter === 'con-imagen') {
                matchesImagen = categoria.imagen && categoria.imagen.trim() !== '';
            } else if (imagenFilter === 'sin-imagen') {
                matchesImagen = !categoria.imagen || categoria.imagen.trim() === '';
            }

            return matchesNombre && matchesEstado && matchesImagen;
        });

        this.pagination.currentPage = 1;
        this.calculatePagination();
        this.renderCategoriasTable();
        this.updateFilterResultsCount();
    }

    updateFilterResultsCount() {
        const countElement = document.getElementById('filter-results-count');
        if (countElement) {
            const total = this.allCategorias.length;
            const filtered = this.filteredCategorias.length;
            countElement.textContent = `${filtered} de ${total} categorías encontradas`;
        }
    }

    setupFilterEvents() {
        const nombreInput = document.getElementById('filter-nombre');
        if (nombreInput) {
            let debounceTimer;
            nombreInput.addEventListener('input', (e) => {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(() => {
                    this.currentFilters.nombre = e.target.value;
                    this.applyFilters();
                }, 300);
            });
        }

        const estadoSelect = document.getElementById('filter-estado');
        if (estadoSelect) {
            estadoSelect.addEventListener('change', (e) => {
                this.currentFilters.estado = e.target.value;
                this.applyFilters();
            });
        }

        const imagenSelect = document.getElementById('filter-imagen');
        if (imagenSelect) {
            imagenSelect.addEventListener('change', (e) => {
                this.currentFilters.imagen = e.target.value;
                this.applyFilters();
            });
        }

        const toggleBtn = document.querySelector('.btn-toggle-filters');
        if (toggleBtn) {
            toggleBtn.addEventListener('click', () => {
                this.toggleFiltersVisibility();
            });
        }

        const exportExcelBtn = document.querySelector('.btn-export-excel');
        const exportCsvBtn = document.querySelector('.btn-export-csv');
        
        if (exportExcelBtn) {
            exportExcelBtn.addEventListener('click', () => this.exportToExcel());
        }
        
        if (exportCsvBtn) {
            exportCsvBtn.addEventListener('click', () => this.exportToCsv());
        }
    }

    clearAllFilters() {
        this.currentFilters = {
            nombre: '',
            estado: '',
            imagen: ''
        };

        const nombreInput = document.getElementById('filter-nombre');
        const estadoSelect = document.getElementById('filter-estado');
        const imagenSelect = document.getElementById('filter-imagen');

        if (nombreInput) nombreInput.value = '';
        if (estadoSelect) estadoSelect.value = '';
        if (imagenSelect) imagenSelect.value = '';

        this.filteredCategorias = [...this.allCategorias];
        this.pagination.currentPage = 1;
        this.calculatePagination();
        this.renderCategoriasTable();
        this.updateFilterResultsCount();
    }

    toggleFiltersVisibility() {
        const filtersBody = document.getElementById('filters-body');
        const toggleBtn = document.querySelector('.btn-toggle-filters i');
        
        if (filtersBody && toggleBtn) {
            if (filtersBody.style.display === 'none') {
                filtersBody.style.display = 'block';
                toggleBtn.className = 'fa-solid fa-chevron-up';
            } else {
                filtersBody.style.display = 'none';
                toggleBtn.className = 'fa-solid fa-chevron-down';
            }
        }
    }

    exportToCsv() {
        const csvContent = this.generateCsvContent();
        const filename = `categorias_pagina_${this.pagination.currentPage}_de_${this.pagination.totalPages}.csv`;
        this.downloadFile(csvContent, filename, 'text/csv');
    }

    exportToExcel() {
        const csvContent = this.generateCsvContent();
        const filename = `categorias_pagina_${this.pagination.currentPage}_de_${this.pagination.totalPages}.xlsx`;
        this.downloadFile(csvContent, filename, 'application/vnd.ms-excel');
    }

    generateCsvContent() {
        const headers = ['ID', 'Nombre', 'Estado', 'Imagen'];
        const csvRows = [headers.join(',')];

        this.filteredCategorias.forEach(categoria => {
            const row = [
                categoria.id,
                `"${categoria.nombre}"`,
                categoria.estado == 1 ? 'Activo' : 'Inactivo',
                categoria.imagen ? 'Si' : 'No'
            ];
            csvRows.push(row.join(','));
        });

        return csvRows.join('\n');
    }

    downloadFile(content, filename, mimeType) {
        const blob = new Blob([content], { type: mimeType });
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = filename;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        window.URL.revokeObjectURL(url);
    }

    async loadCategorias() {
        try {
            const categorias = await this.categoriaCRUD.findAll();
            this.allCategorias = categorias;
            this.filteredCategorias = [...categorias];
            this.pagination.currentPage = 1;
            this.calculatePagination();
            this.renderCategoriasTable();
            this.updateFilterResultsCount();
        } catch (error) {
            this.showError('Error al cargar las categorías: ' + error.message);
        }
    }

    async handleSubmit(e) {
        e.preventDefault();

        if (this.isSubmitting) {
            return;
        }
        
        const form = e.target;
        if (!form.checkValidity()) {
            form.classList.add('was-validated');
            return;
        }

        this.isSubmitting = true;
        const submitBtn = form.querySelector('button[type="submit"]');
        const originalText = submitBtn.textContent;
        
        try {
            submitBtn.disabled = true;
            submitBtn.textContent = 'Procesando...';

            const categoriaData = {
                nombre: document.getElementById('validationCustom01').value.trim(),
                estado: document.getElementById('validationCustom02').value
            };

            const imageInput = document.getElementById('imagen');
            const imageFile = imageInput && imageInput.files.length > 0 ? imageInput.files[0] : null;

            if (!categoriaData.nombre || !categoriaData.estado) {
                this.showError('Todos los campos son obligatorios');
                return;
            }

            if (imageFile) {
                const validation = this.categoriaCRUD.validateFile(imageFile);
                if (!validation.valid) {
                    validation.errors.forEach(err => this.showError(err));
                    return;
                }
            }

            let result;
            const mode = form.dataset.mode || 'create';
            if (mode === 'edit') {
                const id = form.dataset.id;
                result = await this.categoriaCRUD.update(id, categoriaData, imageFile);

                submitBtn.textContent = "<i class='fa-solid fa-plus'></i> Agregar Categoría";
                this.clearImagePreview();
            } else {
                result = await this.categoriaCRUD.create(categoriaData, imageFile);
            }
            
            if (result && result.success) {
                form.reset();
                form.classList.remove('was-validated');
                this.clearImagePreview();
                this.setFormMode('create');
                await this.loadCategorias();
            }
        } catch (error) {
            this.showError('Error al procesar la solicitud: ' + error.message);
        } finally {
            this.isSubmitting = false;
            submitBtn.disabled = false;
            submitBtn.textContent = originalText;
        }
    }

    async handleEdit(id) {
        try {
            const categoria = await this.categoriaCRUD.findById(id);
            if (categoria) {
                this.fillForm(categoria);
                this.setFormMode('edit', id);
            }
        } catch (error) {
            this.showError('Error al cargar los datos de la categoría');
        }
    }

    async handleDelete(id) {
        if (confirm('¿Está seguro de que desea eliminar esta categoría?')) {
            try {
                const result = await this.categoriaCRUD.delete(id);
                if (result && result.success) {
                    await this.loadCategorias();
                }
            } catch (error) {
                this.showError('Error al eliminar la categoría');
            }
        }
    }

    fillForm(categoria) {
        document.getElementById('validationCustom01').value = categoria.nombre;
        document.getElementById('validationCustom02').value = categoria.estado;

        if (categoria.imagen) {
            this.showCurrentImage(categoria.imagen);
        }
    }

    setFormMode(mode, id = null) {
        const form = document.querySelector('.needs-validation');
        const submitBtn = form.querySelector('button[type="submit"]');
        
        this.removeCancelButton();
        
        if (mode === 'edit') {
            submitBtn.textContent = 'Actualizar Categoría';
            form.dataset.mode = 'edit';
            form.dataset.id = id;
            setTimeout(() => {
                this.addCancelButton();
            }, 10);
        } else {
            submitBtn.textContent = 'Agregar Categoría';
            form.dataset.mode = 'create';
            delete form.dataset.id;
            this.clearImagePreview();
        }
    }

    addCancelButton() {
        this.removeCancelButton();
        
        const form = document.querySelector('.needs-validation');
        const submitBtn = form.querySelector('button[type="submit"]');
        const formGroup = submitBtn.parentNode;

        const cancelBtn = document.createElement('button');
        cancelBtn.type = 'button';
        cancelBtn.className = 'btn btn-secondary btn-cancel-edit ms-2';
        cancelBtn.innerHTML = '<i class="fa-solid fa-xmark me-1"></i>Cancelar';
        formGroup.appendChild(cancelBtn);
    }

    removeCancelButton() {
        const cancelBtns = document.querySelectorAll('.btn-cancel-edit');
        cancelBtns.forEach(btn => {
            if (btn.parentNode) {
                btn.parentNode.removeChild(btn);
            }
        });
    }

    cancelEdit() {
        const form = document.querySelector('.needs-validation');
        form.reset();
        form.classList.remove('was-validated');
        this.setFormMode('create');
    }

    handleImagePreview(e) {
        const file = e.target.files[0];
        if (file) {
            const validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/svg+xml', 'image/webp'];
            if (!validTypes.includes(file.type)) {
                alert('Por favor selecciona un archivo de imagen válido (JPG, PNG, SVG, WEBP)');
                e.target.value = '';
                this.clearImagePreview();
                return;
            }

            if (file.size > 5 * 1024 * 1024) {
                alert('El archivo es demasiado grande. El tamaño máximo es 5MB');
                e.target.value = '';
                this.clearImagePreview();
                return;
            }

            const reader = new FileReader();
            reader.onload = (e) => {
                this.showImagePreview(e.target.result);
            };
            reader.readAsDataURL(file);
        } else {
            this.clearImagePreview();
        }
    }

    showImagePreview(src) {
        const previewContainer = document.getElementById('image-preview-container');
        const currentImageContainer = document.getElementById('current-image-container');
        const previewImage = document.getElementById('image-preview');
        
        if (previewContainer && previewImage) {
            previewImage.src = src;
            previewContainer.style.display = 'block';
            
            if (currentImageContainer) {
                currentImageContainer.style.display = 'none';
            }
        }
    }

    showCurrentImage(src) {
        const currentImageContainer = document.getElementById('current-image-container');
        const currentImage = document.getElementById('current-image');
        const previewContainer = document.getElementById('image-preview-container');
        
        if (currentImageContainer && currentImage) {
            currentImage.src = src;
            currentImageContainer.style.display = 'block';
            
            if (previewContainer) {
                previewContainer.style.display = 'none';
            }
        }
    }

    clearImagePreview() {
        const previewContainer = document.getElementById('image-preview-container');
        const currentImageContainer = document.getElementById('current-image-container');
        const imageInput = document.getElementById('imagen');
        
        if (previewContainer) {
            previewContainer.style.display = 'none';
        }
        
        if (currentImageContainer) {
            currentImageContainer.style.display = 'none';
        }
        
        if (imageInput) {
            imageInput.value = '';
        }
    }

    showError(message) {
        if (this.categoriaCRUD && this.categoriaCRUD.showNotification) {
            this.categoriaCRUD.showNotification(message, 'error');
        } else {
            alert(message);
        }
    }

    renderCategoriasTable() {
        let tableContainer = document.getElementById('categorias-table-container');
        
        if (!tableContainer) {
            tableContainer = document.createElement('div');
            tableContainer.id = 'categorias-table-container';
            tableContainer.className = 'mt-4';
            
            const cardBody = document.querySelector('#categoria-list-container');
            
            const filtersSection = this.createFiltersSection();
            cardBody.insertAdjacentHTML('beforeend', filtersSection);
            cardBody.appendChild(tableContainer);
            
            setTimeout(() => {
                this.setupFilterEvents();
            }, 100);
        }
    
        this.calculatePagination();
        const currentPageData = this.getCurrentPageData();
    
        const tableHTML = `
            ${this.createPaginationControls()}
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fa-solid fa-table me-1"></i>
                        Lista de Categorías
                        <span class="badge bg-primary ms-2">${this.filteredCategorias.length}</span>
                    </h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover mb-0">
                            <thead class="table-dark">
                                <tr>
                                    <th style="width: 80px;">ID</th>
                                    <th style="width: 100px;">Imagen</th>
                                    <th>Nombre</th>
                                    <th style="width: 120px;">Estado</th>
                                    <th style="width: 150px;">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${currentPageData.length > 0 ? currentPageData.map(categoria => `
                                    <tr>
                                        <td><strong>#${categoria.id}</strong></td>
                                        <td class="text-center">
                                            ${categoria.imagen
                                                ? `<img src="${categoria.imagen}" alt="${categoria.nombre}" 
                                                     style="width: 50px; height: 50px; object-fit: cover;" 
                                                     class="img-thumbnail rounded-circle">`
                                                : '<div class="bg-light rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;"><i class="fa-solid fa-image-off text-muted"></i></div>'
                                            }
                                        </td>
                                        <td>
                                            <div class="fw-bold">${categoria.nombre}</div>
                                        </td>
                                        <td>
                                            <span class="badge ${categoria.estado == 1 ? 'bg-success' : 'bg-danger'}">
                                                <i class="fa-solid fa-${categoria.estado == 1 ? 'check-circle' : 'xmark'} me-1"></i>
                                                ${categoria.estado == 1 ? 'Activo' : 'Inactivo'}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <button class="btn btn-sm btn-outline-primary btn-edit-categoria" 
                                                        data-id="${categoria.id}" title="Editar">
                                                    <i class="fa-solid fa-pencil"></i>
                                                </button>
                                                <button class="btn btn-sm btn-outline-danger btn-delete-categoria" 
                                                        data-id="${categoria.id}" title="Eliminar">
                                                    <i class="fa-solid fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                `).join('') : `
                                    <tr>
                                        <td colspan="5" class="text-center py-4 text-muted">
                                            <i class="fa-solid fa-info-circle me-2"></i>
                                            No se encontraron categorías que coincidan con los filtros aplicados
                                        </td>
                                    </tr>
                                `}
                            </tbody>
                        </table>
                    </div>
                </div>
                ${currentPageData.length > 0 ? `
                    <div class="card-footer bg-light">
                        <div class="d-flex justify-content-between align-items-center">
                            <small class="text-muted">
                                ${this.pagination.totalPages > 1 ? 
                                    `Página ${this.pagination.currentPage} de ${this.pagination.totalPages}` :
                                    `${this.pagination.totalItems} registro${this.pagination.totalItems !== 1 ? 's' : ''} en total`
                                }
                            </small>
                            <div class="btn-group btn-group-sm">
                                <button type="button" class="btn btn-outline-secondary" onclick="window.print()">
                                    <i class="fa-solid fa-print me-1"></i>
                                    Imprimir
                                </button>
                            </div>
                        </div>
                    </div>
                ` : ''}
            </div>
        `;
    
        tableContainer.innerHTML = tableHTML;
        
        this.setupPaginationEvents();
    }

    setupPaginationEvents() {
        const itemsPerPageSelect = document.getElementById('items-per-page-select');
        if (itemsPerPageSelect && !itemsPerPageSelect.hasAttribute('data-event-bound')) {
            itemsPerPageSelect.addEventListener('change', (e) => {
                const newItemsPerPage = parseInt(e.target.value);
                this.changeItemsPerPage(newItemsPerPage);
            });
            itemsPerPageSelect.setAttribute('data-event-bound', 'true');
        }
    }

    imageFunction() {
        if (this.imageSystemInitialized) {
            return;
        }

        const imageInput = document.getElementById('imagen');
        
        if (!imageInput) {
            return;
        }

        this.createImagePreviewContainers();
        this.setupImageClearButtons();
        this.setupImageValidations(imageInput);
        
        this.imageSystemInitialized = true;
    }
    
    createImagePreviewContainers() {
        const imageInputContainer = document.getElementById('imagen')?.parentNode;
        
        if (!imageInputContainer) return;
        
        if (!document.getElementById('image-preview-container')) {
            const previewContainer = document.createElement('div');
            previewContainer.id = 'image-preview-container';
            previewContainer.className = 'mt-3';
            previewContainer.style.display = 'none';
            
            previewContainer.innerHTML = `
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h6 class="mb-0">Vista previa de nueva imagen</h6>
                        <button type="button" class="btn btn-sm btn-outline-danger" id="clear-preview-btn">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>
                    <div class="card-body text-center">
                        <img id="image-preview" src="" alt="Preview" class="img-fluid" style="max-height: 200px;">
                    </div>
                </div>
            `;
            
            imageInputContainer.appendChild(previewContainer);
        }
        
        if (!document.getElementById('current-image-container')) {
            const currentContainer = document.createElement('div');
            currentContainer.id = 'current-image-container';
            currentContainer.className = 'mt-3';
            currentContainer.style.display = 'none';
            
            currentContainer.innerHTML = `
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h6 class="mb-0">Imagen actual</h6>
                        <button type="button" class="btn btn-sm btn-outline-warning" id="change-image-btn">
                            <i class="fa-solid fa-pencil"></i> Cambiar
                        </button>
                    </div>
                    <div class="card-body text-center">
                        <img id="current-image" src="" alt="Imagen actual" class="img-fluid" style="max-height: 200px;">
                    </div>
                </div>
            `;
            
            imageInputContainer.appendChild(currentContainer);
        }
    }
    
    setupImageClearButtons() {
        document.addEventListener('click', (e) => {
            if (e.target.id === 'clear-preview-btn' || e.target.closest('#clear-preview-btn')) {
                this.clearImagePreview();
            }
            
            if (e.target.id === 'change-image-btn' || e.target.closest('#change-image-btn')) {
                const imageInput = document.getElementById('imagen');
                if (imageInput) {
                    imageInput.click();
                }
            }
        });
    }
    
    setupImageValidations(imageInput) {
        const validateImageFormat = (file) => {
            const validTypes = [
                'image/jpeg', 'image/jpg', 'image/png',
                'image/svg+xml', 'image/webp'
            ];
            
            if (!validTypes.includes(file.type)) {
                this.showError('Formato de imagen no válido. Usa: JPG, PNG, SVG, WEBP');
                return false;
            }
            
            return true;
        };
        
        const validateImageDimensions = (file) => {
            return new Promise((resolve) => {
                const img = new Image();
                const url = URL.createObjectURL(file);
                
                img.onload = () => {
                    URL.revokeObjectURL(url);
                    
                    const minWidth = 100, minHeight = 100;
                    const maxWidth = 2000, maxHeight = 2000;
                    
                    if (img.width < minWidth || img.height < minHeight) {
                        this.showError(`La imagen debe tener al menos ${minWidth}x${minHeight} píxeles`);
                        resolve(false);
                        return;
                    }
                    
                    if (img.width > maxWidth || img.height > maxHeight) {
                        this.showError(`La imagen no debe exceder ${maxWidth}x${maxHeight} píxeles`);
                        resolve(false);
                        return;
                    }
                    
                    resolve(true);
                };
                
                img.onerror = () => {
                    URL.revokeObjectURL(url);
                    this.showError('Error al cargar la imagen');
                    resolve(false);
                };
                
                img.src = url;
            });
        };
        
        this.handleImagePreview = async (e) => {
            const file = e.target.files[0];
            
            if (!file) {
                this.clearImagePreview();
                return;
            }
            
            const validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/svg+xml', 'image/webp'];
            if (!validTypes.includes(file.type)) {
                alert('Por favor selecciona un archivo de imagen válido (JPG, PNG, SVG, WEBP)');
                e.target.value = '';
                this.clearImagePreview();
                return;
            }
            
            if (file.size > 5 * 1024 * 1024) {
                alert('El archivo es demasiado grande. El tamaño máximo es 5MB');
                e.target.value = '';
                this.clearImagePreview();
                return;
            }
            
            if (!validateImageFormat(file)) {
                e.target.value = '';
                this.clearImagePreview();
                return;
            }
            
            if (file.type !== 'image/svg+xml') {
                const isDimensionsValid = await validateImageDimensions(file);
                if (!isDimensionsValid) {
                    e.target.value = '';
                    this.clearImagePreview();
                    return;
                }
            }
            
            const reader = new FileReader();
            reader.onload = (e) => {
                this.showImagePreview(e.target.result);
            };
            reader.readAsDataURL(file);
        };
    }
}