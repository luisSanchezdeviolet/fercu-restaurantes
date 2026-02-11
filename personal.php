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

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Personal | Estudiovioleta</title>
    <?php include 'layouts/title-meta.php'; ?>
    <link rel="stylesheet" href="assets/vendor/daterangepicker/daterangepicker.css">
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
                                        <li class="breadcrumb-item"><a href="javascript:void(0);">Estudiovioleta</a></li>
                                        <li class="breadcrumb-item"><a href="javascript:void(0);">Dashboard</a></li>
                                        <li class="breadcrumb-item active">Personal</li>
                                    </ol>
                                </div>
                                <h4 class="page-title">Personal</h4>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card">
                                <div class="card-body">
                                    <h4 class="header-title">Datos del Empleado</h4>
                                    <p class="text-muted font-14 mb-3">
                                        Ingrese los datos del nuevo empleado. <span class="text-danger">Estas credenciales se usarán para acceder al sistema.</span>
                                    </p>
                                    <form method="post" id="form-empleado">
                                        <div id="alerta-empleado" class="mb-3"></div>
                                        <div class="col-sm-12 mb-4">
                                            <h5 class="text-uppercase mt-0">Información del Empleado</h5>
                                        </div>
                                        <div class="form-group row mb-4">
                                            <label for="name" class="col-sm-2 col-form-label">Nombre</label>
                                            <div class="col-sm-10">
                                                <input type="text" class="form-control" name="name" id="name" placeholder="Ingresa el nombre completo">
                                            </div>
                                        </div>
                                        <div class="form-group row mb-4">
                                            <label for="email" class="col-sm-2 col-form-label">Email</label>
                                            <div class="col-sm-10">
                                                <input type="email" class="form-control" name="email" id="email" placeholder="Ingresa el email">
                                            </div>
                                        </div>
                                        <div class="form-group row mb-4">
                                            <label for="password" class="col-sm-2 col-form-label">Contraseña</label>
                                            <div class="col-sm-10">
                                                <input type="password" class="form-control" name="password" id="password" placeholder="Ingresa la contraseña">
                                            </div>
                                        </div>
                                        <div class="form-group row mb-4">
                                            <label for="role" class="col-sm-2 col-form-label">Rol de Cuenta</label>
                                            <div class="col-sm-10">
                                                <select class="form-control" name="role" id="role">
                                                    <option value="" disabled selected>Selecciona el rol de cuenta</option>
                                                    <option value="Administrador">Administrador</option>
                                                    <option value="Mesero">Mesero</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <div class="col-sm-10 offset-sm-2">
                                                <button type="submit" class="btn btn-primary text-uppercase">Agregar Cuenta</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card">
                                <div class="card-body">
                                    <h4 class="header-title">Lista de Empleados</h4>
                                    <p class="text-muted font-14 mb-3">
                                        Aquí puedes ver todos los empleados registrados en el sistema.
                                    </p>

                                    <!-- Buscador y filtros -->
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="search-input">Buscar empleado:</label>
                                                <input type="text" class="form-control" id="search-input" placeholder="Buscar por nombre o email...">
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label for="role-filter">Filtrar por rol:</label>
                                                <select class="form-control" id="role-filter">
                                                    <option value="">Todos los roles</option>
                                                    <option value="Administrador">Administrador</option>
                                                    <option value="Mesero">Mesero</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label for="records-per-page">Registros por página:</label>
                                                <select class="form-control" id="records-per-page">
                                                    <option value="5">5</option>
                                                    <option value="10" selected>10</option>
                                                    <option value="20">20</option>
                                                    <option value="50">50</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="table-responsive">
                                        <table class="table table-striped table-bordered">
                                            <thead>
                                                <tr>
                                                    <th>Nombre</th>
                                                    <th>Email</th>
                                                    <th>Rol de Cuenta</th>
                                                    <th>Acciones</th>
                                                </tr>
                                            </thead>
                                            <tbody id="empleados-body">
                                                <tr>
                                                    <td colspan="4">Cargando datos...</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>

                                    <div class="row mt-3">
                                        <div class="col-md-6">
                                            <div id="pagination-info" class="text-muted">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <nav aria-label="Navegación de páginas">
                                                <ul class="pagination justify-content-end" id="pagination-controls">
                                                    
                                                </ul>
                                            </nav>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <?php include 'layouts/footer.php'; ?>
        </div>
    </div>

    
    <?php include 'layouts/footer-scripts.php'; ?>
    <script src="assets/js/app.min.js"></script>
</body>

<script src="./presentation/personal/scripts/personal.min.js"></script>

</html>