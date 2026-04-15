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
                                <h4 class="page-title">Ordenes Pendientes</h4>
                                <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between">
                                    <button type="button" class="btn btn-success waves-effect waves-light mb-2 mb-md-0" id="ordenes-finalizadas">
                                        <i class="fas fa-check"></i>
                                        Ordenes Finalizadas</button>
                                    <button type="button" class="btn btn-primary waves-effect waves-light" id="crear-orden">
                                        <i class="fas fa-plus"></i>
                                        Crear Orden</button>
                                </div>
                                <div class="mt-2">
                                    <button type="button" class="btn btn-secondary waves-effect waves-light" id="btn-actualizar-ordenes">
                                        <i class="fas fa-sync"></i>
                                        Actualizar</button>
                                </div>

                                <div class="mt-3">
                                    <div class="card">
                                        <div class="card-body">
                                            <h5 class="card-title">Lista de Órdenes Pendientes</h5>
                                            <p class="text-muted">En esta sección podrás gestionar las órdenes pendientes de los clientes. <span class="text-danger">Puedes crear nuevas órdenes, ver las órdenes finalizadas y realizar un seguimiento del estado de cada orden.</span></p>
                                            <div class="table-responsive">
                                                <table class="table table-striped table-bordered">
                                                    <thead>
                                                        <tr>
                                                            <th
                                                                class="text-center text-nowrap align-middle" style="width: 50px;" title="ID de la orden" data-toggle="tooltip" data-placement="top" data-original-title="ID de la orden"><i class="fas fa-hashtag"></i> ID</th>
                                                            <th
                                                                class="text-center text-nowrap align-middle" style="width: 100px;" title="Mesero asignado a la orden" data-toggle="tooltip" data-placement="top" data-original-title="Mesero asignado a la orden"><i class="fas fa-user"></i> Mesero</th>
                                                            <th
                                                                class="text-center text-nowrap align-middle" style="width: 100px;" title="Mesa asignada a la orden" data-toggle="tooltip" data-placement="top" data-original-title="Mesa asignada a la orden"><i class="fas fa-table"></i> Mesa</th>
                                                            <th
                                                                class="text-center text-nowrap align-middle" style="width: 100px;" title="Total de productos en la orden" data-toggle="tooltip" data-placement="top" data-original-title="Total de productos en la orden"><i class="fas fa-box"></i> Total Productos</th>
                                                            <th
                                                                class="text-center text-nowrap align-middle" style="width: 100px;" title="Total de la orden" data-toggle="tooltip" data-placement="top" data-original-title="Total de la orden"><i class="fas fa-money-bill-wave"></i> Total</th>
                                                            <th
                                                                class="text-center text-nowrap align-middle" style="width: 100px;" title="Estado de la orden" data-toggle="tooltip" data-placement="top" data-original-title="Estado de la orden"><i class="fas fa-clock"></i> Estado</th>
                                                            <th
                                                                class="text-center text-nowrap align-middle" style="width: 100px;" title="Fecha de la orden" data-toggle="tooltip" data-placement="top" data-original-title="Fecha de la orden"><i class="fas fa-calendar-alt"></i> Fecha</th>
                                                            <th
                                                                class="text-center text-nowrap align-middle" style="width: 100px;" title="Acciones disponibles" data-toggle="tooltip" data-placement="top" data-original-title="Acciones disponibles"><i class="fas fa-cogs"></i> Acciones</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody id="ordenes-lista">

                                                    </tbody>
                                                </table>
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
            <script src="presentation/orden/ordenes/ordenes.min.js"></script>
            <script src="presentation/orden/ordenes/init.min.js"></script>
            <script src="presentation/ticket/ticket-customizer.min.js"></script>
            <script src="assets/js/app.min.js"></script>
            <script src="
https://cdn.jsdelivr.net/npm/sweetalert2@11.22.0/dist/sweetalert2.all.min.js
"></script>
</body>

</html>
