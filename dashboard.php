<?php
require_once 'layouts/session.php';
requireLogin();

$userData = getUserData();
?>

<?php include 'layouts/main.php'; ?>

<head>
    <title>Dashboard | Estudiovioleta</title>
    <?php include 'layouts/title-meta.php'; ?>
    <?php include 'layouts/head-css.php'; ?>
</head>

<body>
    <div class="wrapper">
        <?php include 'layouts/menu.php';?>
        <div class="content-page">
            <div class="content">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-12">
                            <div class="page-title-box">
                                <div class="page-title-right">
                                    <ol class="breadcrumb m-0">
                                        <li class="breadcrumb-item"><a href="index.php">Estudiovioleta</a></li>
                                        <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
                                        <li class="breadcrumb-item active">Dashboard</li>
                                    </ol>
                                </div>
                                <h4 class="page-title">Dashboard</h4>
                            </div>
                        </div>
                    </div>

                    <?php if (isSuperAdmin()): ?>
                    <!-- Panel especial para Super Admin -->
                    <div class="row">
                        <div class="col-12">
                            <div class="alert alert-info alert-dismissible fade show" role="alert" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none; color: white;">
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
                                <div class="d-flex align-items-center">
                                    <i class="mdi mdi-shield-crown" style="font-size: 3rem; margin-right: 1rem;"></i>
                                    <div>
                                        <h4 class="alert-heading mb-2" style="color: white;">👑 Acceso Super Admin SAAS</h4>
                                        <p class="mb-3">Tienes acceso al panel de administración del SAAS. Gestiona todas las empresas, suscripciones y estadísticas.</p>
                                        <a href="saas-admin.php" class="btn btn-light btn-lg">
                                            <i class="mdi mdi-view-dashboard"></i> Ir al Panel SAAS
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <div class="row">
                        <div class="col-12">
                            <div class="card-box">
                                <h2 class="header-title text-center mt-5 mb-5 text-uppercase fw-bold">Bienvenido a tu panel de administración!</h4>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <div class="card-box">
                                <h2 class="header-title text-center mt-5 text-uppercase fw-bold">¡Bienvenido!</h2>
                                <p class="text-center">Selección a la izquierda el modulo que deseas administrar.</p>
                                <img src="assets/images/pagina_fondo.png" alt="Marca de agua" class="d-flex mx-auto"/>
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
</html>