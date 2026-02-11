class VentasUI {
  constructor() {
    this.isInitialized = false;
    this.charts = {};
    this.estadisticas = null;
    this.ordenes = [];
    this.ordenesFiltradas = [];
    this.paginaActual = 1;
    this.registrosPorPagina = 10;
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

    await this.setupClickListeners();
    await this.loadStatistics();
    this.setupEventListeners();
    await this.cargarOrdenes();

    console.log("VentasUI inicializado correctamente");
  }

  async setupClickListeners() {
    document.addEventListener("click", (event) => {
      const target = event.target;

      if (target.id === "nueva-orden") {
        window.location.href = "menu.php";
      }

      if (target.id === "ordenes-pendientes") {
        window.location.href = "ordenes.php";
      }
    });
  }

  setupEventListeners() {
    document
      .getElementById("btn-aplicar-filtros")
      .addEventListener("click", () => {
        this.aplicarFiltros();
      });

    document
      .getElementById("btn-limpiar-filtros")
      .addEventListener("click", () => {
        this.limpiarFiltros();
      });

    document.getElementById("filter-search").addEventListener("keyup", (e) => {
      if (e.key === "Enter") {
        this.aplicarFiltros();
      }
    });

    // Event listeners para botones de exportación
    document
      .getElementById("btn-exportar-pdf")
      .addEventListener("click", () => {
        this.exportarAPDF();
      });

    document
      .getElementById("btn-exportar-excel")
      .addEventListener("click", () => {
        this.exportarAExcel();
      });

    document
      .getElementById("btn-exportar-csv")
      .addEventListener("click", () => {
        this.exportarACSV();
      });
  }

  async loadStatistics() {
    try {
      const response = await fetch("api/ordenes.php?action=getOrderStatistics");
      const result = await response.json();
      console.log("Estadísticas cargadas:", result);
      if (result.success) {
        this.estadisticas = result.data;
        this.updateStatisticCards();
        this.createCharts();
      } else {
        console.error("Error al cargar estadísticas:", result.message);
        this.showError("Error al cargar las estadísticas");
      }
    } catch (error) {
      console.error("Error al cargar estadísticas:", error);
      this.showError("Error de conexión al cargar estadísticas");
    }
  }

  updateStatisticCards() {
    const stats = this.estadisticas.estadisticas_por_estado;
    const totales = this.estadisticas.totales;

    document.getElementById("total-ordenes").textContent =
      totales.total_ordenes || 0;
    document.getElementById("ordenes-finalizadas").textContent =
      stats.listo_para_servir || 0;
    document.getElementById("ordenes-pendientes-count").textContent =
      stats.pendiente || 0;
    document.getElementById("ordenes-canceladas").textContent =
      stats.cancelado || 0;
  }

  createCharts() {
    this.createPieChart();
    this.createBarChart();
    this.createLineChart();
  }

  createPieChart() {
    const stats = this.estadisticas.estadisticas_por_estado;

    const options = {
      series: [
        stats.listo_para_servir || 0,
        stats.pendiente || 0,
        stats.cancelado || 0,
        stats.en_preparacion || 0,
      ],
      chart: {
        type: "pie",
        height: 350,
      },
      labels: ["Listo para servir", "Pendiente", "Cancelado", "En preparación"],
      colors: ["#28a745", "#ffc107", "#dc3545", "#17a2b8"],
      legend: {
        position: "bottom",
      },
      responsive: [
        {
          breakpoint: 480,
          options: {
            chart: {
              width: 200,
            },
            legend: {
              position: "bottom",
            },
          },
        },
      ],
    };

    if (this.charts.pie) {
      this.charts.pie.destroy();
    }

    this.charts.pie = new ApexCharts(
      document.querySelector("#chart-ordenes-pie"),
      options
    );
    this.charts.pie.render();
  }

  createBarChart() {
    const stats = this.estadisticas.estadisticas_por_estado;

    const options = {
      series: [
        {
          name: "Cantidad de Órdenes",
          data: [
            stats.listo_para_servir || 0,
            stats.pendiente || 0,
            stats.cancelado || 0,
            stats.en_preparacion || 0,
          ],
        },
      ],
      chart: {
        type: "bar",
        height: 350,
      },
      plotOptions: {
        bar: {
          horizontal: false,
          columnWidth: "55%",
          endingShape: "rounded",
        },
      },
      dataLabels: {
        enabled: false,
      },
      stroke: {
        show: true,
        width: 2,
        colors: ["transparent"],
      },
      xaxis: {
        categories: [
          "Listo para servir",
          "Pendiente",
          "Cancelado",
          "En preparación",
        ],
      },
      yaxis: {
        title: {
          text: "Cantidad de Órdenes",
        },
      },
      fill: {
        opacity: 1,
      },
      colors: ["#28a745", "#ffc107", "#dc3545", "#17a2b8"],
      tooltip: {
        y: {
          formatter: function (val) {
            return val + " órdenes";
          },
        },
      },
    };

    if (this.charts.bar) {
      this.charts.bar.destroy();
    }

    this.charts.bar = new ApexCharts(
      document.querySelector("#chart-ordenes-bar"),
      options
    );
    this.charts.bar.render();
  }

  createLineChart() {
    const estadisticasPorDia = this.estadisticas.estadisticas_por_dia || [];

    const fechas = estadisticasPorDia
      .map((item) => {
        const fecha = new Date(item.fecha + "T00:00:00");
        return fecha.toLocaleDateString("es-ES", {
          month: "short",
          day: "numeric",
        });
      })
      .reverse();

    const ordenes = estadisticasPorDia
      .map((item) => parseInt(item.total_ordenes))
      .reverse();
    const ventas = estadisticasPorDia
      .map((item) => parseFloat(item.total_ventas))
      .reverse();

    const options = {
      series: [
        {
          name: "Órdenes",
          type: "column",
          data: ordenes,
        },
        {
          name: "Ventas ($)",
          type: "line",
          data: ventas,
        },
      ],
      chart: {
        height: 350,
        type: "line",
        stacked: false,
      },
      stroke: {
        width: [0, 2, 5],
        curve: "smooth",
      },
      plotOptions: {
        bar: {
          columnWidth: "50%",
        },
      },
      fill: {
        opacity: [0.85, 0.25, 1],
        gradient: {
          inverseColors: false,
          shade: "light",
          type: "vertical",
          opacityFrom: 0.85,
          opacityTo: 0.55,
          stops: [0, 100, 100, 100],
        },
      },
      labels: fechas,
      markers: {
        size: 0,
      },
      xaxis: {
        type: "category",
      },
      yaxis: [
        {
          title: {
            text: "Cantidad de Órdenes",
          },
        },
        {
          opposite: true,
          title: {
            text: "Ventas ($)",
          },
        },
      ],
      tooltip: {
        shared: true,
        intersect: false,
        y: {
          formatter: function (y) {
            if (typeof y !== "undefined") {
              return y.toFixed(0);
            }
            return y;
          },
        },
      },
      colors: ["#008FFB", "#00E396"],
    };

    if (this.charts.line) {
      this.charts.line.destroy();
    }

    this.charts.line = new ApexCharts(
      document.querySelector("#chart-tendencia-lineal"),
      options
    );
    this.charts.line.render();
  }

  showError(message) {
    console.error(message);
    Swal.fire("Error", message, "error");
  }

  async refreshStatistics() {
    await this.loadStatistics();
  }

  async cargarOrdenes() {
    try {
      const response = await fetch("api/ordenes.php?action=getFinishedOrders");
      const result = await response.json();

      if (result.success) {
        this.ordenes = result.data;
        this.ordenesFiltradas = [...this.ordenes];
        this.renderizarTabla();
        console.log("Órdenes cargadas:", this.ordenes.length);
      } else {
        console.error("Error al cargar órdenes:", result.message);
        this.mostrarError("Error al cargar las órdenes");
      }
    } catch (error) {
      console.error("Error de conexión:", error);
      this.mostrarError("Error al conectar con el servidor");
    }
  }

  aplicarFiltros() {
    const busqueda = document
      .getElementById("filter-search")
      .value.toLowerCase();
    const estado = document.getElementById("filter-estado").value;
    const fechaDesde = document.getElementById("filter-fecha-desde").value;
    const fechaHasta = document.getElementById("filter-fecha-hasta").value;

    this.ordenesFiltradas = this.ordenes.filter((orden) => {
      const coincideBusqueda =
        busqueda === "" ||
        orden.id.toString().includes(busqueda) ||
        (orden.mesero_nombre &&
          orden.mesero_nombre.toLowerCase().includes(busqueda)) ||
        (orden.mesa_numero && orden.mesa_numero.toString().includes(busqueda));

      const coincideEstado = estado === "" || orden.estado === estado;

      let fechaOrden = new Date(orden.created_at);
      fechaOrden.setHours(0, 0, 0, 0);

      let coincideFechaDesde = true;
      if (fechaDesde) {
        const desde = new Date(fechaDesde);
        desde.setHours(0, 0, 0, 0);
        coincideFechaDesde = fechaOrden >= desde;
      }

      let coincideFechaHasta = true;
      if (fechaHasta) {
        const hasta = new Date(fechaHasta);
        hasta.setHours(23, 59, 59, 999);
        coincideFechaHasta = fechaOrden <= hasta;
      }

      return (
        coincideBusqueda &&
        coincideEstado &&
        coincideFechaDesde &&
        coincideFechaHasta
      );
    });

    this.paginaActual = 1;
    this.renderizarTabla();
  }

  limpiarFiltros() {
    document.getElementById("filter-search").value = "";
    document.getElementById("filter-estado").value = "";
    document.getElementById("filter-fecha-desde").value = "";
    document.getElementById("filter-fecha-hasta").value = "";

    this.ordenesFiltradas = [...this.ordenes];
    this.paginaActual = 1;
    this.renderizarTabla();
  }

  renderizarTabla() {
    const tbody = document.querySelector("#tabla-ordenes tbody");
    tbody.innerHTML = "";

    const inicio = (this.paginaActual - 1) * this.registrosPorPagina;
    const fin = Math.min(
      inicio + this.registrosPorPagina,
      this.ordenesFiltradas.length
    );

    document.getElementById("contador-registros").textContent =
      this.ordenesFiltradas.length;

    if (this.ordenesFiltradas.length === 0) {
      const tr = document.createElement("tr");
      tr.innerHTML = `<td colspan="9" class="text-center">No se encontraron órdenes</td>`;
      tbody.appendChild(tr);
      this.renderizarPaginacion();
      return;
    }

    for (let i = inicio; i < fin; i++) {
      const orden = this.ordenesFiltradas[i];
      const fechaFormateada = new Date(orden.created_at).toLocaleString();
      const total = parseFloat(orden.total).toFixed(2);
      let estadoClase = "";

      if (orden.estado === "Entregado") estadoClase = "badge bg-success";
      else if (orden.estado === "Cancelado") estadoClase = "badge bg-danger";
      else if (orden.estado === "Listo para servir")
        estadoClase = "badge bg-success";
      else if (orden.estado === "Completado") estadoClase = "badge bg-info";

      const tr = document.createElement("tr");
      tr.innerHTML = `
        <td>${orden.id}</td>
        <td>${fechaFormateada}</td>
        <td>${orden.mesa_numero || "-"}</td>
        <td>${orden.mesero_nombre || "-"}</td>
        <td>${orden.total_productos ? orden.total_productos : 0} productos</td>
        <td>$${total}</td>
        <td>${orden.metodo_pago || "-"}</td>
        <td><span class="${estadoClase}">${orden.estado}</span></td>
        <td>
          <button class="btn btn-sm btn-info ver-detalle" data-id="${
            orden.id
          }" title="Ver detalle">
            <i class="fas fa-eye"></i>
          </button>
          <button class="btn btn-sm btn-secondary imprimir-ticket" data-id="${
            orden.id
          }" title="Imprimir ticket">
            <i class="fas fa-print"></i>
          </button>
        </td>
      `;
      tbody.appendChild(tr);

      tr.querySelector(".ver-detalle").addEventListener("click", () => {
        this.verDetalleOrden(orden.id);
      });

      tr.querySelector(".imprimir-ticket").addEventListener("click", () => {
        this.imprimirTicket(orden.id);
      });
    }

    this.renderizarPaginacion();
  }

  renderizarPaginacion() {
    const pagination = document.getElementById("paginacion");
    pagination.innerHTML = "";

    const totalPaginas = Math.ceil(
      this.ordenesFiltradas.length / this.registrosPorPagina
    );

    if (totalPaginas <= 1) return;

    const prevLi = document.createElement("li");
    prevLi.className = `page-item ${this.paginaActual === 1 ? "disabled" : ""}`;
    prevLi.innerHTML = `<a class="page-link" href="javascript:void(0)">«</a>`;
    prevLi.addEventListener("click", () => {
      if (this.paginaActual > 1) {
        this.paginaActual--;
        this.renderizarTabla();
      }
    });
    pagination.appendChild(prevLi);

    for (let i = 1; i <= totalPaginas; i++) {
      const pageLi = document.createElement("li");
      pageLi.className = `page-item ${this.paginaActual === i ? "active" : ""}`;
      pageLi.innerHTML = `<a class="page-link" href="javascript:void(0)">${i}</a>`;
      pageLi.addEventListener("click", () => {
        this.paginaActual = i;
        this.renderizarTabla();
      });
      pagination.appendChild(pageLi);
    }

    const nextLi = document.createElement("li");
    nextLi.className = `page-item ${
      this.paginaActual === totalPaginas ? "disabled" : ""
    }`;
    nextLi.innerHTML = `<a class="page-link" href="javascript:void(0)">»</a>`;
    nextLi.addEventListener("click", () => {
      if (this.paginaActual < totalPaginas) {
        this.paginaActual++;
        this.renderizarTabla();
      }
    });
    pagination.appendChild(nextLi);
  }

  verDetalleOrden(ordenId) {
    const modal = new bootstrap.Modal(
      document.getElementById("modalDetalleOrden")
    );
    modal.show();

    const contenido = document.getElementById("contenidoModalOrden");
    contenido.innerHTML = `
        <div class="text-center py-4">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Cargando...</span>
            </div>
            <p class="mt-2 text-muted">Cargando detalles de la orden...</p>
        </div>
    `;

    document.getElementById("btnRegresarCocina").style.display = "none";
    document.getElementById("btnImprimirTicket").onclick = null;

    fetch(`api/ordenes.php?action=findById&id=${ordenId}`)
      .then((response) => response.json())
      .then((result) => {
        if (result.success) {
          const orden = result.data;
          this.mostrarDetalleOrdenEnModal(orden);
          this.configurarBotonesModal(orden);
        } else {
          this.mostrarErrorEnModal("No se pudo cargar el detalle de la orden");
        }
      })
      .catch((error) => {
        console.error("Error:", error);
        this.mostrarErrorEnModal("Error de conexión al cargar el detalle");
      });
  }

  mostrarDetalleOrdenEnModal(orden) {
    const contenido = document.getElementById("contenidoModalOrden");

    document.getElementById("modalDetalleOrdenLabel").innerHTML = `
        <i class="fas fa-receipt me-2"></i>Detalle de Orden #${orden.id}
    `;

    let productos = "";
    let totalCalculado = 0;

    if (orden.productos && orden.productos.length > 0) {
      orden.productos.forEach((producto) => {
        const subtotal = producto.precio * producto.cantidad;
        totalCalculado += subtotal;
        productos += `
                <tr>
                    <td>
                        <div class="d-flex align-items-center">
                            <div class="avatar-sm me-2">
                                <span class="avatar-title bg-soft-primary text-primary rounded-circle">
                                    <i class="fas fa-utensils"></i>
                                </span>
                            </div>
                            <div>
                                <h6 class="mb-0">${producto.nombre}</h6>
                            </div>
                        </div>
                    </td>
                    <td class="text-center">
                        <span class="badge bg-info">${producto.cantidad}</span>
                    </td>
                    <td class="text-end">$${parseFloat(producto.precio).toFixed(
                      2
                    )}</td>
                    <td class="text-end fw-bold">$${subtotal.toFixed(2)}</td>
                </tr>
            `;
      });
    } else {
      productos = `
            <tr>
                <td colspan="4" class="text-center text-muted py-3">
                    <i class="fas fa-box-open me-2"></i>No hay productos en esta orden
                </td>
            </tr>
        `;
    }

    let estadoColor = "secondary";
    let estadoIcon = "fas fa-question-circle";

    switch (orden.estado) {
      case "Entregado":
        estadoColor = "success";
        estadoIcon = "fas fa-check-circle";
        break;
      case "Cancelado":
        estadoColor = "danger";
        estadoIcon = "fas fa-times-circle";
        break;
      case "En preparación":
        estadoColor = "warning";
        estadoIcon = "fas fa-clock";
        break;
      case "Pendiente":
        estadoColor = "info";
        estadoIcon = "fas fa-hourglass-half";
        break;
      case "Listo para servir":
        estadoColor = "primary";
        estadoIcon = "fas fa-bell";
        break;
    }

    contenido.innerHTML = `
        <div class="row">
            <div class="col-md-6">
                <div class="card border-0 bg-light">
                    <div class="card-body">
                        <h6 class="card-title text-primary">
                            <i class="fas fa-info-circle me-2"></i>Información General
                        </h6>
                        <div class="row g-2">
                            <div class="col-6">
                                <small class="text-muted">Mesa:</small>
                                <p class="mb-1 fw-bold">${
                                  orden.mesa_numero || "No asignada"
                                }</p>
                            </div>
                            <div class="col-6">
                                <small class="text-muted">Mesero:</small>
                                <p class="mb-1 fw-bold">${
                                  orden.mesero_nombre || "No asignado"
                                }</p>
                            </div>
                            <div class="col-6">
                                <small class="text-muted">Fecha:</small>
                                <p class="mb-1">${new Date(
                                  orden.created_at
                                ).toLocaleDateString()}</p>
                            </div>
                            <div class="col-6">
                                <small class="text-muted">Hora:</small>
                                <p class="mb-1">${new Date(
                                  orden.created_at
                                ).toLocaleTimeString()}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card border-0 bg-light">
                    <div class="card-body">
                        <h6 class="card-title text-success">
                            <i class="fas fa-dollar-sign me-2"></i>Información de Pago
                        </h6>
                        <div class="row g-2">
                            <div class="col-12">
                                <small class="text-muted">Estado:</small>
                                <p class="mb-1">
                                    <span class="badge bg-${estadoColor}">
                                        <i class="${estadoIcon} me-1"></i>${
      orden.estado
    }
                                    </span>
                                </p>
                            </div>
                            <div class="col-6">
                                <small class="text-muted">Método de Pago:</small>
                                <p class="mb-1 fw-bold">${
                                  orden.metodo_pago || "No especificado"
                                }</p>
                            </div>
                            <div class="col-6">
                                <small class="text-muted">Total:</small>
                                <p class="mb-1 fw-bold text-success fs-5">$${parseFloat(
                                  orden.total
                                ).toFixed(2)}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        ${
          orden.notas
            ? `
            <div class="row mt-3">
                <div class="col-12">
                    <div class="alert alert-info" role="alert">
                        <h6 class="alert-heading">
                            <i class="fas fa-sticky-note me-2"></i>Notas Especiales
                        </h6>
                        <p class="mb-0">${orden.notas}</p>
                    </div>
                </div>
            </div>
        `
            : ""
        }
        
        <div class="row mt-3">
            <div class="col-12">
                <h6 class="text-primary mb-3">
                    <i class="fas fa-shopping-cart me-2"></i>Productos Ordenados
                </h6>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-primary">
                            <tr>
                                <th>Producto</th>
                                <th class="text-center">Cantidad</th>
                                <th class="text-end">Precio Unitario</th>
                                <th class="text-end">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${productos}
                        </tbody>
                        <tfoot class="table-dark">
                            <tr>
                                <th colspan="3" class="text-end">Total de la Orden:</th>
                                <th class="text-end fs-5">$${parseFloat(
                                  orden.total
                                ).toFixed(2)}</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    `;
  }

  configurarBotonesModal(orden) {
    const btnRegresarCocina = document.getElementById("btnRegresarCocina");
    const btnImprimirTicket = document.getElementById("btnImprimirTicket");

    if (orden.estado === "Listo para servir" || orden.estado === "Entregado") {
      btnRegresarCocina.style.display = "inline-block";
      btnRegresarCocina.onclick = () =>
        this.mostrarConfirmacionRegresoCocina(orden.id);
    } else {
      btnRegresarCocina.style.display = "none";
    }

    btnImprimirTicket.onclick = () => this.imprimirTicket(orden.id);
  }

  mostrarConfirmacionRegresoCocina(ordenId) {
    const modalDetalle = bootstrap.Modal.getInstance(
      document.getElementById("modalDetalleOrden")
    );
    modalDetalle.hide();

    const modalConfirmacion = new bootstrap.Modal(
      document.getElementById("modalConfirmacionCocina")
    );
    modalConfirmacion.show();

    document.getElementById("btnConfirmarRegresoCocina").onclick = () => {
      this.regresarOrdenACocina(ordenId);
      modalConfirmacion.hide();
    };
  }

  async regresarOrdenACocina(ordenId) {
    try {
      Swal.fire({
        title: "Procesando...",
        text: "Regresando orden a cocina",
        allowOutsideClick: false,
        didOpen: () => {
          Swal.showLoading();
        },
      });

      const response = await fetch("api/ordenes.php", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify({
          action: "updateEstado",
          id: Number(ordenId),
          nuevo_estado: "Pendiente",
        }),
      });

      const result = await response.json();
      console.log("Resultado de regresar orden a cocina:", result);
      if (result.success) {
        Swal.fire({
          icon: "success",
          title: "¡Éxito!",
          text: "La orden ha sido regresada a cocina correctamente",
          timer: 2000,
          showConfirmButton: false,
        });

        await this.cargarOrdenes();
        await this.refreshStatistics();
      } else {
        Swal.fire({
          icon: "error",
          title: "Error",
          text: result.message || "No se pudo regresar la orden a cocina",
        });
      }
    } catch (error) {
      console.error("Error:", error);
      Swal.fire({
        icon: "error",
        title: "Error de conexión",
        text: "No se pudo conectar con el servidor",
      });
    }
  }

  mostrarErrorEnModal(mensaje) {
    const contenido = document.getElementById("contenidoModalOrden");
    contenido.innerHTML = `
        <div class="text-center py-5">
            <i class="fas fa-exclamation-triangle text-danger" style="font-size: 3rem;"></i>
            <h5 class="mt-3 text-danger">Error</h5>
            <p class="text-muted">${mensaje}</p>
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                <i class="fas fa-times me-1"></i>Cerrar
            </button>
        </div>
    `;
  }

  imprimirTicket(ordenId) {
    fetch(`api/ordenes.php?action=findById&id=${ordenId}`)
      .then((response) => response.json())
      .then((result) => {
        if (result.success) {
          const orden = result.data;

          let config = {};
          try {
            config = JSON.parse(localStorage.getItem("ticketConfig")) || {};
          } catch (e) {
            config = {};
          }

          const {
            showLogo = true,
            logoUrl = "",
            logoPosition = "center",
            businessName = "Mi Restaurante",
            businessAddress = "Calle Principal #123",
            businessPhone = "(555) 123-4567",
            businessInfoFontSize = "12",
            businessInfoPosition = "center",
            ticketTitle = "TICKET DE VENTA",
            titleFontSize = "14",
            titlePosition = "center",
            productNameFontSize = "11",
            productDetailsFontSize = "10",
            productPriceFontSize = "11",
            showProductCode = true,
            showProductPrice = true,
            ticketWidth = "80",
            totalFontSize = "13",
            footerMessage = "¡Gracias por su compra!\nVuelva pronto",
            footerFontSize = "10",
            footerPosition = "center",
          } = config;

          let productos = "";
          let total = 0;
          orden.productos.forEach((producto) => {
            const subtotal = producto.precio * producto.cantidad;
            total += subtotal;

            productos += `
                        <div class="ticket-product">
                            <div style="flex: 1;">
                                ${
                                  showProductCode
                                    ? `<div style="font-size: ${productDetailsFontSize}px;">[${
                                        producto.product_id || "N/A"
                                      }]</div>`
                                    : ""
                                }
                                <div style="font-size: ${productNameFontSize}px;">${
              producto.nombre
            }</div>
                                <div style="font-size: ${productDetailsFontSize}px;">Cant: ${
              producto.cantidad
            }${
              showProductPrice
                ? ` x $${parseFloat(producto.precio).toFixed(2)}`
                : ""
            }</div>
                            </div>
                            <div style="font-weight: bold; font-size: ${productPriceFontSize}px;">$${subtotal.toFixed(
              2
            )}</div>
                        </div>
                    `;
          });

          let logoHtml = "";
          if (showLogo && logoUrl) {
            logoHtml = `
                        <div class="text-${logoPosition}">
                            <img src="${logoUrl}" class="ticket-logo" alt="Logo">
                        </div>
                    `;
          }

          let businessInfoHtml = "";
          if (businessName || businessAddress || businessPhone) {
            businessInfoHtml = `
                        <div class="ticket-business-info text-${businessInfoPosition}" style="font-size: ${businessInfoFontSize}px;">
                            ${
                              businessName
                                ? `<div><strong>${businessName}</strong></div>`
                                : ""
                            }
                            ${
                              businessAddress
                                ? `<div>${businessAddress}</div>`
                                : ""
                            }
                            ${
                              businessPhone ? `<div>${businessPhone}</div>` : ""
                            }
                        </div>
                    `;
          }

          let footerHtml = "";
          if (footerMessage) {
            const footerLines = footerMessage.split("\n");
            footerHtml = `
                        <div class="ticket-footer text-${footerPosition}" style="font-size: ${footerFontSize}px;">
                            ${footerLines
                              .map((line) => `<div>${line}</div>`)
                              .join("")}
                        </div>
                    `;
          }

          const ticketHtml = `
                    <!DOCTYPE html>
                    <html>
                    <head>
                        <title>Ticket - Orden #${orden.id}</title>
                        <style>
                            @media print {
                                body {
                                    margin: 0;
                                    padding: 0;
                                    font-family: 'Courier New', monospace;
                                    font-size: 12px;
                                    line-height: 1.2;
                                }
                                .ticket-preview {
                                    background: white !important;
                                    border: none !important;
                                    box-shadow: none !important;
                                    padding: 5px !important;
                                    margin: 0 !important;
                                    max-width: none !important;
                                }
                            }
                            body {
                                font-family: 'Courier New', monospace;
                                font-size: 12px;
                                line-height: 1.2;
                                margin: 0;
                                padding: 10px;
                            }
                            .ticket-preview {
                                background: white;
                                padding: 15px;
                                font-family: 'Courier New', monospace;
                                font-size: 12px;
                                line-height: 1.2;
                                max-width: ${
                                  ticketWidth === "58" ? "220px" : "300px"
                                };
                                margin: 0 auto;
                            }
                            .ticket-logo {
                                max-width: 100px;
                                height: auto;
                                margin-bottom: 10px;
                            }
                            .ticket-business-info {
                                margin-bottom: 15px;
                            }
                            .ticket-title {
                                font-weight: bold;
                                font-size: ${titleFontSize}px;
                                margin: 15px 0;
                                border-bottom: 1px dashed #333;
                                padding-bottom: 5px;
                            }
                            .ticket-order-info {
                                margin: 10px 0;
                                font-size: 11px;
                            }
                            .ticket-products {
                                margin: 15px 0;
                            }
                            .ticket-product {
                                display: flex;
                                justify-content: space-between;
                                margin-bottom: 3px;
                                font-size: 11px;
                            }
                            .ticket-total {
                                border-top: 1px dashed #333;
                                padding-top: 10px;
                                margin-top: 15px;
                                font-weight: bold;
                                font-size: ${totalFontSize}px;
                            }
                            .ticket-footer {
                                margin-top: 20px;
                                font-size: ${footerFontSize}px;
                                border-top: 1px dashed #333;
                                padding-top: 10px;
                            }
                            .text-left { text-align: left !important; }
                            .text-center { text-align: center !important; }
                            .text-right { text-align: right !important; }
                        </style>
                    </head>
                    <body>
                        <div class="ticket-preview">
                            ${logoHtml}
                            ${businessInfoHtml}
                            <div class="ticket-title text-${titlePosition}">
                                ${ticketTitle}
                            </div>
                            <div class="ticket-order-info">
                                <div>Orden: #${orden.id}</div>
                                <div>Mesero: ${
                                  orden.mesero_nombre || "No asignado"
                                }</div>
                                <div>Mesa: ${
                                  orden.mesa_numero || "No asignada"
                                }</div>
                                <div>Fecha: ${new Date(
                                  orden.created_at
                                ).toLocaleString("es-MX")}</div>
                            </div>
                            <div class="ticket-products">
                                <div style="border-bottom: 1px dashed #333; margin-bottom: 5px; font-weight: bold;">PRODUCTOS</div>
                                ${productos}
                            </div>
                            <div class="ticket-total text-right">
                                <div>TOTAL: $${parseFloat(orden.total).toFixed(
                                  2
                                )}</div>
                            </div>
                            <div class="ticket-order-info">
                                <div>Método de pago: ${
                                  orden.metodo_pago || "No especificado"
                                }</div>
                            </div>
                            ${footerHtml}
                        </div>
                        <script>
                            window.onload = function() {
                                window.print();
                                setTimeout(function() { window.close(); }, 500);
                            }
                        </script>
                    </body>
                    </html>
                `;

          const printWindow = window.open("", "_blank");
          printWindow.document.write(ticketHtml);
          printWindow.document.close();
        } else {
          Swal.fire(
            "Error",
            "No se pudo cargar la información para imprimir",
            "error"
          );
        }
      })
      .catch((error) => {
        console.error("Error:", error);
        Swal.fire(
          "Error",
          "Error de conexión al cargar la información para imprimir",
          "error"
        );
      });
  }

  mostrarError(mensaje) {
    Swal.fire({
      title: "Error",
      text: mensaje,
      icon: "error",
      confirmButtonText: "Aceptar",
    });
  }

  exportarAPDF() {
    try {
      const { jsPDF } = window.jspdf;
      const doc = new jsPDF();

      const fechaActual = new Date().toLocaleDateString("es-ES");
      const horaActual = new Date().toLocaleTimeString("es-ES");

      doc.setFontSize(20);
      doc.setTextColor(40, 40, 40);
      doc.text("REPORTE DE ÓRDENES FINALIZADAS", 105, 20, { align: "center" });

      doc.setFontSize(12);
      doc.setTextColor(80, 80, 80);
      doc.text(`Fecha de generación: ${fechaActual} ${horaActual}`, 20, 35);
      doc.text(`Total de registros: ${this.ordenesFiltradas.length}`, 20, 45);

      const columns = [
        { header: "ID", dataKey: "id" },
        { header: "Fecha", dataKey: "fecha" },
        { header: "Mesa", dataKey: "mesa" },
        { header: "Mesero", dataKey: "mesero" },
        { header: "Productos", dataKey: "productos" },
        { header: "Total", dataKey: "total" },
        { header: "Método Pago", dataKey: "metodoPago" },
        { header: "Estado", dataKey: "estado" },
      ];

      const data = this.ordenesFiltradas.map((orden) => ({
        id: orden.id,
        fecha: new Date(orden.created_at).toLocaleDateString("es-ES"),
        mesa: orden.mesa_numero || "-",
        mesero: orden.mesero_nombre || "-",
        productos: `${orden.total_productos || 0} productos`,
        total: `$${parseFloat(orden.total).toFixed(2)}`,
        metodoPago: orden.metodo_pago || "-",
        estado: orden.estado,
      }));

      doc.autoTable({
        columns: columns,
        body: data,
        startY: 55,
        theme: "striped",
        headStyles: {
          fillColor: [41, 128, 185],
          textColor: 255,
          fontStyle: "bold",
        },
        styles: {
          fontSize: 8,
          cellPadding: 3,
        },
        columnStyles: {
          id: { cellWidth: 15 },
          fecha: { cellWidth: 25 },
          mesa: { cellWidth: 20 },
          mesero: { cellWidth: 30 },
          productos: { cellWidth: 25 },
          total: { cellWidth: 20 },
          metodoPago: { cellWidth: 25 },
          estado: { cellWidth: 25 },
        },
      });

      const pageCount = doc.internal.getNumberOfPages();
      for (let i = 1; i <= pageCount; i++) {
        doc.setPage(i);
        doc.setFontSize(10);
        doc.setTextColor(128);
        doc.text(`Página ${i} de ${pageCount}`, 105, 285, { align: "center" });
        doc.text("Estudiovioleta - Sistema de Restaurante", 105, 295, {
          align: "center",
        });
      }

      doc.save(`ordenes-finalizadas-${fechaActual.replace(/\//g, "-")}.pdf`);

      Swal.fire({
        icon: "success",
        title: "¡Éxito!",
        text: "Reporte PDF generado correctamente",
        timer: 2000,
        showConfirmButton: false,
      });
    } catch (error) {
      console.error("Error al generar PDF:", error);
      Swal.fire({
        icon: "error",
        title: "Error",
        text: "No se pudo generar el archivo PDF",
      });
    }
  }

  exportarAExcel() {
    try {
      const data = this.ordenesFiltradas.map((orden) => ({
        ID: orden.id,
        Fecha: new Date(orden.created_at).toLocaleDateString("es-ES"),
        Hora: new Date(orden.created_at).toLocaleTimeString("es-ES"),
        Mesa: orden.mesa_numero || "-",
        Mesero: orden.mesero_nombre || "-",
        "Cantidad Productos": orden.total_productos || 0,
        Total: parseFloat(orden.total).toFixed(2),
        "Método de Pago": orden.metodo_pago || "-",
        Estado: orden.estado,
        Notas: orden.notas || "-",
      }));

      const wb = XLSX.utils.book_new();
      const ws = XLSX.utils.json_to_sheet(data);

      const colWidths = [
        { wch: 8 }, 
        { wch: 12 }, 
        { wch: 10 }, 
        { wch: 8 }, 
        { wch: 20 },
        { wch: 15 }, 
        { wch: 12 }, 
        { wch: 15 }, 
        { wch: 15 }, 
        { wch: 30 },
      ];
      ws["!cols"] = colWidths;

      const resumenData = [
        ["RESUMEN DE ÓRDENES FINALIZADAS"],
        [""],
        ["Fecha de generación:", new Date().toLocaleDateString("es-ES")],
        ["Hora de generación:", new Date().toLocaleTimeString("es-ES")],
        ["Total de registros:", this.ordenesFiltradas.length],
        [""],
        ["ESTADÍSTICAS POR ESTADO:"],
      ];

      if (this.estadisticas && this.estadisticas.estadisticas_por_estado) {
        const stats = this.estadisticas.estadisticas_por_estado;
        resumenData.push(
          ["Listo para servir:", stats.listo_para_servir || 0],
          ["Pendiente:", stats.pendiente || 0],
          ["Cancelado:", stats.cancelado || 0],
          ["En preparación:", stats.en_preparacion || 0]
        );
      }

      const wsResumen = XLSX.utils.aoa_to_sheet(resumenData);
      wsResumen["!cols"] = [{ wch: 25 }, { wch: 15 }];

      XLSX.utils.book_append_sheet(wb, wsResumen, "Resumen");
      XLSX.utils.book_append_sheet(wb, ws, "Órdenes Finalizadas");

      const fechaActual = new Date()
        .toLocaleDateString("es-ES")
        .replace(/\//g, "-");
      XLSX.writeFile(wb, `ordenes-finalizadas-${fechaActual}.xlsx`);

      Swal.fire({
        icon: "success",
        title: "¡Éxito!",
        text: "Archivo Excel generado correctamente",
        timer: 2000,
        showConfirmButton: false,
      });
    } catch (error) {
      console.error("Error al generar Excel:", error);
      Swal.fire({
        icon: "error",
        title: "Error",
        text: "No se pudo generar el archivo Excel",
      });
    }
  }

  exportarACSV() {
    try {
      const headers = [
        "ID",
        "Fecha",
        "Hora",
        "Mesa",
        "Mesero",
        "Cantidad Productos",
        "Total",
        "Método de Pago",
        "Estado",
        "Notas",
      ];

      const rows = this.ordenesFiltradas.map((orden) => [
        orden.id,
        new Date(orden.created_at).toLocaleDateString("es-ES"),
        new Date(orden.created_at).toLocaleTimeString("es-ES"),
        orden.mesa_numero || "-",
        orden.mesero_nombre || "-",
        orden.total_productos || 0,
        parseFloat(orden.total).toFixed(2),
        orden.metodo_pago || "-",
        orden.estado,
        (orden.notas || "-").replace(/,/g, ";"),
      ]);

      const csvContent = [headers, ...rows]
        .map((row) => row.map((field) => `"${field}"`).join(","))
        .join("\n");

      const BOM = "\uFEFF";
      const csvWithBOM = BOM + csvContent;

      const blob = new Blob([csvWithBOM], { type: "text/csv;charset=utf-8;" });
      const link = document.createElement("a");

      if (link.download !== undefined) {
        const url = URL.createObjectURL(blob);
        link.setAttribute("href", url);
        const fechaActual = new Date()
          .toLocaleDateString("es-ES")
          .replace(/\//g, "-");
        link.setAttribute("download", `ordenes-finalizadas-${fechaActual}.csv`);
        link.style.visibility = "hidden";
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
      }

      Swal.fire({
        icon: "success",
        title: "¡Éxito!",
        text: "Archivo CSV generado correctamente",
        timer: 2000,
        showConfirmButton: false,
      });
    } catch (error) {
      console.error("Error al generar CSV:", error);
      Swal.fire({
        icon: "error",
        title: "Error",
        text: "No se pudo generar el archivo CSV",
      });
    }
  }
}
