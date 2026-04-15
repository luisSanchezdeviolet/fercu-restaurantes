<?php include 'layouts/session.php'; ?>
<?php requireLogin(); ?>
<?php include 'layouts/main.php'; ?>
<?php include 'middleware/redirection.php'; ?>
<?php include 'components/categorias/ModalDeleteCategoria.php'; ?>


<head>
    <title>Categorias | Estudiovioleta</title>
    <?php include 'layouts/title-meta.php'; ?>
    <?php include 'layouts/head-css.php'; ?>
    <style>
        .image-preview {
            max-width: 200px;
            max-height: 200px;
            object-fit: cover;
            border-radius: 8px;
        }

        .image-upload-container {
            position: relative;
        }

        .image-upload-help {
            font-size: 0.875rem;
            color: #6c757d;
        }

        .current-image {
            border: 2px solid #dee2e6;
            padding: 10px;
            border-radius: 8px;
            background-color: #f8f9fa;
        }
    </style>
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
                                        <li class="breadcrumb-item active">Categorias</li>
                                    </ol>
                                </div>
                                <h4 class="page-title">Categorias</h4>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card">
                                <div class="card-body">
                                    <h4 class="header-title">Datos de la categoria</h4>
                                    <p class="text-muted font-14 mb-3">
                                        Ingrese los datos de la nueva categoria. <span class="text-danger">Esto se mostrara en el menú.</span>
                                    </p>
                                    <form class="needs-validation" novalidate enctype="multipart/form-data">
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label for="validationCustom01">Nombre</label>
                                                <input type="text" class="form-control" id="validationCustom01" name="nombre" placeholder="Nombre" required>
                                                <div class="valid-feedback">
                                                    Datos correctos.
                                                </div>
                                                <div class="invalid-feedback">
                                                    Por favor ingrese el nombre de la categoría.
                                                </div>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="validationCustom02">Estado de la categoria</label>
                                                <select class="form-control" id="validationCustom02" name="estado" required>
                                                    <option value="">Seleccione una opción</option>
                                                    <option value="1">Activo</option>
                                                    <option value="0">Inactivo</option>
                                                </select>
                                                <div class="valid-feedback">
                                                    Datos correctos.
                                                </div>
                                                <div class="invalid-feedback">
                                                    Por favor seleccione el estado de la categoría.
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-12 mb-3">
                                                <div class="image-upload-container">
                                                    <label for="imagen">Imagen de la Categoría</label>
                                                    <input type="file" class="form-control" id="imagen" name="imagen" accept="image/*">
                                                    <small class="form-text image-upload-help">
                                                        <i class="mdi mdi-information-outline"></i>
                                                        Selecciona una imagen para la categoría (opcional). Formatos permitidos: JPG, PNG, SVG, WEBP.
                                                    </small>

                                                    <div id="image-preview-container" class="mt-3" style="display: none;">
                                                        <div class="current-image">
                                                            <div class="d-flex align-items-center">
                                                                <img id="image-preview" src="" alt="Vista previa" class="image-preview me-3">
                                                                <div>
                                                                    <h6 class="mb-1">Vista previa</h6>
                                                                    <small class="text-muted">Esta imagen se subirá con la categoría</small>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div id="current-image-container" class="mt-3" style="display: none;">
                                                        <div class="current-image">
                                                            <div class="d-flex align-items-center">
                                                                <img id="current-image" src="" alt="Imagen actual" class="image-preview me-3">
                                                                <div>
                                                                    <h6 class="mb-1">Imagen actual</h6>
                                                                    <small class="text-muted">Selecciona una nueva imagen para cambiarla</small>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <button class="btn btn-primary" type="submit">
                                                <i class="fa-solid fa-plus me-1"></i>
                                                Agregar Categoria
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-12">
                            <div class="card">
                                <div class="card-body" id="categoria-list-container">
                                    <h4 class="header-title">Lista de Categorias</h4>
                                    <p class="text-muted font-14 mb-3">
                                        Aquí puedes ver todas las categorías creadas. Puedes editar o eliminar cada una de ellas.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <?php include 'layouts/footer.php'; ?>
                </div>
            </div>
        </div>
    </div>

    
    <?php include 'layouts/footer-scripts.php'; ?>

    <script src="presentation/categorias/services/CategoriaService.min.js"></script>
    <script src="presentation/categorias/modules/CategoriaCRUD.min.js"></script>
    <script src="presentation/categorias/ui/categorias.min.js"></script>
    <script src="presentation/categorias/js/categoria.min.js"></script>

    <script src="assets/js/app.min.js"></script>
</body>

</html>
