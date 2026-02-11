class OrdenesPanelJS {
  constructor() {}

  async init() {
    try {
      this.setupEventListeners();
      this.configureButtonsListeners();
      this.refreshPage();
    } catch (error) {
      console.error("Error al inicializar OrdenesPanelJS:", error);
    }
  }

  setupEventListeners() {
    document.addEventListener("DOMContentLoaded", () => {
      this.obtenerOrdenes();
    });
  }

  configureButtonsListeners() {
    document.addEventListener("click", (event) => {
      if (event.target.id === "crear-orden") {
        window.location.href = "menu.php";
      }

      if (event.target.id === "ordenes-finalizadas") {
        window.location.href = "ventas.php";
      }
    });
  }

  async obtenerOrdenes() {
    const listaOrdenes = document.getElementById("ordenes-lista");
    try {
      const response = await fetch(
        "/api/ordenes.php?action=filterByEstado&estado=Pendiente",
        {
          method: "GET",
        }
      );
      if (response.success) {
        throw new Error("Error al obtener las órdenes");
      }
      const data = await response.json();
      if (!data || !data.data || data.data.length === 0) {
        listaOrdenes.innerHTML = `
          <td colspan="9" class="text-center">
            <div class="alert alert-info" role="alert">
              No hay órdenes pendientes.
            </div>
          </td>
        `;
        return;
      }
      if (!Array.isArray(data.data)) {
        listaOrdenes.innerHTML = `
          <td colspan="9" class="text-center">
        <div class="alert alert-danger" role="alert">Error al obtener las órdenes.</div>
        </td>
        `;
        return;
      }
      listaOrdenes.innerHTML = data.data
        .map((orden) => this.templateOrden(orden))
        .join("");
      this.setupButtonsListeners();
    } catch (error) {
      console.error("Error al obtener las órdenes:", error);
    }
  }

  formatPrecio(precio) {
    return new Intl.NumberFormat("es-MX", {
      style: "currency",
      currency: "MXN",
    }).format(precio);
  }

  templateOrden(orden) {
    return `
      <tr data-id="${orden.id}">
      <td>${orden.id}</td>
      <td>${orden.mesero_nombre}</td>
      <td>${orden.mesa_numero}</td>
      <td>${
        orden.productos
          ? orden.productos.reduce((acc, p) => acc + p.cantidad, 0)
          : 0
      }</td>
      <td>${this.formatPrecio(
        orden.total ||
          (orden.productos
            ? orden.productos.reduce((acc, p) => acc + p.precio * p.cantidad, 0)
            : 0)
      )}</td>
      <td>
        <span class="badge ${
          orden.estado === "Pendiente"
            ? "bg-warning text-dark"
            : orden.estado === "En Preparación"
            ? "bg-danger"
            : orden.estado === "Listo para servir"
            ? "bg-info"
            : orden.estado === "Entregado"
            ? "bg-success"
            : "bg-secondary"
        } text-uppercase fw-bold">${orden.estado}</span>
      </td>
      <td>${new Date(orden.fecha).toLocaleString("es-MX", {
        year: "numeric",
        month: "2-digit",
        day: "2-digit",
        hour: "2-digit",
        minute: "2-digit",
      })}</td>
      <td class="text-center d-flex justify-content-center align-items-center gap-1">
      <button class="btn btn-info btn-sm ver-productos" data-id="${
        orden.id
      }" title="Ver Productos">
        <i class="bi bi-eye" style="pointer-events: none;"></i>
      </button>
      <button class="btn btn-success btn-sm finalizar-orden" data-id="${
        orden.id
      }" title="Finalizar">
        <i class="bi bi-check-circle" style="pointer-events: none;"></i>
      </button>
      <button class="btn btn-primary btn-sm editar-orden" data-id="${
        orden.id
      }" title="Editar">
        <i class="bi bi-pencil" style="pointer-events: none;"></i>
      </button>
      <button class="btn btn-danger btn-sm eliminar-orden" data-id="${
        orden.id
      }" title="Eliminar">
        <i class="bi bi-trash" style="pointer-events: none;"></i>
      </button>
      <button class="btn btn-secondary btn-sm imprimir-ticket" data-id="${
        orden.id
      }" title="Imprimir">
        <i class="bi bi-printer" style="pointer-events: none;"></i>
      </button>
    </td>
      </tr>
    `;
  }

  async refreshPage() {
    try {
      const btnActualizarOrdenes = document.getElementById(
        "btn-actualizar-ordenes"
      );
      if (!btnActualizarOrdenes) {
        throw new Error("Elemento btn-actualizar-ordenes no encontrado");
      }

      btnActualizarOrdenes.addEventListener("click", async () => {
        btnActualizarOrdenes.innerHTML = `<div class="text-center"><i class="fas fa-sync"></i> Cargando...</div>`;
        await this.obtenerOrdenes();
        btnActualizarOrdenes.innerHTML = `<i class="fas fa-sync"></i> Actualizar`;
      });
    } catch (error) {
      console.error("Error al refrescar la página:", error);
    }
  }

  setupButtonsListeners() {
    document.addEventListener("click", (event) => {
      if (event.target.classList.contains("finalizar-orden")) {
        const ordenId = event.target.getAttribute("data-id");
        this.finalizarOrden(ordenId);
      } else if (event.target.classList.contains("editar-orden")) {
        const ordenId = event.target.getAttribute("data-id");
        this.editarOrden(ordenId);
      } else if (event.target.classList.contains("eliminar-orden")) {
        const ordenId = event.target.getAttribute("data-id");
        this.eliminarOrden(ordenId);
      } else if (event.target.classList.contains("imprimir-ticket")) {
        const ordenId = event.target.getAttribute("data-id");
        this.imprimirTicket(ordenId);
      } else if (event.target.classList.contains("ver-productos")) {
        const ordenId = event.target.getAttribute("data-id");
        this.verProductosOrden(ordenId);
      }
    });
  }

  editarOrden(ordenId) {
    window.location.href = `crear-orden.php?orden_id=${ordenId}`;
  }

  async eliminarOrden(ordenId) {
    try {
      const nuevo_estado = "Cancelado";
      Swal.fire({
        title: "¿Estás seguro?",
        text: "Una vez cancelada, no podrás recuperar esta orden.",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Sí, cancelar orden",
        cancelButtonText: "No, mantener orden",
      }).then(async (result) => {
        if (result.isConfirmed) {
          const response = await fetch("/api/ordenes.php", {
            method: "POST",
            headers: {
              "Content-Type": "application/json",
            },
            body: JSON.stringify({
              action: "updateEstado",
              id: Number(ordenId),
              nuevo_estado,
            }),
          });
          const data = await response.json();
          if (data.success) {
            await this.obtenerOrdenes();
            Swal.fire("¡Cancelada!", "La orden ha sido cancelada.", "success");
          } else {
            Swal.fire(
              "Error",
              data.message || "No se pudo cancelar la orden",
              "error"
            );
          }
        }
      });
    } catch (error) {
      console.error("Error al eliminar la orden:", error);
      Swal.fire("Error", "Ocurrió un error al cancelar la orden", "error");
    }
  }
  imprimirTicket(ordenId) {
    if (window.ticketCustomizer) {
      window.ticketCustomizer.openModal(ordenId);
    } else {
      console.error("TicketCustomizer no está disponible");
    }
  }

  async finalizarOrden(ordenId) {
    try {
      const modalExistente = document.getElementById("modalFinalizarOrden");
      if (modalExistente) {
        const modalInstance = bootstrap.Modal.getInstance(modalExistente);
        if (modalInstance) {
          modalInstance.hide();
        }
        modalExistente.remove();
      }

      const response = await fetch(
        `/api/ordenes.php?action=findById&id=${ordenId}`,
        {
          method: "GET",
        }
      );

      if (!response.ok) {
        throw new Error("Error al obtener los datos de la orden");
      }

      const data = await response.json();

      if (!data.success) {
        throw new Error(
          data.message || "Error al obtener los datos de la orden"
        );
      }

      const orden = data.data;
      this.mostrarModalFinalizarOrden(orden);
    } catch (error) {
      console.error("Error al finalizar la orden:", error);
      Swal.fire(
        "Error",
        "No se pudo cargar la información de la orden",
        "error"
      );
    }
  }

  mostrarModalFinalizarOrden(orden) {
    this.limpiarModalesExistentes();

    const modalHtml = `
  <div class="modal fade" id="modalFinalizarOrden" tabindex="-1" aria-labelledby="modalFinalizarOrdenLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header bg-success text-white">
          <h5 class="modal-title" id="modalFinalizarOrdenLabel">
            <i class="bi bi-check-circle me-2"></i>Finalizar Orden #${orden.id}
          </h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <!-- Resumen de la orden -->
          <div class="card mb-4 border-success">
            <div class="card-header bg-light">
              <h6 class="mb-0"><i class="bi bi-receipt me-2"></i>Resumen de la Orden</h6>
            </div>
            <div class="card-body">
              <div class="row">
                <div class="col-md-6">
                  <p class="mb-1"><strong>Mesa:</strong> ${
                    orden.mesa_numero
                  }</p>
                  <p class="mb-1"><strong>Mesero:</strong> ${
                    orden.mesero_nombre
                  }</p>
                </div>
                <div class="col-md-6">
                  <p class="mb-1"><strong>Fecha:</strong> ${new Date(
                    orden.created_at
                  ).toLocaleString("es-MX")}</p>
                  <p class="mb-1"><strong>Notas:</strong> ${
                    orden.notas || "Sin notas"
                  }</p>
                </div>
              </div>
              <hr>
              <h6>Productos:</h6>
              <div class="table-responsive">
                <table class="table table-sm">
                  <tbody>
                    ${orden.productos
                      .map(
                        (producto) => `
                      <tr>
                        <td>${producto.nombre}</td>
                        <td class="text-center">x${producto.cantidad}</td>
                        <td class="text-end">${this.formatPrecio(
                          producto.precio * producto.cantidad
                        )}</td>
                      </tr>
                    `
                      )
                      .join("")}
                  </tbody>
                </table>
              </div>
              <div class="text-end">
                <h5 class="text-success fw-bold">Total: ${this.formatPrecio(
                  orden.total
                )}</h5>
              </div>
            </div>
          </div>

          <!-- Métodos de pago -->
          <div class="card border-primary">
            <div class="card-header bg-primary text-white">
              <h6 class="mb-0"><i class="bi bi-credit-card me-2"></i>Método de Pago</h6>
            </div>
            <div class="card-body">
              <div class="row g-3">
                <div class="col-md-3">
                  <button type="button" class="btn btn-outline-success w-100 metodo-pago-btn" data-metodo="Efectivo">
                    <i class="bi bi-cash-stack d-block fs-2 mb-2"></i>
                    <span>Efectivo</span>
                  </button>
                </div>
                <div class="col-md-3">
                  <button type="button" class="btn btn-outline-primary w-100 metodo-pago-btn" data-metodo="Tarjeta de credito">
                    <i class="bi bi-credit-card d-block fs-2 mb-2"></i>
                    <span>Crédito</span>
                  </button>
                </div>
                <div class="col-md-3">
                  <button type="button" class="btn btn-outline-warning w-100 metodo-pago-btn" data-metodo="Tarjeta de debito">
                    <i class="bi bi-credit-card-2-front d-block fs-2 mb-2"></i>
                    <span>Débito</span>
                  </button>
                </div>
                <div class="col-md-3">
                  <button type="button" class="btn btn-outline-info w-100 metodo-pago-btn" data-metodo="Transferencia bancaria">
                    <i class="bi bi-phone d-block fs-2 mb-2"></i>
                    <span>Transferencia</span>
                  </button>
                </div>
              </div>

              <!-- Panel de efectivo -->
              <div id="panelEfectivo" class="mt-4" style="display: none;">
                <div class="alert alert-info">
                  <i class="bi bi-info-circle me-2"></i>
                  <strong>Total a pagar:</strong> ${this.formatPrecio(
                    orden.total
                  )}
                </div>
                
                <div class="row g-3">
                  <div class="col-md-6">
                    <label for="montoRecibido" class="form-label">Monto recibido:</label>
                    <div class="input-group">
                      <span class="input-group-text">$</span>
                      <input type="number" class="form-control" id="montoRecibido" 
                             placeholder="0.00" step="0.01" min="${
                               orden.total
                             }">
                    </div>
                  </div>
                  <div class="col-md-6">
                    <label class="form-label">Cambio a entregar:</label>
                    <div class="alert alert-success mb-0">
                      <span id="cambioMostrar" class="fw-bold fs-5">$0.00</span>
                    </div>
                  </div>
                </div>
                
                <div class="row mt-3">
                  <div class="col-12">
                    <div class="d-flex gap-2 flex-wrap">
                      <button type="button" class="btn btn-outline-secondary btn-sm monto-rapido" data-monto="${
                        orden.total
                      }">
                        Pago Exacto
                      </button>
                      <button type="button" class="btn btn-outline-secondary btn-sm monto-rapido" data-monto="${
                        Math.ceil(orden.total / 50) * 50
                      }">
                        ${Math.ceil(orden.total / 50) * 50}
                      </button>
                      <button type="button" class="btn btn-outline-secondary btn-sm monto-rapido" data-monto="${
                        Math.ceil(orden.total / 100) * 100
                      }">
                        ${Math.ceil(orden.total / 100) * 100}
                      </button>
                      <button type="button" class="btn btn-outline-secondary btn-sm monto-rapido" data-monto="${
                        orden.total + 100
                      }">
                        ${orden.total + 100}
                      </button>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Panel confirmación otros métodos -->
              <div id="panelOtrosMetodos" class="mt-4" style="display: none;">
                <div class="alert alert-success text-center">
                  <i class="bi bi-check-circle fs-1"></i>
                  <h5 class="mt-2">¿Confirmar pago?</h5>
                  <p class="mb-0">Total: <strong>${this.formatPrecio(
                    orden.total
                  )}</strong></p>
                  <p class="mb-0">Método: <strong id="metodoSeleccionado"></strong></p>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
            <i class="bi bi-x-circle me-1"></i>Cancelar
          </button>
          <button type="button" class="btn btn-success" id="btnConfirmarPago" disabled>
            <i class="bi bi-check-circle me-1"></i>Confirmar Pago
          </button>
        </div>
      </div>
    </div>
  </div>
`;

    document.body.insertAdjacentHTML("beforeend", modalHtml);

    this.configurarEventListenersModal(orden);

    try {
      const modalElement = document.getElementById("modalFinalizarOrden");
      const modal = new bootstrap.Modal(modalElement, {
        backdrop: "static",
        keyboard: false,
      });

      this.modalInstance = modal;

      modal.show();
    } catch (error) {
      console.error("Error al crear el modal:", error);
    }
  }

  limpiarModalesExistentes() {
    const modalExistente = document.getElementById("modalFinalizarOrden");
    if (modalExistente) {
      const modalInstance = bootstrap.Modal.getInstance(modalExistente);
      if (modalInstance) {
        modalInstance.dispose();
      }
      modalExistente.remove();
    }

    const backdrops = document.querySelectorAll(".modal-backdrop");
    backdrops.forEach((backdrop) => backdrop.remove());

    document.body.classList.remove("modal-open");
    document.body.style.removeProperty("padding-right");
  }

  configurarEventListenersModal(orden) {
    const modal = document.getElementById("modalFinalizarOrden");
    const metodoButtons = modal.querySelectorAll(".metodo-pago-btn");
    const panelEfectivo = modal.querySelector("#panelEfectivo");
    const panelOtrosMetodos = modal.querySelector("#panelOtrosMetodos");
    const montoRecibido = modal.querySelector("#montoRecibido");
    const cambioMostrar = modal.querySelector("#cambioMostrar");
    const btnConfirmarPago = modal.querySelector("#btnConfirmarPago");
    const metodoSeleccionado = modal.querySelector("#metodoSeleccionado");
    const montosRapidos = modal.querySelectorAll(".monto-rapido");

    let metodoPagoActual = null;

    modal.addEventListener("hidden.bs.modal", () => {
      this.limpiarModalesExistentes();
    });

    metodoButtons.forEach((button) => {
      button.addEventListener("click", () => {
        if (button.disabled) return;

        metodoButtons.forEach((btn) => {
          btn.classList.remove(
            "btn-success",
            "btn-primary",
            "btn-info",
            "btn-warning"
          );
          btn.classList.add(
            "btn-outline-success",
            "btn-outline-primary",
            "btn-outline-info",
            "btn-outline-warning"
          );
        });

        button.classList.remove(
          "btn-outline-success",
          "btn-outline-primary",
          "btn-outline-info",
          "btn-outline-warning"
        );

        const metodo = button.dataset.metodo;
        metodoPagoActual = metodo;

        if (metodo === "Efectivo") {
          button.classList.add("btn-success");
          panelEfectivo.style.display = "block";
          panelOtrosMetodos.style.display = "none";
          btnConfirmarPago.disabled = true;
          setTimeout(() => montoRecibido.focus(), 100);
        } else {
          if (metodo === "Tarjeta de credito") {
            button.classList.add("btn-primary");
          } else if (metodo === "Tarjeta de debito") {
            button.classList.add("btn-warning");
          } else if (metodo === "Transferencia bancaria") {
            button.classList.add("btn-info");
          }

          panelEfectivo.style.display = "none";
          panelOtrosMetodos.style.display = "block";
          metodoSeleccionado.textContent = metodo;
          btnConfirmarPago.disabled = false;
        }
      });
    });

    if (montoRecibido) {
      montoRecibido.addEventListener("input", () => {
        const recibido = parseFloat(montoRecibido.value) || 0;
        const cambio = recibido - orden.total;

        if (recibido >= orden.total) {
          cambioMostrar.textContent = this.formatPrecio(cambio);
          cambioMostrar.parentElement.className = "alert alert-success mb-0";
          btnConfirmarPago.disabled = false;
        } else {
          cambioMostrar.textContent = "Monto insuficiente";
          cambioMostrar.parentElement.className = "alert alert-danger mb-0";
          btnConfirmarPago.disabled = true;
        }
      });
    }

    montosRapidos.forEach((button) => {
      button.addEventListener("click", () => {
        const monto = parseFloat(button.dataset.monto);
        montoRecibido.value = monto;
        montoRecibido.dispatchEvent(new Event("input"));
      });
    });

    btnConfirmarPago.addEventListener("click", async () => {
      if (btnConfirmarPago.disabled) return;

      await this.procesarPagoFinal(
        orden,
        metodoPagoActual,
        montoRecibido?.value
      );
    });
  }

  async procesarPagoFinal(orden, metodoPago, montoRecibido = null) {
    try {
      const btnConfirmar = document.querySelector("#btnConfirmarPago");
      btnConfirmar.innerHTML =
        '<i class="spinner-border spinner-border-sm me-2"></i>Procesando...';
      btnConfirmar.disabled = true;

      const dataPago = {
        action: "finalizarOrden",
        id: Number(orden.id),
        metodo_pago: metodoPago,
        nuevo_estado: "Listo para servir",
      };

      if (metodoPago === "Efectivo" && montoRecibido) {
        dataPago.monto_recibido = parseFloat(montoRecibido);
        dataPago.cambio = parseFloat(montoRecibido) - orden.total;
      }

      const response = await fetch("/api/ordenes.php", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify(dataPago),
      });

      const data = await response.json();

      if (data.success) {
        if (this.modalInstance) {
          this.modalInstance.hide();
        }

        let mensajeConfirmacion = `La orden #${orden.id} ha sido finalizada exitosamente.`;

        if (metodoPago === "Efectivo" && dataPago.cambio > 0) {
          mensajeConfirmacion += `\n\nCambio a entregar: ${this.formatPrecio(
            dataPago.cambio
          )}`;
        }

        await Swal.fire({
          title: "¡Pago Completado!",
          text: mensajeConfirmacion,
          icon: "success",
          confirmButtonText: "Aceptar",
        });

        const pilarSeleccionado =
          document.getElementById("select-pilar-filtrado")?.value || "all";
        await this.obtenerOrdenes(pilarSeleccionado);
      } else {
        throw new Error(data.message || "No se pudo finalizar la orden");
      }
    } catch (error) {
      console.error("Error al procesar el pago:", error);

      const btnConfirmar = document.querySelector("#btnConfirmarPago");
      if (btnConfirmar) {
        btnConfirmar.innerHTML =
          '<i class="bi bi-check-circle me-1"></i>Confirmar Pago';
        btnConfirmar.disabled = false;
      }

      Swal.fire(
        "Error",
        "Ocurrió un error al procesar el pago: " + error.message,
        "error"
      );
    }
  }

  async verProductosOrden(ordenId) {
    try {
      this.limpiarModalesVerProductos();

      const response = await fetch(
        `/api/ordenes.php?action=findById&id=${ordenId}`,
        {
          method: "GET",
        }
      );

      if (!response.ok) {
        throw new Error("Error al obtener los datos de la orden");
      }

      const data = await response.json();

      if (!data.success) {
        throw new Error(
          data.message || "Error al obtener los datos de la orden"
        );
      }

      const orden = data.data;
      this.mostrarModalVerProductos(orden);
    } catch (error) {
      console.error("Error al ver productos de la orden:", error);
      Swal.fire(
        "Error",
        "No se pudo cargar la información de los productos",
        "error"
      );
    }
  }

  mostrarModalVerProductos(orden) {
    this.limpiarModalesVerProductos();

    const modalHtml = `
    <div class="modal fade" id="modalVerProductos" tabindex="-1" aria-labelledby="modalVerProductosLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header bg-info text-white">
            <h5 class="modal-title" id="modalVerProductosLabel">
              <i class="bi bi-eye me-2"></i>Productos de la Orden #${orden.id}
            </h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <!-- Información de la orden -->
            <div class="row mb-4">
              <div class="col-md-6">
                <div class="card border-info">
                  <div class="card-body">
                    <h6 class="card-title text-info">
                      <i class="bi bi-info-circle me-2"></i>Información General
                    </h6>
                    <p class="mb-1"><strong>Mesa:</strong> ${
                      orden.mesa_numero
                    }</p>
                    <p class="mb-1"><strong>Mesero:</strong> ${
                      orden.mesero_nombre
                    }</p>
                    <p class="mb-1"><strong>Estado:</strong> 
                      <span class="badge ${
                        orden.estado === "Pendiente"
                          ? "bg-warning text-dark"
                          : orden.estado === "En Preparación"
                          ? "bg-danger"
                          : orden.estado === "Listo para servir"
                          ? "bg-info"
                          : orden.estado === "Entregado"
                          ? "bg-success"
                          : "bg-secondary"
                      } text-uppercase">${orden.estado}</span>
                    </p>
                  </div>
                </div>
              </div>
              <div class="col-md-6">
                <div class="card border-success">
                  <div class="card-body">
                    <h6 class="card-title text-success">
                      <i class="bi bi-calendar-check me-2"></i>Detalles de Tiempo
                    </h6>
                    <p class="mb-1"><strong>Fecha:</strong> ${new Date(
                      orden.created_at
                    ).toLocaleDateString("es-MX")}</p>
                    <p class="mb-1"><strong>Hora:</strong> ${new Date(
                      orden.created_at
                    ).toLocaleTimeString("es-MX")}</p>
                    <p class="mb-1"><strong>Notas:</strong> ${
                      orden.notas || "Sin notas especiales"
                    }</p>
                  </div>
                </div>
              </div>
            </div>

            <!-- Lista de productos -->
            <div class="card">
              <div class="card-header bg-light">
                <h6 class="mb-0">
                  <i class="bi bi-cart-check me-2"></i>Productos Ordenados
                  <span class="badge bg-primary ms-2">${
                    orden.productos ? orden.productos.length : 0
                  } producto(s)</span>
                </h6>
              </div>
              <div class="card-body p-0">
                ${
                  orden.productos && orden.productos.length > 0
                    ? `
                  <div class="table-responsive">
                    <table class="table table-hover mb-0">
                      <thead class="table-light">
                        <tr>
                          <th scope="col">Producto</th>
                          <th scope="col" class="text-center">Cantidad</th>
                          <th scope="col" class="text-end">Precio Unit.</th>
                          <th scope="col" class="text-end">Subtotal</th>
                        </tr>
                      </thead>
                      <tbody>
                        ${orden.productos
                          .map(
                            (producto, index) => `
                          <tr>
                            <td>
                              <div class="d-flex align-items-center">
                                <div class="badge bg-secondary me-2">${
                                  index + 1
                                }</div>
                                <div>
                                  <strong>${producto.nombre}</strong>
                                  ${
                                    producto.descripcion
                                      ? `<br><small class="text-muted">${producto.descripcion}</small>`
                                      : ""
                                  }
                                </div>
                              </div>
                            </td>
                            <td class="text-center">
                              <span class="badge bg-info rounded-pill">${
                                producto.cantidad
                              }</span>
                            </td>
                            <td class="text-end">
                              <strong>${this.formatPrecio(
                                producto.precio
                              )}</strong>
                            </td>
                            <td class="text-end">
                              <strong class="text-success">${this.formatPrecio(
                                producto.precio * producto.cantidad
                              )}</strong>
                            </td>
                          </tr>
                        `
                          )
                          .join("")}
                      </tbody>
                    </table>
                  </div>
                  
                  <!-- Resumen totales -->
                  <div class="bg-light p-3 mt-3">
                    <div class="row">
                      <div class="col-md-6">
                        <div class="row">
                          <div class="col-6">
                            <strong>Total de productos:</strong>
                          </div>
                          <div class="col-6 text-end">
                            <span class="badge bg-info">${orden.productos.reduce(
                              (acc, p) => acc + p.cantidad,
                              0
                            )}</span>
                          </div>
                        </div>
                      </div>
                      <div class="col-md-6">
                        <div class="row">
                          <div class="col-6">
                            <strong>Total a pagar:</strong>
                          </div>
                          <div class="col-6 text-end">
                            <h5 class="text-success mb-0">${this.formatPrecio(
                              orden.total ||
                                orden.productos.reduce(
                                  (acc, p) => acc + p.precio * p.cantidad,
                                  0
                                )
                            )}</h5>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                `
                    : `
                  <div class="text-center py-5">
                    <i class="bi bi-cart-x text-muted" style="font-size: 3rem;"></i>
                    <h5 class="text-muted mt-3">No hay productos en esta orden</h5>
                    <p class="text-muted">Esta orden no contiene productos registrados.</p>
                  </div>
                `
                }
              </div>
            </div>
          </div>
          <div class="modal-footer bg-light">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
              <i class="bi bi-x-circle me-1"></i>Cerrar
            </button>
            <button type="button" class="btn btn-primary" id="btnImprimirDesdeModal" data-orden-id="${
              orden.id
            }">
              <i class="bi bi-printer me-1"></i>Imprimir Ticket
            </button>
          </div>
        </div>
      </div>
    </div>
  `;

    document.body.insertAdjacentHTML("beforeend", modalHtml);

    this.configurarEventListenersModalVerProductos();

    try {
      const modalElement = document.getElementById("modalVerProductos");
      const modal = new bootstrap.Modal(modalElement, {
        backdrop: "static",
        keyboard: false,
      });

      this.modalVerProductosInstance = modal;
      modal.show();
    } catch (error) {
      console.error("Error al crear el modal:", error);
    }
  }

  configurarEventListenersModalVerProductos() {
    const modal = document.getElementById("modalVerProductos");

    if (modal) {
      modal.addEventListener("hidden.bs.modal", () => {
        this.limpiarModalesVerProductos();
      });

      const btnImprimir = modal.querySelector("#btnImprimirDesdeModal");
      if (btnImprimir) {
        btnImprimir.addEventListener("click", () => {
          const ordenId = btnImprimir.getAttribute("data-orden-id");
          this.imprimirTicketDesdeModal(ordenId);
        });
      }
    }
  }

  limpiarModalesVerProductos() {
    const modalExistente = document.getElementById("modalVerProductos");
    if (modalExistente) {
      const modalInstance = bootstrap.Modal.getInstance(modalExistente);
      if (modalInstance) {
        modalInstance.dispose();
      }
      modalExistente.remove();
    }

    const backdrops = document.querySelectorAll(".modal-backdrop");
    backdrops.forEach((backdrop) => backdrop.remove());

    document.body.classList.remove("modal-open");
    document.body.style.removeProperty("padding-right");
  }

  imprimirTicketDesdeModal(ordenId) {
    try {
      if (this.modalVerProductosInstance) {
        this.modalVerProductosInstance.hide();
      }

      if (window.ticketCustomizer) {
        setTimeout(() => {
          window.ticketCustomizer.openModal(ordenId);
        }, 300);
      } else {
        console.error("TicketCustomizer no está disponible");
        Swal.fire({
          title: "Error",
          text: "El sistema de impresión no está disponible en este momento",
          icon: "error",
        });
      }
    } catch (error) {
      console.error("Error al abrir el ticket customizer:", error);
      Swal.fire({
        title: "Error",
        text: "Ocurrió un error al abrir el sistema de impresión",
        icon: "error",
      });
    }
  }
}
