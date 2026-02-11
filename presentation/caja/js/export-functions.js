class ExportManager {
  constructor() {
    this.initializeEvents();
    this.initializeTooltips();
  }

  initializeEvents() {
    document
      .getElementById("exportCsv")
      .addEventListener("click", () => this.exportToCSV());
    document
      .getElementById("exportPdf")
      .addEventListener("click", () => this.exportToPDF());
    document
      .getElementById("exportExcel")
      .addEventListener("click", () => this.exportToExcel());

    document
      .getElementById("exportCsvMobile")
      .addEventListener("click", () => this.exportToCSV());
    document
      .getElementById("exportPdfMobile")
      .addEventListener("click", () => this.exportToPDF());
    document
      .getElementById("exportExcelMobile")
      .addEventListener("click", () => this.exportToExcel());

    const headerCsv = document.getElementById("exportCsvHeader");
    const headerPdf = document.getElementById("exportPdfHeader");
    const headerExcel = document.getElementById("exportExcelHeader");

    if (headerCsv)
      headerCsv.addEventListener("click", () => this.exportToCSV());
    if (headerPdf)
      headerPdf.addEventListener("click", () => this.exportToPDF());
    if (headerExcel)
      headerExcel.addEventListener("click", () => this.exportToExcel());
  }

  initializeTooltips() {
    const tooltipTriggerList = [].slice.call(
      document.querySelectorAll('[data-bs-toggle="tooltip"]')
    );
    tooltipTriggerList.map(function (tooltipTriggerEl) {
      return new bootstrap.Tooltip(tooltipTriggerEl);
    });
  }

  getTableData() {
    const table = document.getElementById("cajasTable");
    const headers = [];
    const rows = [];

    const headerCells = table.querySelectorAll("thead th");
    headerCells.forEach((cell, index) => {
      if (index < headerCells.length - 1) {
        headers.push(cell.textContent.trim());
      }
    });

    const bodyRows = table.querySelectorAll("tbody tr");
    bodyRows.forEach((row) => {
      const rowData = [];
      const cells = row.querySelectorAll("td");
      cells.forEach((cell, index) => {
        if (index < cells.length - 1) {
          rowData.push(cell.textContent.trim());
        }
      });
      if (rowData.length > 0) {
        rows.push(rowData);
      }
    });

    return { headers, rows };
  }

  exportToCSV() {
    try {
      const { headers, rows } = this.getTableData();

      if (rows.length === 0) {
        Swal.fire({
          icon: "warning",
          title: "Sin datos",
          text: "No hay datos para exportar",
          timer: 2000,
          showConfirmButton: false,
        });
        return;
      }

      let csvContent = "\uFEFF";

      csvContent += headers.join(",") + "\n";

      rows.forEach((row) => {
        const escapedRow = row.map((cell) => {
          const escaped = cell.replace(/"/g, '""');
          return escaped.includes(",") ? `"${escaped}"` : escaped;
        });
        csvContent += escapedRow.join(",") + "\n";
      });

      const blob = new Blob([csvContent], { type: "text/csv;charset=utf-8;" });
      const link = document.createElement("a");
      const url = URL.createObjectURL(blob);

      link.setAttribute("href", url);
      link.setAttribute(
        "download",
        `historial_cajas_${this.getCurrentDate()}.csv`
      );
      link.style.visibility = "hidden";

      document.body.appendChild(link);
      link.click();
      document.body.removeChild(link);

      this.showSuccessMessage("CSV descargado exitosamente");
    } catch (error) {
      console.error("Error exportando CSV:", error);
      this.showErrorMessage("Error al exportar CSV");
    }
  }

  exportToPDF() {
    try {
      const { headers, rows } = this.getTableData();

      if (rows.length === 0) {
        Swal.fire({
          icon: "warning",
          title: "Sin datos",
          text: "No hay datos para exportar",
          timer: 2000,
          showConfirmButton: false,
        });
        return;
      }

      const { jsPDF } = window.jspdf;
      const doc = new jsPDF({
        orientation: "landscape",
        unit: "mm",
        format: "a4",
      });

      doc.setFont("helvetica");

      doc.setFontSize(16);
      doc.setTextColor(40, 40, 40);
      doc.text("Historial de Cajas - Estudiovioleta", 20, 20);

      doc.setFontSize(10);
      doc.setTextColor(100, 100, 100);
      doc.text(`Generado el: ${new Date().toLocaleString("es-ES")}`, 20, 30);

      doc.autoTable({
        head: [headers],
        body: rows,
        startY: 40,
        styles: {
          fontSize: 8,
          cellPadding: 3,
          overflow: "linebreak",
          halign: "center",
        },
        headStyles: {
          fillColor: [41, 128, 185],
          textColor: 255,
          fontStyle: "bold",
        },
        alternateRowStyles: {
          fillColor: [240, 248, 255],
        },
        margin: { top: 40, left: 20, right: 20, bottom: 20 },
      });

      doc.save(`historial_cajas_${this.getCurrentDate()}.pdf`);

      this.showSuccessMessage("PDF descargado exitosamente");
    } catch (error) {
      console.error("Error exportando PDF:", error);
      this.showErrorMessage("Error al exportar PDF");
    }
  }

  exportToExcel() {
    try {
      const { headers, rows } = this.getTableData();

      if (rows.length === 0) {
        Swal.fire({
          icon: "warning",
          title: "Sin datos",
          text: "No hay datos para exportar",
          timer: 2000,
          showConfirmButton: false,
        });
        return;
      }

      const wb = XLSX.utils.book_new();

      const wsData = [headers, ...rows];
      const ws = XLSX.utils.aoa_to_sheet(wsData);

      const colWidths = headers.map((header, index) => {
        const maxLength = Math.max(
          header.length,
          ...rows.map((row) => (row[index] ? row[index].toString().length : 0))
        );
        return { wch: Math.min(Math.max(maxLength + 2, 10), 30) };
      });
      ws["!cols"] = colWidths;

      const headerStyle = {
        font: { bold: true, color: { rgb: "FFFFFF" } },
        fill: { fgColor: { rgb: "2980B9" } },
        alignment: { horizontal: "center", vertical: "center" },
      };

      headers.forEach((header, index) => {
        const cellAddress = XLSX.utils.encode_cell({ r: 0, c: index });
        if (!ws[cellAddress]) ws[cellAddress] = {};
        ws[cellAddress].s = headerStyle;
      });

      XLSX.utils.book_append_sheet(wb, ws, "Historial de Cajas");

      wb.Props = {
        Title: "Historial de Cajas",
        Subject: "Reporte de Cajas",
        Author: "Estudiovioleta",
        CreatedDate: new Date(),
      };

      XLSX.writeFile(wb, `historial_cajas_${this.getCurrentDate()}.xlsx`);

      this.showSuccessMessage("Excel descargado exitosamente");
    } catch (error) {
      console.error("Error exportando Excel:", error);
      this.showErrorMessage("Error al exportar Excel");
    }
  }

  getCurrentDate() {
    const now = new Date();
    const year = now.getFullYear();
    const month = String(now.getMonth() + 1).padStart(2, "0");
    const day = String(now.getDate()).padStart(2, "0");
    return `${year}-${month}-${day}`;
  }

  showSuccessMessage(message) {
    Swal.fire({
      icon: "success",
      title: "¡Éxito!",
      text: message,
      timer: 2000,
      showConfirmButton: false,
      toast: true,
      position: "top-end",
    });
  }

  showErrorMessage(message) {
    Swal.fire({
      icon: "error",
      title: "Error",
      text: message,
      timer: 3000,
      showConfirmButton: false,
      toast: true,
      position: "top-end",
    });
  }
}

document.addEventListener("DOMContentLoaded", function () {
  new ExportManager();
});
