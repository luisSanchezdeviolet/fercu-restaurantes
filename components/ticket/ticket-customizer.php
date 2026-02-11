<div class="modal fade" id="ticketCustomModal" tabindex="-1" role="dialog" aria-labelledby="ticketCustomModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="ticketCustomModalLabel">
                    <i class="fas fa-ticket-alt"></i> Personalizar Ticket para Impresión Térmica
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" id="closeTicketCustomModal"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-header">
                                <h6><i class="fas fa-cog"></i> Configuración</h6>
                            </div>
                            <div class="card-body">
                                <div class="form-group mb-3">
                                    <label class="form-label">Logo</label>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="showLogo" checked>
                                        <label class="form-check-label" for="showLogo">
                                            Mostrar Logo
                                        </label>
                                    </div>
                                    <select class="form-select mt-2" id="logoPosition">
                                        <option value="center">Centro</option>
                                        <option value="left">Izquierda</option>
                                        <option value="right">Derecha</option>
                                    </select>
                                    <input type="file" class="form-control mt-2" id="logoFile" accept="image/*">
                                    <div class="form-check mt-2">
                                        <input class="form-check-input" type="checkbox" id="convertToBlackWhite" checked>
                                        <label class="form-check-label" for="convertToBlackWhite">
                                            Convertir a B/N (Impresoras Térmicas)
                                        </label>
                                    </div>
                                </div>

                                <div class="form-group mb-3">
                                    <label class="form-label">Información del Negocio</label>
                                    <input type="text" class="form-control mb-2" id="businessName" placeholder="Nombre del Negocio" value="Mi Restaurante">
                                    <input type="text" class="form-control mb-2" id="businessAddress" placeholder="Dirección" value="Calle Principal #123">
                                    <input type="text" class="form-control mb-2" id="businessPhone" placeholder="Teléfono" value="(555) 123-4567">
                                    <select class="form-select" id="businessInfoPosition">
                                        <option value="center">Centro</option>
                                        <option value="left">Izquierda</option>
                                        <option value="right">Derecha</option>
                                    </select>
                                </div>

                                <div class="form-group mb-3">
                                    <label class="form-label">Configuración Térmica</label>
                                    <select class="form-select mb-2" id="ticketWidth">
                                        <option value="58">58mm (Móvil)</option>
                                        <option value="80" selected>80mm (Estándar)</option>
                                    </select>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="thermalMode" checked>
                                        <label class="form-check-label" for="thermalMode">
                                            Modo Impresora Térmica
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="rawBTCompatible" checked>
                                        <label class="form-check-label" for="rawBTCompatible">
                                            Compatible con RawBT
                                        </label>
                                    </div>
                                </div>

                                <div class="form-group mb-3">
                                    <label class="form-label">Título del Ticket</label>
                                    <input type="text" class="form-control mb-2" id="ticketTitle" value="TICKET DE VENTA">
                                    <select class="form-select" id="titlePosition">
                                        <option value="center">Centro</option>
                                        <option value="left">Izquierda</option>
                                        <option value="right">Derecha</option>
                                    </select>
                                </div>

                                <div class="form-group mb-3">
                                    <label class="form-label">Mostrar en Productos</label>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="showProductCode" checked>
                                        <label class="form-check-label" for="showProductCode">Código</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="showProductPrice" checked>
                                        <label class="form-check-label" for="showProductPrice">Precio Unitario</label>
                                    </div>
                                </div>

                                <div class="form-group mb-3">
                                    <label class="form-label">Mensaje de Pie</label>
                                    <textarea class="form-control" id="footerMessage" rows="3" placeholder="¡Gracias por su compra!">¡Gracias por su compra!
Vuelva pronto</textarea>
                                    <select class="form-select mt-2" id="footerPosition">
                                        <option value="center">Centro</option>
                                        <option value="left">Izquierda</option>
                                        <option value="right">Derecha</option>
                                    </select>
                                </div>

                                <div class="form-group mb-3">
                                    <label class="form-label">Tamaños de Fuente (px)</label>
                                    <div class="row">
                                        <div class="col-6">
                                            <label class="form-label-sm">Info Negocio</label>
                                            <select class="form-select form-select-sm" id="businessInfoFontSize">
                                                <option value="10">10px</option>
                                                <option value="11">11px</option>
                                                <option value="12" selected>12px</option>
                                                <option value="13">13px</option>
                                                <option value="14">14px</option>
                                                <option value="15">15px</option>
                                                <option value="16">16px</option>
                                                <option value="17">17px</option>
                                                <option value="18">18px</option>
                                            </select>
                                        </div>
                                        <div class="col-6">
                                            <label class="form-label-sm">Título</label>
                                            <select class="form-select form-select-sm" id="titleFontSize">
                                                <option value="12">12px</option>
                                                <option value="13">13px</option>
                                                <option value="14" selected>14px</option>
                                                <option value="15">15px</option>
                                                <option value="16">16px</option>
                                                <option value="17">17px</option>
                                                <option value="18">18px</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="row mt-2">
                                        <div class="col-6">
                                            <label class="form-label-sm">Nombre Producto</label>
                                            <select class="form-select form-select-sm" id="productNameFontSize">
                                                <option value="9">9px</option>
                                                <option value="10">10px</option>
                                                <option value="11" selected>11px</option>
                                                <option value="12">12px</option>
                                                <option value="13">13px</option>
                                                <option value="14">14px</option>
                                                <option value="15">15px</option>
                                                <option value="16">16px</option>
                                                <option value="17">17px</option>
                                                <option value="18">18px</option>
                                            </select>
                                        </div>
                                        <div class="col-6">
                                            <label class="form-label-sm">Detalles Producto</label>
                                            <select class="form-select form-select-sm" id="productDetailsFontSize">
                                                <option value="8">8px</option>
                                                <option value="9">9px</option>
                                                <option value="10" selected>10px</option>
                                                <option value="11">11px</option>
                                                <option value="12">12px</option>
                                                <option value="13">13px</option>
                                                <option value="14">14px</option>
                                                <option value="15">15px</option>
                                                <option value="16">16px</option>
                                                <option value="17">17px</option>
                                                <option value="18">18px</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="row mt-2">
                                        <div class="col-4">
                                            <label class="form-label-sm">Precio Producto</label>
                                            <select class="form-select form-select-sm" id="productPriceFontSize">
                                                <option value="9">9px</option>
                                                <option value="10">10px</option>
                                                <option value="11" selected>11px</option>
                                                <option value="12">12px</option>
                                                <option value="13">13px</option>
                                                <option value="14">14px</option>
                                                <option value="15">15px</option>
                                                <option value="16">16px</option>
                                                <option value="17">17px</option>
                                                <option value="18">18px</option>
                                            </select>
                                        </div>
                                        <div class="col-4">
                                            <label class="form-label-sm">Total</label>
                                            <select class="form-select form-select-sm" id="totalFontSize">
                                                <option value="11">11px</option>
                                                <option value="12">12px</option>
                                                <option value="13" selected>13px</option>
                                                <option value="14">14px</option>
                                                <option value="15">15px</option>
                                                <option value="16">16px</option>
                                                <option value="17">17px</option>
                                                <option value="18">18px</option>
                                            </select>
                                        </div>
                                        <div class="col-4">
                                            <label class="form-label-sm">Pie</label>
                                            <select class="form-select form-select-sm" id="footerFontSize">
                                                <option value="8">8px</option>
                                                <option value="9">9px</option>
                                                <option value="10" selected>10px</option>
                                                <option value="11">11px</option>
                                                <option value="12">12px</option>
                                                <option value="13">13px</option>
                                                <option value="14">14px</option>
                                                <option value="15">15px</option>
                                                <option value="16">16px</option>
                                                <option value="17">17px</option>
                                                <option value="18">18px</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h6><i class="fas fa-eye"></i> Vista Previa</h6>                                <div class="d-flex flex-column flex-sm-row gap-2">
                                    <button type="button" class="btn btn-sm btn-outline-secondary" id="resetConfig">
                                        <i class="fas fa-undo"></i> Restablecer
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-primary" id="saveConfig">
                                        <i class="fas fa-save"></i> Guardar Config
                                    </button>
                                    <div class="btn-group" role="group">
                                        <button type="button" class="btn btn-sm btn-outline-info dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="fas fa-bluetooth"></i> ESC/POS
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li><a class="dropdown-item" href="#" id="generateRawBT">
                                                <i class="fas fa-code"></i> Ver Código
                                            </a></li>
                                            <li><a class="dropdown-item" href="#" id="downloadPRN">
                                                <i class="fas fa-download"></i> Descargar .PRN
                                            </a></li>
                                            <li><a class="dropdown-item" href="#" id="sendToPrinter">
                                                <i class="fas fa-print"></i> Enviar a Impresora
                                            </a></li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li><a class="dropdown-item" href="#" id="configurePrinter">
                                                <i class="fas fa-cog"></i> Configurar Impresora
                                            </a></li>
                                        </ul>
                                    </div>                                    <button type="button" class="btn btn-sm btn-outline-success" id="printAccess">
                                        <i class="fas fa-print"></i> Imprimir HTML
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-dark" id="printStatic">
                                        <i class="fas fa-file-alt"></i> Imprimir B/N
                                    </button>
                                </div>
                            </div>
                            <div class="card-body">
                                <div id="ticketPreview" class="ticket-preview mx-auto">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" id="cancelTicketCustom">
                    <i class="fas fa-times"></i> Cancelar
                </button>
                <button type="button" class="btn btn-primary" id="printTicket">
                    <i class="fas fa-print"></i> Imprimir Ticket
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="printerConfigModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-print"></i> Configurar Impresora Térmica
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i>
                    Configure la conexión con su impresora térmica para envío directo.
                </div>
                
                <div class="form-group mb-3">
                    <label class="form-label">Tipo de Conexión</label>
                    <select class="form-select" id="printerConnectionType">
                        <option value="usb">USB (Windows)</option>
                        <option value="network">Red/IP</option>
                        <option value="bluetooth">Bluetooth</option>
                        <option value="serial">Puerto Serie</option>
                    </select>
                </div>

                <div id="usbConfig" class="printer-config-section">
                    <div class="form-group mb-3">
                        <label class="form-label">Nombre de la Impresora</label>
                        <select class="form-select" id="usbPrinterName">
                            <option value="">Seleccionar impresora...</option>
                        </select>
                        <small class="form-text text-muted">
                            Las impresoras disponibles se cargarán automáticamente
                        </small>
                    </div>
                </div>

                <div id="networkConfig" class="printer-config-section" style="display: none;">
                    <div class="form-group mb-3">
                        <label class="form-label">Dirección IP</label>
                        <input type="text" class="form-control" id="printerIP" placeholder="192.168.1.100">
                    </div>
                    <div class="form-group mb-3">
                        <label class="form-label">Puerto</label>
                        <input type="number" class="form-control" id="printerPort" value="9100" placeholder="9100">
                    </div>
                </div>

                <div id="bluetoothConfig" class="printer-config-section" style="display: none;">
                    <div class="form-group mb-3">
                        <label class="form-label">Dispositivo Bluetooth</label>
                        <select class="form-select" id="bluetoothDevice">
                            <option value="">Buscar dispositivos...</option>
                        </select>
                        <button type="button" class="btn btn-sm btn-outline-primary mt-2" id="scanBluetooth">
                            <i class="fas fa-search"></i> Buscar Dispositivos
                        </button>
                    </div>
                </div>

                <div id="serialConfig" class="printer-config-section" style="display: none;">
                    <div class="form-group mb-3">
                        <label class="form-label">Puerto Serie</label>
                        <select class="form-select" id="serialPort">
                            <option value="COM1">COM1</option>
                            <option value="COM2">COM2</option>
                            <option value="COM3">COM3</option>
                            <option value="COM4">COM4</option>
                        </select>
                    </div>
                    <div class="form-group mb-3">
                        <label class="form-label">Velocidad (Baud Rate)</label>
                        <select class="form-select" id="baudRate">
                            <option value="9600">9600</option>
                            <option value="19200">19200</option>
                            <option value="38400">38400</option>
                            <option value="115200" selected>115200</option>
                        </select>
                    </div>
                </div>

                <div class="form-group mb-3">
                    <button type="button" class="btn btn-outline-success" id="testPrinter">
                        <i class="fas fa-vial"></i> Probar Conexión
                    </button>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="savePrinterConfig">
                    <i class="fas fa-save"></i> Guardar Configuración
                </button>
            </div>
        </div>
    </div>
</div>

<style>
    .ticket-preview {
        background: white;
        border: 2px dashed #ddd;
        padding: 10px;
        font-family: 'Courier New', monospace;
        font-size: 12px;
        line-height: 1.3;
        max-width: 300px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        color: #000;
        transition: all 0.3s ease;
    }

    .ticket-preview.width-58 {
        max-width: 220px;
        font-size: 10px;
    }

    .ticket-preview.width-80 {
        max-width: 300px;
        font-size: 12px;
    }

    .ticket-preview.thermal-mode {
        background: #f8f8f8;
        color: #000;
        border: 2px solid #666;
        box-shadow: 0 2px 15px rgba(0, 0, 0, 0.2);
    }

    .ticket-preview.thermal-mode img {
        filter: contrast(150%) brightness(40%);
    }

    .ticket-logo {
        max-width: 80px;
        height: auto;
        margin-bottom: 8px;
        display: block;
    }

    .ticket-business-info {
        margin-bottom: 12px;
        font-size: 11px;
    }

    .ticket-title {
        font-weight: bold;
        font-size: 13px;
        margin: 12px 0;
        border-bottom: 1px dashed #333;
        padding-bottom: 4px;
    }

    .ticket-order-info {
        margin: 8px 0;
        font-size: 10px;
    }

    .ticket-products {
        margin: 12px 0;
    }

    .ticket-product {
        display: flex;
        justify-content: space-between;
        margin-bottom: 2px;
        font-size: 10px;
        align-items: flex-start;
    }

    .ticket-product-info {
        flex: 1;
        margin-right: 5px;
    }

    .ticket-product-price {
        font-weight: bold;
        white-space: nowrap;
    }

    .ticket-total {
        border-top: 1px dashed #333;
        padding-top: 8px;
        margin-top: 12px;
        font-weight: bold;
        font-size: 12px;
    }

    .ticket-footer {
        margin-top: 15px;
        font-size: 9px;
        border-top: 1px dashed #333;
        padding-top: 8px;
    }

    .text-left {
        text-align: left !important;
    }

    .text-center {
        text-align: center !important;
    }

    .text-right {
        text-align: right !important;
    }

    .separator-line {
        border-bottom: 1px dashed #333;
        margin: 5px 0;
    }

    /* Estilos específicos para modo térmico */
    .thermal-mode .ticket-logo {
        max-width: 70px;
    }

    .thermal-mode .ticket-business-info {
        font-size: 10px;
        margin-bottom: 8px;
    }

    .thermal-mode .ticket-title {
        font-size: 12px;
        margin: 8px 0;
        font-weight: bold;
    }

    .thermal-mode .ticket-order-info {
        font-size: 9px;
        margin: 6px 0;
    }

    .thermal-mode .ticket-product {
        font-size: 9px;
        margin-bottom: 1px;
    }

    .thermal-mode .ticket-total {
        font-size: 11px;
        margin-top: 8px;
        padding-top: 6px;
    }

    .thermal-mode .ticket-footer {
        font-size: 8px;
        margin-top: 10px;
        padding-top: 6px;
    }

    @media (max-width: 768px) {
        .modal-dialog.modal-xl {
            max-width: 95%;
            margin: 1rem auto;
        }
        
        .ticket-preview {
            max-width: 280px;
        }
        
        .ticket-preview.width-58 {
            max-width: 200px;
        }
    }

    .form-label-sm {
        font-size: 0.75rem;
        font-weight: 500;
        margin-bottom: 0.25rem;
        color: #6c757d;
    }

    .form-select-sm {
        padding: 0.25rem 0.5rem;
        font-size: 0.75rem;
    }

    .printer-config-section {
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        padding: 15px;
        background-color: #f8f9fa;
    }

    .printer-config-section .form-label {
        font-weight: 600;
        color: #495057;
    }

    .dropdown-menu {
        border: 1px solid #dee2e6;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }

    .dropdown-item {
        padding: 8px 16px;
        transition: background-color 0.15s ease-in-out;
    }

    .dropdown-item:hover {
        background-color: #f8f9fa;
    }

    .dropdown-item i {
        width: 20px;
        margin-right: 8px;
    }

    .btn:disabled {
        opacity: 0.6;
        cursor: not-allowed;
    }

    .connection-status {
        display: inline-flex;
        align-items: center;
        font-size: 0.875rem;
        margin-top: 5px;
    }

    .connection-status.success {
        color: #28a745;
    }

    .connection-status.error {
        color: #dc3545;
    }

    .connection-status.warning {
        color: #ffc107;
    }

    .connection-status i {
        margin-right: 5px;
    }

    @keyframes pulse {
        0% { opacity: 1; }
        50% { opacity: 0.5; }
        100% { opacity: 1; }
    }    .btn .fa-spinner {
        animation: pulse 1s infinite;
    }

    #printStatic {
        border-color: #495057;
        color: #495057;
        transition: all 0.3s ease;
    }

    #printStatic:hover {
        background-color: #495057;
        border-color: #495057;
        color: white;
        transform: translateY(-1px);
        box-shadow: 0 2px 8px rgba(73, 80, 87, 0.3);
    }

    #printStatic:active {
        transform: translateY(0);
        box-shadow: 0 1px 4px rgba(73, 80, 87, 0.3);
    }

    #printStatic .fa-file-alt {
        margin-right: 4px;
    }
</style>