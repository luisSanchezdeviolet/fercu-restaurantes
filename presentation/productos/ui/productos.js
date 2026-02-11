class ProductosUI {
    constructor() {
        this.isInitialized = false;
        this.productoCRUD = new ProductoCRUD();
        this.currentIngredientes = [];
        this.allIngredientes = [];
        this.allCategorias = [];
        this.selectedIngredientes = new Map();
        this.filteredIngredientes = [];
        this.currentPage = 1;
        this.itemsPerPage = 10;
        this.totalItems = 0;
        this.editingProductId = null;
        this.currentFilters = {
            search: '',
            categoria: '',
            estado: ''
        };
        this.productos = [];
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

        await this.loadCategorias();
        await this.loadIngredientes();

        this.initializeForm();
        this.initializeIngredientesUI();
        this.initializeSearchIngredientes();
        this.initializeCollapseToggle();
        this.createSubmitButton();

        this.initializeFilters();
        await this.loadProductos();
        this.initializeImagePreview();

        console.log('ProductosUI inicializado correctamente');
    }

    async loadCategorias() {
        try {
            const response = await fetch('../../../api/categorias.php?action=filterByEstado&estado=1');
            const result = await response.json();

            if (result.success) {
                this.allCategorias = result.data;
                this.populateCategoriasSelect();
            }
        } catch (error) {
            console.error('Error al cargar categorías:', error);
            this.productoCRUD.showNotification('Error al cargar categorías', 'error');
        }
    }

    async loadIngredientes() {
        try {
            const response = await fetch('../../../api/ingredientes.php?action=filterByEstado&estado=Disponible');
            const result = await response.json();

            if (result.success) {
                this.allIngredientes = result.data;
                this.filteredIngredientes = [...this.allIngredientes];
                this.renderIngredientesDisponibles();
            }
        } catch (error) {
            console.error('Error al cargar ingredientes:', error);
            this.productoCRUD.showNotification('Error al cargar ingredientes', 'error');
        }
    }

    populateCategoriasSelect() {
        const select = document.getElementById('categorias');
        if (!select) return;

        while (select.children.length > 1) {
            select.removeChild(select.lastChild);
        }

        this.allCategorias.forEach(categoria => {
            const option = document.createElement('option');
            option.value = categoria.id;
            option.textContent = categoria.nombre;
            select.appendChild(option);
        });
    }

    initializeIngredientesUI() {
        this.renderIngredientesDisponibles();
        this.renderIngredientesSeleccionados();
    }

    initializeCollapseToggle() {
        const collapseElement = document.getElementById('formulario-producto');
        const toggleIcon = document.getElementById('toggle-icon');
        const toggleText = document.getElementById('toggle-text');

        if (!collapseElement || !toggleIcon || !toggleText) return;

        collapseElement.addEventListener('show.bs.collapse', () => {
            toggleIcon.className = 'fas fa-minus';
            toggleText.textContent = 'Cerrar Formulario';
        });

        collapseElement.addEventListener('hide.bs.collapse', () => {
            toggleIcon.className = 'fas fa-plus';
            toggleText.textContent = 'Abrir Formulario';
        });
    }

    initializeSearchIngredientes() {
        const searchInput = document.getElementById('search-ingredientes');
        if (!searchInput) return;

        searchInput.addEventListener('input', (e) => {
            this.filterIngredientes(e.target.value);
        });
    }

    initializeFilters() {
        const filtersContainer = document.getElementById('filtros-productos');
        if (!filtersContainer) return;

        filtersContainer.innerHTML = `
        <div class="row g-3 mb-4">
            <div class="col-12 col-md-3">
                <div class="input-group">
                    <span class="input-group-text">
                        <i class="fas fa-search"></i>
                    </span>
                    <input type="text" class="form-control" id="search-productos" placeholder="Buscar productos...">
                </div>
            </div>
            <div class="col-12 col-md-2">
                <select class="form-select" id="filter-categoria">
                    <option value="">Todas las categorías</option>
                </select>
            </div>
            <div class="col-12 col-md-2">
                <select class="form-select" id="filter-estado">
                    <option value="">Todos los estados</option>
                    <option value="Disponible">Disponible</option>
                    <option value="No Disponible">No Disponible</option>
                </select>
            </div>
            <div class="col-12 col-md-2">
                <select class="form-select" id="items-per-page">
                    <option value="5">5 por página</option>
                    <option value="10" selected>10 por página</option>
                    <option value="20">20 por página</option>
                    <option value="50">50 por página</option>
                </select>
            </div>
            <div class="col-12 col-md-3">
                <button class="btn btn-outline-secondary w-100" id="clear-filters">
                    <i class="fas fa-eraser"></i> Limpiar Filtros
                </button>
            </div>
        </div>
    `;

        // Poblar categorías
        const categorySelect = document.getElementById('filter-categoria');
        this.allCategorias.forEach(categoria => {
            const option = document.createElement('option');
            option.value = categoria.id;
            option.textContent = categoria.nombre;
            categorySelect.appendChild(option);
        });

        // Event listeners
        document.getElementById('search-productos').addEventListener('input',
            this.debounce((e) => this.handleFilterChange('search', e.target.value), 300)
        );

        document.getElementById('filter-categoria').addEventListener('change',
            (e) => this.handleFilterChange('categoria', e.target.value)
        );

        document.getElementById('filter-estado').addEventListener('change',
            (e) => this.handleFilterChange('estado', e.target.value)
        );

        // NUEVO: Event listener para cambio de elementos por página
        document.getElementById('items-per-page').addEventListener('change',
            (e) => this.handleItemsPerPageChange(parseInt(e.target.value))
        );

        document.getElementById('clear-filters').addEventListener('click',
            () => this.clearFilters()
        );
    }

    handleItemsPerPageChange(newItemsPerPage) {
        this.itemsPerPage = newItemsPerPage;
        this.currentPage = 1; // Resetear a la primera página
        this.loadProductos();
    }

    filterIngredientes(searchTerm) {
        const term = searchTerm.toLowerCase().trim();

        if (!term) {
            this.filteredIngredientes = [...this.allIngredientes];
        } else {
            this.filteredIngredientes = this.allIngredientes.filter(ingrediente =>
                ingrediente.nombre.toLowerCase().includes(term)
            );
        }

        this.renderIngredientesDisponibles();
    }

    renderIngredientesDisponibles() {
        const container = document.getElementById('ingredientes-disponibles');
        if (!container) return;

        if (this.filteredIngredientes.length === 0) {
            container.innerHTML = '<p class="text-muted text-center">No se encontraron ingredientes</p>';
            return;
        }

        let html = '';
        this.filteredIngredientes.forEach(ingrediente => {
            const isSelected = this.selectedIngredientes.has(ingrediente.id.toString());

            html += `
                <div class="ingrediente-item mb-2">
                    <div class="form-check d-flex align-items-center p-2">
                        <input 
                            class="form-check-input me-3" 
                            type="checkbox" 
                            id="ingrediente-${ingrediente.id}"
                            data-ingrediente-id="${ingrediente.id}"
                            data-ingrediente-nombre="${ingrediente.nombre}"
                            data-ingrediente-unidad="${ingrediente.unidad}"
                            ${isSelected ? 'checked' : ''}
                        >
                        <label class="form-check-label flex-grow-1" for="ingrediente-${ingrediente.id}">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="fw-medium">${ingrediente.nombre}</span>
                                <span class="badge bg-light text-dark">${ingrediente.unidad}</span>
                            </div>
                        </label>
                    </div>
                </div>
            `;
        });

        container.innerHTML = html;

        container.querySelectorAll('input[type="checkbox"]').forEach(checkbox => {
            checkbox.addEventListener('change', (e) => {
                this.handleIngredienteToggle(e);
            });
        });
    }

    handleIngredienteToggle(e) {
        const checkbox = e.target;
        const id = checkbox.dataset.ingredienteId;
        const nombre = checkbox.dataset.ingredienteNombre;
        const unidad = checkbox.dataset.ingredienteUnidad;

        if (checkbox.checked) {
            this.selectedIngredientes.set(id, {
                nombre: nombre,
                cantidad: 1,
                unidad: unidad
            });
        } else {
            this.selectedIngredientes.delete(id);
        }

        this.renderIngredientesSeleccionados();
    }

    renderIngredientesSeleccionados() {
        const container = document.getElementById('lista-ingredientes-seleccionados');
        if (!container) return;

        if (this.selectedIngredientes.size === 0) {
            container.innerHTML = '<p class="text-muted">No hay ingredientes seleccionados</p>';
            return;
        }

        let html = '';
        for (let [id, data] of this.selectedIngredientes) {
            html += `
                <div class="card mb-2">
                    <div class="card-body p-3">
                        <div class="row align-items-center">
                            <div class="col-12 col-md-5 mb-2 mb-md-0">
                                <h6 class="mb-1">${data.nombre}</h6>
                                <small class="text-muted">Unidad: ${data.unidad}</small>
                            </div>
                            <div class="col-8 col-md-5 mb-2 mb-md-0">
                                <label class="form-label small">Cantidad</label>
                                <div class="input-group input-group-sm">
                                    <input 
                                        type="number" 
                                        class="form-control cantidad-input" 
                                        data-ingrediente-id="${id}"
                                        value="${data.cantidad}" 
                                        min="0.1" 
                                        step="0.1"
                                        required
                                    >
                                    <span class="input-group-text">${data.unidad}</span>
                                </div>
                            </div>
                            <div class="col-4 col-md-2 text-end">
                                <button 
                                    type="button" 
                                    class="btn btn-outline-danger btn-sm remove-ingrediente"
                                    data-ingrediente-id="${id}"
                                    title="Remover ingrediente"
                                >
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }

        container.innerHTML = html;

        container.querySelectorAll('.cantidad-input').forEach(input => {
            input.addEventListener('change', (e) => {
                const id = e.target.dataset.ingredienteId;
                const cantidad = parseFloat(e.target.value) || 0;

                if (this.selectedIngredientes.has(id)) {
                    this.selectedIngredientes.get(id).cantidad = cantidad;
                }
            });
        });

        container.querySelectorAll('.remove-ingrediente').forEach(button => {
            button.addEventListener('click', (e) => {
                const id = e.target.closest('[data-ingrediente-id]').dataset.ingredienteId;
                this.removeIngrediente(id);
            });
        });
    }

    removeIngrediente(ingredienteId) {
        this.selectedIngredientes.delete(ingredienteId);

        const checkbox = document.getElementById(`ingrediente-${ingredienteId}`);
        if (checkbox) {
            checkbox.checked = false;
        }

        this.renderIngredientesSeleccionados();
    }

    validateIngredientes() {
        let isValid = true;
        let errors = [];

        for (let [id, data] of this.selectedIngredientes) {
            if (!data.cantidad || data.cantidad <= 0) {
                errors.push(`La cantidad para ${data.nombre} debe ser mayor a 0`);
                isValid = false;
            }
        }

        if (errors.length > 0) {
            this.productoCRUD.showNotification(errors.join(', '), 'error');
        }

        return isValid;
    }

    initializeForm() {
        const form = document.querySelector('.needs-validation');
        if (!form) return;

        const newForm = form.cloneNode(true);
        form.parentNode.replaceChild(newForm, form);

        newForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            e.stopPropagation();

            if (newForm.checkValidity() && this.validateIngredientes()) {
                await this.handleFormSubmit(newForm);
            }

            newForm.classList.add('was-validated');
        });
    }

    createSubmitButton() {
        const form = document.querySelector('.needs-validation');
        if (!form) return;

        let submitButton = form.querySelector('button[type="submit"]');

        if (!submitButton) {
            const buttonContainer = document.createElement('div');
            buttonContainer.className = 'row mt-4';
            buttonContainer.innerHTML = `
            <div class="col-12">
                <div class="d-flex justify-content-end gap-2">
                    <button type="button" class="btn btn-secondary" id="resetForm">
                        <i class="fas fa-undo"></i> Limpiar
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Guardar Producto
                    </button>
                </div>
            </div>
        `;

            form.appendChild(buttonContainer);

            const resetButton = buttonContainer.querySelector('#resetForm');
            resetButton.addEventListener('click', () => {
                this.resetForm();
            });
        }

        this.updateSubmitButton('create');
    }

    async handleFormSubmit(form) {
        const formData = new FormData(form);

        const productoData = {
            nombre: formData.get('nombre'),
            precio: parseFloat(formData.get('precio')),
            categoria_id: formData.get('categorias') || null,
            descripcion: formData.get('descripcion') || null,
            ingredientes: [],
            estado: formData.get('estado')
        };

        if (this.editingProductId) {
            productoData.id = this.editingProductId;
        }

        for (let [id, data] of this.selectedIngredientes) {
            productoData.ingredientes.push({
                ingrediente_id: parseInt(id),
                cantidad: data.cantidad
            });
        }

        const imageFile = formData.get('imagen');
        const hasImage = imageFile && imageFile.size > 0;

        try {
            let result;

            if (this.editingProductId) {
                result = await this.productoCRUD.update(this.editingProductId, productoData, hasImage ? imageFile : null);

                if (result.success) {
                    this.productoCRUD.showNotification('Producto actualizado exitosamente', 'success');
                    this.resetForm();
                    await this.loadProductos();
                }
            } else {
                result = await this.productoCRUD.create(productoData, hasImage ? imageFile : null);

                if (result.success) {
                    this.productoCRUD.showNotification('Producto creado exitosamente', 'success');
                    this.resetForm();
                    await this.loadProductos();
                }
            }
        } catch (error) {
            console.error('Error al guardar producto:', error);
            const action = this.editingProductId ? 'actualizar' : 'crear';
            this.productoCRUD.showNotification(`Error al ${action} el producto`, 'error');
        }
    }

    resetForm() {
        const form = document.querySelector('.needs-validation');
        if (form) {
            form.reset();
            form.classList.remove('was-validated');
        }

        this.editingProductId = null;
        this.updateSubmitButton('create');

        this.selectedIngredientes.clear();

        const searchInput = document.getElementById('search-ingredientes');
        if (searchInput) {
            searchInput.value = '';
            this.filteredIngredientes = [...this.allIngredientes];
        }

        this.renderIngredientesDisponibles();
        this.renderIngredientesSeleccionados();

        const collapseElement = document.getElementById('formulario-producto');
        if (collapseElement && collapseElement.classList.contains('show')) {
            const bsCollapse = new bootstrap.Collapse(collapseElement, {
                toggle: false
            });
            bsCollapse.hide();
        }

        this.productoCRUD.showNotification('Formulario limpiado', 'info');
    }

    updateSubmitButton(mode) {
        const submitButton = document.querySelector('button[type="submit"]');
        if (submitButton) {
            if (mode === 'edit') {
                submitButton.innerHTML = '<i class="fas fa-save"></i> Actualizar Producto';
                submitButton.className = 'btn btn-warning';
            } else {
                submitButton.innerHTML = '<i class="fas fa-save"></i> Guardar Producto';
                submitButton.className = 'btn btn-primary';
            }
        }
    }

    initializeImagePreview() {
        const imageInput = document.getElementById('imagen');
        if (!imageInput) return;

        imageInput.addEventListener('change', (e) => {
            const file = e.target.files[0];
            if (file) {
                const validation = this.productoCRUD.validateFile(file);
                if (!validation.valid) {
                    e.target.value = '';
                    return;
                }

                this.showImagePreview(file);
            }
        });
    }

    showImagePreview(file) {
        const reader = new FileReader();
        reader.onload = (e) => {
            let preview = document.getElementById('image-preview');
            if (!preview) {
                preview = document.createElement('div');
                preview.id = 'image-preview';
                preview.className = 'mt-2';

                const imageInput = document.getElementById('imagen');
                imageInput.parentNode.appendChild(preview);
            }

            preview.innerHTML = `
                <div class="card" style="max-width: 200px;">
                    <img src="${e.target.result}" class="card-img-top" alt="Vista previa" style="height: 150px; object-fit: cover;">
                    <div class="card-body p-2">
                        <small class="text-muted">${file.name}</small>
                    </div>
                </div>
            `;
        };
        reader.readAsDataURL(file);
    }

    async loadProductos() {
        try {
            // Construir parámetros de consulta
            const params = new URLSearchParams({
                action: 'findAll',
                page: this.currentPage,
                limit: this.itemsPerPage
            });

            // Agregar filtros si existen
            if (this.currentFilters.search.trim()) {
                params.append('search', this.currentFilters.search.trim());
            }
            if (this.currentFilters.categoria) {
                params.append('categoria_id', this.currentFilters.categoria);
            }
            if (this.currentFilters.estado) {
                params.append('estado', this.currentFilters.estado);
            }

            const response = await fetch(`../../../api/productos.php?${params.toString()}`);
            const result = await response.json();

            if (result.success) {
                this.productos = result.data || [];
                this.totalItems = result.total || 0;
                this.renderTable();
                this.renderPagination();
            } else {
                throw new Error(result.message || 'Error al cargar productos');
            }
        } catch (error) {
            console.error('Error al cargar productos:', error);
            this.productoCRUD.showNotification('Error al cargar productos', 'error');
            this.renderEmptyTable();
        }
    }

    renderTable() {
        const tableContainer = document.getElementById('tabla-productos');
        if (!tableContainer) return;

        if (this.productos.length === 0) {
            this.renderEmptyTable();
            return;
        }

        const isMobile = window.innerWidth < 768;

        if (isMobile) {
            this.renderMobileCards();
        } else {
            this.renderDesktopTable();
        }
    }

    renderDesktopTable() {
        const tableContainer = document.getElementById('tabla-productos');

        const tableHTML = `
            <thead class="table-dark">
                <tr>
                    <th scope="col" class="text-center" style="width: 80px;">
                        <i class="fas fa-image"></i>
                    </th>
                    <th scope="col">
                        <i class="fas fa-utensils me-1"></i>Producto
                    </th>
                    <th scope="col" class="d-none d-md-table-cell">
                        <i class="fas fa-tag me-1"></i>Categoría
                    </th>
                    <th scope="col" class="text-center">
                        <i class="fas fa-dollar-sign me-1"></i>Precio
                    </th>
                    <th scope="col" class="text-center d-none d-lg-table-cell">
                        <i class="fas fa-info-circle me-1"></i>Estado
                    </th>
                    <th scope="col" class="text-center" style="width: 150px;">
                        <i class="fas fa-cogs me-1"></i>Acciones
                    </th>
                </tr>
            </thead>
            <tbody>
                ${this.productos.map(producto => this.renderProductoRow(producto)).join('')}
            </tbody>
        `;

        tableContainer.innerHTML = tableHTML;
        this.attachTableEventListeners();
    }

    renderMobileCards() {
        const tableContainer = document.getElementById('tabla-productos');

        let cardsHTML = '';
        this.productos.forEach(producto => {
            const estadoBadge = producto.estado === 'Disponible'
                ? '<span class="badge bg-success"><i class="fas fa-check me-1"></i>Disponible</span>'
                : '<span class="badge bg-danger"><i class="fas fa-times me-1"></i>No Disponible</span>';

            const imageSrc = producto.imagen
                ? `${producto.imagen}`
                : 'assets/images/brands/slack.png';

            cardsHTML += `
                <div class="card mb-3 shadow-sm" data-producto-id="${producto.id}">
                    <div class="card-body p-3">
                        <div class="row g-3">
                            <!-- Imagen del producto -->
                            <div class="col-4 col-sm-3">
                                <div class="text-center">
                                    <img src="${imageSrc}" 
                                        alt="${producto.nombre}" 
                                        class="img-fluid rounded"
                                        style="width: 80px; height: 80px; object-fit: cover;"
                                        onerror="this.src='assets/images/brands/slack.png'">
                                </div>
                            </div>
                            
                            <!-- Información del producto -->
                            <div class="col-8 col-sm-9">
                                <div class="h-100 d-flex flex-column">
                                    <!-- Nombre y categoría -->
                                    <div class="flex-grow-1">
                                        <h6 class="card-title mb-1 fw-bold text-truncate">${producto.nombre}</h6>
                                        <div class="mb-2">
                                            <span class="badge bg-light text-dark small">
                                                <i class="fas fa-tag me-1"></i>${producto.categoria_nombre || 'Sin categoría'}
                                            </span>
                                        </div>
                                        ${producto.descripcion ? `
                                            <p class="card-text small text-muted mb-2" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                                                ${producto.descripcion}
                                            </p>
                                        ` : ''}
                                    </div>
                                    
                                    <!-- Precio y estado -->
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <div class="fw-bold text-success fs-5">
                                            <i class="fas fa-dollar-sign"></i>${parseFloat(producto.precio).toFixed(2)}
                                        </div>
                                        <div>
                                            ${estadoBadge}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Botones de acción -->
                        <div class="row mt-3">
                            <div class="col-12">
                                <div class="d-flex gap-2 justify-content-center">
                                    <button type="button" 
                                            class="btn btn-outline-info btn-sm view-producto flex-fill" 
                                            data-producto-id="${producto.id}"
                                            title="Ver detalles">
                                        <i class="fas fa-eye me-1"></i>Ver
                                    </button>
                                    <button type="button" 
                                            class="btn btn-outline-warning btn-sm edit-producto flex-fill" 
                                            data-producto-id="${producto.id}"
                                            title="Editar">
                                        <i class="fas fa-edit me-1"></i>Editar
                                    </button>
                                    <button type="button" 
                                            class="btn btn-outline-danger btn-sm delete-producto flex-fill" 
                                            data-producto-id="${producto.id}"
                                            title="Eliminar">
                                        <i class="fas fa-trash me-1"></i>Eliminar
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        });

        tableContainer.innerHTML = `<div class="mobile-cards-container">${cardsHTML}</div>`;
        this.attachTableEventListeners();
    }

    renderProductoRow(producto) {
        const estadoBadge = producto.estado === 'Disponible'
            ? '<span class="badge bg-success"><i class="fas fa-check me-1"></i>Disponible</span>'
            : '<span class="badge bg-danger"><i class="fas fa-times me-1"></i>No Disponible</span>';

        const imageSrc = producto.imagen
            ? `${producto.imagen}`
            : 'assets/images/brands/slack.png';

        return `
            <tr data-producto-id="${producto.id}">
                <td class="text-center">
                    <div class="avatar-sm">
                        <img src="${imageSrc}" 
                            alt="${producto.nombre}" 
                            class="img-thumbnail rounded-circle"
                            style="width: 50px; height: 50px; object-fit: cover;"
                            onerror="this.src='assets/images/brands/slack.png'">
                    </div>
                </td>
                <td>
                    <div>
                        <h6 class="mb-1 fw-bold">${producto.nombre}</h6>
                        ${producto.descripcion ? `<small class="text-muted">${this.truncateText(producto.descripcion, 50)}</small>` : ''}
                        <div class="d-md-none mt-1">
                            <small class="text-muted">
                                <i class="fas fa-tag me-1"></i>${producto.categoria_nombre || 'Sin categoría'}
                            </small>
                        </div>
                        <div class="d-lg-none mt-1">
                            ${estadoBadge}
                        </div>
                    </div>
                </td>
                <td class="d-none d-md-table-cell">
                    <span class="badge bg-light text-dark">
                        <i class="fas fa-tag me-1"></i>${producto.categoria_nombre || 'Sin categoría'}
                    </span>
                </td>
                <td class="text-center">
                    <span class="fw-bold text-success">
                        <i class="fas fa-dollar-sign"></i>${parseFloat(producto.precio).toFixed(2)}
                    </span>
                </td>
                <td class="text-center d-none d-lg-table-cell">
                    ${estadoBadge}
                </td>
                <td class="text-center">
                    <div class="btn-group" role="group">
                        <button type="button" 
                                class="btn btn-outline-info btn-sm view-producto" 
                                data-producto-id="${producto.id}"
                                title="Ver detalles">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button type="button" 
                                class="btn btn-outline-warning btn-sm edit-producto" 
                                data-producto-id="${producto.id}"
                                title="Editar">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button type="button" 
                                class="btn btn-outline-danger btn-sm delete-producto" 
                                data-producto-id="${producto.id}"
                                title="Eliminar">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `;
    }

    renderEmptyTable() {
        const tableContainer = document.getElementById('tabla-productos');
        if (!tableContainer) return;

        tableContainer.innerHTML = `
            <div class="text-center py-5">
                <div class="empty-state">
                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No se encontraron productos</h5>
                    <p class="text-muted">Intenta ajustar los filtros o agrega un nuevo producto</p>
                </div>
            </div>
        `;
    }

    renderPagination() {
        const paginationContainer = document.getElementById('paginacion-productos');
        if (!paginationContainer) return;

        const totalPages = Math.ceil(this.totalItems / this.itemsPerPage);

        if (totalPages <= 1) {
            paginationContainer.innerHTML = this.totalItems > 0 ? `
            <div class="text-center mt-2">
                <small class="text-muted">
                    <i class="fas fa-info-circle me-1"></i>
                    Mostrando ${this.totalItems} producto${this.totalItems !== 1 ? 's' : ''}
                </small>
            </div>
        ` : '';
            return;
        }

        let paginationHTML = `
        <nav aria-label="Paginación de productos">
            <ul class="pagination justify-content-center mb-0">
    `;

        // Botón anterior
        paginationHTML += `
        <li class="page-item ${this.currentPage === 1 ? 'disabled' : ''}">
            <button class="page-link" data-page="${this.currentPage - 1}" ${this.currentPage === 1 ? 'disabled' : ''}>
                <i class="fas fa-chevron-left"></i>
            </button>
        </li>
    `;

        // Lógica mejorada para mostrar páginas
        const startPage = Math.max(1, this.currentPage - 2);
        const endPage = Math.min(totalPages, this.currentPage + 2);

        // Primera página y puntos suspensivos si es necesario
        if (startPage > 1) {
            paginationHTML += `
            <li class="page-item">
                <button class="page-link" data-page="1">1</button>
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

        // Páginas del rango actual
        for (let i = startPage; i <= endPage; i++) {
            paginationHTML += `
            <li class="page-item ${i === this.currentPage ? 'active' : ''}">
                <button class="page-link" data-page="${i}">${i}</button>
            </li>
        `;
        }

        // Última página y puntos suspensivos si es necesario
        if (endPage < totalPages) {
            if (endPage < totalPages - 1) {
                paginationHTML += `
                <li class="page-item disabled">
                    <span class="page-link">...</span>
                </li>
            `;
            }
            paginationHTML += `
            <li class="page-item">
                <button class="page-link" data-page="${totalPages}">${totalPages}</button>
            </li>
        `;
        }

        // Botón siguiente
        paginationHTML += `
        <li class="page-item ${this.currentPage === totalPages ? 'disabled' : ''}">
            <button class="page-link" data-page="${this.currentPage + 1}" ${this.currentPage === totalPages ? 'disabled' : ''}>
                <i class="fas fa-chevron-right"></i>
            </button>
        </li>
    `;

        paginationHTML += `
            </ul>
        </nav>
        <div class="text-center mt-2">
            <small class="text-muted">
                <i class="fas fa-info-circle me-1"></i>
                Mostrando ${Math.min(((this.currentPage - 1) * this.itemsPerPage) + 1, this.totalItems)} - 
                ${Math.min(this.currentPage * this.itemsPerPage, this.totalItems)} 
                de ${this.totalItems} productos
            </small>
        </div>
    `;

        paginationContainer.innerHTML = paginationHTML;

        // Event listeners para botones de paginación
        paginationContainer.querySelectorAll('button[data-page]').forEach(button => {
            button.addEventListener('click', async (e) => {
                const page = parseInt(e.target.dataset.page);
                if (page && page !== this.currentPage && page >= 1 && page <= totalPages) {
                    this.currentPage = page;
                    await this.loadProductos();
                }
            });
        });
    }

    attachTableEventListeners() {
        document.querySelectorAll('.view-producto').forEach(button => {
            button.addEventListener('click', (e) => {
                const productId = e.target.closest('[data-producto-id]').dataset.productoId;
                this.viewProducto(productId);
            });
        });

        document.querySelectorAll('.edit-producto').forEach(button => {
            button.addEventListener('click', (e) => {
                const productId = e.target.closest('[data-producto-id]').dataset.productoId;
                this.editProducto(productId);
            });
        });

        document.querySelectorAll('.delete-producto').forEach(button => {
            button.addEventListener('click', (e) => {
                const productId = e.target.closest('[data-producto-id]').dataset.productoId;
                this.deleteProducto(productId);
            });
        });
    }

    handleFilterChange(filterType, value) {
        this.currentFilters[filterType] = value;
        this.currentPage = 1; // Siempre resetear a la primera página cuando se cambia un filtro
        this.loadProductos();
    }

    clearFilters() {
        this.currentFilters = {
            search: '',
            categoria: '',
            estado: ''
        };
        this.currentPage = 1;
        this.itemsPerPage = 10; // Resetear a valor por defecto

        // Limpiar campos del formulario
        document.getElementById('search-productos').value = '';
        document.getElementById('filter-categoria').value = '';
        document.getElementById('filter-estado').value = '';
        document.getElementById('items-per-page').value = '10';

        this.loadProductos();
    }

    async viewProducto(productId) {
        try {
            const response = await fetch(`../../../api/productos.php?action=findById&id=${productId}`);
            const result = await response.json();

            if (result.success) {
                this.showProductoModal(result.data, 'view');
            }
        } catch (error) {
            console.error('Error al cargar producto:', error);
            this.productoCRUD.showNotification('Error al cargar producto', 'error');
        }
    }

    async editProducto(productId) {
        try {
            const response = await fetch(`../../../api/productos.php?action=findById&id=${productId}`);
            const result = await response.json();

            if (result.success) {
                this.loadProductoInForm(result.data);
            }
        } catch (error) {
            console.error('Error al cargar producto:', error);
            this.productoCRUD.showNotification('Error al cargar producto', 'error');
        }
    }

    async deleteProducto(productId) {
        const producto = this.productos.find(p => p.id == productId);
        if (!producto) return;

        // Crear el modal de confirmación
        this.showDeleteModal(producto, async () => {
            try {
                const deleteResult = await this.productoCRUD.delete(productId);
                if (deleteResult.success) {
                    this.productoCRUD.showNotification('Producto eliminado exitosamente', 'success');
                    await this.loadProductos();
                }
            } catch (error) {
                console.error('Error al eliminar producto:', error);
                this.productoCRUD.showNotification('Error al eliminar producto', 'error');
            }
        });
    }

    // Agrega este nuevo método a la clase:

    showDeleteModal(producto, onConfirm) {
        const modalHTML = `
        <div class="modal fade" id="deleteProductoModal" tabindex="-1" aria-labelledby="deleteProductoModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title" id="deleteProductoModalLabel">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            Confirmar Eliminación
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>
                    <div class="modal-body">
                        <div class="text-center mb-3">
                            <i class="fas fa-trash-alt fa-4x text-danger mb-3"></i>
                            <h5 class="mb-3">¿Estás seguro de eliminar este producto?</h5>
                        </div>
                        
                        <div class="card bg-light">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col-3 text-center">
                                        <img src="${producto.imagen || 'assets/images/brands/slack.png'}" 
                                            alt="${producto.nombre}" 
                                            class="img-fluid rounded"
                                            style="width: 60px; height: 60px; object-fit: cover;"
                                            onerror="this.src='assets/images/brands/slack.png'">
                                    </div>
                                    <div class="col-9">
                                        <h6 class="mb-1 fw-bold">${producto.nombre}</h6>
                                        <small class="text-muted">
                                            <i class="fas fa-tag me-1"></i>${producto.categoria_nombre || 'Sin categoría'}
                                        </small>
                                        <div class="mt-1">
                                            <span class="fw-bold text-success">
                                                <i class="fas fa-dollar-sign"></i>${parseFloat(producto.precio).toFixed(2)}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="alert alert-warning mt-3 mb-0">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <strong>¡Atención!</strong> Esta acción no se puede deshacer. El producto será eliminado permanentemente.
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times me-1"></i>Cancelar
                        </button>
                        <button type="button" class="btn btn-danger" id="confirmDeleteProducto">
                            <i class="fas fa-trash me-1"></i>Sí, Eliminar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;

        // Remover modal existente si existe
        const existingModal = document.getElementById('deleteProductoModal');
        if (existingModal) {
            existingModal.remove();
        }

        // Insertar el modal en el DOM
        document.body.insertAdjacentHTML('beforeend', modalHTML);

        // Inicializar el modal
        const modal = new bootstrap.Modal(document.getElementById('deleteProductoModal'));

        // Agregar evento al botón de confirmación
        document.getElementById('confirmDeleteProducto').addEventListener('click', () => {
            modal.hide();
            onConfirm();
        });

        // Limpiar el modal del DOM cuando se cierre
        document.getElementById('deleteProductoModal').addEventListener('hidden.bs.modal', function () {
            this.remove();
        });

        // Mostrar el modal
        modal.show();
    }

    truncateText(text, maxLength) {
        if (!text) return '';
        return text.length > maxLength ? text.substring(0, maxLength) + '...' : text;
    }

    debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }

    loadProductoInForm(producto) {
        this.editingProductId = producto.id;

        const collapseElement = document.getElementById('formulario-producto');
        if (!collapseElement.classList.contains('show')) {
            const bsCollapse = new bootstrap.Collapse(collapseElement);
            bsCollapse.show();
        }

        document.getElementById('nombre').value = producto.nombre || '';
        document.getElementById('precio').value = producto.precio || '';
        document.getElementById('categorias').value = producto.categoria_id || '';
        document.getElementById('estado').value = producto.estado || '';
        document.getElementById('descripcion').value = producto.descripcion || '';

        this.selectedIngredientes.clear();
        if (producto.ingredientes && producto.ingredientes.length > 0) {
            producto.ingredientes.forEach(ing => {
                this.selectedIngredientes.set(ing.ingrediente_id.toString(), {
                    nombre: ing.ingrediente_nombre,
                    cantidad: parseFloat(ing.cantidad),
                    unidad: ing.unidad
                });
            });
        }

        this.renderIngredientesDisponibles();
        this.renderIngredientesSeleccionados();

        this.updateSubmitButton('edit');

        collapseElement.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    showProductoModal(producto, mode = 'view') {
        const modalHTML = `
        <div class="modal fade" id="productoModal" tabindex="-1" aria-labelledby="productoModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title" id="productoModalLabel">
                            <i class="fas fa-utensils me-2"></i>
                            ${mode === 'view' ? 'Detalles del Producto' : 'Editar Producto'}
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>
                    <div class="modal-body">
                        ${this.renderProductoModalContent(producto, mode)}
                    </div>
                    <div class="modal-footer">
                        ${mode === 'view' ?
                `<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                <i class="fas fa-times"></i> Cerrar
                            </button>` :
                `<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                <i class="fas fa-times"></i> Cancelar
                            </button>
                            <button type="button" class="btn btn-primary" id="saveProductoChanges">
                                <i class="fas fa-save"></i> Guardar Cambios
                            </button>`
            }
                    </div>
                </div>
            </div>
        </div>
    `;

        const existingModal = document.getElementById('productoModal');
        if (existingModal) {
            existingModal.remove();
        }

        document.body.insertAdjacentHTML('beforeend', modalHTML);

        const modal = new bootstrap.Modal(document.getElementById('productoModal'));
        modal.show();

        document.getElementById('productoModal').addEventListener('hidden.bs.modal', function () {
            this.remove();
        });
    }

    renderProductoModalContent(producto, mode) {
        const estadoBadge = producto.estado === 'Disponible'
            ? '<span class="badge bg-success fs-6"><i class="fas fa-check me-1"></i>Disponible</span>'
            : '<span class="badge bg-danger fs-6"><i class="fas fa-times me-1"></i>No Disponible</span>';

        const imageSrc = producto.imagen
            ? `${producto.imagen}`
            : '../../../assets/images/no-image.png';

        const ingredientesHTML = producto.ingredientes && producto.ingredientes.length > 0
            ? producto.ingredientes.map(ing => `
            <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                <div>
                    <strong>${ing.ingrediente_nombre}</strong>
                    <small class="text-muted d-block">${ing.unidad}</small>
                </div>
                <span class="badge bg-light text-dark">${ing.cantidad} ${ing.unidad}</span>
            </div>
        `).join('')
            : '<p class="text-muted fst-italic">No hay ingredientes registrados</p>';

        return `
        <div class="row g-4">
            <div class="col-12 col-md-4">
                <div class="text-center">
                    <img src="${imageSrc}" 
                        alt="${producto.nombre}" 
                        class="img-fluid rounded shadow"
                        style="max-height: 250px; width: 100%; object-fit: cover;"
                        onerror="this.src='../../../assets/images/no-image.png'">
                </div>
            </div>
            
            <div class="col-12 col-md-8">
                <div class="mb-3">
                    <h4 class="fw-bold text-primary mb-2">${producto.nombre}</h4>
                    <div class="d-flex flex-wrap gap-2 mb-3">
                        <span class="badge bg-light text-dark fs-6">
                            <i class="fas fa-tag me-1"></i>${producto.categoria_nombre || 'Sin categoría'}
                        </span>
                        ${estadoBadge}
                    </div>
                </div>
                
                <div class="row g-3">
                    <div class="col-12">
                        <div class="card bg-light">
                            <div class="card-body text-center">
                                <h5 class="card-title text-success mb-0">
                                    <i class="fas fa-dollar-sign"></i>${parseFloat(producto.precio).toFixed(2)}
                                </h5>
                                <small class="text-muted">Precio</small>
                            </div>
                        </div>
                    </div>
                </div>
                
                ${producto.descripcion ? `
                    <div class="mt-3">
                        <h6 class="fw-bold mb-2">
                            <i class="fas fa-align-left me-1"></i>Descripción
                        </h6>
                        <p class="text-muted">${producto.descripcion}</p>
                    </div>
                ` : ''}
            </div>
        </div>
        
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-light">
                        <h6 class="card-title mb-0">
                            <i class="fas fa-list-ul me-2"></i>Ingredientes
                            ${producto.ingredientes ? `<span class="badge bg-primary ms-2">${producto.ingredientes.length}</span>` : ''}
                        </h6>
                    </div>
                    <div class="card-body">
                        ${ingredientesHTML}
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Información adicional -->
        <div class="row mt-3">
            <div class="col-12">
                <div class="card bg-light">
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-6">
                                <small class="text-muted d-block">ID del Producto</small>
                                <strong>#${producto.id}</strong>
                            </div>
                            <div class="col-6">
                                <small class="text-muted d-block">Fecha de Registro</small>
                                <strong>${producto.created_at ? new Date(producto.created_at).toLocaleDateString('es-ES') : 'N/A'}</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;
    }
}