<?php include 'layouts/session.php'; ?>
<?php include 'layouts/main.php'; ?>

<head>
    <title>Productos | Estudiovioleta</title>
    <?php include 'layouts/title-meta.php'; ?>
    <?php include 'layouts/head-css.php'; ?>
</head>

<body>
    <div class="wrapper">
        <?php include 'layouts/menu.php'; ?>
        <div class="content-page">
            <div class="content">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-12">
                            <div class="page-title-box">
                                <div class="page-title-right">
                                    <ol class="breadcrumb m-0">
                                        <li class="breadcrumb-item"><a href="javascript: void(0);">Estudiovioleta</a></li>
                                        <li class="breadcrumb-item"><a href="javascript: void(0);">Dashboard</a></li>
                                        <li class="breadcrumb-item active">Productos</li>
                                    </ol>
                                </div>
                                <h4 class="page-title">Productos</h4>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                                        <div>
                                            <h4 class="header-title mb-1">Agregar Nuevo Producto</h4>
                                            <p class="text-muted font-14 mb-0">
                                                Crear un nuevo producto para el menú
                                            </p>
                                        </div>
                                        <button
                                            class="btn btn-primary"
                                            type="button"
                                            data-bs-toggle="collapse"
                                            data-bs-target="#formulario-producto"
                                            aria-expanded="false"
                                            aria-controls="formulario-producto"
                                            id="toggle-form-btn">
                                            <i class="fas fa-plus" id="toggle-icon"></i>
                                            <span id="toggle-text">Abrir Formulario</span>
                                        </button>
                                    </div>

                                    <div class="collapse" id="formulario-producto">
                                        <div class="border-top pt-3">
                                            <h5 class="mb-3">Datos del Producto</h5>
                                            <p class="text-muted font-14 mb-3">
                                                Aquí rellenaras los campos de tu producto para agregar. <span class="text-danger">Este producto se mostrara en el menú dependiendo su estado</span>
                                            </p>
                                            <form class="needs-validation" novalidate>
                                                <div class="row">
                                                    <div class="mb-3 col-md-6">
                                                        <label for="nombre" class="form-label">Nombre del Platillo</label>
                                                        <input type="text" class="form-control" id="nombre" name="nombre" required>
                                                        <div class="invalid-feedback">
                                                            Por favor ingresa un nombre válido.
                                                        </div>
                                                        <div class="valid-feedback">
                                                            Dato correcto.
                                                        </div>
                                                    </div>
                                                    <div class="mb-3 col-md-6">
                                                        <label for="precio" class="form-label">Precio del Platillo</label>
                                                        <input type="number" class="form-control" id="precio" name="precio" min="0" step="0.01" required>
                                                        <div class="invalid-feedback">
                                                            Por favor ingresa un precio válido.
                                                        </div>
                                                        <div class="valid-feedback">
                                                            Dato correcto.
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="mb-3 col-md-6">
                                                        <label for="categorias" class="form-label">Categoría <span class="text-danger">(Solo Activas)</span></label>
                                                        <select class="form-select" id="categorias" name="categorias" required>
                                                            <option value="">Selecciona una categoría</option>
                                                        </select>
                                                        <div class="invalid-feedback">
                                                            Por favor selecciona una categoría.
                                                        </div>
                                                        <div class="valid-feedback">
                                                            Dato correcto.
                                                        </div>
                                                    </div>
                                                    <div class="mb-3 col-md-6">
                                                        <label for="estado" class="form-label">Estado del Platillo</label>
                                                        <select class="form-select" id="estado" name="estado" required>
                                                            <option value="">Selecciona un estado</option>
                                                            <option value="Disponible">Disponible</option>
                                                            <option value="No Disponible">No Disponible</option>
                                                        </select>
                                                        <div class="invalid-feedback">
                                                            Por favor selecciona un estado.
                                                        </div>
                                                        <div class="valid-feedback">
                                                            Dato correcto.
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="mb-3 col-12">
                                                        <label class="form-label">Ingredientes del Platillo <span class="text-danger">(Solo Activos)</span></label>

                                                        <div class="mb-3">
                                                            <div class="input-group">
                                                                <input type="text" class="form-control" id="search-ingredientes" placeholder="Buscar ingrediente...">
                                                                <span class="input-group-text"><i class="fas fa-search"></i></span>
                                                            </div>
                                                        </div>

                                                        <div id="ingredientes-disponibles" class="border rounded p-3 mb-3" style="max-height: 300px; overflow-y: auto;">
                                                            <p class="text-muted text-center">Cargando ingredientes...</p>
                                                        </div>

                                                        <div id="ingredientes-seleccionados">
                                                            <h6 class="mb-3">Ingredientes Seleccionados</h6>
                                                            <div id="lista-ingredientes-seleccionados">
                                                                <p class="text-muted">No hay ingredientes seleccionados</p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="mb-3">
                                                    <label for="descripcion" class="form-label">Descripción del Platillo <span class="text-danger">(opcional)</span></label>
                                                    <textarea class="form-control" id="descripcion" name="descripcion" rows="3"></textarea>
                                                    <div class="invalid-feedback">
                                                        Por favor ingresa una descripción válida.
                                                    </div>
                                                    <div class="valid-feedback">
                                                        Dato correcto.
                                                    </div>
                                                </div>
                                                <div class="mb-3">
                                                    <label for="imagen" class="form-label">Imagen del Platillo <span class="text-danger">(opcional)</span></label>
                                                    <input type="file" class="form-control" id="imagen" name="imagen" accept="image/*">
                                                    <div class="invalid-feedback">
                                                        Por favor selecciona una imagen válida.
                                                    </div>
                                                    <div class="valid-feedback">
                                                        Dato correcto.
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="card">
                                <div class="card-body">
                                    <h4 class="header-title mb-3">Lista de Productos</h4>
                                    <p class="text-muted font-14 mb-3">
                                        Aquí puedes ver todos los productos disponibles en el menú. <span class="text-danger">Puedes editar o eliminar productos existentes.</span>
                                    </p>
                                    <div id="filtros-productos" class="mb-3"></div>
                                    <div class="table-responsive">
                                        <table class="table table-striped table-bordered" id="tabla-productos">

                                        </table>
                                    </div>
                                    <div id="paginacion-productos" class="mt-3">
                                        <!-- Paginación se generará aquí -->
                                    </div>
                                </div>
                            </div>

                            <?php include 'layouts/footer.php'; ?>
                        </div>
                    </div>
                    
                    <?php include 'layouts/footer-scripts.php'; ?>
                    <script src="presentation/productos/services/ProductoService.min.js"></script>
                    <script src="presentation/productos/modules/ProductoCRUD.min.js"></script>
                    <script src="presentation/productos/ui/productos.min.js"></script>
                    <script src="presentation/productos/js/producto.min.js"></script>
                    <script src="assets/js/app.min.js"></script>
</body>

</html>