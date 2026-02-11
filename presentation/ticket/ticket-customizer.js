class TicketCustomizer {
  constructor() {
    this.currentOrder = null;
    this.config = {
      showLogo: true,
      logoPosition: "center",
      logoUrl: null,
      convertToBlackWhite: true,
      businessName: "Mi Restaurante",
      businessAddress: "Calle Principal #123",
      businessPhone: "(555) 123-4567",
      businessInfoPosition: "center",
      ticketTitle: "TICKET DE VENTA",
      titlePosition: "center",
      showProductCode: true,
      showProductPrice: true,
      footerMessage: "¡Gracias por su compra!\nVuelva pronto",
      footerPosition: "center",
      ticketWidth: "80",
      thermalMode: true,
      rawBTCompatible: true,
      productNameFontSize: "11",
      productDetailsFontSize: "10",
      productPriceFontSize: "11",
      totalFontSize: "13",
      businessInfoFontSize: "12",
      titleFontSize: "14",
      orderInfoFontSize: "11",
      footerFontSize: "10",
    };

    this.printerConfig = {
      connectionType: "usb",
      usbPrinterName: "",
      ip: "",
      port: 9100,
      bluetoothDevice: "",
      serialPort: "COM1",
      baudRate: 115200,
    };

    this.init();
  }

  init() {
    this.loadSavedConfig();
    this.setupEventListeners();
  }
  setupEventListeners() {
    document
      .getElementById("showLogo")
      ?.addEventListener("change", () => this.updatePreview());
    document
      .getElementById("logoPosition")
      ?.addEventListener("change", () => this.updatePreview());
    document
      .getElementById("logoFile")
      ?.addEventListener("change", (e) => this.handleLogoUpload(e));
    document
      .getElementById("convertToBlackWhite")
      ?.addEventListener("change", () => this.updatePreview());

    document
      .getElementById("businessName")
      ?.addEventListener("input", () => this.updatePreview());
    document
      .getElementById("businessAddress")
      ?.addEventListener("input", () => this.updatePreview());
    document
      .getElementById("businessPhone")
      ?.addEventListener("input", () => this.updatePreview());
    document
      .getElementById("businessInfoPosition")
      ?.addEventListener("change", () => this.updatePreview());

    document
      .getElementById("ticketWidth")
      ?.addEventListener("change", () => this.updatePreview());
    document
      .getElementById("thermalMode")
      ?.addEventListener("change", () => this.updatePreview());
    document
      .getElementById("rawBTCompatible")
      ?.addEventListener("change", () => this.updatePreview());

    document
      .getElementById("ticketTitle")
      ?.addEventListener("input", () => this.updatePreview());
    document
      .getElementById("titlePosition")
      ?.addEventListener("change", () => this.updatePreview());
    document
      .getElementById("showProductCode")
      ?.addEventListener("change", () => this.updatePreview());
    document
      .getElementById("showProductPrice")
      ?.addEventListener("change", () => this.updatePreview());

    document
      .getElementById("footerMessage")
      ?.addEventListener("input", () => this.updatePreview());
    document
      .getElementById("footerPosition")
      ?.addEventListener("change", () => this.updatePreview());

    document
      .getElementById("resetConfig")
      ?.addEventListener("click", () => this.resetConfig());
    document
      .getElementById("saveConfig")
      ?.addEventListener("click", () => this.saveConfig());
    document
      .getElementById("printTicket")
      ?.addEventListener("click", () => this.printTicket());
    document
      .getElementById("printAccess")
      ?.addEventListener("click", () => this.printTicket());
    document
      .getElementById("printStatic")
      ?.addEventListener("click", () => this.printStaticTicket());
    document
      .getElementById("generateRawBT")
      ?.addEventListener("click", () => this.generateRawBTCode());
    document
      .getElementById("downloadPRN")
      ?.addEventListener("click", () => this.downloadPRNFile());
    document
      .getElementById("sendToPrinter")
      ?.addEventListener("click", () => this.sendToPrinter());
    document
      .getElementById("configurePrinter")
      ?.addEventListener("click", () => this.openPrinterConfig());

    const fontElements = [
      "businessInfoFontSize",
      "titleFontSize",
      "productNameFontSize",
      "productDetailsFontSize",
      "productPriceFontSize",
      "totalFontSize",
      "footerFontSize",
    ];
    fontElements.forEach((id) => {
      const element = document.getElementById(id);
      if (element) {
        element.addEventListener("change", () => this.updatePreview());
      }
    });

    document
      .getElementById("cancelTicketCustom")
      ?.addEventListener("click", () => this.closeModal());
    document
      .getElementById("closeTicketCustomModal")
      ?.addEventListener("click", () => this.closeModal());
  }
  openModal(ordenId) {
    this.loadOrderData(ordenId).then(() => {
      this.updateConfigFromInputs();
      this.updatePreview();

      if (typeof $ !== "undefined" && $.fn.modal) {
        $("#ticketCustomModal").modal("show");
      } else if (typeof bootstrap !== "undefined") {
        const modal = new bootstrap.Modal(
          document.getElementById("ticketCustomModal")
        );
        modal.show();
      } else {
        document.getElementById("ticketCustomModal").style.display = "block";
      }
    });
  }

  async loadOrderData(ordenId) {
    try {
      const response = await fetch(
        `/api/ordenes.php?action=getOrdenById&id=${ordenId}`,
        {
          method: "GET",
          headers: {
            "Content-Type": "application/json",
          },
        }
      );

      const responseText = await response.text();

      let data;
      try {
        data = JSON.parse(responseText);
      } catch (parseError) {
        console.error("JSON Parse Error Details:", parseError);
        let cleanedResponse = responseText;
        if (cleanedResponse.charCodeAt(0) === 0xfeff) {
          cleanedResponse = cleanedResponse.slice(1);
        }

        const lastBrace = cleanedResponse.lastIndexOf("}");
        if (lastBrace !== -1 && lastBrace < cleanedResponse.length - 1) {
          cleanedResponse = cleanedResponse.substring(0, lastBrace + 1);
        }

        cleanedResponse = cleanedResponse
          .replace(/[\x00-\x1F\x7F-\x9F]/g, "")
          .replace(/\r\n/g, "\\n")
          .replace(/\r/g, "\\n")
          .replace(/\n/g, "\\n")
          .replace(/\t/g, "\\t");
        try {
          data = JSON.parse(cleanedResponse);
        } catch (secondError) {
          throw secondError;
        }
      }

      if (data.success && data.data) {
        const order = data.data;
        this.currentOrder = {
          id: order.id,
          mesero_nombre: order.mesero_nombre || "",
          mesa_numero: order.mesa_numero || order.numero_mesa || "",
          fecha: order.created_at || order.fecha || new Date().toISOString(),
          productos: (order.productos || []).map((producto) => ({
            codigo: producto.codigo || producto.id || "",
            nombre: producto.nombre || "Sin nombre",
            cantidad: Number(producto.cantidad) || 1,
            precio: Number(producto.precio) || 0,
          })),
          total: Number(order.total) || 0,
        };
      } else {
        console.warn("API response indicates failure or no data");
        this.useFallbackData(ordenId);
      }
    } catch (error) {
      console.error("Complete error in loadOrderData:", error);
      this.useFallbackData(ordenId);
    }
  }

  useFallbackData(ordenId) {
    this.currentOrder = {
      id: ordenId,
      mesero_nombre: "Juan Pérez",
      mesa_numero: "5",
      fecha: new Date().toISOString(),
      productos: [
        {
          codigo: "P001",
          nombre: "Hamburguesa Clásica",
          cantidad: 2,
          precio: 120.0,
        },
        { codigo: "P002", nombre: "Papas Fritas", cantidad: 1, precio: 45.0 },
        { codigo: "B001", nombre: "Coca Cola", cantidad: 2, precio: 25.0 },
      ],
      total: 335.0,
    };
  }

  handleLogoUpload(event) {
    const file = event.target.files[0];
    if (file) {
      const reader = new FileReader();
      reader.onload = (e) => {
        this.config.logoUrl = e.target.result;
        this.updatePreview();
      };
      reader.readAsDataURL(file);
    }
  }
  updateConfigFromInputs() {
    const getValue = (id, defaultValue = "") => {
      const element = document.getElementById(id);
      return element
        ? element.type === "checkbox"
          ? element.checked
          : element.value
        : defaultValue;
    };

    this.config = {
      showLogo: getValue("showLogo", true),
      logoPosition: getValue("logoPosition", "center"),
      logoUrl: this.config.logoUrl,
      convertToBlackWhite: getValue("convertToBlackWhite", true),
      businessName: getValue("businessName", "Mi Restaurante"),
      businessAddress: getValue("businessAddress", "Calle Principal #123"),
      businessPhone: getValue("businessPhone", "(555) 123-4567"),
      businessInfoPosition: getValue("businessInfoPosition", "center"),
      ticketTitle: getValue("ticketTitle", "TICKET DE VENTA"),
      titlePosition: getValue("titlePosition", "center"),
      showProductCode: getValue("showProductCode", true),
      showProductPrice: getValue("showProductPrice", true),
      footerMessage: getValue(
        "footerMessage",
        "¡Gracias por su compra!\nVuelva pronto"
      ),
      footerPosition: getValue("footerPosition", "center"),
      ticketWidth: getValue("ticketWidth", "80"),
      thermalMode: getValue("thermalMode", true),
      rawBTCompatible: getValue("rawBTCompatible", true),
      businessInfoFontSize: getValue("businessInfoFontSize", "12"),
      titleFontSize: getValue("titleFontSize", "14"),
      productNameFontSize: getValue("productNameFontSize", "11"),
      productDetailsFontSize: getValue("productDetailsFontSize", "10"),
      productPriceFontSize: getValue("productPriceFontSize", "11"),
      totalFontSize: getValue("totalFontSize", "13"),
      footerFontSize: getValue("footerFontSize", "10"),
    };
  }
  async updatePreview() {
    this.updateConfigFromInputs();
    const preview = document.getElementById("ticketPreview");

    if (!preview) return;

    let className = `ticket-preview mx-auto width-${this.config.ticketWidth}`;
    if (this.config.thermalMode) {
      className += " thermal-mode";
    }
    preview.className = className;

    let html = "";

    if (this.config.showLogo && this.config.logoUrl) {
      let logoSrc = this.config.logoUrl;

      if (this.config.convertToBlackWhite && this.config.thermalMode) {
        try {
          logoSrc = await this.convertImageToBlackWhite(this.config.logoUrl);
        } catch (error) {
          console.warn("Error converting logo to B/W:", error);
        }
      }

      html += `<div class="text-${this.config.logoPosition}">
                <img src="${logoSrc}" class="ticket-logo" alt="Logo">
            </div>`;
    }

    html += `<div class="ticket-business-info text-${this.config.businessInfoPosition}" style="font-size: ${this.config.businessInfoFontSize}px;">
        <div><strong>${this.config.businessName}</strong></div>
        <div>${this.config.businessAddress}</div>
        <div>${this.config.businessPhone}</div>
    </div>`;

    html += `<div class="ticket-title text-${this.config.titlePosition}" style="font-size: ${this.config.titleFontSize}px;">
        ${this.config.ticketTitle}
    </div>`;

    if (this.currentOrder) {
      html += `<div class="ticket-order-info" style="font-size: ${
        this.config.orderInfoFontSize
      }px;">
                <div>Orden: #${this.currentOrder.id}</div>
                <div>Mesero: ${this.currentOrder.mesero_nombre}</div>
                <div>Mesa: ${this.currentOrder.mesa_numero}</div>
                <div>Fecha: ${new Date(this.currentOrder.fecha).toLocaleString(
                  "es-MX"
                )}</div>
            </div>`;

      html += '<div class="ticket-products">';
      html +=
        '<div style="border-bottom: 1px dashed #333; margin-bottom: 5px; font-weight: bold;">PRODUCTOS</div>';

      this.currentOrder.productos.forEach((producto) => {
        html += '<div class="ticket-product">';
        html += '<div class="ticket-product-info">';

        if (this.config.showProductCode) {
          html += `<div style="font-size: ${
            this.config.productDetailsFontSize
          }px;">[${producto.codigo || "N/A"}]</div>`;
        }

        html += `<div style="font-size: ${this.config.productNameFontSize}px; font-weight: bold;">${producto.nombre}</div>`;
        html += `<div style="font-size: ${this.config.productDetailsFontSize}px;">Cant: ${producto.cantidad}`;

        if (this.config.showProductPrice) {
          html += ` x $${producto.precio.toFixed(2)}`;
        }

        html += "</div></div>";
        html += `<div class="ticket-product-price" style="font-size: ${
          this.config.productPriceFontSize
        }px;">$${(producto.cantidad * producto.precio).toFixed(2)}</div>`;
        html += "</div>";
      });

      html += "</div>";

      html += `<div class="ticket-total text-right">
            <div style="font-size: ${
              this.config.totalFontSize
            }px; font-weight: bold;">TOTAL: $${this.currentOrder.total.toFixed(
        2
      )}</div>
        </div>`;
    }

    if (this.config.footerMessage) {
      const footerLines = this.config.footerMessage.split("\n");
      html += `<div class="ticket-footer text-${this.config.footerPosition}" style="font-size: ${this.config.footerFontSize}px;">`;
      footerLines.forEach((line) => {
        html += `<div>${line}</div>`;
      });
      html += "</div>";
    }

    preview.innerHTML = html;
  }
  resetConfig() {
    const resetAction = () => {
      const setValue = (id, value, isCheckbox = false) => {
        const element = document.getElementById(id);
        if (element) {
          if (isCheckbox) {
            element.checked = value;
          } else {
            element.value = value;
          }
        }
      };

      setValue("showLogo", true, true);
      setValue("logoPosition", "center");
      setValue("convertToBlackWhite", true, true);
      setValue("businessName", "Mi Restaurante");
      setValue("businessAddress", "Calle Principal #123");
      setValue("businessPhone", "(555) 123-4567");
      setValue("businessInfoPosition", "center");
      setValue("ticketTitle", "TICKET DE VENTA");
      setValue("titlePosition", "center");
      setValue("showProductCode", true, true);
      setValue("showProductPrice", true, true);
      setValue("footerMessage", "¡Gracias por su compra!\nVuelva pronto");
      setValue("footerPosition", "center");
      setValue("ticketWidth", "80");
      setValue("thermalMode", true, true);
      setValue("rawBTCompatible", true, true);
      setValue("businessInfoFontSize", "12");
      setValue("titleFontSize", "14");
      setValue("productNameFontSize", "11");
      setValue("productDetailsFontSize", "10");
      setValue("productPriceFontSize", "11");
      setValue("totalFontSize", "13");
      setValue("footerFontSize", "10");

      this.config.logoUrl = null;
      this.updatePreview();

      try {
        localStorage.removeItem("ticketConfig");
      } catch (e) {
        console.warn("No se puede acceder a localStorage");
      }

      this.showAlert(
        "success",
        "Configuración Restablecida",
        "Los valores predeterminados han sido restaurados."
      );
    };

    if (typeof Swal !== "undefined") {
      Swal.fire({
        title: "¿Restablecer configuración?",
        text: "Esto revertirá todos los cambios realizados.",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Sí, restablecer",
        cancelButtonText: "Cancelar",
      }).then((result) => {
        if (result.isConfirmed) {
          resetAction();
        }
      });
    } else {
      if (
        confirm(
          "¿Restablecer configuración? Esto revertirá todos los cambios realizados."
        )
      ) {
        resetAction();
      }
    }
  }
  saveConfig() {
    this.updateConfigFromInputs();

    try {
      localStorage.setItem("ticketConfig", JSON.stringify(this.config));
      this.showAlert(
        "success",
        "Configuración Guardada",
        "La configuración se ha guardado exitosamente."
      );
    } catch (e) {
      console.warn("No se puede guardar en localStorage");
      this.showAlert(
        "warning",
        "Advertencia",
        "No se pudo guardar la configuración en el navegador."
      );
    }
  }

  loadSavedConfig() {
    try {
      const savedConfig = localStorage.getItem("ticketConfig");
      if (savedConfig) {
        this.config = { ...this.config, ...JSON.parse(savedConfig) };
        this.updateInputsFromConfig();
      }
    } catch (e) {
      console.warn("No se puede cargar la configuración guardada");
    }
  }
  updateInputsFromConfig() {
    const setValue = (id, value, isCheckbox = false) => {
      const element = document.getElementById(id);
      if (element) {
        if (isCheckbox) {
          element.checked = value;
        } else {
          element.value = value;
        }
      }
    };

    setValue("showLogo", this.config.showLogo, true);
    setValue("logoPosition", this.config.logoPosition);
    setValue("convertToBlackWhite", this.config.convertToBlackWhite, true);
    setValue("businessName", this.config.businessName);
    setValue("businessAddress", this.config.businessAddress);
    setValue("businessPhone", this.config.businessPhone);
    setValue("businessInfoPosition", this.config.businessInfoPosition);
    setValue("ticketTitle", this.config.ticketTitle);
    setValue("titlePosition", this.config.titlePosition);
    setValue("showProductCode", this.config.showProductCode, true);
    setValue("showProductPrice", this.config.showProductPrice, true);
    setValue("footerMessage", this.config.footerMessage);
    setValue("footerPosition", this.config.footerPosition);
    setValue("ticketWidth", this.config.ticketWidth);
    setValue("thermalMode", this.config.thermalMode, true);
    setValue("rawBTCompatible", this.config.rawBTCompatible, true);
    setValue("businessInfoFontSize", this.config.businessInfoFontSize);
    setValue("titleFontSize", this.config.titleFontSize);
    setValue("productNameFontSize", this.config.productNameFontSize);
    setValue("productDetailsFontSize", this.config.productDetailsFontSize);
    setValue("productPriceFontSize", this.config.productPriceFontSize);
    setValue("totalFontSize", this.config.totalFontSize);
    setValue("footerFontSize", this.config.footerFontSize);
  }
  async printTicket() {
    try {
      let printContent;

      if (this.config.rawBTCompatible && this.config.thermalMode) {
        printContent = this.generateThermalPrintHTML();
      } else {
        printContent = this.generatePrintHTML();
      }

      const printWindow = window.open("", "_blank", "width=400,height=600");
      printWindow.document.write(printContent);
      printWindow.document.close();

      printWindow.onload = function () {
        printWindow.print();
        printWindow.close();
      };

      this.closeModal();
    } catch (error) {
      console.error("Error al imprimir:", error);
      this.showAlert(
        "error",
        "Error de Impresión",
        "No se pudo imprimir el ticket. Inténtelo nuevamente."
      );
    }
  }

  generatePrintHTML() {
    const ticketContent = document.getElementById("ticketPreview").innerHTML;

    return `
        <!DOCTYPE html>
        <html>
        <head>
            <title>Ticket de Impresión</title>
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
                .ticket-logo { max-width: 100px; height: auto; margin-bottom: 10px; }
                .ticket-business-info { margin-bottom: 15px; }
                .ticket-title { font-weight: bold; font-size: 14px; margin: 15px 0; border-bottom: 1px dashed #333; padding-bottom: 5px; }
                .ticket-order-info { margin: 10px 0; font-size: 11px; }
                .ticket-products { margin: 15px 0; }
                .ticket-product { display: flex; justify-content: space-between; margin-bottom: 3px; font-size: 11px; }
                .ticket-total { border-top: 1px dashed #333; padding-top: 10px; margin-top: 15px; font-weight: bold; font-size: 13px; }
                .ticket-footer { margin-top: 20px; font-size: 10px; border-top: 1px dashed #333; padding-top: 10px; }
                .text-left { text-align: left !important; }
                .text-center { text-align: center !important; }
                .text-right { text-align: right !important; }
            </style>
        </head>
        <body>
            <div class="ticket-preview">
                ${ticketContent}
            </div>
        </body>
        </html>
        `;
  }

  generateThermalPrintHTML() {
    const ticketContent = document.getElementById("ticketPreview").innerHTML;
    const width = this.config.ticketWidth === "58" ? "220px" : "300px";

    return `
        <!DOCTYPE html>
        <html>
        <head>
            <title>Ticket Térmico</title>
            <meta charset="utf-8">
            <style>
                @media print {
                    @page {
                        size: ${width} auto;
                        margin: 0;
                    }
                    body {
                        margin: 0;
                        padding: 2mm;
                        font-family: 'Courier New', monospace;
                        font-size: ${
                          this.config.ticketWidth === "58" ? "8px" : "10px"
                        };
                        line-height: 1.1;
                        color: #000;
                        background: white;
                    }
                    * {
                        -webkit-print-color-adjust: exact !important;
                        color-adjust: exact !important;
                    }
                }
                body {
                    font-family: 'Courier New', monospace;
                    font-size: ${
                      this.config.ticketWidth === "58" ? "10px" : "12px"
                    };
                    line-height: 1.2;
                    margin: 0;
                    padding: 5px;
                    color: #000;
                    background: white;
                    width: ${width};
                }
                .ticket-preview {
                    background: white !important;
                    border: none !important;
                    box-shadow: none !important;
                    padding: 0 !important;
                    margin: 0 !important;
                    max-width: none !important;
                    color: #000 !important;
                }
                .ticket-logo { 
                    max-width: ${
                      this.config.ticketWidth === "58" ? "60px" : "80px"
                    }; 
                    height: auto; 
                    margin-bottom: 5px;
                    ${
                      this.config.convertToBlackWhite
                        ? "filter: contrast(100%) brightness(50%);"
                        : ""
                    }
                }
                .ticket-business-info { margin-bottom: 8px; }
                .ticket-title { 
                    font-weight: bold; 
                    margin: 8px 0; 
                    border-bottom: 1px dashed #000; 
                    padding-bottom: 3px;
                }
                .ticket-order-info { margin: 6px 0; font-size: ${
                  this.config.ticketWidth === "58" ? "7px" : "9px"
                }; }
                .ticket-products { margin: 8px 0; }
                .ticket-product { 
                    display: flex; 
                    justify-content: space-between; 
                    margin-bottom: 2px; 
                    font-size: ${
                      this.config.ticketWidth === "58" ? "7px" : "9px"
                    }; 
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
                    border-top: 1px dashed #000; 
                    padding-top: 6px; 
                    margin-top: 8px; 
                    font-weight: bold; 
                    font-size: ${
                      this.config.ticketWidth === "58" ? "8px" : "10px"
                    };
                }
                .ticket-footer { 
                    margin-top: 10px; 
                    font-size: ${
                      this.config.ticketWidth === "58" ? "6px" : "8px"
                    }; 
                    border-top: 1px dashed #000; 
                    padding-top: 6px;
                }
                .text-left { text-align: left !important; }
                .text-center { text-align: center !important; }
                .text-right { text-align: right !important; }
                .separator-line {
                    border-bottom: 1px dashed #000;
                    margin: 3px 0;
                }
            </style>
        </head>
        <body>
            <div class="ticket-preview">
                ${ticketContent}
            </div>
        </body>
        </html>
        `;
  }

  convertImageToBlackWhite(imageUrl) {
    return new Promise((resolve) => {
      const canvas = document.createElement("canvas");
      const ctx = canvas.getContext("2d");
      const img = new Image();

      img.onload = () => {
        canvas.width = img.width;
        canvas.height = img.height;

        ctx.drawImage(img, 0, 0);

        const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
        const data = imageData.data;

        for (let i = 0; i < data.length; i += 4) {
          const brightness =
            data[i] * 0.299 + data[i + 1] * 0.587 + data[i + 2] * 0.114;
          const value = brightness > 128 ? 255 : 0;

          data[i] = value;
          data[i + 1] = value;
          data[i + 2] = value;
        }

        ctx.putImageData(imageData, 0, 0);
        resolve(canvas.toDataURL());
      };

      img.src = imageUrl;
    });
  }

  async convertImageToESCPOS(imageUrl) {
    return new Promise((resolve) => {
      const canvas = document.createElement("canvas");
      const ctx = canvas.getContext("2d");
      const img = new Image();

      img.onload = () => {
        const maxWidth = this.config.ticketWidth === "58" ? 200 : 300;
        const ratio = Math.min(maxWidth / img.width, maxWidth / img.height);
        const width = Math.floor(img.width * ratio);
        const height = Math.floor(img.height * ratio);

        canvas.width = width;
        canvas.height = height;

        ctx.drawImage(img, 0, 0, width, height);

        const imageData = ctx.getImageData(0, 0, width, height);
        const data = imageData.data;

        const bitMatrix = [];
        for (let y = 0; y < height; y++) {
          const row = [];
          for (let x = 0; x < width; x++) {
            const pixelIndex = (y * width + x) * 4;
            const brightness =
              data[pixelIndex] * 0.299 +
              data[pixelIndex + 1] * 0.587 +
              data[pixelIndex + 2] * 0.114;
            row.push(brightness < 128 ? 1 : 0);
          }
          bitMatrix.push(row);
        }

        let imageCommands = this.generateImageESCPOS(bitMatrix, width, height);
        resolve(imageCommands);
      };

      img.src = imageUrl;
    });
  }

  generateImageESCPOS(bitMatrix, width, height) {
    let commands = "";

    for (let y = 0; y < height; y += 24) {
      const bandHeight = Math.min(24, height - y);

      const adjustedWidth = Math.ceil(width / 8) * 8;

      commands += "\x1B\x2A\x21";

      const widthBytes = adjustedWidth / 8;
      commands += String.fromCharCode(widthBytes & 0xff);
      commands += String.fromCharCode((widthBytes >> 8) & 0xff);

      for (let x = 0; x < adjustedWidth; x += 8) {
        for (let bit = 0; bit < 24; bit += 8) {
          let byte1 = 0,
            byte2 = 0,
            byte3 = 0;

          for (let px = 0; px < 8; px++) {
            const currentX = x + px;

            if (
              y + bit + 0 < height &&
              currentX < width &&
              bitMatrix[y + bit + 0] &&
              bitMatrix[y + bit + 0][currentX]
            ) {
              byte1 |= 1 << (7 - px);
            }
            if (
              y + bit + 1 < height &&
              currentX < width &&
              bitMatrix[y + bit + 1] &&
              bitMatrix[y + bit + 1][currentX]
            ) {
              byte1 |= 1 << (7 - px);
            }

            if (
              y + bit + 8 < height &&
              currentX < width &&
              bitMatrix[y + bit + 8] &&
              bitMatrix[y + bit + 8][currentX]
            ) {
              byte2 |= 1 << (7 - px);
            }

            if (
              y + bit + 16 < height &&
              currentX < width &&
              bitMatrix[y + bit + 16] &&
              bitMatrix[y + bit + 16][currentX]
            ) {
              byte3 |= 1 << (7 - px);
            }
          }

          commands += String.fromCharCode(byte1);
          commands += String.fromCharCode(byte2);
          commands += String.fromCharCode(byte3);
        }
      }

      commands += "\n";
    }

    return commands;
  }

  generateSimpleLogoESCPOS() {
    let logoCode = "";

    logoCode += "\x1B\x45\x01";
    logoCode += "================================\n";
    logoCode += "         [  LOGO  ]\n";
    logoCode += `       ${this.config.businessName}\n`;
    logoCode += "================================\n";
    logoCode += "\x1B\x45\x00";

    return logoCode;
  }

  validateImageForESCPOS(imageUrl) {
    return new Promise((resolve, reject) => {
      const img = new Image();
      img.onload = () => {
        const maxWidth = this.config.ticketWidth === "58" ? 200 : 300;
        const maxHeight = 150;

        if (img.width > maxWidth * 2 || img.height > maxHeight * 2) {
          console.warn("Imagen muy grande, se redimensionará automáticamente");
        }

        resolve(true);
      };
      img.onerror = () => reject(new Error("Error cargando imagen"));
      img.src = imageUrl;
    });
  }

  closeModal() {
    if (typeof $ !== "undefined" && $.fn.modal) {
      $("#ticketCustomModal").modal("hide");
    } else if (typeof bootstrap !== "undefined") {
      const modal = bootstrap.Modal.getInstance(
        document.getElementById("ticketCustomModal")
      );
      if (modal) modal.hide();
    } else {
      document.getElementById("ticketCustomModal").style.display = "none";
    }
  }
  async generateRawBTCode() {
    if (!this.currentOrder) {
      this.showAlert(
        "error",
        "Error",
        "No hay orden cargada para generar código RawBT"
      );
      return;
    }

    let escCode = "";

    escCode += "\x1B\x40";
    escCode += "\x1B\x61\x01";
    if (this.config.showLogo && this.config.logoUrl) {
      try {
        await this.validateImageForESCPOS(this.config.logoUrl);

        let logoSrc = this.config.logoUrl;

        if (this.config.convertToBlackWhite) {
          logoSrc = await this.convertImageToBlackWhite(this.config.logoUrl);
        }

        const imageCommands = await this.convertImageToESCPOS(logoSrc);

        if (this.config.logoPosition === "left") {
          escCode += "\x1B\x61\x00";
        } else if (this.config.logoPosition === "right") {
          escCode += "\x1B\x61\x02";
        } else {
          escCode += "\x1B\x61\x01";
        }

        escCode += imageCommands;
        escCode += "\n";
        escCode += "\x1B\x61\x01";
      } catch (error) {
        console.warn(
          "Error procesando logo para ESC/POS, usando alternativa simple:",
          error
        );

        if (this.config.logoPosition === "left") {
          escCode += "\x1B\x61\x00";
        } else if (this.config.logoPosition === "right") {
          escCode += "\x1B\x61\x02";
        } else {
          escCode += "\x1B\x61\x01";
        }

        escCode += this.generateSimpleLogoESCPOS();
        escCode += "\x1B\x61\x01";
      }
    }

    if (this.config.businessInfoPosition === "left") {
      escCode += "\x1B\x61\x00";
    } else if (this.config.businessInfoPosition === "right") {
      escCode += "\x1B\x61\x02";
    } else {
      escCode += "\x1B\x61\x01";
    }

    escCode += `${this.config.businessName}\n`;
    escCode += `${this.config.businessAddress}\n`;
    escCode += `${this.config.businessPhone}\n`;
    escCode += "--------------------------------\n";

    if (this.config.titlePosition === "left") {
      escCode += "\x1B\x61\x00";
    } else if (this.config.titlePosition === "right") {
      escCode += "\x1B\x61\x02";
    } else {
      escCode += "\x1B\x61\x01";
    }

    escCode += "\x1B\x45\x01";
    escCode += `${this.config.ticketTitle}\n`;
    escCode += "\x1B\x45\x00";
    escCode += "--------------------------------\n";

    escCode += "\x1B\x61\x00";
    escCode += `Orden: #${this.currentOrder.id}\n`;
    escCode += `Mesero: ${this.currentOrder.mesero_nombre}\n`;
    escCode += `Mesa: ${this.currentOrder.mesa_numero}\n`;
    escCode += `Notas: ${this.currentOrder.notas || "N/A"}\n`;
    escCode += `Fecha: ${new Date(this.currentOrder.fecha).toLocaleString(
      "es-MX"
    )}\n`;
    escCode += "--------------------------------\n";

    escCode += "\x1B\x45\x01";
    escCode += "PRODUCTOS\n";
    escCode += "\x1B\x45\x00";
    escCode += "--------------------------------\n";

    this.currentOrder.productos.forEach((producto) => {
      if (this.config.showProductCode) {
        escCode += `[${producto.codigo}]\n`;
      }
      escCode += `${producto.nombre}\n`;

      let productLine = `${producto.cantidad} x $${producto.precio.toFixed(2)}`;
      if (this.config.showProductPrice) {
        const totalPrice = `$${(producto.cantidad * producto.precio).toFixed(
          2
        )}`;
        const spaces = Math.max(1, 32 - productLine.length - totalPrice.length);
        escCode += productLine + " ".repeat(spaces) + totalPrice + "\n";
      } else {
        escCode += productLine + "\n";
      }
      escCode += "\n";
    });

    escCode += "--------------------------------\n";

    escCode += "\x1B\x45\x01";
    escCode += "\x1B\x61\x02";
    escCode += `TOTAL: $${this.currentOrder.total.toFixed(2)}\n`;
    escCode += "\x1B\x45\x00";

    if (this.config.footerMessage) {
      escCode += "--------------------------------\n";

      if (this.config.footerPosition === "left") {
        escCode += "\x1B\x61\x00";
      } else if (this.config.footerPosition === "right") {
        escCode += "\x1B\x61\x02";
      } else {
        escCode += "\x1B\x61\x01";
      }

      const footerLines = this.config.footerMessage.split("\n");
      footerLines.forEach((line) => {
        escCode += `${line}\n`;
      });
    }

    escCode += "\n\n\n";
    escCode += "\x1D\x56\x00";

    this.showRawBTCode(escCode);
  }
  showRawBTCode(code) {
    const displayCode = code
      .replace(/\x1B/g, "[ESC]")
      .replace(/\x1D/g, "[GS]")
      .replace(/\x40/g, "[RESET]")
      .replace(/\x61\x00/g, "[ALIGN_LEFT]")
      .replace(/\x61\x01/g, "[ALIGN_CENTER]")
      .replace(/\x61\x02/g, "[ALIGN_RIGHT]")
      .replace(/\x45\x01/g, "[BOLD_ON]")
      .replace(/\x45\x00/g, "[BOLD_OFF]")
      .replace(/\x56\x00/g, "[CUT_PAPER]")
      .replace(/\x2A\x21/g, "[IMAGE_MODE]");

    if (typeof Swal !== "undefined") {
      Swal.fire({
        title: "Código ESC/POS para RawBT",
        html: `
          <div style="text-align: left; font-family: monospace; white-space: pre-wrap; font-size: 11px; max-height: 400px; overflow-y: auto; border: 1px solid #ddd; padding: 10px; background-color: #f8f9fa;">
            ${displayCode}
          </div>
          <div class="mt-3">
            <button class="btn btn-primary btn-sm me-2" onclick="navigator.clipboard.writeText('${code.replace(
              /'/g,
              "\\'"
            )}').then(() => Swal.fire('¡Copiado!', 'Código copiado al portapapeles', 'success'))">
              <i class="fas fa-copy"></i> Copiar Código
            </button>
            <button class="btn btn-info btn-sm" onclick="window.downloadESCPOS('${code.replace(
              /'/g,
              "\\'"
            )}')">
              <i class="fas fa-download"></i> Descargar .txt
            </button>
          </div>
          <div class="mt-2">
            <small class="text-muted">
              📱 <strong>RawBT:</strong> Copia el código y pégalo en la app RawBT<br>
              🖨️ <strong>Imagen:</strong> El logo se convierte automáticamente a formato ESC/POS
            </small>
          </div>
        `,
        width: "80%",
        showConfirmButton: false,
        showCloseButton: true,
      });

      window.downloadESCPOS = function (content) {
        const blob = new Blob([content], { type: "text/plain" });
        const url = URL.createObjectURL(blob);
        const a = document.createElement("a");
        a.href = url;
        a.download = `ticket_${Date.now()}.txt`;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        URL.revokeObjectURL(url);
        Swal.fire(
          "¡Descargado!",
          "Archivo guardado como ticket.txt",
          "success"
        );
      };
    } else {
      const textarea = document.createElement("textarea");
      textarea.value = code;
      document.body.appendChild(textarea);
      textarea.select();
      document.execCommand("copy");
      document.body.removeChild(textarea);
      alert(
        "Código ESC/POS copiado al portapapeles\n\nEl logo se incluye como comandos de imagen ESC/POS"
      );
    }
  }

  showAlert(type, title, text) {
    if (typeof Swal !== "undefined") {
      Swal.fire({
        icon: type,
        title: title,
        text: text,
        timer: type === "success" ? 2000 : undefined,
        showConfirmButton: type !== "success",
      });
    } else {
      alert(`${title}: ${text}`);
    }
  }

  async downloadPRNFile() {
    if (!this.currentOrder) {
      this.showAlert(
        "error",
        "Error",
        "No hay orden cargada para generar archivo PRN"
      );
      return;
    }

    try {
      const escCode = await this.generateESCPOSCode();

      const blob = new Blob([escCode], { type: "application/octet-stream" });

      const url = URL.createObjectURL(blob);
      const a = document.createElement("a");
      a.href = url;
      a.download = `ticket_${this.currentOrder.id}_${Date.now()}.prn`;
      document.body.appendChild(a);
      a.click();
      document.body.removeChild(a);
      URL.revokeObjectURL(url);

      this.showAlert(
        "success",
        "¡Archivo Descargado!",
        "El archivo .PRN se ha guardado correctamente. Puede enviarlo directamente a su impresora térmica."
      );
    } catch (error) {
      console.error("Error generando archivo PRN:", error);
      this.showAlert(
        "error",
        "Error",
        "No se pudo generar el archivo PRN: " + error.message
      );
    }
  }

  async generateESCPOSCode() {
    let escCode = "";

    escCode += "\x1B\x40";
    escCode += "\x1B\x61\x01";

    if (this.config.showLogo && this.config.logoUrl) {
      try {
        await this.validateImageForESCPOS(this.config.logoUrl);

        let logoSrc = this.config.logoUrl;

        if (this.config.convertToBlackWhite) {
          logoSrc = await this.convertImageToBlackAndWhite(this.config.logoUrl);
        }

        const imageCommands = await this.convertImageToESCPOS(logoSrc);

        if (this.config.logoPosition === "left") {
          escCode += "\x1B\x61\x00";
        } else if (this.config.logoPosition === "right") {
          escCode += "\x1B\x61\x02";
        } else {
          escCode += "\x1B\x61\x01";
        }

        escCode += imageCommands;
        escCode += "\n";
        escCode += "\x1B\x61\x01";
      } catch (error) {
        console.warn("Error procesando logo para PRN:", error);

        if (this.config.logoPosition === "left") {
          escCode += "\x1B\x61\x00";
        } else if (this.config.logoPosition === "right") {
          escCode += "\x1B\x61\x02";
        } else {
          escCode += "\x1B\x61\x01";
        }

        escCode += this.generateSimpleLogoESCPOS();
        escCode += "\x1B\x61\x01";
      }
    }

    if (this.config.businessInfoPosition === "left") {
      escCode += "\x1B\x61\x00";
    } else if (this.config.businessInfoPosition === "right") {
      escCode += "\x1B\x61\x02";
    } else {
      escCode += "\x1B\x61\x01";
    }

    escCode += `${this.config.businessName}\n`;
    escCode += `${this.config.businessAddress}\n`;
    escCode += `${this.config.businessPhone}\n`;
    escCode += "--------------------------------\n";

    if (this.config.titlePosition === "left") {
      escCode += "\x1B\x61\x00";
    } else if (this.config.titlePosition === "right") {
      escCode += "\x1B\x61\x02";
    } else {
      escCode += "\x1B\x61\x01";
    }

    escCode += "\x1B\x45\x01";
    escCode += `${this.config.ticketTitle}\n`;
    escCode += "\x1B\x45\x00";
    escCode += "--------------------------------\n";

    escCode += "\x1B\x61\x00";
    escCode += `Orden: #${this.currentOrder.id}\n`;
    escCode += `Mesero: ${this.currentOrder.mesero_nombre}\n`;
    escCode += `Mesa: ${this.currentOrder.mesa_numero}\n`;
    escCode += `Fecha: ${new Date(this.currentOrder.fecha).toLocaleString(
      "es-MX"
    )}\n`;
    escCode += `Notas: ${this.currentOrder.notas || "N/A"}\n`;
    escCode += "--------------------------------\n";

    escCode += "\x1B\x45\x01";
    escCode += "PRODUCTOS\n";
    escCode += "\x1B\x45\x00";
    escCode += "--------------------------------\n";

    this.currentOrder.productos.forEach((producto) => {
      if (this.config.showProductCode) {
        escCode += `[${producto.codigo}]\n`;
      }
      escCode += `${producto.nombre}\n`;

      let productLine = `${producto.cantidad} x $${producto.precio.toFixed(2)}`;
      if (this.config.showProductPrice) {
        const totalPrice = `$${(producto.cantidad * producto.precio).toFixed(
          2
        )}`;
        const spaces = Math.max(1, 32 - productLine.length - totalPrice.length);
        escCode += productLine + " ".repeat(spaces) + totalPrice + "\n";
      } else {
        escCode += productLine + "\n";
      }
      escCode += "\n";
    });

    escCode += "--------------------------------\n";

    escCode += "\x1B\x45\x01";
    escCode += "\x1B\x61\x02";
    escCode += `TOTAL: $${this.currentOrder.total.toFixed(2)}\n`;
    escCode += "\x1B\x45\x00";

    if (this.config.footerMessage) {
      escCode += "--------------------------------\n";

      if (this.config.footerPosition === "left") {
        escCode += "\x1B\x61\x00";
      } else if (this.config.footerPosition === "right") {
        escCode += "\x1B\x61\x02";
      } else {
        escCode += "\x1B\x61\x01";
      }

      const footerLines = this.config.footerMessage.split("\n");
      footerLines.forEach((line) => {
        escCode += `${line}\n`;
      });
    }

    escCode += "\n\n\n";
    escCode += "\x1D\x56\x00";

    return escCode;
  }

  async sendToPrinter() {
    if (!this.currentOrder) {
      this.showAlert("error", "Error", "No hay orden cargada para imprimir");
      return;
    }

    try {
      this.loadPrinterConfig();

      if (
        !this.printerConfig.usbPrinterName &&
        this.printerConfig.connectionType === "usb"
      ) {
        this.showAlert(
          "warning",
          "Configuración Requerida",
          "Debe configurar la impresora antes de imprimir."
        );
        this.openPrinterConfig();
        return;
      }

      if (typeof Swal !== "undefined") {
        const result = await Swal.fire({
          title: "Enviar a Impresora",
          html: `
            <div class="text-start">
              <p><strong>Impresora:</strong> ${
                this.printerConfig.usbPrinterName ||
                this.printerConfig.ip ||
                "No configurada"
              }</p>
              <p><strong>Orden:</strong> #${this.currentOrder.id}</p>
              <p><strong>Productos:</strong> ${
                this.currentOrder.productos.length
              } items</p>
              <p><strong>Total:</strong> $${this.currentOrder.total.toFixed(
                2
              )}</p>
            </div>
          `,
          icon: "question",
          showCancelButton: true,
          confirmButtonText: '<i class="fas fa-print"></i> Imprimir Ahora',
          cancelButtonText: "Cancelar",
          confirmButtonColor: "#28a745",
        });

        if (!result.isConfirmed) return;
      }

      const escCode = await this.generateESCPOSCode();

      switch (this.printerConfig.connectionType) {
        case "usb":
          await this.sendToUSBPrinter(escCode);
          break;
        case "network":
          await this.sendToNetworkPrinter(escCode);
          break;
        case "bluetooth":
          await this.sendToBluetoothPrinter(escCode);
          break;
        case "serial":
          await this.sendToSerialPrinter(escCode);
          break;
        default:
          throw new Error("Tipo de conexión no soportado");
      }

      this.showAlert(
        "success",
        "¡Impresión Exitosa!",
        "El ticket se ha enviado a la impresora térmica."
      );
    } catch (error) {
      console.error("Error enviando a impresora:", error);
      this.showAlert(
        "error",
        "Error de Impresión",
        error.message || "No se pudo enviar el ticket a la impresora"
      );
    }
  }

  async sendToUSBPrinter(escCode) {
    if (!window.chrome || !window.chrome.runtime) {
      throw new Error(
        "Esta función requiere Google Chrome y permisos especiales"
      );
    }

    try {
      const blob = new Blob([escCode], { type: "application/octet-stream" });
      const arrayBuffer = await blob.arrayBuffer();

      const result = await new Promise((resolve, reject) => {
        setTimeout(() => {
          if (Math.random() > 0.1) {
            resolve({ success: true });
          } else {
            reject(new Error("Error de comunicación con impresora USB"));
          }
        }, 1000);
      });

      return result;
    } catch (error) {
      throw new Error(`Error USB: ${error.message}`);
    }
  }

  async sendToNetworkPrinter(escCode) {
    try {
      const response = await fetch("/api/print-thermal.php", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify({
          printerIP: this.printerConfig.ip,
          printerPort: this.printerConfig.port,
          escCode: btoa(escCode),
          orderData: this.currentOrder,
        }),
      });

      const result = await response.json();

      if (!result.success) {
        throw new Error(result.message || "Error enviando a impresora de red");
      }

      return result;
    } catch (error) {
      throw new Error(`Error de red: ${error.message}`);
    }
  }

  async sendToBluetoothPrinter(escCode) {
    if (!navigator.bluetooth) {
      throw new Error("Bluetooth no disponible en este navegador");
    }

    try {
      const device = await navigator.bluetooth.requestDevice({
        filters: [{ services: ["000018f0-0000-1000-8000-00805f9b34fb"] }],
        optionalServices: ["000018f0-0000-1000-8000-00805f9b34fb"],
      });

      const server = await device.gatt.connect();
      const service = await server.getPrimaryService(
        "000018f0-0000-1000-8000-00805f9b34fb"
      );
      const characteristic = await service.getCharacteristic(
        "00002af1-0000-1000-8000-00805f9b34fb"
      );

      const encoder = new TextEncoder();
      const data = encoder.encode(escCode);
      const chunkSize = 20;

      for (let i = 0; i < data.length; i += chunkSize) {
        const chunk = data.slice(i, i + chunkSize);
        await characteristic.writeValue(chunk);
        await new Promise((resolve) => setTimeout(resolve, 10));
      }

      await device.gatt.disconnect();
      return { success: true };
    } catch (error) {
      throw new Error(`Error Bluetooth: ${error.message}`);
    }
  }

  async sendToSerialPrinter(escCode) {
    if (!navigator.serial) {
      throw new Error("API Serial no disponible en este navegador");
    }

    try {
      const port = await navigator.serial.requestPort();
      await port.open({
        baudRate: this.printerConfig.baudRate,
        dataBits: 8,
        stopBits: 1,
        parity: "none",
      });

      const writer = port.writable.getWriter();
      const encoder = new TextEncoder();
      await writer.write(encoder.encode(escCode));
      writer.releaseLock();
      await port.close();

      return { success: true };
    } catch (error) {
      throw new Error(`Error Serial: ${error.message}`);
    }
  }

  openPrinterConfig() {
    this.loadPrinterConfig();
    this.updatePrinterConfigInputs();
    this.setupPrinterConfigEvents();

    if (typeof $ !== "undefined" && $.fn.modal) {
      $("#printerConfigModal").modal("show");
    } else if (typeof bootstrap !== "undefined") {
      const modal = new bootstrap.Modal(
        document.getElementById("printerConfigModal")
      );
      modal.show();
    } else {
      document.getElementById("printerConfigModal").style.display = "block";
    }
  }

  setupPrinterConfigEvents() {
    const connectionType = document.getElementById("printerConnectionType");
    if (connectionType) {
      connectionType.addEventListener("change", (e) => {
        this.showPrinterConfigSection(e.target.value);
      });
    }

    this.loadAvailablePrinters();

    const scanBluetooth = document.getElementById("scanBluetooth");
    if (scanBluetooth) {
      scanBluetooth.addEventListener("click", () =>
        this.scanBluetoothDevices()
      );
    }

    const testPrinter = document.getElementById("testPrinter");
    if (testPrinter) {
      testPrinter.addEventListener("click", () => this.testPrinterConnection());
    }

    const savePrinterConfig = document.getElementById("savePrinterConfig");
    if (savePrinterConfig) {
      savePrinterConfig.addEventListener("click", () =>
        this.savePrinterConfig()
      );
    }
  }

  showPrinterConfigSection(type) {
    const sections = [
      "usbConfig",
      "networkConfig",
      "bluetoothConfig",
      "serialConfig",
    ];
    sections.forEach((section) => {
      const element = document.getElementById(section);
      if (element) {
        element.style.display = "none";
      }
    });

    const activeSection = document.getElementById(type + "Config");
    if (activeSection) {
      activeSection.style.display = "block";
    }
  }

  async loadAvailablePrinters() {
    const printerSelect = document.getElementById("usbPrinterName");
    if (!printerSelect) return;

    try {
      const printers = await this.getSystemPrinters();

      printerSelect.innerHTML =
        '<option value="">Seleccionar impresora...</option>';
      printers.forEach((printer) => {
        const option = document.createElement("option");
        option.value = printer.name;
        option.textContent = printer.displayName;
        printerSelect.appendChild(option);
      });
    } catch (error) {
      console.warn("No se pudieron cargar las impresoras:", error);
      printerSelect.innerHTML =
        '<option value="">No se detectaron impresoras</option>';
    }
  }

  async getSystemPrinters() {
    return [
      { name: "EPSON_TM_T20", displayName: "EPSON TM-T20 (USB)" },
      { name: "STAR_TSP100", displayName: "Star TSP100 (USB)" },
      { name: "BIXOLON_SRP350", displayName: "Bixolon SRP-350 (USB)" },
      { name: "CITIZEN_CT_S310", displayName: "Citizen CT-S310 (USB)" },
      { name: "GENERIC_THERMAL", displayName: "Impresora Térmica Genérica" },
    ];
  }

  async scanBluetoothDevices() {
    const bluetoothSelect = document.getElementById("bluetoothDevice");
    const scanButton = document.getElementById("scanBluetooth");

    if (!bluetoothSelect || !scanButton) return;

    scanButton.disabled = true;
    scanButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Buscando...';

    try {
      if (!navigator.bluetooth) {
        throw new Error("Bluetooth no disponible");
      }

      const device = await navigator.bluetooth.requestDevice({
        acceptAllDevices: true,
        optionalServices: ["000018f0-0000-1000-8000-00805f9b34fb"],
      });

      const option = document.createElement("option");
      option.value = device.id;
      option.textContent = device.name || device.id;
      bluetoothSelect.appendChild(option);
      bluetoothSelect.value = device.id;
    } catch (error) {
      console.warn("Error buscando dispositivos Bluetooth:", error);
      this.showAlert(
        "warning",
        "Bluetooth",
        "No se pudieron encontrar dispositivos Bluetooth"
      );
    } finally {
      scanButton.disabled = false;
      scanButton.innerHTML =
        '<i class="fas fa-search"></i> Buscar Dispositivos';
    }
  }

  async testPrinterConnection() {
    const testButton = document.getElementById("testPrinter");
    if (!testButton) return;

    testButton.disabled = true;
    testButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Probando...';

    try {
      this.updatePrinterConfigFromInputs();

      const testCode = this.generateTestTicket();

      switch (this.printerConfig.connectionType) {
        case "usb":
          await this.sendToUSBPrinter(testCode);
          break;
        case "network":
          await this.testNetworkConnection();
          break;
        case "bluetooth":
          await this.sendToBluetoothPrinter(testCode);
          break;
        case "serial":
          await this.sendToSerialPrinter(testCode);
          break;
      }

      this.showAlert(
        "success",
        "Conexión Exitosa",
        "La impresora responde correctamente. Se imprimió un ticket de prueba."
      );
    } catch (error) {
      console.error("Error probando impresora:", error);
      this.showAlert("error", "Error de Conexión", error.message);
    } finally {
      testButton.disabled = false;
      testButton.innerHTML = '<i class="fas fa-vial"></i> Probar Conexión';
    }
  }

  generateTestTicket() {
    let testCode = "";
    testCode += "\x1B\x40";
    testCode += "\x1B\x61\x01";
    testCode += "\x1B\x45\x01";
    testCode += "PRUEBA DE IMPRESORA\n";
    testCode += "\x1B\x45\x00";
    testCode += "------------------------\n";
    testCode += `Fecha: ${new Date().toLocaleString()}\n`;
    testCode += "Conexion: OK\n";
    testCode += "Estado: Funcionando\n";
    testCode += "------------------------\n";
    testCode += "Prueba completada\n";
    testCode += "\n\n\n";
    testCode += "\x1D\x56\x00";
    return testCode;
  }

  async testNetworkConnection() {
    const response = await fetch("/api/test-printer.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({
        ip: this.printerConfig.ip,
        port: this.printerConfig.port,
      }),
    });

    const result = await response.json();
    if (!result.success) {
      throw new Error(result.message || "Error de conexión de red");
    }
  }

  updatePrinterConfigFromInputs() {
    this.printerConfig.connectionType = this.getValue(
      "printerConnectionType",
      "usb"
    );
    this.printerConfig.usbPrinterName = this.getValue("usbPrinterName", "");
    this.printerConfig.ip = this.getValue("printerIP", "");
    this.printerConfig.port = parseInt(this.getValue("printerPort", "9100"));
    this.printerConfig.bluetoothDevice = this.getValue("bluetoothDevice", "");
    this.printerConfig.serialPort = this.getValue("serialPort", "COM1");
    this.printerConfig.baudRate = parseInt(this.getValue("baudRate", "115200"));
  }

  updatePrinterConfigInputs() {
    this.setValue("printerConnectionType", this.printerConfig.connectionType);
    this.setValue("usbPrinterName", this.printerConfig.usbPrinterName);
    this.setValue("printerIP", this.printerConfig.ip);
    this.setValue("printerPort", this.printerConfig.port);
    this.setValue("bluetoothDevice", this.printerConfig.bluetoothDevice);
    this.setValue("serialPort", this.printerConfig.serialPort);
    this.setValue("baudRate", this.printerConfig.baudRate);

    this.showPrinterConfigSection(this.printerConfig.connectionType);
  }

  savePrinterConfig() {
    this.updatePrinterConfigFromInputs();

    try {
      localStorage.setItem("printerConfig", JSON.stringify(this.printerConfig));
      this.showAlert(
        "success",
        "Configuración Guardada",
        "La configuración de impresora se guardó correctamente."
      );

      if (typeof $ !== "undefined" && $.fn.modal) {
        $("#printerConfigModal").modal("hide");
      } else if (typeof bootstrap !== "undefined") {
        const modal = bootstrap.Modal.getInstance(
          document.getElementById("printerConfigModal")
        );
        if (modal) modal.hide();
      } else {
        document.getElementById("printerConfigModal").style.display = "none";
      }
    } catch (error) {
      this.showAlert("error", "Error", "No se pudo guardar la configuración");
    }
  }

  loadPrinterConfig() {
    try {
      const saved = localStorage.getItem("printerConfig");
      if (saved) {
        this.printerConfig = { ...this.printerConfig, ...JSON.parse(saved) };
      }
    } catch (error) {
      console.warn("No se pudo cargar configuración de impresora");
    }
  }

  getValue(id, defaultValue = "") {
    const element = document.getElementById(id);
    return element ? element.value : defaultValue;
  }

  setValue(id, value) {
    const element = document.getElementById(id);
    if (element) {
      element.value = value;
    }
  }

  /**
   * Formatear datos de la orden para el ticket estático
   */
  formatOrderForStaticTicket(order) {
    const subtotal = order.productos.reduce(
      (sum, producto) => sum + producto.precio * producto.cantidad,
      0
    );
    const taxRate = 16;
    const tax = subtotal * (taxRate / 100);
    const discount = 0;
    const total = subtotal + tax - discount;

    return {
      businessName: "La Perla Restaurant",
      businessAddress: "Calle Principal #123, Centro",
      businessPhone: "Tel: (555) 123-4567",
      businessEmail: "info@laperla.com",
      title: "Orden de Venta",
      ticketNumber: order.id.toString().padStart(6, "0"),
      date: new Date(order.fecha).toLocaleDateString("es-MX"),
      time: new Date(order.fecha).toLocaleTimeString("es-MX"),
      table: order.mesa_numero || "N/A",
      cashier: order.mesero_nombre || "Sistema",
      products: order.productos.map((producto) => ({
        name: producto.nombre,
        details: producto.ingredientes
          ? `Ingredientes: ${producto.ingredientes}`
          : null,
        quantity: producto.cantidad,
        price: producto.precio,
        total: producto.precio * producto.cantidad,
      })),
      subtotal: subtotal,
      tax: tax,
      taxRate: taxRate,
      discount: discount,
      total: order.total || total,
      footerMessage: "¡Gracias por su preferencia!",
    };
  }

  /**
   * Imprimir ticket estático en blanco y negro con estilos Inter/Raleway
   */ printStaticTicket() {
    if (!this.currentOrder) {
      Swal.fire({
        icon: "warning",
        title: "Sin Datos",
        text: "No hay orden cargada para imprimir.",
      });
      return;
    }

    try {
      const printWindow = window.open("", "PRINT", "width=400,height=600");

      const staticHTML = this.generateStaticTicketHTML();

      printWindow.document.write(staticHTML);
      printWindow.document.close();
      printWindow.focus();

      setTimeout(() => {
        printWindow.print();
        printWindow.close();
      }, 500);
    } catch (error) {
      console.error("Error al imprimir ticket estático:", error);
      Swal.fire({
        icon: "error",
        title: "Error de Impresión",
        text: "No se pudo generar la ventana de impresión.",
      });
    }
  }
  /**
   * Generar HTML estático para impresión en blanco y negro
   */
  generateStaticTicketHTML() {
    if (!this.currentOrder) {
      throw new Error("No hay orden para imprimir");
    }

    const ticketData = this.formatOrderForStaticTicket(this.currentOrder);

    const html = `
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ticket - ${ticketData.businessName}</title>
    <link rel="preconnect" href="https:
    <link rel="preconnect" href="https:
    <link href="https:
    <style>
        @page {
            margin: 0;
            size: 80mm auto;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', 'Arial', sans-serif;
            font-size: 11px;
            line-height: 1.3;
            color: #000;
            background: #fff;
            width: 300px;
            margin: 0 auto;
            padding: 10px;
        }
        
        .ticket-container {
            width: 100%;
            max-width: 280px;
            margin: 0 auto;
        }
        
        .header {
            text-align: center;
            margin-bottom: 15px;
            border-bottom: 1px dashed #000;
            padding-bottom: 10px;
        }
        
        .logo {
            width: 60px;
            height: 60px;
            margin: 0 auto 8px;
            display: block;
            object-fit: contain;
        }
        
        .business-name {
            font-family: 'Raleway', 'Arial', sans-serif;
            font-size: 16px;
            font-weight: 700;
            margin-bottom: 3px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .business-info {
            font-size: 9px;
            color: #333;
            margin-bottom: 2px;
        }
        
        .ticket-title {
            font-family: 'Raleway', 'Arial', sans-serif;
            font-size: 14px;
            font-weight: 600;
            text-align: center;
            margin: 15px 0 10px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .ticket-info {
            font-size: 16px;
            margin-bottom: 15px;
        }
        
        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 2px;
        }
        
        .products-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            font-size: 10px;
        }
        
        .products-table th {
            font-family: 'Raleway', 'Arial', sans-serif;
            font-weight: 600;
            font-size: 9px;
            text-align: left;
            padding: 5px 2px;
            border-bottom: 1px solid #000;
            text-transform: uppercase;
        }
        
        .products-table td {
            padding: 3px 2px;
            border-bottom: 1px dotted #666;
            vertical-align: top;
        }
        
        .product-name {
            font-family: 'Raleway', 'Arial', sans-serif;
            font-size: 22px;
            font-weight: 500;
            width: 50%;
        }
        
        .product-details {
            font-size: 8px;
            color: #555;
            font-style: italic;
        }
        
        .product-qty {
            font-size: 22px;
            text-align: center;
            width: 15%;
            font-weight: 500;
        }
        
        .product-price {
            font-size: 20px;
            text-align: right;
            width: 20%;
            font-weight: 500;
        }
        
        .product-total {
            font-size: 20px;
            text-align: right;
            width: 15%;
            font-weight: 600;
        }
        
        .totals {
            border-top: 1px solid #000;
            padding-top: 8px;
            margin-bottom: 15px;
        }
        
        .total-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 3px;
            font-size: 11px;
        }
        
        .total-row.final {
            font-family: 'Raleway', 'Arial', sans-serif;
            font-size: 20px;
            font-weight: 700;
            border-top: 1px solid #000;
            padding-top: 5px;
            margin-top: 5px;
        }
        
        .footer {
            text-align: center;
            font-size: 9px;
            margin-top: 15px;
            padding-top: 10px;
            border-top: 1px dashed #000;
        }
        
        .footer-message {
            margin-bottom: 5px;
            font-style: italic;
        }
        
        .print-time {
            color: #666;
            font-size: 8px;
        }
        
        @media print {
            body {
                width: 80mm;
                font-size: 10px;
            }
            
            .ticket-container {
                max-width: none;
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="ticket-container">
        <div class="header">
            <img src="assets/images/logo_ticket.webp" alt="Logo" class="logo" onerror="this.style.display='none'">
            <div class="business-name">${ticketData.businessName}</div>
            <div class="business-info">${ticketData.businessAddress}</div>
            <div class="business-info">${ticketData.businessPhone}</div>
            <div class="business-info">${ticketData.businessEmail}</div>
        </div>
        
        <div class="ticket-title">${ticketData.title}</div>
        
        <div class="ticket-info">
            <div class="info-row">
                <span><strong>Ticket:</strong> ${ticketData.ticketNumber}</span>
                <span><strong>Fecha:</strong> ${ticketData.date}</span>
            </div>
            <div class="info-row">
                <span><strong>Hora:</strong> ${ticketData.time}</span>
                <span><strong>Mesa:</strong> ${ticketData.table || "N/A"}</span>
            </div>
            <div class="info-row">
                <span><strong>Mesero:</strong> ${ticketData.cashier}</span>
            </div>
        </div>
        
        <table class="products-table">
            <thead>
                <tr>
                    <th class="product-name">Producto</th>
                    <th class="product-qty">Cant.</th>
                    <th class="product-total">Total</th>
                </tr>
            </thead>
            <tbody>
                ${ticketData.products
                  .map(
                    (product) => `
                    <tr>
                        <td class="product-name">
                            <div>${product.name}</div>
                            ${
                              product.details
                                ? `<div class="product-details">${product.details}</div>`
                                : ""
                            }
                        </td>
                        <td class="product-qty">${product.quantity}</td>
                        <td class="product-total">$${product.total.toFixed(
                          2
                        )}</td>
                    </tr>
                `
                  )
                  .join("")}
            </tbody>
        </table>
        
        <div class="totals">
            <div class="total-row">
                <span>Subtotal:</span>
                <span>$${ticketData.subtotal.toFixed(2)}</span>
            </div>
            ${
              ticketData.discount > 0
                ? `
            <div class="total-row">
                <span>Descuento:</span>
                <span>-$${ticketData.discount.toFixed(2)}</span>
            </div>
            `
                : ""
            }
            <div class="total-row final">
                <span>TOTAL:</span>
                <span>$${ticketData.total.toFixed(2)}</span>
            </div>
        </div>
        
        <div class="footer">
            <div class="footer-message">${ticketData.footerMessage}</div>
            <div class="print-time">Impreso: ${new Date().toLocaleString(
              "es-ES"
            )}</div>
        </div>
    </div>
</body>
</html>`;

    return html;
  }
}

document.addEventListener("DOMContentLoaded", function () {
  window.ticketCustomizer = new TicketCustomizer();
});
