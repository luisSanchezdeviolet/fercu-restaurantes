class CajasUI {
  constructor() {
    this.isInitialized = false;
    this.apiUrl = "api/cajas.php";
    this.currentPage = 1;
    this.itemsPerPage = 10;
    this.totalItems = 0;
    this.allData = [];
    this.filteredData = [];
    this.detalleParaImprimir = null;
  }

  async init() {
    if (document.readyState === "loading") {
      document.addEventListener("DOMContentLoaded", () => {
        this.initializeComponents();
      });
    } else {
      this.initializeComponents();
    }
  }

  async initializeComponents() {
    this.isInitialized = true;
    this.setupEventListeners();
    await this.loadData();
    await this.loadStats();
    await this.loadEncargados();
    this.renderTable();
    this.renderPagination();
    await this.cargarProductosActivos();
    console.log("CajasUI inicializado correctamente");
  }

  setupEventListeners() {
    const btnCorteConInventario = document.getElementById(
      "corte-caja-inventario"
    );
    if (btnCorteConInventario) {
      btnCorteConInventario.addEventListener("click", () => {
        this.showModal("corteCajaConInventarioModal");
      });
    }

    const btnCorteSinInventario = document.getElementById(
      "corte-caja-sin-inventario"
    );
    if (btnCorteSinInventario) {
      btnCorteSinInventario.addEventListener("click", () => {
        this.showModal("corteCajaSinInventarioModal");
      });
    }

    const btnConfirmarConInventario = document.getElementById(
      "confirmarCorteConInventario"
    );
    if (btnConfirmarConInventario) {
      btnConfirmarConInventario.addEventListener("click", () => {
        this.procesarCorte(true);
      });
    }

    const btnConfirmarSinInventario = document.getElementById(
      "confirmarCorteSinInventario"
    );
    if (btnConfirmarSinInventario) {
      btnConfirmarSinInventario.addEventListener("click", () => {
        this.procesarCorte(false);
      });
    }

    this.setupModalEventListeners();

    const btnAplicarFiltros = document.getElementById("aplicarFiltros");
    if (btnAplicarFiltros) {
      btnAplicarFiltros.addEventListener("click", () => {
        this.aplicarFiltros();
      });
    }

    const btnLimpiarFiltros = document.getElementById("limpiarFiltros");
    if (btnLimpiarFiltros) {
      btnLimpiarFiltros.addEventListener("click", () => {
        this.limpiarFiltros();
      });
    }

    const btnImprimirDetalle = document.getElementById("imprimirDetalle");
    if (btnImprimirDetalle) {
      btnImprimirDetalle.addEventListener("click", () => {
        this.imprimirDetalle();
      });
    }
  }

  setupModalEventListeners() {
    const modalConInventario = document.getElementById(
      "corteCajaConInventarioModal"
    );
    if (modalConInventario) {
      modalConInventario.addEventListener("hidden.bs.modal", () => {
        this.limpiarCampoEncargado("encargadoConInventario");
        this.hideLoading();
      });
    }

    const modalSinInventario = document.getElementById(
      "corteCajaSinInventarioModal"
    );
    if (modalSinInventario) {
      modalSinInventario.addEventListener("hidden.bs.modal", () => {
        this.limpiarCampoEncargado("encargadoSinInventario");
        this.hideLoading();
      });
    }
  }

  limpiarCampoEncargado(inputId) {
    const input = document.getElementById(inputId);
    if (input) {
      input.value = "";

      input.classList.remove("is-invalid", "is-valid");
    }
  }

  async loadData() {
    try {
      const response = await fetch(this.apiUrl + "?action=findAll");
      const data = await response.json();

      if (data.success) {
        this.allData = data.data;
        this.filteredData = [...this.allData];
        this.totalItems = this.filteredData.length;
        this.currentPage = 1;
      } else {
        console.error("Error al cargar datos:", data.message);
        this.allData = [];
        this.filteredData = [];
        this.totalItems = 0;
      }
    } catch (error) {
      console.error("Error AJAX:", error);
      this.showErrorMessage("No se pudieron cargar los datos de las cajas");
      this.allData = [];
      this.filteredData = [];
      this.totalItems = 0;
    }
  }

  renderTable() {
    const tbody = document.getElementById("cajasTableBody");
    if (!tbody) return;

    const startIndex = (this.currentPage - 1) * this.itemsPerPage;
    const endIndex = startIndex + this.itemsPerPage;
    const pageData = this.filteredData.slice(startIndex, endIndex);

    if (pageData.length === 0) {
      tbody.innerHTML = `
        <tr>
          <td colspan="9" class="text-center text-muted py-4">
            <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
            No se encontraron cajas registradas
          </td>
        </tr>
      `;
      return;
    }

    tbody.innerHTML = pageData
      .map(
        (caja) => `
      <tr>
        <td class="fw-bold">${caja.id}</td>
        <td>${caja.fecha_cierre}</td>
        <td>${this.formatTime(caja.created_at)}</td>
        <td>${
          caja.encargado || '<span class="text-muted">Sin encargado</span>'
        }</td>
        <td class="text-end fw-bold text-success">$${this.formatCurrency(
          caja.total || 0
        )}</td>
        <td class="text-center">${parseInt(
          caja.total_productos || 0
        ).toLocaleString("es-MX")}</td>
        <td class="text-center">
          ${
            caja.estado_inventario === "Si"
              ? `<span class="badge bg-info">
              <i class="fas fa-check me-1"></i>Actualizado
            </span>`
              : `<span class="badge bg-secondary"><i class="fas fa-times me-1"></i>No actualizado</span>`
          }
        </td>
        <td class="text-center">
          <span class="badge bg-success">
            <i class="fas fa-check-circle me-1"></i>Cerrada
          </span>
        </td>
        <td class="text-center">
          <div class="btn-group" role="group">
            <button type="button" class="btn btn-sm btn-outline-primary" 
                    onclick="cajasUI.verDetalle(${caja.id})" 
                    title="Ver Detalle">
              <i class="fas fa-eye"></i>
            </button>
            <button type="button" class="btn btn-sm btn-outline-success" 
                    onclick="cajasUI.imprimirCaja(${caja.id})" 
                    title="Imprimir">
              <i class="fas fa-print"></i>
            </button>
          </div>
        </td>
      </tr>
    `
      )
      .join("");
  }

  renderPagination() {
    const pagination = document.getElementById("paginationCajas");
    if (!pagination) return;

    const totalPages = Math.ceil(this.totalItems / this.itemsPerPage);

    if (totalPages <= 1) {
      pagination.innerHTML = "";
      return;
    }

    let paginationHTML = "";

    paginationHTML += `
      <li class="page-item ${this.currentPage === 1 ? "disabled" : ""}">
        <a class="page-link" href="#" onclick="cajasUI.goToPage(${
          this.currentPage - 1
        }); return false;">
          <i class="fas fa-chevron-left"></i>
        </a>
      </li>
    `;

    const startPage = Math.max(1, this.currentPage - 2);
    const endPage = Math.min(totalPages, this.currentPage + 2);

    if (startPage > 1) {
      paginationHTML += `
        <li class="page-item">
          <a class="page-link" href="#" onclick="cajasUI.goToPage(1); return false;">1</a>
        </li>
      `;
      if (startPage > 2) {
        paginationHTML += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
      }
    }

    for (let i = startPage; i <= endPage; i++) {
      paginationHTML += `
        <li class="page-item ${i === this.currentPage ? "active" : ""}">
          <a class="page-link" href="#" onclick="cajasUI.goToPage(${i}); return false;">${i}</a>
        </li>
      `;
    }

    if (endPage < totalPages) {
      if (endPage < totalPages - 1) {
        paginationHTML += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
      }
      paginationHTML += `
        <li class="page-item">
          <a class="page-link" href="#" onclick="cajasUI.goToPage(${totalPages}); return false;">${totalPages}</a>
        </li>
      `;
    }

    paginationHTML += `
      <li class="page-item ${
        this.currentPage === totalPages ? "disabled" : ""
      }">
        <a class="page-link" href="#" onclick="cajasUI.goToPage(${
          this.currentPage + 1
        }); return false;">
          <i class="fas fa-chevron-right"></i>
        </a>
      </li>
    `;

    pagination.innerHTML = paginationHTML;
  }

  goToPage(page) {
    const totalPages = Math.ceil(this.totalItems / this.itemsPerPage);
    if (page < 1 || page > totalPages) return;

    this.currentPage = page;
    this.renderTable();
    this.renderPagination();
  }

  formatDate(dateString) {
    if (!dateString) return '<span class="text-muted">Sin fecha</span>';
    const fecha = new Date(dateString);
    return fecha.toLocaleDateString("es-MX");
  }

  formatTime(dateString) {
    if (!dateString) return '<span class="text-muted">Sin hora</span>';
    const fecha = new Date(dateString);
    return fecha.toLocaleTimeString("es-MX", {
      hour: "2-digit",
      minute: "2-digit",
    });
  }

  formatCurrency(amount) {
    return parseFloat(amount || 0).toLocaleString("es-MX", {
      minimumFractionDigits: 2,
      maximumFractionDigits: 2,
    });
  }

  async loadStats() {
    try {
      if (this.allData.length === 0) {
        await this.loadData();
      }

      const hoy = new Date().toISOString().split("T")[0];
      const cajasHoy = this.allData.filter(
        (caja) => caja.fecha_cierre && caja.fecha_cierre.split(" ")[0] === hoy
      );

      const totalVentas = cajasHoy.reduce(
        (sum, caja) => sum + parseFloat(caja.total || 0),
        0
      );
      const promedioVentas =
        cajasHoy.length > 0 ? totalVentas / cajasHoy.length : 0;

      this.updateElement("totalCajas", this.allData.length);
      this.updateElement("ventasHoy", "$" + this.formatCurrency(totalVentas));
      this.updateElement("cajasHoy", cajasHoy.length);
      this.updateElement(
        "promedioVentas",
        "$" + this.formatCurrency(promedioVentas)
      );
    } catch (error) {
      console.error("Error al cargar estadísticas:", error);
    }
  }

  updateElement(id, value) {
    const element = document.getElementById(id);
    if (element) {
      element.textContent = value;
    }
  }

  async loadEncargados() {
    try {
      if (this.allData.length === 0) {
        await this.loadData();
      }

      const encargados = [
        ...new Set(this.allData.map((caja) => caja.encargado).filter(Boolean)),
      ];
      const select = document.getElementById("filtroEncargado");

      if (select) {
        const firstOption = select.firstElementChild;
        select.innerHTML = "";
        if (firstOption) {
          select.appendChild(firstOption);
        }

        encargados.forEach((encargado) => {
          const option = document.createElement("option");
          option.value = encargado;
          option.textContent = encargado;
          select.appendChild(option);
        });
      }
    } catch (error) {
      console.error("Error al cargar encargados:", error);
    }
  }

  aplicarFiltros() {
    const fechaDesde = document.getElementById("fechaDesde")?.value;
    const fechaHasta = document.getElementById("fechaHasta")?.value;
    const encargado = document.getElementById("filtroEncargado")?.value;
    const estado = document.getElementById("filtroEstado")?.value;

    this.filteredData = this.allData.filter((caja) => {
      if (fechaDesde && caja.fecha_cierre) {
        const fechaCaja = new Date(caja.fecha_cierre.split(" ")[0]);
        const fechaDesdeDate = new Date(fechaDesde);
        if (fechaCaja < fechaDesdeDate) return false;
      }

      if (fechaHasta && caja.fecha_cierre) {
        const fechaCaja = new Date(caja.fecha_cierre.split(" ")[0]);
        const fechaHastaDate = new Date(fechaHasta);
        if (fechaCaja > fechaHastaDate) return false;
      }

      if (
        encargado &&
        (!caja.encargado ||
          !caja.encargado.toLowerCase().includes(encargado.toLowerCase()))
      ) {
        return false;
      }

      if (estado && estado !== "cerrada") {
        return false;
      }

      return true;
    });

    this.totalItems = this.filteredData.length;
    this.currentPage = 1;
    this.renderTable();
    this.renderPagination();

    if (this.filteredData.length === 0) {
      this.showInfoMessage("No se encontraron cajas con los filtros aplicados");
    }
  }

  limpiarFiltros() {
    const campos = [
      "fechaDesde",
      "fechaHasta",
      "filtroEncargado",
      "filtroEstado",
    ];
    campos.forEach((campo) => {
      const element = document.getElementById(campo);
      if (element) {
        element.value = "";
      }
    });

    this.filteredData = [...this.allData];
    this.totalItems = this.filteredData.length;
    this.currentPage = 1;
    this.renderTable();
    this.renderPagination();
  }

  async verDetalle(cajaId) {
    try {
      this.showLoading();
      const response = await fetch(
        `${this.apiUrl}?action=summary&id=${cajaId}`
      );
      const data = await response.json();

      if (data.success) {
        const detalle = data.data;
        this.mostrarDetalleModal(detalle);
      } else {
        this.showErrorMessage(
          data.message || "No se pudo obtener el detalle de la caja"
        );
      }
    } catch (error) {
      console.error("Error al obtener detalle:", error);
      this.showErrorMessage("No se pudo obtener el detalle de la caja");
    } finally {
      this.hideLoading();
    }
  }

  mostrarDetalleModal(detalle) {
    const modal = document.getElementById("detalleCajaModal");
    const content = document.getElementById("detalleCajaContent");

    if (!modal || !content) return;

    let productosHtml = "";
    if (detalle.productos && detalle.productos.length > 0) {
      productosHtml = detalle.productos
        .map(
          (producto) => `
        <tr>
          <td>${producto.producto_nombre || "Sin nombre"}</td>
          <td class="text-center">${producto.cantidad}</td>
          <td class="text-end">$${this.formatCurrency(
            producto.precio || 0
          )}</td>
          <td class="text-end">$${this.formatCurrency(producto.total || 0)}</td>
        </tr>
      `
        )
        .join("");
    } else {
      productosHtml =
        '<tr><td colspan="4" class="text-center text-muted">No hay productos registrados</td></tr>';
    }

    content.innerHTML = `
      <div class="row mb-4">
        <div class="col-md-6">
          <h6><i class="fas fa-info-circle me-2"></i>Información General</h6>
          <table class="table table-sm">
            <tr><td><strong>ID de Caja:</strong></td><td>${
              detalle.caja_info?.id || "N/A"
            }</td></tr>
            <tr><td><strong>Encargado:</strong></td><td>${
              detalle.caja_info?.encargado || "N/A"
            }</td></tr>
            <tr><td><strong>Fecha de Cierre:</strong></td><td>${
              detalle.caja_info?.fecha_cierre || "N/A"
            }</td></tr>
            <tr><td><strong>Total de Ventas:</strong></td><td><strong>$${this.formatCurrency(
              detalle.caja_info?.total || 0
            )}</strong></td></tr>
          </table>
        </div>
        <div class="col-md-6">
          <h6><i class="fas fa-chart-bar me-2"></i>Estadísticas</h6>
          <table class="table table-sm">
            <tr><td><strong>Productos Vendidos:</strong></td><td>${
              detalle.estadisticas?.total_productos_vendidos || 0
            }</td></tr>
            <tr><td><strong>Tipos de Productos:</strong></td><td>${
              detalle.estadisticas?.tipos_productos || 0
            }</td></tr>
            <tr><td><strong>Promedio por Producto:</strong></td><td>$${this.formatCurrency(
              detalle.estadisticas?.promedio_por_producto || 0
            )}</td></tr>
          </table>
        </div>
      </div>
      
      <div class="row">
        <div class="col-12">
          <h6><i class="fas fa-shopping-cart me-2"></i>Productos Vendidos</h6>
          <div class="table-responsive">
            <table class="table table-striped table-sm">
              <thead class="table-dark">
                <tr>
                  <th>Producto</th>
                  <th class="text-center">Cantidad</th>
                  <th class="text-end">Precio Unit.</th>
                  <th class="text-end">Total</th>
                </tr>
              </thead>
              <tbody>
                ${productosHtml}
              </tbody>
            </table>
          </div>
        </div>
      </div>
    `;

    this.detalleParaImprimir = detalle;

    const bsModal = new bootstrap.Modal(modal);
    bsModal.show();
  }

  async imprimirCaja(cajaId) {
    try {
      this.showLoading();
      const response = await fetch(
        `${this.apiUrl}?action=summary&id=${cajaId}`
      );
      const data = await response.json();

      if (data.success) {
        this.imprimirDetalleCaja(data.data);
      } else {
        this.showErrorMessage("No se pudo obtener el detalle para imprimir");
      }
    } catch (error) {
      console.error("Error al imprimir:", error);
      this.showErrorMessage("No se pudo imprimir la caja");
    } finally {
      this.hideLoading();
    }
  }

  imprimirDetalle() {
    if (this.detalleParaImprimir) {
      this.imprimirDetalleCaja(this.detalleParaImprimir);
    }
  }

  imprimirDetalleCaja(detalle) {
    const ventana = window.open("", "_blank");

    let config = {};
    try {
      config = JSON.parse(localStorage.getItem("ticketConfig")) || {};
    } catch (e) {
      config = {};
    }

    const fontFamily = "Arial, sans-serif";
    const businessInfoFontSize = config.businessInfoFontSize || 12;
    const titleFontSize = config.titleFontSize || 14;
    const productNameFontSize = config.productNameFontSize || 11;
    const productDetailsFontSize = config.productDetailsFontSize || 10;
    const productPriceFontSize = config.productPriceFontSize || 11;
    const totalFontSize = config.totalFontSize || 13;
    const footerFontSize = config.footerFontSize || 10;
    const ticketWidth = config.ticketWidth || 80;
    const logoUrl = config.logoUrl || "";
    const showLogo = config.showLogo !== false;
    const logoPosition = config.logoPosition || "center";
    const businessName = config.businessName || "Estudiovioleta";
    const businessAddress = config.businessAddress || "";
    const businessPhone = config.businessPhone || "";
    const businessInfoPosition = config.businessInfoPosition || "center";
    const ticketTitle = config.ticketTitle || "Detalle de Caja";
    const titlePosition = config.titlePosition || "center";
    const footerMessage = config.footerMessage || "";
    const footerPosition = config.footerPosition || "center";

    let productosHtml = "";
    if (detalle.productos && detalle.productos.length > 0) {
      productosHtml = detalle.productos
        .map(
          (producto) => `
                <tr>
                  <td style="font-size:${productNameFontSize}px;">${
            producto.producto_nombre || "Sin nombre"
          }</td>
                  <td style="text-align: center; font-size:${productDetailsFontSize}px;">${
            producto.cantidad
          }</td>
                  <td style="text-align: right; font-size:${productPriceFontSize}px;">$${this.formatCurrency(
            producto.precio || 0
          )}</td>
                  <td style="text-align: right; font-size:${productPriceFontSize}px;">$${this.formatCurrency(
            producto.total || 0
          )}</td>
                </tr>
              `
        )
        .join("");
    }

    let footerHtml = "";
    if (footerMessage) {
      const lines = footerMessage.split("\n");
      footerHtml = `<div class="ticket-footer text-${footerPosition}" style="font-size: ${footerFontSize}px;">`;
      lines.forEach((line) => {
        footerHtml += `<div>${line}</div>`;
      });
      footerHtml += "</div>";
    }

    const html = `
          <!DOCTYPE html>
          <html>
          <head>
            <title>${ticketTitle} #${detalle.caja_info?.id}</title>
            <style>
              body { 
                font-family: ${fontFamily}; 
                margin: 20px; 
                font-size: ${businessInfoFontSize}px;
                width: ${ticketWidth}mm;
                max-width: 100%;
              }
              .header { text-align: center; margin-bottom: 20px; }
              .ticket-logo { max-width: 100px; height: auto; margin-bottom: 10px; }
              .ticket-business-info { margin-bottom: 10px; font-size: ${businessInfoFontSize}px; }
              .ticket-title { font-weight: bold; font-size: ${titleFontSize}px; margin: 10px 0; border-bottom: 1px dashed #333; padding-bottom: 5px; text-align: ${titlePosition}; }
              .info-section { margin-bottom: 20px; }
              .info-table { width: 100%; border-collapse: collapse; font-size: ${businessInfoFontSize}px;}
              .info-table td { padding: 5px; border-bottom: 1px solid #eee; }
              .products-table { width: 100%; border-collapse: collapse; margin-top: 20px; }
              .products-table th, .products-table td { padding: 8px; border: 1px solid #ddd; }
              .products-table th { background-color: #f5f5f5; font-size: ${titleFontSize}px;}
              .total-row { font-weight: bold; background-color: #f9f9f9; }
              .ticket-total { font-size: ${totalFontSize}px; font-weight: bold; }
              .ticket-footer { font-size: ${footerFontSize}px; margin-top: 20px; border-top: 1px dashed #333; padding-top: 10px; }
              .text-left { text-align: left !important; }
              .text-center { text-align: center !important; }
              .text-right { text-align: right !important; }
              @media print { body { margin: 0; } }
            </style>
          </head>
          <body>
            <div class="header">
              ${
                showLogo && logoUrl
                  ? `<div class="text-${logoPosition}"><img src="${logoUrl}" class="ticket-logo" alt="Logo"></div>`
                  : ""
              }
              <div class="ticket-business-info text-${businessInfoPosition}">
                <div><strong>${businessName}</strong></div>
                ${businessAddress ? `<div>${businessAddress}</div>` : ""}
                ${businessPhone ? `<div>${businessPhone}</div>` : ""}
              </div>
              <div class="ticket-title">CORTE DE CAJA #${
                detalle.caja_info?.id
              }</div>
              <div style="font-size: ${productDetailsFontSize}px; margin-bottom: 5px;">Fecha de impresión: ${new Date().toLocaleString(
      "es-MX"
    )}</div>
            </div>
            
            <div class="info-section">
              <h3 style="font-size:${titleFontSize}px;">Información General</h3>
              <table class="info-table">
                <tr><td><strong>Encargado:</strong></td><td>${
                  detalle.caja_info?.encargado || "N/A"
                }</td></tr>
                <tr><td><strong>Fecha de Cierre:</strong></td><td>${
                  detalle.caja_info?.fecha_cierre || "N/A"
                }</td></tr>
                <tr><td><strong>Total de Ventas:</strong></td><td class="ticket-total"><strong>$${this.formatCurrency(
                  detalle.caja_info?.total || 0
                )}</strong></td></tr>
                <tr><td><strong>Productos Vendidos:</strong></td><td>${
                  detalle.estadisticas?.total_productos_vendidos || 0
                }</td></tr>
                <tr><td><strong>Tipos de Productos:</strong></td><td>${
                  detalle.estadisticas?.tipos_productos || 0
                }</td></tr>
              </table>
            </div>
            
            <div class="info-section">
              <h3 style="font-size:${titleFontSize}px;">Productos Vendidos</h3>
              <table class="products-table">
                <thead>
                  <tr>
                    <th>Producto</th>
                    <th>Cantidad</th>
                    <th>Precio Unit.</th>
                    <th>Total</th>
                  </tr>
                </thead>
                <tbody>
                  ${productosHtml}
                </tbody>
              </table>
            </div>
          </body>
          </html>
        `;

    ventana.document.write(html);
    ventana.document.close();
    setTimeout(() => {
      ventana.print();
    }, 250);
  }

  showModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
      const bsModal = new bootstrap.Modal(modal);
      bsModal.show();
    }
  }

  hideModal(modalId) {
    return new Promise((resolve) => {
      const modal = document.getElementById(modalId);
      if (modal) {
        const bsModal = bootstrap.Modal.getInstance(modal);
        if (bsModal) {
          modal.addEventListener(
            "hidden.bs.modal",
            () => {
              resolve();
            },
            { once: true }
          );

          bsModal.hide();
        } else {
          resolve();
        }
      } else {
        resolve();
      }
    });
  }

  validarEncargado(conInventario) {
    const encargadoInput = conInventario
      ? document.getElementById("encargadoConInventario")
      : document.getElementById("encargadoSinInventario");

    const encargado = encargadoInput?.value?.trim();

    if (!encargado) {
      if (encargadoInput) {
        encargadoInput.classList.add("is-invalid");
        encargadoInput.focus();
      }
      return false;
    }

    if (encargadoInput) {
      encargadoInput.classList.remove("is-invalid");
      encargadoInput.classList.add("is-valid");
    }

    return encargado;
  }

  async procesarCorte(conInventario) {
    try {
      const encargado = this.validarEncargado(conInventario);
      if (encargado === false) {
        this.showWarningMessage("Por favor ingrese el nombre del encargado");
        return;
      }

      this.showLoading();

      const response = await fetch(this.apiUrl, {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify({
          action: "closeBox",
          encargado: encargado,
          updateInventory: conInventario,
        }),
      });

      const resultado = await response.json();

      const modalId = conInventario
        ? "corteCajaConInventarioModal"
        : "corteCajaSinInventarioModal";

      await this.hideModal(modalId);

      if (resultado.success) {
        await this.loadData();
        await this.loadStats();
        this.renderTable();
        this.renderPagination();
        await this.cargarProductosActivos();
        this.showSuccessMessage(conInventario, resultado.data);
      } else {
        if (
          resultado.message &&
          (resultado.message.toLowerCase().includes("no hay ordenes") ||
            resultado.message.toLowerCase().includes("sin ordenes"))
        ) {
          this.showInfoMessage(
            "No hay órdenes pendientes para procesar en este momento"
          );
        } else {
          this.showErrorMessage(
            resultado.message || "Error al procesar el corte de caja"
          );
        }
      }
    } catch (error) {
      console.error("Error al procesar corte:", error);

      const modalId = conInventario
        ? "corteCajaConInventarioModal"
        : "corteCajaSinInventarioModal";

      await this.hideModal(modalId);

      this.hideLoading();

      this.showErrorMessage("Error de conexión al procesar el corte");
    }
  }

  showLoading() {
    if (typeof Swal !== "undefined") {
      Swal.fire({
        title: "Procesando...",
        text: "Realizando corte de caja",
        allowOutsideClick: false,
        allowEscapeKey: false,
        showConfirmButton: false,
        didOpen: () => {
          Swal.showLoading();
        },
      });
    }
  }

  hideLoading() {
    if (typeof Swal !== "undefined") {
      if (Swal.isVisible()) {
        Swal.close();
      }

      try {
        Swal.close();
      } catch (e) {}
    }
  }

  showSuccessMessage(conInventario, data = null) {
    let mensaje = conInventario
      ? "Corte de caja realizado correctamente. El inventario ha sido actualizado."
      : "Corte de caja realizado correctamente. El inventario no ha sido modificado.";

    if (data) {
      mensaje += `\n\nResumen:\n- Total: $${this.formatCurrency(
        data.total || 0
      )}\n- Órdenes procesadas: ${
        data.ordenes_procesadas || 0
      }\n- Productos vendidos: ${data.productos_vendidos || 0}`;
    }

    if (typeof Swal !== "undefined") {
      Swal.fire({
        icon: "success",
        title: "Corte Realizado",
        text: mensaje,
        confirmButtonText: "Aceptar",
        allowOutsideClick: true,
        allowEscapeKey: true,
      });
    } else {
      alert(mensaje);
    }
  }

  showErrorMessage(mensaje) {
    if (typeof Swal !== "undefined") {
      Swal.fire({
        icon: "error",
        title: "Error",
        text: mensaje,
        confirmButtonText: "Aceptar",
        allowOutsideClick: true,
        allowEscapeKey: true,
      });
    } else {
      alert("Error: " + mensaje);
    }
  }

  showWarningMessage(mensaje) {
    if (typeof Swal !== "undefined") {
      Swal.fire({
        icon: "warning",
        title: "Atención",
        text: mensaje,
        confirmButtonText: "Aceptar",
        allowOutsideClick: true,
        allowEscapeKey: true,
      });
    } else {
      alert("Atención: " + mensaje);
    }
  }

  showInfoMessage(mensaje) {
    if (typeof Swal !== "undefined") {
      Swal.fire({
        icon: "info",
        title: "Información",
        text: mensaje,
        confirmButtonText: "Aceptar",
        allowOutsideClick: true,
        allowEscapeKey: true,
      });
    } else {
      alert("Info: " + mensaje);
    }
  }

  async cargarProductosActivos() {
    try {
      const response = await fetch(
        "api/cajas.php?action=getProductosAntesDeCerrarCaja"
      );
      const data = await response.json();

      const contenedor = document.getElementById("productosActivosContent");
      if (!contenedor) return;

      if (!data.success || !data.data || data.data.length === 0) {
        contenedor.innerHTML = `<div class="alert alert-info mb-0">No hay productos activos en este momento.</div>`;
        return;
      }

      let tabla = `
            <table class="table table-striped table-bordered table-sm">
                <thead class="table-light">
                    <tr>
                        <th>Producto</th>
                        <th class="text-center">Cantidad</th>
                        <th class="text-end">Precio Unitario</th>
                        <th class="text-end">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    ${data.data
                      .map(
                        (prod) => `
                        <tr>
                            <td>${prod.nombre}</td>
                            <td class="text-center">${prod.cantidad_total}</td>
                            <td class="text-end">$${parseFloat(
                              prod.precio
                            ).toFixed(2)}</td>
                            <td class="text-end">$${parseFloat(
                              prod.subtotal
                            ).toFixed(2)}</td>
                        </tr>
                    `
                      )
                      .join("")}
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="3" class="text-end">Total vendido:</th>
                        <th class="text-end text-success">$${parseFloat(
                          data.total_vendido
                        ).toFixed(2)}</th>
                    </tr>
                </tfoot>
            </table>
        `;
      contenedor.innerHTML = tabla;
    } catch (error) {
      const contenedor = document.getElementById("productosActivosContent");
      if (contenedor) {
        contenedor.innerHTML = `<div class="alert alert-danger mb-0">Error al cargar productos activos.</div>`;
      }
    }
  }
}
