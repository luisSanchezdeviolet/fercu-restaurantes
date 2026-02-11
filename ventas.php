<?php include 'layouts/session.php'; ?>
<?php include 'layouts/main.php'; ?>

<head>
    <title>Ventas | Estudiovioleta</title>
    <?php include 'layouts/title-meta.php'; ?>
    <?php include 'layouts/head-css.php'; ?>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/apexcharts@latest/dist/apexcharts.css">
    <link rel="stylesheet" href="assets/css/custom-modal.css">
    <link href="
https://cdn.jsdelivr.net/npm/sweetalert2@11.22.0/dist/sweetalert2.min.css
" rel="stylesheet">
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.31/jspdf.plugin.autotable.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
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
                                        <li class="breadcrumb-item active">Ventas</li>
                                    </ol>
                                </div>
                                <h4 class="page-title">Ordenes Finalizadas</h4>
                                <div class="d-flex justify-content-between align-items-stretch mt-3 flex-column flex-md-row gap-2">
                                    <button type="button" class="btn btn-primary waves-effect waves-light w-100 w-auto" id="nueva-orden">
                                        <i class="fas fa-plus me-1"></i>
                                        Nueva Orden
                                    </button>
                                    <button type="button" class="btn btn-secondary waves-effect waves-light w-100 w-auto" id="ordenes-pendientes">
                                        <i class="fas fa-rotate-left me-1"></i>
                                        Ordenes Pendientes
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-md-3">
                            <div class="card mb-lg-0">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h5 class="text-muted fw-normal mt-0 text-truncate" title="Total Ordenes">Total Órdenes</h5>
                                            <h3 class="my-2 py-1" id="total-ordenes">0</h3>
                                        </div>
                                        <div class="avatar-sm">
                                            <span class="avatar-title bg-soft-primary text-white rounded">
                                                <i class="fas fa-receipt"></i>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="card mb-lg-0">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h5 class="text-muted fw-normal mt-0 text-truncate" title="Ordenes Finalizadas">Finalizadas</h5>
                                            <h3 class="my-2 py-1 text-success" id="ordenes-finalizadas">0</h3>
                                        </div>
                                        <div class="avatar-sm">
                                            <span class="avatar-title bg-soft-success text-success rounded">
                                                <i class="fas fa-check-circle"></i>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="card mb-lg-0">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h5 class="text-muted fw-normal mt-0 text-truncate" title="Ordenes Pendientes">Pendientes</h5>
                                            <h3 class="my-2 py-1 text-warning" id="ordenes-pendientes-count">0</h3>
                                        </div>
                                        <div class="avatar-sm">
                                            <span class="avatar-title bg-soft-warning text-warning rounded">
                                                <i class="fas fa-clock"></i>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="card mb-lg-0">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h5 class="text-muted fw-normal mt-0 text-truncate" title="Ordenes Canceladas">Canceladas</h5>
                                            <h3 class="my-2 py-1 text-danger" id="ordenes-canceladas">0</h3>
                                        </div>
                                        <div class="avatar-sm">
                                            <span class="avatar-title bg-soft-danger text-danger rounded">
                                                <i class="fas fa-times-circle"></i>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <div>
                                            <h4 class="header-title mb-1">Órdenes Finalizadas</h4>
                                            <p class="text-muted font-14 mb-0">
                                                Lista de todas las órdenes finalizadas con opciones de filtrado.
                                            </p>
                                        </div>
                                        <div class="d-flex gap-2">
                                            <button type="button" class="btn btn-danger btn-sm" id="btn-exportar-pdf" title="Exportar a PDF">
                                                <i class="fas fa-file-pdf me-1"></i>PDF
                                            </button>
                                            <button type="button" class="btn btn-success btn-sm" id="btn-exportar-excel" title="Exportar a Excel">
                                                <i class="fas fa-file-excel me-1"></i>Excel
                                            </button>
                                            <button type="button" class="btn btn-info btn-sm" id="btn-exportar-csv" title="Exportar a CSV">
                                                <i class="fas fa-file-csv me-1"></i>CSV
                                            </button>
                                        </div>
                                    </div>

                                    <div class="mb-4">
                                        <div class="row g-2">
                                            <div class="col-md-3">
                                                <div class="input-group">
                                                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                                                    <input type="text" class="form-control" id="filter-search" placeholder="Buscar...">
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <select class="form-select" id="filter-estado">
                                                    <option value="">Todos los estados</option>
                                                    <option value="Listo para servir">Listo para servir</option>
                                                    <option value="Completado">Completadas</option>
                                                    <option value="Cancelado">Cancelado</option>
                                                </select>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="input-group">
                                                    <span class="input-group-text"><i class="fas fa-calendar"></i></span>
                                                    <input type="date" class="form-control" id="filter-fecha-desde">
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="input-group">
                                                    <span class="input-group-text"><i class="fas fa-calendar"></i></span>
                                                    <input type="date" class="form-control" id="filter-fecha-hasta">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row mt-2">
                                            <div class="col-12 d-flex flex-column flex-md-row justify-content-between align-items-stretch align-items-md-center gap-1">
                                                <button class="btn btn-primary btn-sm w-100 w-auto" id="btn-aplicar-filtros">
                                                    <i class="fas fa-filter me-1"></i> Aplicar Filtros
                                                </button>
                                                <button class="btn btn-secondary btn-sm w-100 w-auto" id="btn-limpiar-filtros">
                                                    <i class="fas fa-broom me-1"></i> Limpiar Filtros
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="table-responsive">
                                        <table id="tabla-ordenes" class="table table-striped table-centered mb-0">
                                            <thead>
                                                <tr>
                                                    <th>ID</th>
                                                    <th>Fecha</th>
                                                    <th>Mesa</th>
                                                    <th>Mesero</th>
                                                    <th>Productos</th>
                                                    <th>Total</th>
                                                    <th>Método de Pago</th>
                                                    <th>Estado</th>
                                                    <th>Acciones</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <!-- Los datos se cargarán dinámicamente -->
                                            </tbody>
                                        </table>
                                    </div>

                                    <div class="row mt-3">
                                        <div class="col-12">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div class="text-muted">
                                                    Mostrando <span id="contador-registros">0</span> registros
                                                </div>
                                                <nav>
                                                    <ul class="pagination pagination-sm mb-0" id="paginacion">
                                                        <!-- La paginación se generará dinámicamente -->
                                                    </ul>
                                                </nav>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row my-4">
                        <h4 class="col-12 mb-3 fw-bold">Estadísticas de Ventas</h4>
                        <div class="col-lg-6">
                            <div class="card mb-lg-0">
                                <div class="card-body">
                                    <h4 class="header-title">Distribución de Órdenes</h4>
                                    <p class="text-muted font-14 mb-3">
                                        Gráfico circular que muestra la distribución de órdenes por estado.
                                    </p>
                                    <div id="chart-ordenes-pie"></div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-6">
                            <div class="card mb-lg-0">
                                <div class="card-body">
                                    <h4 class="header-title">Órdenes por Estado</h4>
                                    <p class="text-muted font-14 mb-3">
                                        Gráfico de barras que muestra la cantidad de órdenes por estado.
                                    </p>
                                    <div id="chart-ordenes-bar"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body">
                                    <h4 class="header-title">Órdenes de los Últimos 7 Días</h4>
                                    <p class="text-muted font-14 mb-3">
                                        Tendencia de órdenes en los últimos 7 días.
                                    </p>
                                    <div id="chart-tendencia-lineal"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Modal de Detalle de Orden -->
                    <div class="modal fade" id="modalDetalleOrden" tabindex="-1" aria-labelledby="modalDetalleOrdenLabel" aria-hidden="true">
                        <div class="modal-dialog modal-lg modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header bg-primary text-white">
                                    <h5 class="modal-title" id="modalDetalleOrdenLabel">
                                        <i class="fas fa-receipt me-2"></i>Detalle de Orden
                                    </h5>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <div id="contenidoModalOrden">
                                        <!-- El contenido se cargará dinámicamente -->
                                        <div class="text-center py-4">
                                            <div class="spinner-border text-primary" role="status">
                                                <span class="visually-hidden">Cargando...</span>
                                            </div>
                                            <p class="mt-2 text-muted">Cargando detalles de la orden...</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer bg-light">
                                    <div class="d-flex justify-content-between w-100">
                                        <div>
                                            <button type="button" class="btn btn-warning" id="btnRegresarCocina" style="display: none;">
                                                <i class="fas fa-undo me-1"></i>Regresar a Cocina
                                            </button>
                                        </div>
                                        <div>
                                            <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">
                                                <i class="fas fa-times me-1"></i>Cerrar
                                            </button>
                                            <button type="button" class="btn btn-success" id="btnImprimirTicket">
                                                <i class="fas fa-print me-1"></i>Imprimir Ticket
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal fade" id="modalConfirmacionCocina" tabindex="-1" aria-labelledby="modalConfirmacionCocinaLabel" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header bg-warning text-white">
                                    <h5 class="modal-title" id="modalConfirmacionCocinaLabel">
                                        <i class="fas fa-exclamation-triangle me-2"></i>Confirmar Acción
                                    </h5>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="text-center">
                                        <i class="fas fa-utensils text-warning" style="font-size: 3rem;"></i>
                                        <h5 class="mt-3">¿Regresar orden a cocina?</h5>
                                        <p class="text-muted">Esta acción cambiará el estado de la orden a "Pendiente" y será visible nuevamente en el sistema de cocina.</p>
                                        <div class="alert alert-warning" role="alert">
                                            <small><strong>Nota:</strong> Esta acción no se puede deshacer automáticamente.</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                        <i class="fas fa-times me-1"></i>Cancelar
                                    </button>
                                    <button type="button" class="btn btn-warning" id="btnConfirmarRegresoCocina">
                                        <i class="fas fa-check me-1"></i>Confirmar Regreso
                                    </button>
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

    <script src="https://cdn.jsdelivr.net/npm/apexcharts@latest/dist/apexcharts.min.js"></script>

    <script src="presentation/ventas/ui/ventas.min.js"></script>
    <script src="presentation/ventas/js/venta.min.js"></script>
    <script src="assets/js/app.min.js"></script>
    <script src="
https://cdn.jsdelivr.net/npm/sweetalert2@11.22.0/dist/sweetalert2.all.min.js
"></script>
</body>

</html>