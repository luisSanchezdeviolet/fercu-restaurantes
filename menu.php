<?php include 'layouts/session.php'; ?>
<?php include 'layouts/main.php'; ?>

<head>
    <title>Ventas | Estudiovioleta</title>
    <?php include 'layouts/title-meta.php'; ?>

    <?php include 'layouts/head-css.php'; ?>

    <style>
        .mesa-card {
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .mesa-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .mesa-disponible {
            border-left: 4px solid #28a745;
        }

        .mesa-disponible .card-body {
            background: linear-gradient(135deg, #d4edda, #f8f9fa);
        }

        .mesa-reservada {
            border-left: 4px solid #ffc107;
        }

        .mesa-reservada .card-body {
            background: linear-gradient(135deg, #fff3cd, #f8f9fa);
        }

        .mesa-ocupada {
            border-left: 4px solid #dc3545;
        }

        .mesa-ocupada .card-body {
            background: linear-gradient(135deg, #f8d7da, #f8f9fa);
        }

        .mesa-default {
            border-left: 4px solid #6c757d;
        }

        .mesa-icon {
            width: 60px;
            height: 60px;
            margin-bottom: 10px;
        }

        .status-badge {
            font-size: 0.8rem;
            padding: 4px 8px;
        }

        .loading {
            text-align: center;
            padding: 20px;
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
                                        <li class="breadcrumb-item active">POS</li>
                                    </ol>
                                </div>
                                <h4 class="page-title">Punto de Venta - Estudiovioleta</h4>
                                <p class="text-muted">
                                    Selecciona una mesa para iniciar una nueva venta. <span class="text-danger">Puedes agregar productos al carrito, aplicar descuentos y finalizar la venta con diferentes métodos de pago.</span>
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <div id="loading" class="loading" style="display: none;">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Cargando...</span>
                                </div>
                                <p>Cargando mesas...</p>
                            </div>

                            <div id="mesas-container" class="row">
                            </div>

                            <div id="error-message" class="alert alert-danger" style="display: none;"></div>
                        </div>
                    </div>

                    <?php include 'layouts/footer.php'; ?>
                </div>
            </div>
            
            <?php include 'layouts/footer-scripts.php'; ?>
            <script src="presentation/menu/ui/menus.min.js"></script>
            <script src="presentation/menu/js/menu.min.js"></script>
            <script src="assets/js/app.min.js"></script>
</body>

</html>