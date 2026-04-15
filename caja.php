<?php include 'layouts/session.php'; ?>
<?php requireLogin(); ?>
<?php include 'layouts/main.php'; ?>

<head>
    <title>Caja | Estudiovioleta</title>
    <?php include 'layouts/title-meta.php'; ?>
    <?php include 'layouts/head-css.php'; ?>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/apexcharts@latest/dist/apexcharts.css">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.22.0/dist/sweetalert2.min.css" rel="stylesheet">
</head>

<body>
    <div class="loading-overlay" id="loadingOverlay">
        <div class="spinner"></div>
    </div>

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
                                        <li class="breadcrumb-item active">Caja</li>
                                    </ol>
                                </div>
                                <h4 class="page-title">
                                    <i class="fas fa-cash-register me-3"></i>Gestión de Corte de Caja
                                </h4>
                                <div class="d-flex flex-column flex-md-row justify-content-between gap-2 align-items-stretch">
                                    <button class="btn btn-primary w-auto" id="corte-caja-inventario">
                                        <i class="fas fa-check me-2"></i>Realizar Corte Con Inventario
                                    </button>
                                    <button class="btn btn-info w-auto" id="corte-caja-sin-inventario">
                                        <i class="fas fa-times me-2"></i>Realizar Corte Sin Inventario
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-xl-3 col-md-6 mb-3">
                            <div class="card border-0 shadow-sm h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h3 class="text-primary fw-bold mb-1" id="totalCajas">0</h3>
                                            <p class="text-muted mb-0 small">Total Cajas</p>
                                        </div>
                                        <div class="bg-primary bg-opacity-10 p-3 rounded-circle">
                                            <i class="fas fa-box fa-2x text-primary"></i>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer bg-transparent border-0 pt-0">
                                    <small class="text-success">
                                        <i class="fas fa-arrow-up me-1"></i>Desde el inicio
                                    </small>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6 mb-3">
                            <div class="card border-0 shadow-sm h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h3 class="text-success fw-bold mb-1" id="ventasHoy">$0</h3>
                                            <p class="text-muted mb-0 small">Ventas Hoy</p>
                                        </div>
                                        <div class="bg-success bg-opacity-10 p-3 rounded-circle">
                                            <i class="fas fa-chart-line fa-2x text-success"></i>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer bg-transparent border-0 pt-0">
                                    <small class="text-success">
                                        <i class="fas fa-arrow-up me-1"></i>Ingresos del día
                                    </small>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6 mb-3">
                            <div class="card border-0 shadow-sm h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h3 class="text-info fw-bold mb-1" id="cajasHoy">0</h3>
                                            <p class="text-muted mb-0 small">Cajas Hoy</p>
                                        </div>
                                        <div class="bg-info bg-opacity-10 p-3 rounded-circle">
                                            <i class="fas fa-calendar-day fa-2x text-info"></i>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer bg-transparent border-0 pt-0">
                                    <small class="text-info">
                                        <i class="fas fa-clock me-1"></i>Operaciones diarias
                                    </small>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6 mb-3">
                            <div class="card border-0 shadow-sm h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h3 class="text-warning fw-bold mb-1" id="promedioVentas">$0</h3>
                                            <p class="text-muted mb-0 small">Promedio Ventas</p>
                                        </div>
                                        <div class="bg-warning bg-opacity-10 p-3 rounded-circle">
                                            <i class="fas fa-calculator fa-2x text-warning"></i>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer bg-transparent border-0 pt-0">
                                    <small class="text-warning">
                                        <i class="fas fa-chart-bar me-1"></i>Media histórica
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card mb-4">
                        <div class="card-header" id="productosActivosHeader">
                            <h5 class="mb-0 d-flex justify-content-between align-items-center">
                                <span>
                                    <i class="fas fa-list me-2"></i>Productos activos del momento
                                </span>
                                <button class="btn btn-link text-primary" type="button" data-bs-toggle="collapse" data-bs-target="#productosActivosCollapse" aria-expanded="false" aria-controls="productosActivosCollapse">
                                    <span id="productosActivosToggleText">Mostrar</span>
                                </button>
                            </h5>
                        </div>
                        <div id="productosActivosCollapse" class="collapse">
                            <div class="card-body">
                                <div id="productosActivosContent" class="table-responsive">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Filtros -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-filter me-2"></i>Filtros de Búsqueda
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3">
                                    <label class="form-label">Fecha Desde</label>
                                    <input type="date" class="form-control" id="fechaDesde">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Fecha Hasta</label>
                                    <input type="date" class="form-control" id="fechaHasta">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Encargado</label>
                                    <select class="form-select" id="filtroEncargado">
                                        <option value="">Todos los encargados</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Estado</label>
                                    <select class="form-select" id="filtroEstado">
                                        <option value="">Todos los estados</option>
                                        <option value="activa">Activa</option>
                                        <option value="cerrada">Cerrada</option>
                                    </select>
                                </div>
                            </div>
                            <div class="row mt-3">
                                <div class="col-md-12">
                                    <button class="btn btn-primary me-2" id="aplicarFiltros">
                                        <i class="fas fa-search me-1"></i>Aplicar Filtros
                                    </button>
                                    <button class="btn btn-secondary" id="limpiarFiltros">
                                        <i class="fas fa-broom me-1"></i>Limpiar Filtros
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tabla de Cajas (sin DataTables) -->
                    <div class="card">
                        <div class="card-header">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-table me-2"></i>Historial de Cajas
                                </h5>
                                <div class="d-flex gap-2 flex-wrap">
                                    <button type="button" class="btn btn-success btn-sm" id="exportCsv" title="Exportar a CSV" data-bs-toggle="tooltip" data-bs-placement="top">
                                        <i class="fas fa-file-csv me-1"></i>CSV
                                    </button>
                                    <button type="button" class="btn btn-danger btn-sm" id="exportPdf" title="Exportar a PDF" data-bs-toggle="tooltip" data-bs-placement="top">
                                        <i class="fas fa-file-pdf me-1"></i>PDF
                                    </button>
                                    <button type="button" class="btn btn-primary btn-sm" id="exportExcel" title="Exportar a Excel" data-bs-toggle="tooltip" data-bs-placement="top">
                                        <i class="fas fa-file-excel me-1"></i>Excel
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped table-bordered" id="cajasTable">
                                    <thead class="table-dark">
                                        <tr>
                                            <th><i class="fas fa-hashtag me-1"></i>ID</th>
                                            <th><i class="fas fa-calendar me-1"></i>Fecha</th>
                                            <th><i class="fas fa-clock me-1"></i>Hora</th>
                                            <th><i class="fas fa-user me-1"></i>Encargado</th>
                                            <th><i class="fas fa-dollar-sign me-1"></i>Total Ventas</th>
                                            <th><i class="fas fa-shopping-cart me-1"></i>Productos</th>
                                            <th><i class="fas fa-boxes me-1"></i>Inventario</th>
                                            <th><i class="fas fa-info-circle me-1"></i>Estado</th>
                                            <th><i class="fas fa-tools me-1"></i>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody id="cajasTableBody">
                                    </tbody>
                                </table>

                                <nav aria-label="Paginación de cajas" class="mt-3">
                                    <ul class="pagination justify-content-center" id="paginationCajas">
                                    </ul>
                                </nav>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="corteCajaConInventarioModal" tabindex="-1" aria-labelledby="corteCajaConInventarioModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="corteCajaConInventarioModalLabel">
                        <i class="fas fa-check me-2"></i>Corte de Caja con Inventario
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="text-center mb-3">
                        <i class="fas fa-exclamation-triangle text-warning" style="font-size: 3rem;"></i>
                    </div>
                    <h6 class="text-center mb-3">¿Está seguro de realizar el corte de caja?</h6>
                    <div class="alert alert-info">
                        <strong>Importante:</strong> Al realizar este corte:
                        <ul class="mb-0 mt-2">
                            <li>Se descontarán los productos vendidos del inventario</li>
                            <li>Se actualizarán las existencias automáticamente</li>
                            <li>Esta acción no se puede deshacer</li>
                        </ul>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nombre del Encargado *</label>
                        <input type="text" class="form-control" id="encargadoConInventario" placeholder="Ingrese el nombre del encargado" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>Cancelar
                    </button>
                    <button type="button" class="btn btn-primary" id="confirmarCorteConInventario">
                        <i class="fas fa-check me-1"></i>Confirmar Corte
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="corteCajaSinInventarioModal" tabindex="-1" aria-labelledby="corteCajaSinInventarioModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title" id="corteCajaSinInventarioModalLabel">
                        <i class="fas fa-times me-2"></i>Corte de Caja sin Inventario
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="text-center mb-3">
                        <i class="fas fa-exclamation-triangle text-warning" style="font-size: 3rem;"></i>
                    </div>
                    <h6 class="text-center mb-3">¿Está seguro de realizar el corte de caja?</h6>
                    <div class="alert alert-warning">
                        <strong>Atención:</strong> Al realizar este corte:
                        <ul class="mb-0 mt-2">
                            <li><strong>NO</strong> se descontarán productos del inventario</li>
                            <li>Las existencias permanecerán sin cambios</li>
                            <li>Solo se registrará el corte de ventas</li>
                            <li>Esta acción no se puede deshacer</li>
                        </ul>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nombre del Encargado *</label>
                        <input type="text" class="form-control" id="encargadoSinInventario" placeholder="Ingrese el nombre del encargado" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>Cancelar
                    </button>
                    <button type="button" class="btn btn-info" id="confirmarCorteSinInventario">
                        <i class="fas fa-check me-1"></i>Confirmar Corte
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="detalleCajaModal" tabindex="-1" aria-labelledby="detalleCajaModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title" id="detalleCajaModalLabel">
                        <i class="fas fa-eye me-2"></i>Detalle de Caja
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="detalleCajaContent">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>Cerrar
                    </button>
                    <button type="button" class="btn btn-primary" id="imprimirDetalle">
                        <i class="fas fa-print me-1"></i>Imprimir
                    </button>
                </div>
            </div>
        </div>
    </div>

    
    <?php include 'layouts/footer-scripts.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/apexcharts@latest"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.22.0/dist/sweetalert2.all.min.js"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.31/jspdf.plugin.autotable.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>

    <script src="presentation/caja/ui/cajas.min.js"></script>
    <script src="presentation/caja/js/caja.min.js"></script>
    <script src="assets/js/app.min.js"></script>

    <script src="presentation/caja/js/export-functions.min.js"></script>
</body>

</html>
