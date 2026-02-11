<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fercu Restaurante - Sistema de Gestión para Restaurantes</title>
    <link rel="shortcut icon" href="./assets/images/logo.png" type="image/x-icon">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700&family=Playfair+Display:wght@500;600;700&display=swap" rel="stylesheet"> 
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="assets/css/landing.css">
</head>
<body>
    
    <header class="header contenedor">
        <div class="header__logo">
            <img src="./assets/images/logo.webp" alt="Fercu Restaurante" height="50">
        </div>

        <nav class="navegacion">
            <a href="login.php" class="navegacion__link">Iniciar Sesión</a>
            <a href="#" class="navegacion__link navegacion__link--registrar">Obtener Demo</a>
        </nav>
    </header>

    <section class="formulario">
        <div class="contenedor">
            <div class="formulario__grid">
            <div class="formulario__contenido">
                <h2 class="formulario__heading">Control Total de tu Restaurante en una Sola Plataforma</h2>
                <p class="formulario__descripcion">Gestiona mesas, órdenes, inventario y caja de forma eficiente</p>
                
                <h3 class="formulario__heading formulario__text">Prueba gratis por 15 días</h3>
                <button class="formulario__submit" id="btn-mas-info">Más información</button>
            </div>
            <div class="formulario__imagen-contenedor">
                <img class="formulario__imagen" src="assets/images/restaurant-hero.svg" alt="imagen restaurante">
            </div>
        </div>
        </div>
    </section>

    <section class="pasos">
        <div class="pasos__wave-container">
            <svg viewBox="0 0 1200 60" preserveAspectRatio="none" class="pasos__wave">
                <path d="M0,60 L0,0 L1200,0 L1200,60 Z"></path>
            </svg>
        </div>

        <div class="pasos__contenido">
            <h2 class="pasos__heading" id="pasos-heading">¿Cómo empezar? <span class="pasos__heading--descripcion">tan fácil como 1, 2 y 3</span></h2>
            <div class="pasos__grid contenedor">
                <div class="pasos__paso">
                    <div class="pasos__numero">1</div>
                    <h3 class="pasos__titulo">Regístrate Gratis</h3>
                    <p class="pasos__texto">Crea tu cuenta en 2 minutos. Obtén 15 días de prueba gratuita sin tarjeta de crédito.</p>
                </div>
                <div class="pasos__paso">
                    <div class="pasos__numero">2</div>
                    <h3 class="pasos__titulo">Configura tu Restaurante</h3>
                    <p class="pasos__texto">Añade tus mesas, menú, ingredientes y personal. Todo desde un panel intuitivo.</p>
                </div>
                <div class="pasos__paso">
                    <div class="pasos__numero">3</div>
                    <h3 class="pasos__titulo">¡A Trabajar!</h3>
                    <p class="pasos__texto">Empieza a tomar órdenes, gestionar mesas y controlar tu operación profesionalmente.</p>
                </div>
            </div>
        </div>
    </section>
    
    <section class="testimoniales">
        <div class="contenedor">
            <h2 class="testimoniales__heading" id="testimonios">Restaurantes que confían en nosotros</h2>

            <div class="testimoniales__grid">
            <div class="testimonial">
                <header class="testimonial__header">
                    <div class="testimonial__imagen">
                        <i class="bi bi-shop"></i>
                    </div>
        
                    <div class="testimonial__informacion">
                        <p class="testimonial__autor">Restaurante "La Cocina de María"</p>
                        <div class="testimonial__calificacion">
                            <i class="bi bi-star-fill text-warning"></i>
                            <i class="bi bi-star-fill text-warning"></i>
                            <i class="bi bi-star-fill text-warning"></i>
                            <i class="bi bi-star-fill text-warning"></i>
                            <i class="bi bi-star-fill text-warning"></i>
                        </div>
                    </div>
                </header>
        
                <blockquote class="testimonial__texto">
                    "Desde que usamos Fercu Restaurante, el servicio es más rápido y sin errores. Los meseros toman las órdenes digitalmente y la cocina las recibe al instante. ¡Nuestros clientes están más contentos!"
                </blockquote>
            </div>
        
            <div class="testimonial">
                <header class="testimonial__header">
                    <div class="testimonial__imagen">
                        <i class="bi bi-shop"></i>
                    </div>
        
                    <div class="testimonial__informacion">
                        <p class="testimonial__autor">Taquería "El Buen Sabor"</p>
                        <div class="testimonial__calificacion">
                            <i class="bi bi-star-fill text-warning"></i>
                            <i class="bi bi-star-fill text-warning"></i>
                            <i class="bi bi-star-fill text-warning"></i>
                            <i class="bi bi-star-fill text-warning"></i>
                            <i class="bi bi-star-fill text-warning"></i>
                        </div>
                    </div>
                </header>
        
                <blockquote class="testimonial__texto">
                    "El control de mesas nos cambió la vida. Ya no hay confusiones sobre qué mesa ordenó qué. El cierre de caja es automático y sin errores. Lo recomiendo 100%."
                </blockquote>
            </div>
        
            <div class="testimonial">
                <header class="testimonial__header">
                    <div class="testimonial__imagen">
                        <i class="bi bi-shop"></i>
                    </div>
        
                    <div class="testimonial__informacion">
                        <p class="testimonial__autor">Café "Aroma y Sabor"</p>
                        <div class="testimonial__calificacion">
                            <i class="bi bi-star-fill text-warning"></i>
                            <i class="bi bi-star-fill text-warning"></i>
                            <i class="bi bi-star-fill text-warning"></i>
                            <i class="bi bi-star-fill text-warning"></i>
                            <i class="bi bi-star-fill text-warning"></i>
                        </div>
                    </div>
                </header>
        
                <blockquote class="testimonial__texto">
                    "Llevamos el control de inventario de ingredientes y eso nos ayudó a reducir desperdicios en un 30%. Además, los reportes nos muestran qué platillos se venden más. Excelente herramienta."
                </blockquote>
            </div>
        </div>
        </div>        
    </section>

    <main class="favoritos">
        <h2 class="favoritos__heading" id="funciones">Funcionalidades diseñadas para tu restaurante</h2>

        <div class="favoritos__grid contenedor">
            <div class="favorito">
                <div class="favorito__grid">
                    <div class="favorito__imagen">
                        <i class="bi bi-grid-3x3-gap-fill"></i>
                    </div>
                    <div class="favorito__contenido">
                        <h3 class="favorito__nombre">Control de Mesas</h3>
                        <p class="favorito__descripcion">Visualiza el estado de tus mesas en tiempo real: disponibles, ocupadas o reservadas. Administra capacidad y rotación eficientemente.</p>
                    </div>
                </div>
            </div>

            <div class="favorito">
                <div class="favorito__grid">
                    <div class="favorito__imagen">
                        <i class="bi bi-receipt"></i>
                    </div>
                    <div class="favorito__contenido">
                        <h3 class="favorito__nombre">Gestión de Órdenes</h3>
                        <p class="favorito__descripcion">Toma comandas digitalmente, asigna meseros, y rastrea cada orden desde que se toma hasta que se entrega al cliente.</p>
                    </div>
                </div>
            </div>

            <div class="favorito">
                <div class="favorito__grid">
                    <div class="favorito__imagen">
                        <i class="bi bi-egg-fried"></i>
                    </div>
                    <div class="favorito__contenido">
                        <h3 class="favorito__nombre">Menú Digital</h3>
                        <p class="favorito__descripcion">Administra tu menú con fotos, precios, categorías y disponibilidad. Actualiza platillos en tiempo real.</p>
                    </div>
                </div>
            </div>

            <div class="favorito">
                <div class="favorito__grid">
                    <div class="favorito__imagen">
                        <i class="bi bi-box-seam-fill"></i>
                    </div>
                    <div class="favorito__contenido">
                        <h3 class="favorito__nombre">Inventario de Ingredientes</h3>
                        <p class="favorito__descripcion">Controla el stock de ingredientes, recibe alertas de bajo inventario y actualiza automáticamente al cerrar caja.</p>
                    </div>
                </div>
            </div>

            <div class="favorito">
                <div class="favorito__grid">
                    <div class="favorito__imagen">
                        <i class="bi bi-cash-stack"></i>
                    </div>
                    <div class="favorito__contenido">
                        <h3 class="favorito__nombre">Cierre de Caja</h3>
                        <p class="favorito__descripcion">Genera reportes automáticos de ventas diarias, platillos más vendidos, métodos de pago y totales consolidados.</p>
                    </div>
                </div>
            </div>

            <div class="favorito">
                <div class="favorito__grid">
                    <div class="favorito__imagen">
                        <i class="bi bi-people-fill"></i>
                    </div>
                    <div class="favorito__contenido">
                        <h3 class="favorito__nombre">Gestión de Personal</h3>
                        <p class="favorito__descripcion">Administra meseros y personal. Controla permisos y monitorea el rendimiento de cada empleado.</p>
                    </div>
                </div>
            </div>

            <div class="favorito">
                <div class="favorito__grid">
                    <div class="favorito__imagen">
                        <i class="bi bi-printer-fill"></i>
                    </div>
                    <div class="favorito__contenido">
                        <h3 class="favorito__nombre">Comandas de Cocina</h3>
                        <p class="favorito__descripcion">Imprime comandas directamente a la cocina con impresoras térmicas. Separa por estaciones y optimiza tiempos.</p>
                    </div>
                </div>
            </div>

            <div class="favorito">
                <div class="favorito__grid">
                    <div class="favorito__imagen">
                        <i class="bi bi-bar-chart-fill"></i>
                    </div>
                    <div class="favorito__contenido">
                        <h3 class="favorito__nombre">Reportes y Estadísticas</h3>
                        <p class="favorito__descripcion">Analiza ventas por día, semana o mes. Identifica platillos populares y optimiza tu operación con datos reales.</p>
                    </div>
                </div>
            </div>

            <div class="favorito">
                <div class="favorito__grid">
                    <div class="favorito__imagen">
                        <i class="bi bi-clock-history"></i>
                    </div>
                    <div class="favorito__contenido">
                        <h3 class="favorito__nombre">Seguimiento de Órdenes</h3>
                        <p class="favorito__descripcion">Estados de orden: Pendiente → En preparación → Lista → Entregada. Control total del flujo de servicio.</p>
                    </div>
                </div>
            </div>
        </div>

    </main>

    <section class="repartidores">
        <h2 class="repartidores__heading">Comienza gratis hoy mismo</h2>

        <div class="repartidores__grid contenedor">
            <div class="repartidores__imagen">
                <img src="assets/images/dashboard-preview.jpg" alt="dashboard preview">
            </div>

            <div class="repartidores__contenido">
                <p class="repartidores__texto"><strong>15 días de prueba gratuita</strong> para que explores todas las funcionalidades sin limitaciones. No requiere tarjeta de crédito, sin pagos anticipados, sin compromisos.</p>
                <p class="repartidores__texto">Regístrate en menos de 2 minutos y empieza hoy mismo a transformar la forma en que administras tu restaurante. Gestiona mesas, órdenes, inventario y caja desde una sola plataforma.</p>
                <p class="repartidores__texto"><strong>Al finalizar tu prueba,</strong> podrás elegir el plan que mejor se adapte a tu restaurante y continuar disfrutando de todas las ventajas del sistema.</p>

                <a href="#" class="repartidores__enlace">Solicita tu demo gratuita</a>
            </div>
        </div>

    </section>

    <section class="planes seccion">
        <div class="contenedor">
            <h2>¿Te gustó el sistema? Adquiere un plan profesional</h2>
            <p class="descripcion">Inicia gratis, contrata un plan en cuanto estés listo</p>
            <div class="planes-activos">
                <div class="toggle-background"></div>
                <button class="mensualidad active" data-plan="mensual">Mes</button>
                <button class="anualidad" data-plan="anual">Año</button>
            </div>
            <div class="nuestros-planes">
                <div class="plan inicio">
                    <h3>Básico</h3>
                    <p class="precio" data-mensual="$399.00 Mes" data-anual="$3,990.00 Año">$399.00 Mes</p>
                    <ul class="listado">
                        <li>Hasta 10 mesas</li>
                        <li>Hasta 3 usuarios</li>
                        <li>Órdenes ilimitadas</li>
                        <li>Gestión de inventario</li>
                        <li>Reportes básicos</li>
                        <li>Soporte por email</li>
                        <li>Actualizaciones incluidas</li>
                    </ul>
                    <button class="boton-plan" 
                    data-tipo="basico" 
                    data-mensual="399" 
                    data-anual="3990"
                    data-plan-id-mensual="2"
                    data-plan-id-anual="3"
                    data-ahorro="17%">Inscribirme</button>
                </div>

                <div class="plan pro">
                    <h3>Professional</h3>
                    <p class="precio" data-mensual="$899.00 Mes" data-anual="$8,630.00 Año">$899.00 Mes</p>
                    <ul class="listado">
                        <li>Mesas ilimitadas</li>
                        <li>Usuarios ilimitados</li>
                        <li>Reportes avanzados</li>
                        <li>Multi-sucursales</li>
                        <li>Acceso a API</li>
                        <li>Integraciones</li>
                        <li>Soporte prioritario 24/7</li>
                        <li>Capacitación incluida</li>
                    </ul>
                    <button class="boton-plan" 
                    data-tipo="professional" 
                    data-mensual="899" 
                    data-anual="8630"
                    data-plan-id-mensual="4"
                    data-plan-id-anual="5"
                    data-ahorro="20%">Inscribirme</button>
                </div>
            </div>
        </div>
    </section>

    <!-- Modal con planes suscripcion -->
    <?php require_once 'modal-planes.php'; ?>

    <footer class="footer">
        <div class="footer__grid contenedor">
            <div class="footer__widget">
                <h3 class="footer__heading">Sobre Fercu Restaurante</h3>
                <p class="footer__texto">Sistema completo de gestión para restaurantes. Controla mesas, órdenes, menú, inventario de ingredientes, personal y caja desde una sola plataforma en la nube. Paga solo lo que uses con planes flexibles mensuales o anuales.</p>
            </div>
    
            <div class="footer__widget">
                <h3 class="footer__heading">Navegación</h3>
                <nav class="footer__nav">
                    <a href="#funciones" class="footer__link">Funciones del sistema</a>
                    <a href="#testimonios" class="footer__link">Testimonios</a>
                    <a href="login.php" class="footer__link">Iniciar Sesión</a>
                </nav>
            </div>
    
            <div class="footer__widget">
                <h3 class="footer__heading">Contacto y Soporte</h3>
                <p class="footer__texto">
                    ¿Tienes dudas sobre el sistema?<br>
                    Nuestro equipo está listo para ayudarte.<br><br>
                    ✉️ Email: <a href="mailto:contacto@fercupuntodeventa.com" class="footer__link">contacto@fercupuntodeventa.com</a><br>
                    📱 WhatsApp: <a href="https://wa.me/523310732086" class="footer__link">+52 33 1073 2086</a>
                </p>
            </div>
        </div>
    
        <p class="footer__copyright">
            © 2025 Fercu Restaurante - Sistema de Gestión para Restaurantes. Todos los derechos reservados.
        </p>
    </footer>

    <?php require 'register.php'; ?>
    <script>
        const base_url = window.location.origin + '/'; 
    </script>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
    <script src="./assets/js/landing.js"></script>

</body>
</html>


