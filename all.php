<?php include 'layouts/session.php'; ?>
<?php requireLogin(); ?>
<?php include 'layouts/main.php'; ?>
<?php include 'components/ticket/ticket-customizer.php'; ?>

<head>
    <title>Ordenes | Estudiovioleta</title>
    <?php include 'layouts/title-meta.php'; ?>

    <?php include 'layouts/head-css.php'; ?>
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
                                        <li class="breadcrumb-item"><a href="javascript: void(0);">Dashboard</a></li>
                                        <li class="breadcrumb-item active">Ordenes</li>
                                    </ol>
                                </div>
                                <h4 class="page-title">Listado de Ordenes</h4>
                                <div class="d-flex justify-content-between flex-column flex-md-row">
                                    <div class="d-flex align-items-center gap-4 mb-2 mb-md-0">
                                        <label for="select-pilar-filtrado" class="form-label w-50">Filtrar por:</label>
                                        <select class="form-select" id="select-pilar-filtrado">
                                            <option value="all">Todo</option>
                                        </select>
                                    </div>
                                    <button class="btn btn-primary d-flex align-items-center gap-2" id="btn-actualizar-ordenes">
                                        <i class="fas fa-sync"></i> Actualizar
                                    </button>
                                </div>

                                <div id="ordenes-lista" class="row my-4 gap-lg-2">

                                </div>
                            </div>
                        </div>
                    </div>



                    <?php include 'layouts/footer.php'; ?>
                </div>
            </div>
            
            <?php include 'layouts/footer-scripts.php'; ?>
            <script src="presentation/orden/all/all.min.js"></script>
            <script src="presentation/orden/all/init.min.js"></script>
            <script src="presentation/ticket/ticket-customizer.min.js"></script>
            <script src="assets/js/app.min.js"></script>
            <script src="
https://cdn.jsdelivr.net/npm/sweetalert2@11.22.0/dist/sweetalert2.all.min.js
"></script>
</body>

</html>
