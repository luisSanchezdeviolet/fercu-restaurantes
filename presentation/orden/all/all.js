class OrdenesGetAll {
  constructor() {}

  async init() {
    try {
      this.setupEventListeners();
      this.getFiltersConfig();
      this.refreshPage();
    } catch (error) {
      console.error("Error al inicializar OrdenesGetAll:", error);
    }
  }

  setupEventListeners() {
    document.addEventListener("DOMContentLoaded", () => {
      this.obtenerOrdenes();
    });
  }

  async obtenerOrdenes(filter = "all") {
    const listaOrdenes = document.getElementById("ordenes-lista");
    try {
      if (filter === "all") {
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
          listaOrdenes.innerHTML = `<div class="alert alert-info" role="alert">No hay órdenes pendientes.</div>`;
          return;
        }
        if (!Array.isArray(data.data)) {
          listaOrdenes.innerHTML = `<div class="alert alert-danger" role="alert">Error al obtener las órdenes.</div>`;
          return;
        }
        listaOrdenes.innerHTML = data.data
          .map((orden) => this.templateOrden(orden))
          .join("");
      } else {
        const response = await fetch(
          `/api/ordenes.php?action=filterAllOrdersByPilar&pilar=${filter}&estado=Pendiente`,
          {
            method: "GET",
          }
        );
        if (response.success) {
          throw new Error("Error al obtener las órdenes" + response.message);
        }
        const data = await response.json();
        if (!data || !data.data || data.data.length === 0) {
          listaOrdenes.innerHTML = `<div class="alert alert-info" role="alert">No hay órdenes pendientes con esos productos seleccionados.</div>`;
          return;
        }
        listaOrdenes.innerHTML = data.data
          .map((orden) => this.templateOrden(orden))
          .join("");
      }
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
      <div class="card mb-3 shadow-sm col-12 col-md-6 col-lg-4 col-xl-3" data-id="${
        orden.id
      }">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center mb-2">
            <h5 class="card-title mb-0 text-primary">Orden #${orden.id}</h5>
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
          </div>
          <p class="mb-1 text-dark fw-medium"><strong class="text-muted">Mesero:</strong> ${
            orden.mesero_nombre
          }</p>
          <p class="mb-1 text-dark fw-medium"><strong class="text-muted">Mesa:</strong> ${
            orden.mesa_numero
          }</p>
          <p class="mb-1 text-dark fw-medium"><strong class="text-muted">Notas:</strong> ${
            orden.notas || "Sin notas"
          }</p>
          <p class="mb-1 text-dark fw-medium"><strong class="text-muted">Fecha y Hora:</strong> ${new Date(
            orden.fecha
          ).toLocaleString("es-MX", {
            year: "numeric",
            month: "long",
            day: "numeric",
            hour: "numeric",
            minute: "numeric",
            second: "numeric",
          })}
          </p>
          <h6 class="mt-3">Productos:</h6>
          <ul class="list-group list-group-flush mb-3">
            ${orden.productos
              .map(
                (producto) => `
              <li class="list-group-item px-0 py-1 d-flex justify-content-between align-items-center">
                <p class="mb-0 text-danger fw-bolder"><strong class="text-dark">${
                  producto.nombre
                }</strong> <br> x ${producto.cantidad}</p>
                <span class="text-dark fw-bolder">Total: ${this.formatPrecio(
                  producto.precio * producto.cantidad
                )}</span>
              </li>
            `
              )
              .join("")}
          </ul>
          <h5 class="text-success fw-bolder text-uppercase text-end">Total: ${this.formatPrecio(
            orden.total || 0
          )}</h5>
          <div class="d-flex gap-2 flex-column">
            <button class="btn btn-success btn-sm finalizar-orden" data-id="${
              orden.id
            }"><i class="bi bi-check-circle"></i> Finalizar</button>
            <button class="btn btn-primary btn-sm editar-orden" data-id="${
              orden.id
            }"><i class="bi bi-pencil"></i> Editar</button>
            <button class="btn btn-danger btn-sm eliminar-orden" data-id="${
              orden.id
            }"><i class="bi bi-trash"></i> Eliminar</button>
            <button class="btn btn-secondary btn-sm imprimir-ticket" data-id="${
              orden.id
            }"><i class="bi bi-printer"></i> Imprimir</button>
          </div>
        </div>
      </div>
    `;
  }

  async getFiltersConfig() {
    try {
      const selectPilares = document.getElementById("select-pilar-filtrado");
      if (!selectPilares) {
        throw new Error("Elemento select-pilar-filtrado no encontrado");
      }
      selectPilares.addEventListener("change", (event) => {
        console.log("Pilar seleccionado:", event.target.value);
        this.obtenerOrdenes(event.target.value);
      });

      const response = await fetch("/api/ordenes.php?action=getFilterConfig", {
        method: "GET",
      });
      if (response.success) {
        throw new Error("Error al obtener la configuración de filtros");
      }
      const data = await response.json();
      const pilares = data.data.pilares;
      pilares.forEach((pilar) => {
        const option = document.createElement("option");
        option.value = pilar;
        option.textContent = pilar.charAt(0).toUpperCase() + pilar.slice(1);
        selectPilares.appendChild(option);
      });
    } catch (error) {
      console.error("Error al obtener la configuración de filtros:", error);
    }
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
        const pilarSeleccionado = document.getElementById(
          "select-pilar-filtrado"
        ).value;
        await this.obtenerOrdenes(pilarSeleccionado);
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
}
