<?php
require_once 'layouts/session.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role'])) {
    header('Location: login.php');
    exit();
}

if ($_SESSION['user_role'] !== 'Administrador') {
    header('Location: index.php');
    exit();
}

requireLogin();
$userData = getUserData();
include 'layouts/main.php';
?>
<?php include 'layouts/main.php'; ?>



<head>
    <title>Mesas | Estudiovioleta</title>
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
                                        <li class="breadcrumb-item active">Mesas</li>
                                    </ol>
                                </div>
                                <h4 class="page-title">Lista de Mesas</h4>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body">
                                    <h4 class="header-title">Datos de la Mesa</h4>
                                    <p class="text-muted font-14 mb-3">
                                        Aquí puedes agregar una nueva mesa al sistema. <span class="text-danger">Estas se mostraran en tu menú.</span>
                                    </p>

                                    <form class="needs-validation" novalidate>
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label for="numero_mesa">Numero De Mesa</label>
                                                <input type="number" class="form-control" id="numero_mesa" placeholder="Numero de la Mesa" required>
                                                <div class="invalid-feedback">
                                                    Por favor ingresa un número válido.
                                                </div>
                                                <div class="valid-feedback">
                                                    Número de mesa válido.
                                                </div>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="mesa_capacidad">Capacidad</label>
                                                <input type="number" class="form-control" id="mesa_capacidad" placeholder="Capacidad de la Mesa" required>
                                                <div class="invalid-feedback">
                                                    Por favor ingresa una capacidad válida.
                                                </div>
                                                <div class="valid-feedback">
                                                    Capacidad de mesa válida.
                                                </div>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="mesa_estado">Estado</label>
                                                <select class="form-control" id="mesa_estado" required>
                                                    <option value="">Selecciona un estado</option>
                                                    <option value="Disponible">Disponible</option>
                                                    <option value="Ocupada">Ocupada</option>
                                                    <option value="Reservada">Reservada</option>
                                                </select>
                                                <div class="invalid-feedback">
                                                    Por favor selecciona un estado.
                                                </div>
                                                <div class="valid-feedback">
                                                    Estado seleccionado correctamente.
                                                </div>
                                            </div>
                                        </div>
                                        <button class="btn btn-primary" type="submit">Agregar Mesa</button>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="card">
                                <div class="card-body">
                                    <h4 class="header-title">Lista de Mesas</h4>
                                    <p class="text-muted font-14 mb-3">
                                        Aquí puedes ver todas las mesas registradas en el sistema. <span class="text-danger">Puedes editar o eliminar mesas existentes.</span>
                                    </p>
                                    <div id="filtros"></div>
                                    <div id="mesa-lista"></div>
                                    <div id="mesa-estadisticas"></div>
                                    <div id="paginacion"></div>
                                </div>
                            </div>
                        </div>
                    </div>
            <?php include 'layouts/footer.php'; ?>
        </div>
    </div>
    
    <?php include 'layouts/footer-scripts.php'; ?>
    <script src="presentation/mesas/services/MesaService.min.js"></script>
    <script src="presentation/mesas/modules/MesaCRUD.min.js"></script>
    <script src="presentation/mesas/ui/mesas.min.js"></script>
    <script src="presentation/mesas/js/mesa.min.js"></script>
    <script src="assets/js/app.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts@latest"></script>
</body>
</html>