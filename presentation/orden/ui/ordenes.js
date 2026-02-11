class OrdenUI {
  mesaSeleccionada = null;
  mesaId = null;
  mesaNumero = null;
  estado = null;
  ordenId = null;
  esEdicion = false;

  constructor() {
    this.mesaId = null;
    this.mesaNumero = null;
    this.estado = null;
    const carritoGuardado = sessionStorage.getItem("carrito");
    this.carrito = carritoGuardado ? JSON.parse(carritoGuardado) : [];
    this.actualizarCarritoUI();
  }

  async init() {
    try {
      this.setupEventListeners();
      this.obtenerMesaSeleccionada();
      this.obtenerCategorias();
      this.obtenerProductosPorCategoria(0);
      this.setupFilterCategoriaButtons();
      this.setupSearchFunctionality();
    } catch (error) {
      console.error("Error al inicializar OrdenUI:", error);
    }
  }

  setupEventListeners() {
    document.addEventListener("DOMContentLoaded", () => {
      this.obtenerMesaSeleccionada();
    });
  }

  async obtenerMesaSeleccionada() {
    const urlParams = new URLSearchParams(window.location.search);

    if (urlParams.has("orden_id")) {
      const ordenId = urlParams.get("orden_id");
      this.ordenId = ordenId;
      this.esEdicion = true;

      try {
        const response = await fetch(
          `api/ordenes.php?action=findById&id=${ordenId}`
        );
        const result = await response.json();
        if (result.success && result.data) {
          this.mesaSeleccionada = {
            id: result.data.mesa_id || result.data.mesa_id,
            numero_mesa: result.data.mesa_numero,
            estado: result.data.estado,
            source: "orden",
          };
          this.mesaId = this.mesaSeleccionada.id;
          this.mesaNumero = this.mesaSeleccionada.numero_mesa;
          this.estado = this.mesaSeleccionada.estado;

          if (Array.isArray(result.data.productos)) {
            this.carrito = result.data.productos.map((p) => ({
              id: p.product_id || p.id,
              nombre: p.nombre,
              precio: parseFloat(p.precio),
              imagen: p.imagen,
              cantidad: parseInt(p.cantidad),
            }));
            sessionStorage.setItem("carrito", JSON.stringify(this.carrito));
            this.actualizarCarritoUI();
          }

          const textAreaAnotaciones = document.getElementById(
            "textAreaAnotaciones"
          );
          if (textAreaAnotaciones && result.data.notas) {
            textAreaAnotaciones.value = result.data.notas;
          }

          const breadcrumbActive = document.querySelector(
            ".breadcrumb-item.active"
          );
          const pageTitle = document.querySelector(".page-title");
          const invoiceMesa = document.querySelector("#numMesa");
          if (pageTitle)
            pageTitle.textContent = `Editar Orden - Mesa ${this.mesaNumero}`;
          if (breadcrumbActive) breadcrumbActive.textContent = "Editar Orden";
          if (invoiceMesa) invoiceMesa.textContent = this.mesaNumero;
          return this.mesaSeleccionada;
        } else {
          Swal.fire("Error", "No se pudo cargar la orden para editar", "error");
          window.location.href = "ordenes.php";
        }
      } catch (error) {
        console.error("Error al obtener la orden:", error);
        Swal.fire("Error", "No se pudo cargar la orden", "error");
        window.location.href = "ordenes.php";
      }
      return null;
    }

    if (urlParams.has("mesa_id")) {
      this.mesaNumero = urlParams.get("mesa_numero");
      this.mesaId = urlParams.get("mesa_id");
      this.estado = urlParams.get("estado");

      try {
        const response = await fetch(
          `api/mesas.php?action=findById&id=${this.mesaId}`
        );
        const result = await response.json();
        if (result.success && result.data.estado === "Disponible") {
          this.mesaSeleccionada = {
            id: result.data.id,
            numero_mesa: result.data.numero_mesa,
            estado: result.data.estado,
            source: "url",
          };
          this.mesaId = result.data.id;
          this.mesaNumero = result.data.numero_mesa;
          this.estado = result.data.estado;
        } else {
          window.location.href = "menu.php";
        }
      } catch (error) {
        console.error("Error al obtener la mesa seleccionada:", error);
      }

      const breadcrumbActive = document.querySelector(
        ".breadcrumb-item.active"
      );
      const pageTitle = document.querySelector(".page-title");
      const invoiceMesa = document.querySelector("#numMesa");
      if (pageTitle)
        pageTitle.textContent = `Lista de Productos - Mesa ${this.mesaNumero}`;
      if (breadcrumbActive)
        breadcrumbActive.textContent = "Mesa " + this.mesaNumero;
      if (invoiceMesa) invoiceMesa.textContent = this.mesaNumero;
      return this.mesaSeleccionada;
    }
    return null;
  }

  async obtenerCategorias() {
    const loading = document.getElementById("loading");
    const container = document.getElementById("mesas-container");
    const errorDiv = document.getElementById("error-message");

    try {
      loading.style.display = "block";
      container.innerHTML = "";
      errorDiv.style.display = "none";

      const response = await fetch(
        "api/categorias.php?action=filterByEstado&estado=1"
      );
      const result = await response.json();

      if (result.success) {
        this.mostrarCategorias(result.data);
      } else {
        throw new Error(result.message || "Error al cargar las categorias");
      }
    } catch (error) {
      console.error("Error:", error);
      errorDiv.textContent = "Error al cargar las categorias: " + error.message;
      errorDiv.style.display = "block";
    } finally {
      loading.style.display = "none";
    }
  }

  async mostrarCategorias(categorias) {
    const container = document.getElementById("categories-container");
    container.innerHTML = "";

    const swiperContainer = document.createElement("div");
    swiperContainer.className = "swiper mySwiper";
    swiperContainer.style.padding = "20px 0";

    const swiperWrapper = document.createElement("div");
    swiperWrapper.className = "swiper-wrapper";

    const todosSlide = document.createElement("div");
    todosSlide.className = "swiper-slide";
    todosSlide.innerHTML = `
            <div class="categoria">
                <button type="button" class="btn btn-outline-primary border-0 shadow-sm d-flex flex-column align-items-center justify-content-center categoria-btn" 
                        data-categoria-id="0"
                        id="categoria-todos"
                        title="Todos" 
                        data-bs-toggle="tooltip" 
                        data-bs-placement="top">
                    <i class="fas fa-th-large categoria-icon mb-2"></i>    
                    <span class="categoria-text">Todos</span>
                </button>
            </div>
        `;
    swiperWrapper.appendChild(todosSlide);

    categorias.forEach((categoria) => {
      const swiperSlide = document.createElement("div");
      swiperSlide.className = "swiper-slide";
      const imagenUrl = categoria.imagen
        ? categoria.imagen
        : "assets/images/brands/slack.png";
      swiperSlide.innerHTML = `
            <div class="categoria">
                <button type="button" class="btn btn-outline-primary border-0 shadow-sm d-flex flex-column align-items-center justify-content-center categoria-btn" 
                        data-categoria-id="${categoria.id || ""}"
                        title="${categoria.nombre}" 
                        data-bs-toggle="tooltip" 
                        data-bs-placement="top">
                    ${categoria.imagen ? 
                        `<img src="${imagenUrl}" alt="${categoria.nombre}" class="categoria-img mb-2">` : 
                        `<i class="fas fa-utensils categoria-icon mb-2"></i>`
                    }    
                    <span class="categoria-text">${categoria.nombre}</span>
                </button>
            </div>
        `;
      swiperWrapper.appendChild(swiperSlide);
    });

    swiperContainer.appendChild(swiperWrapper);

    const pagination = document.createElement("div");
    pagination.className = "swiper-pagination";
    swiperContainer.appendChild(pagination);

    container.appendChild(swiperContainer);

    const existingStyle = document.getElementById("swiper-gap-style");
    if (existingStyle) {
      existingStyle.remove();
    }

    const style = document.createElement("style");
    style.id = "swiper-gap-style";
    style.textContent = `
        .mySwiper {
            padding-left: 10px !important;
            padding-right: 10px !important;
        }
        
        .mySwiper .swiper-wrapper {
            padding-bottom: 10px;
        }
        
        .categoria {
            width: 100%;
            height: 100%;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        
        .categoria-btn {
            width: 100%;
            height: 100px;
            min-height: 90px;
            padding: 12px 8px;
            border-radius: 12px;
            transition: all 0.2s ease;
            background: transparent;
            position: relative;
            overflow: hidden;
        }
        
        .categoria-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15) !important;
        }
        
        .categoria-btn.active {
            background: var(--bs-primary) !important;
            color: #0d6efd !important;
            border-color: var(--bs-primary) !important;
            box-shadow: 0 4px 12px rgba(var(--bs-primary-rgb), 0.3) !important;
        }
        
        .categoria-btn.active .categoria-icon {
            color: #0d6efd !important;
        }
        
        .categoria-text {
            font-size: 0.8rem;
            font-weight: 500;
            color: var(--bs-body-color);
            margin: 0;
            text-align: center;
            line-height: 1.2;
            word-wrap: break-word;
            overflow-wrap: break-word;
            hyphens: auto;
        }
        
        @media (max-width: 480px) {
            .categoria-btn {
                height: 85px;
                min-height: 75px;
                padding: 8px 6px;
            }
            
            .categoria-img {
                width: 20px;
                height: 20px;
            }
            
            .categoria-icon {
                font-size: 1.25rem;
            }
            
            .categoria-text {
                font-size: 0.7rem;
                line-height: 1.1;
            }
        }
        
        @media (min-width: 481px) and (max-width: 576px) {
            .categoria-btn {
                height: 90px;
                min-height: 80px;
                padding: 10px 6px;
            }
            
            .categoria-img {
                width: 22px;
                height: 22px;
            }
            
            .categoria-icon {
                font-size: 1.35rem;
            }
            
            .categoria-text {
                font-size: 0.75rem;
            }
        }
        
        @media (min-width: 577px) and (max-width: 768px) {
            .categoria-btn {
                height: 95px;
                min-height: 85px;
                padding: 10px 8px;
            }
            
            .categoria-img {
                width: 24px;
                height: 24px;
            }
            
            .categoria-icon {
                font-size: 1.4rem;
            }
        }
        
        @media (min-width: 769px) and (max-width: 992px) {
            .categoria-btn {
                height: 100px;
                min-height: 90px;
            }
        }

        @media (min-width: 993px) {
            .categoria-btn {
                height: 105px;
                min-height: 95px;
                padding: 12px 10px;
            }
            
            .categoria-img {
                width: 26px;
                height: 26px;
            }
            
            .categoria-icon {
                font-size: 1.6rem;
            }
            
            .categoria-text {
                font-size: 0.85rem;
            }
        }
        
        .swiper-pagination {
            bottom: -5px !important;
        }
        
        .swiper-pagination-bullet {
            background: var(--bs-primary);
            opacity: 0.4;
        }
        
        .swiper-pagination-bullet-active {
            opacity: 1;
        }
    `;
    document.head.appendChild(style);

    if (window.Swiper) {
      new Swiper(".mySwiper", {
        slidesPerView: "auto",
        spaceBetween: 12,
        centeredSlides: false,
        grabCursor: true,
        navigation: false,
        pagination: {
          el: ".swiper-pagination",
          clickable: true,
          dynamicBullets: true,
        },
        breakpoints: {
          320: {
            slidesPerView: 2,
            spaceBetween: 8,
          },
          400: {
            slidesPerView: 2.5,
            spaceBetween: 10,
          },
          480: {
            slidesPerView: 3,
            spaceBetween: 12,
          },
          576: {
            slidesPerView: 3.5,
            spaceBetween: 14,
          },
          768: {
            slidesPerView: 4.5,
            spaceBetween: 16,
          },
          992: {
            slidesPerView: 5.5,
            spaceBetween: 18,
          },
          1200: {
            slidesPerView: 6,
            spaceBetween: 20,
          },
        },
        watchSlidesProgress: true,
        watchSlidesVisibility: true,
        resistanceRatio: 0.85,
        threshold: 5,
        longSwipesRatio: 0.1,
        longSwipesMs: 100,
      });
    } else {
      console.warn(
        "Swiper.js is not loaded. Please include Swiper CSS and JS in your HTML."
      );
    }

    this.setupFilterCategoriaButtons();
  }

  async obtenerProductosPorCategoria(categoriaId) {
    const loading = document.getElementById("loading");
    const container = document.getElementById("product-list");
    const errorDiv = document.getElementById("error-message");
    let response = null;
    try {
      loading.style.display = "block";
      container.innerHTML = "";
      errorDiv.style.display = "none";
      if (categoriaId === 0 || categoriaId === "0") {
        response = await fetch(
          "api/productos.php?action=filterByEstado&estado=Disponible"
        );
      } else {
        response = await fetch(
          `api/productos.php?action=filterByCategoria&categoria_id=${categoriaId}`
        );
      }
      const result = await response.json();

      if (result.success) {
        this.mostrarProductos(result.data);
      } else {
        throw new Error(result.message || "Error al cargar los productos");
      }
    } catch (error) {
      console.error("Error:", error);
      errorDiv.textContent = "Error al cargar los productos: " + error.message;
      errorDiv.style.display = "block";
    } finally {
      loading.style.display = "none";
    }
  }

  setupFilterCategoriaButtons() {
    const categoriaButtons = document.querySelectorAll(".categoria-btn");
    categoriaButtons.forEach((button) => {
      button.addEventListener("click", () => {
        categoriaButtons.forEach((btn) => btn.classList.remove("active"));

        button.classList.add("active");

        const container = document.getElementById("product-list");
        container.innerHTML =
          '<div class="text-center py-4"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Cargando productos...</span></div></div>';

        const categoriaId = button.getAttribute("data-categoria-id");
        this.obtenerProductosPorCategoria(categoriaId);
      });
    });
  }

  mostrarProductos(productos) {
    const container = document.getElementById("product-list");
    container.innerHTML = "";

    if (!productos || productos.length === 0) {
      const noProductsMessage = document.createElement("div");
      noProductsMessage.className = "alert alert-info";
      noProductsMessage.textContent =
        "No hay productos disponibles en esta categoría.";
      noProductsMessage.id = "no-products-message";
      container.appendChild(noProductsMessage);
      return;
    }

    productos.forEach((producto) => {
      const productoDiv = document.createElement("div");
      productoDiv.className = "col-6 col-sm-6 col-md-4 col-lg-3 col-xl-2 mb-3";
      const imagen = producto.imagen
        ? producto.imagen
        : "assets/images/brands/slack.png";
      const nombre = producto.nombre || "Producto";
      const descripcion = producto.descripcion || "";
      const precio = producto.precio
        ? `$${parseFloat(producto.precio).toFixed(2)}`
        : "";
      const esNuevo = producto.nuevo
        ? `<span class="badge bg-success rounded-pill fs-6">Nuevo</span>`
        : "";
      productoDiv.innerHTML = `
                <div class="card product-card border-0 shadow-sm h-100" data-producto-id="${producto.id}">
                    <div class="position-relative overflow-hidden rounded-top">
                        <img src="${imagen}" class="card-img-top object-fit-cover" alt="${nombre}" style="height: 160px;">
                        ${esNuevo ? `<div class="position-absolute top-0 end-0 m-2">${esNuevo}</div>` : ''}
                    </div>
                    <div class="card-body p-3 d-flex flex-column">
                        <h6 class="card-title mb-2 fw-semibold text-body h4" style="line-height: 1.3; word-wrap: break-word; overflow-wrap: break-word;">${nombre}</h6>
                        ${descripcion ? `<p class="card-text text-muted small mb-3 flex-grow-1" style="font-size: 0.8rem; line-height: 1.4; word-wrap: break-word; overflow-wrap: break-word;">${descripcion}</p>` : ''}
                        <div class="mt-auto">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="precio-producto h4 mb-0 text-primary fw-bold">${precio}</span>
                            </div>
                            <button type="button" class="btn btn-outline-primary btn-sm w-100 agregar-btn" data-producto-id="${producto.id}">
                                <i class="fas fa-plus me-1"></i>Agregar
                            </button>
                        </div>
                    </div>
                </div>
            `;
      container.appendChild(productoDiv);
    });

    this.setupAgregarButtons();
    const existingProductStyle = document.getElementById(
      "product-responsive-style"
    );
    if (existingProductStyle) {
      existingProductStyle.remove();
    }

    const productStyle = document.createElement("style");
    productStyle.id = "product-responsive-style";
    productStyle.textContent = `
        .product-card {
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        
        .product-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15) !important;
        }
        
        .product-card .card-title {
            word-wrap: break-word;
            overflow-wrap: break-word;
            hyphens: auto;
        }
        
        .product-card .card-text {
            word-wrap: break-word;
            overflow-wrap: break-word;
            hyphens: auto;
        }
   
        @media (max-width: 575px) {
            .product-card {
                font-size: 0.85rem;
            }
            
            .product-card .card-img-top {
                height: 120px;
                object-fit: cover;
            }
            
            .product-card .card-title {
                font-size: 0.85rem;
                line-height: 1.3;
                margin-bottom: 0.5rem !important;
                min-height: auto;
                max-height: none;
            }
            
            .product-card .card-text {
                font-size: 0.75rem;
                line-height: 1.4;
                margin-bottom: 0.75rem !important;
                min-height: auto;
                max-height: none;
            }
            
            .product-card .h6 {
                font-size: 0.9rem !important;
            }
            
            .product-card .btn {
                font-size: 0.75rem;
                padding: 0.4rem 0.6rem;
            }
            
            .product-card .card-body {
                padding: 0.75rem;
                min-height: 140px;
            }
            
            .product-card .badge {
                font-size: 0.65rem;
            }
        }
        
        @media (min-width: 576px) and (max-width: 767px) {
            .product-card .card-img-top {
                height: 140px;
                object-fit: cover;
            }
            
            .product-card .card-body {
                min-height: 160px;
            }
        }
        
        @media (min-width: 768px) {
            .product-card .card-img-top {
                height: 180px;
                object-fit: cover;
            }
            
            .product-card .card-body {
                min-height: 180px;
            }
        }
    `;
    document.head.appendChild(productStyle);
  }

  setupAgregarButtons() {
    const agregarButtons = document.querySelectorAll(".agregar-btn");
    agregarButtons.forEach((button) => {
      button.addEventListener("click", (event) => {
        const productoId = button.getAttribute("data-producto-id");
        this.agregarProducto(productoId, button);
      });
    });
  }

  agregarProducto(productoId, buttonElement = null) {
    if (!this.mesaSeleccionada) {
      alert("Por favor, selecciona una mesa antes de agregar productos.");
      return;
    }

    let productoCard = null;
    if (buttonElement) {
      productoCard = buttonElement.closest(".product-card");
    } else {
      productoCard = document
        .querySelector(`.agregar-btn[data-producto-id="${productoId}"]`)
        ?.closest(".product-card");
    }
    if (!productoCard) {
      alert("No se pudo encontrar la tarjeta del producto.");
      return;
    }
    const nombre = productoCard.querySelector(".card-title")?.textContent || "";
    const precioText =
      productoCard.querySelector(".precio-producto")?.textContent || "";
    const precio = parseFloat(precioText.replace("$", "")) || 0;
    const imagen = productoCard.querySelector("img")?.getAttribute("src") || "";

    const idStr = String(productoId);
    const existente = this.carrito.find((item) => String(item.id) === idStr);
    if (existente) {
      existente.cantidad += 1;
    } else {
      this.carrito.push({
        id: idStr,
        nombre,
        precio,
        imagen,
        cantidad: 1,
      });
    }

    sessionStorage.setItem("carrito", JSON.stringify(this.carrito));
    this.actualizarCarritoUI();
  }

  actualizarCarritoUI() {
    const user = document.querySelector("#menAcargo")?.textContent;
    const split = user ? user.split("-") : [];
    const meseroId = split.length > 0 ? split[0].trim() : "";
    const cartCount = document.getElementById("cart-count");
    const cartList = document.getElementById("cart-list");
    const emptyCart = document.getElementById("empty-cart");
    const cartFooter = document.getElementById("cart-footer");
    const cartTotal = document.getElementById("cart-total");
    const textAreaAnotaciones = document.getElementById("textAreaAnotaciones");

    const totalCantidad = this.carrito.reduce(
      (sum, item) => sum + item.cantidad,
      0
    );
    cartCount.textContent = totalCantidad;

    if (this.carrito.length === 0) {
      emptyCart.style.display = "";
      cartFooter.style.display = "none";
      cartList.innerHTML = "";
      textAreaAnotaciones.value = "";
      cartTotal.textContent = "$0.00";
      sessionStorage.setItem("carrito", JSON.stringify(this.carrito));
      return;
    } else {
      emptyCart.style.display = "none";
      cartFooter.style.display = "";
    }

    cartList.innerHTML = "";
    let total = 0;
    this.carrito.forEach((item) => {
      total += item.precio * item.cantidad;
      const div = document.createElement("div");
      div.className = "d-flex align-items-center border-bottom py-2 gap-2 m-2";
      div.innerHTML = `
                <img src="${item.imagen}" alt="${
        item.nombre
      }" style="width:40px;height:40px;object-fit:cover;border-radius:6px;">
                <div class="flex-grow-1">
                    <div class="fw-bold">${item.nombre}</div>
                    <div class="text-muted small">$${item.precio.toFixed(
                      2
                    )} x ${item.cantidad}</div>
                </div>
                <div class="d-flex align-items-center gap-1">
                    <button class="btn btn-sm btn-outline-secondary btn-restar" data-id="${
                      item.id
                    }"><i class="fas fa-minus"></i></button>
                    <span class="mx-1">${item.cantidad}</span>
                    <button class="btn btn-sm btn-outline-secondary btn-sumar" data-id="${
                      item.id
                    }"><i class="fas fa-plus"></i></button>
                    <button class="btn btn-sm btn-outline-danger btn-eliminar" data-id="${
                      item.id
                    }"><i class="fas fa-trash"></i></button>
                </div>
            `;
      cartList.appendChild(div);
    });
    cartTotal.textContent = `$${total.toFixed(2)}`;

    sessionStorage.setItem("carrito", JSON.stringify(this.carrito));

    cartList.querySelectorAll(".btn-sumar").forEach((btn) => {
      btn.onclick = (e) => {
        e.preventDefault();
        const id = btn.getAttribute("data-id");
        const item = this.carrito.find((i) => String(i.id) === String(id));
        if (item) {
          item.cantidad += 1;
          sessionStorage.setItem("carrito", JSON.stringify(this.carrito));
          this.actualizarCarritoUI();
        }
      };
    });

    cartList.querySelectorAll(".btn-restar").forEach((btn) => {
      btn.onclick = (e) => {
        e.preventDefault();
        const id = btn.getAttribute("data-id");
        const item = this.carrito.find((i) => String(i.id) === String(id));
        if (item && item.cantidad > 1) {
          item.cantidad -= 1;
          sessionStorage.setItem("carrito", JSON.stringify(this.carrito));
          this.actualizarCarritoUI();
        } else if (item) {
          this.carrito = this.carrito.filter(
            (i) => String(i.id) !== String(id)
          );
          sessionStorage.setItem("carrito", JSON.stringify(this.carrito));
          this.actualizarCarritoUI();
        }
      };
    });

    cartList.querySelectorAll(".btn-eliminar").forEach((btn) => {
      btn.onclick = (e) => {
        e.preventDefault();
        const id = btn.getAttribute("data-id");
        this.carrito = this.carrito.filter((i) => String(i.id) !== String(id));
        sessionStorage.setItem("carrito", JSON.stringify(this.carrito));
        this.actualizarCarritoUI();
      };
    });

    document.getElementById("btnClearCart").onclick = () => {
      Swal.fire({
        title: "¿Estás seguro?",
        text: "¿Deseas vaciar el carrito?",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Sí, vaciar carrito",
        cancelButtonText: "Cancelar",
      }).then((result) => {
        if (this.esEdicion) {
          Swal.fire({
            icon: "warning",
            title: "No puedes vaciar el carrito en modo edición",
            text: "Por favor, edita los productos directamente.",
            confirmButtonText: "Aceptar",
          });
          return;
        }
        if (result.isConfirmed) {
          this.carrito = [];
          sessionStorage.setItem("carrito", JSON.stringify(this.carrito));
          this.actualizarCarritoUI();
          Swal.fire("Carrito vaciado", "", "success");
        }
      });
    };

    document.getElementById("btnConfirmOrder").onclick = () => {
      if (this.carrito.length === 0) {
        Swal.fire({
          icon: "warning",
          title: "Carrito vacío",
          text: "Por favor, agrega productos al carrito antes de confirmar la orden.",
          confirmButtonText: "Aceptar",
        });
        return;
      }

      const tituloModal = this.esEdicion
        ? "¿Actualizar orden?"
        : "¿Confirmar orden?";
      const textoModal = this.esEdicion
        ? "¿Deseas actualizar esta orden?"
        : "¿Deseas confirmar la orden?";
      const textoBoton = this.esEdicion
        ? "Sí, actualizar orden"
        : "Sí, confirmar orden";

      Swal.fire({
        title: tituloModal,
        text: textoModal,
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: textoBoton,
        cancelButtonText: "Cancelar",
      }).then((result) => {
        if (result.isConfirmed) {
          if (this.esEdicion) {
            this.actualizarOrden(total, meseroId, textAreaAnotaciones.value);
          } else {
            this.crearOrden(total, meseroId, textAreaAnotaciones.value);
          }
        }
      });
    };
  }

  crearOrden(total, meseroId, notas) {
    const orden = {
      numero_mesa: this.mesaId,
      user_id: parseInt(meseroId),
      productos: this.carrito,
      estado: "Pendiente",
      notas: notas,
      total: total.toFixed(2),
    };

    fetch("api/ordenes.php?action=create", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify(orden),
    })
      .then((response) => response.json())
      .then((data) => {
        if (data.success) {
          Swal.fire("Orden confirmada", "", "success");
          this.carrito = [];
          sessionStorage.setItem("carrito", JSON.stringify(this.carrito));
          this.actualizarCarritoUI();
          setTimeout(() => {
            window.location.href = "ordenes.php";
          }, 2000);
        } else {
          Swal.fire("Error al confirmar la orden", data.message, "error");
        }
      })
      .catch((error) => {
        console.error("Error al crear la orden:", error);
        Swal.fire(
          "Error del servidor",
          "No se pudo crear la orden. Inténtalo de nuevo más tarde.",
          "error"
        );
      });
  }

  actualizarOrden(total, meseroId, notas) {
    const ordenActualizada = {
      action: "update",
      id: this.ordenId,
      numero_mesa: this.mesaId,
      user_id: parseInt(meseroId),
      productos: this.carrito,
      estado: this.estado,
      notas: notas,
      total: total.toFixed(2),
    };

    fetch("api/ordenes.php", {
      method: "PUT",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify(ordenActualizada),
    })
      .then((response) => response.json())
      .then((data) => {
        if (data.success) {
          Swal.fire(
            "Orden actualizada",
            "La orden se ha actualizado correctamente",
            "success"
          );
          setTimeout(() => {
            window.location.href = "ordenes.php";
          }, 2000);
        } else {
          Swal.fire("Error al actualizar la orden", data.message, "error");
        }
      })
      .catch((error) => {
        console.error("Error al actualizar la orden:", error);
        Swal.fire(
          "Error del servidor",
          "No se pudo actualizar la orden. Inténtalo de nuevo más tarde.",
          "error"
        );
      });
  }

  async searchProducts() {
    const searchInput = document.querySelector("#searchProduct");
    const filter = searchInput.value.trim();

    if (filter.length === 0) {
      this.obtenerProductosPorCategoria(0);
      return;
    }

    try {
      const response = await fetch(
        `api/productos.php?action=searchByName&nombre=${encodeURIComponent(
          filter
        )}`
      );
      const result = await response.json();

      if (result.data.length === 0) {
        const container = document.getElementById("product-list");
        container.innerHTML = `
                <div class="col-12">
                    <div class="alert alert-warning">
                        No se encontraron productos que coincidan con "${filter}"
                    </div>
                </div>
            `;
        return;
      }

      if (result.success) {
        this.mostrarProductos(result.data);
      } else {
        const container = document.getElementById("product-list");
        container.innerHTML = `
                <div class="col-12">
                    <div class="alert alert-warning">
                        No se encontraron productos que coincidan con "${filter}"
                    </div>
                </div>
            `;
      }
    } catch (error) {
      console.error("Error en la búsqueda:", error);
      const container = document.getElementById("product-list");
      container.innerHTML = `
            <div class="col-12">
                <div class="alert alert-danger">
                    Error al realizar la búsqueda. Por favor, inténtalo de nuevo.
                </div>
            </div>
        `;
    }
  }

  setupSearchFunctionality() {
    const searchInput = document.querySelector("#searchProduct");
    const searchButton = document.querySelector("#btnSearchProduct");

    if (!searchInput) {
      console.warn("Campo de búsqueda no encontrado");
      return;
    }

    let searchTimeout;
    searchInput.addEventListener("input", () => {
      clearTimeout(searchTimeout);
      searchTimeout = setTimeout(() => {
        const searchTerm = searchInput.value.trim();
        if (searchTerm.length >= 2) {
          this.searchProducts();
        } else if (searchTerm.length === 0) {
          const categoriaButtons = document.querySelectorAll(".categoria-btn");
          categoriaButtons.forEach((btn) => btn.classList.remove("active"));
          document.getElementById("categoria-todos")?.classList.add("active");
          this.obtenerProductosPorCategoria(0);
        }
      }, 300);
    });

    searchInput.addEventListener("keypress", (e) => {
      if (e.key === "Enter") {
        e.preventDefault();
        clearTimeout(searchTimeout);
        this.searchProducts();
      }
    });

    if (searchButton) {
      searchButton.addEventListener("click", (e) => {
        e.preventDefault();
        this.searchProducts();
      });
    }
  }

  limpiarBusqueda() {
    const searchInput = document.querySelector("#searchProduct");
    if (searchInput) {
      searchInput.value = "";
      const categoriaButtons = document.querySelectorAll(".categoria-btn");
      categoriaButtons.forEach((btn) => btn.classList.remove("active"));
      document.getElementById("categoria-todos")?.classList.add("active");
      this.obtenerProductosPorCategoria(0);
    }
  }
}
