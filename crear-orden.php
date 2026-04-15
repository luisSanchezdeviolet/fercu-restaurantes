<?php include 'layouts/session.php'; ?>
<?php requireLogin(); ?>
<?php include 'layouts/main.php'; ?>

<head>
    <title>Ventas | Estudiovioleta</title>
    <?php include 'layouts/title-meta.php'; ?>

    <?php include 'layouts/head-css.php'; ?>

    <style>
        .product-card {
            transition: all 0.3s ease;
            border: none;
            border-radius: 1rem;
            overflow: hidden;
            font-size: 12px;
        }

        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 1rem 3rem rgba(0, 0, 0, .175) !important;
        }

        .card-img-top {
            height: 120px;
            width: 100%;
            aspect-ratio: 1 / 2;
            transition: transform 0.3s ease;
            background-color: #f8f9fa;
        }

        .product-card:hover .card-img-top {
            transform: scale(1.05);
        }

        .price-tag {
            background: linear-gradient(45deg, #ff6b6b, #ee5a24);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .card-title {
            color: #2d3748;
            font-weight: 600;
        }

        .card-text {
            color: #718096;
        }

        .floating-cart-btn {
            z-index: 1040;
            transition: all 0.3s ease;
        }

        .floating-cart-btn:hover {
            transform: translateY(-2px);
        }

        .product-card {
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 .5rem 1rem rgba(0, 0, 0, .15) !important;
        }

        .card-img-top {
            height: 100px;
            object-fit: cover;
        }

        @media (max-width: 768px) {
            #cartOffcanvas {
                width: 100% !important;
            }

            .floating-cart-btn {
                width: 40px;
                height: 40px;
                bottom: 20px;
                right: 20px;
            }
        }
    </style>
    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />

    <link href="
https://cdn.jsdelivr.net/npm/sweetalert2@11.22.0/dist/sweetalert2.min.css
" rel="stylesheet">
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
                                        <li class="breadcrumb-item"><a href="javascript: void(0);">Menu</a></li>
                                        <li class="breadcrumb-item active"></li>
                                    </ol>
                                </div>
                                <h4 class="page-title">Lista de Productos - </h4>
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
                            <div class="card">
                                <div class="row p-2">
                                    <div class="col-12">
                                        <div class="card">
                                            <div class="card-body">
                                                <div class="d-flex justify-content-between align-items-center mb-3">
                                                    <h4 class="header-title">Listado de Productos</h4>
                                                    <div class="ms-auto">
                                                        <div class="input-group">
                                                            <input type="text" class="form-control" id="searchProduct" placeholder="Buscar producto..." />
                                                            <button type="button" class="btn border" id="btnSearchProduct">
                                                                <i class="fas fa-search"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="d-flex gap-3 my-3" id="categories-container">
                                                </div>

                                                <div class="row" id="product-list">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <button class="btn btn-primary position-fixed bottom-0 end-0 m-4 rounded-circle p-3 shadow-lg floating-cart-btn d-flex align-items-center justify-content-center"
                        type="button" data-bs-toggle="offcanvas" data-bs-target="#cartOffcanvas">
                        <i class="fas fa-shopping-cart fs-4"></i>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" id="cart-count">
                            0
                        </span>
                    </button>

                    <div class="offcanvas offcanvas-end" tabindex="-1" id="cartOffcanvas" style="width: 400px;">
                        <div class="offcanvas-header bg-primary text-white">
                            <h5 class="offcanvas-title">
                                <i class="fas fa-shopping-cart me-2"></i>Mi Orden
                            </h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas"></button>
                        </div>

                        <div class="offcanvas-body p-0">
                            <div class="p-3 border-bottom bg-light">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <h6 class="mb-0 text-muted">Mesa:</h6>
                                    <span class="fw-bold" id="numMesa"></span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center">
                                    <h6 class="mb-0 text-muted">Mesero:</h6>
                                    <span class="fw-bold" id="menAcargo">
                                        <?php echo $_SESSION['user_id']; ?> - <?php echo $_SESSION['user_name']; ?>
                                    </span>
                                </div>
                            </div>

                            <div class="flex-grow-1 overflow-auto" id="cart-items">
                                <div class="p-4 text-center text-muted" id="empty-cart">
                                    <i class="fas fa-shopping-cart fa-3x mb-3 opacity-50"></i>
                                    <p class="mb-0">No hay productos en la orden</p>
                                </div>

                                <div id="cart-list"></div>
                            </div>

                            <div class="border-top bg-light p-3" id="cart-footer" style="display: none;">
                                <p class="text-muted mb-2">Notas: <span id="text-danger">(opcional)</span></p>
                                <textarea class="form-control mb-3" id="textAreaAnotaciones" rows="3" placeholder="Escribe tus notas aquí..."></textarea>

                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h5 class="mb-0">Total:</h5>
                                    <h4 class="mb-0 text-primary fw-bold" id="cart-total">$0.00</h4>
                                </div>

                                <div class="d-grid gap-2">
                                    <button class="btn btn-danger btn-sm" id="btnClearCart">
                                        <i class="fas fa-trash me-2"></i>Limpiar Orden
                                    </button>
                                    <button class="btn btn-success" id="btnConfirmOrder">
                                        <i class="fas fa-check me-2"></i>Confirmar Orden
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <?php include 'layouts/footer.php'; ?>
                </div>
            </div>
            
            <?php include 'layouts/footer-scripts.php'; ?>
            <script src="presentation/orden/ui/ordenes.min.js"></script>
            <script src="presentation/orden/js/orden.min.js"></script>
            <script src="assets/js/app.min.js"></script>
            <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
            <script src="
https://cdn.jsdelivr.net/npm/sweetalert2@11.22.0/dist/sweetalert2.all.min.js
"></script>
</body>

</html>
