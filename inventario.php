<?php include 'layouts/session.php'; ?>
<?php requireLogin(); ?>
<?php include 'layouts/main.php'; ?>

<head>
    <title>Inventario | Estudiovioleta</title>
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
                                        <li class="breadcrumb-item active">Inventario</li>
                                    </ol>
                                </div>
                                <h4 class="page-title">Inventario</h4>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-body">
                                        <h4 class="header-title">Gestion de tu Inventario</h4>
                                        <p class="text-muted font-14 mb-3">
                                            Aquí puedes visualizar graficamente el estado de tu inventario, así como agregar nuevos ingredientes. <span class="text-danger">Recuerda que los ingredientes son esenciales para la creación de productos.</span>
                                        </p>
                                        <div id="inventory-chart">

                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-body">
                                        <h4 class="header-title">Datos del Ingrediente</h4>
                                        <p class="text-muted font-14 mb-3">
                                            Aquí puedes agregar un nuevo ingrediente al sistema. <span class="text-danger">Estos se utilizarán en tus productos.</span>
                                        </p>
                                        <form class="needs-validation" novalidate>
                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <label for="validationCustom01">Nombre</label>
                                                    <input type="text" class="form-control" id="validationCustom01" name="nombre" placeholder="Nombre" required>
                                                    <div class="valid-feedback">
                                                        Datos correctos.
                                                    </div>
                                                    <div class="invalid-feedback">
                                                        Por favor ingrese el nombre del ingrediente.
                                                    </div>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label for="validationCustom02">Estado del ingrediente</label>
                                                    <select class="form-control" id="validationCustom02" name="estado" required>
                                                        <option value="">Seleccione una opción</option>
                                                        <option value="Disponible">Disponible</option>
                                                        <option value="Agotado">Agotado</option>
                                                    </select>
                                                    <div class="valid-feedback">
                                                        Datos correctos.
                                                    </div>
                                                    <div class="invalid-feedback">
                                                        Por favor seleccione el estado del ingrediente.
                                                    </div>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label for="validationCustom03">Cantidad</label>
                                                    <input type="number" class="form-control" id="validationCustom03" name="cantidad" placeholder="Cantidad" required>
                                                    <div class="valid-feedback">
                                                        Datos correctos.
                                                    </div>
                                                    <div class="invalid-feedback">
                                                        Por favor ingrese la cantidad del ingrediente.
                                                    </div>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label for="validationCustom04">Unidad de Medida</label>
                                                    <select class="form-control" id="validationCustom04" name="unidadMedida" required>
                                                        <option value="">Seleccione una unidad de medida</option>
                                                        <option value="Kilogramos">Kilogramos</option>
                                                        <option value="Litros">Litros</option>
                                                        <option value="Unidades">Unidades</option>
                                                        <option value="Gramos">Gramos</option>
                                                    </select>
                                                    <div class="valid-feedback">
                                                        Datos correctos.
                                                    </div>
                                                    <div class="invalid-feedback">
                                                        Por favor seleccione una unidad de medida.
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <button class="btn btn-primary" type="submit">
                                                    <i class="fa-solid fa-plus me-1"></i>
                                                    Agregar Ingrediente
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                                <div class="col-lg-12">
                                    <div class="card">
                                        <div class="card-body" id="ingrediente-list-container">
                                            <h4 class="header-title">Lista de Ingredientes</h4>
                                            <p class="text-muted font-14 mb-3">
                                                Aquí puedes ver todos los ingredientes creados. Puedes editar o eliminar cada uno de ellos.
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>  
                    </div>
                            <?php include 'layouts/footer.php'; ?>
                        </div>
                        
                        <?php include 'layouts/footer-scripts.php'; ?>
                        <script src="presentation/ingredientes/services/IngredienteService.min.js"></script>
                        <script src="presentation/ingredientes/modules/IngredienteCRUD.min.js"></script>
                        <script src="presentation/ingredientes/ui/ingredientes.min.js"></script>
                        <script src="presentation/ingredientes/js/ingrediente.min.js"></script>
                        <script src="assets/js/app.min.js"></script>
                        <script src="https://cdn.jsdelivr.net/npm/apexcharts@latest"></script>
</body>

</html>
