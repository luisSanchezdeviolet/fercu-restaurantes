<!-- ========== Left Sidebar Start ========== -->
<div class="leftside-menu">
  <!-- Brand Logo Light -->
  <a href="index.php" class="logo logo-light">
    <span class="logo-lg">
      <img src="assets/images/logo.png" alt="logo" />
    </span>
    <span class="logo-sm">
      <img src="assets/images/logo-sm.png" alt="small logo" />
    </span>
  </a>

  <a href="index.php" class="logo logo-dark">
    <span class="logo-lg">
      <img src="assets/images/logo-dark.png" alt="dark logo" />
    </span>
    <span class="logo-sm">
      <img src="assets/images/logo-sm.png" alt="small logo" />
    </span>
  </a>

  <div
    class="button-sm-hover"
    data-bs-toggle="tooltip"
    data-bs-placement="right"
    title="Show Full Sidebar"
  >
    <i class="ri-checkbox-blank-circle-line align-middle"></i>
  </div>

  <div class="button-close-fullsidebar">
    <i class="ri-close-fill align-middle"></i>
  </div>

  <div class="h-100" id="leftside-menu-container" data-simplebar>
    <ul class="side-nav">
      <?php if (isSuperAdmin()): ?>
      <!-- Sección Super Admin SAAS -->
      <li class="side-nav-title" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 10px; border-radius: 5px; margin: 10px;">
        <i class="mdi mdi-shield-crown"></i> SUPER ADMIN SAAS
      </li>
      <li class="side-nav-item">
        <a href="saas-admin.php" class="side-nav-link" style="background-color: rgba(102, 126, 234, 0.1);">
          <i class="mdi mdi-view-dashboard-variant" style="color: #667eea;"></i>
          <span style="color: #667eea; font-weight: 600;"> Panel SAAS </span>
        </a>
      </li>
      <li class="side-nav-title">Navegación Normal</li>
      <?php endif; ?>
      
      <?php if ($_SESSION['user_role'] === 'Administrador') { ?>
      <li class="side-nav-title">Navegación</li>

      <li class="side-nav-item">
      <a
        data-bs-toggle="collapse"
        href="#sidebarDashboards"
        aria-expanded="false"
        aria-controls="sidebarDashboards"
        class="side-nav-link"
      >
        <i class="ri-home-4-line"></i>
        <span class="badge bg-success float-end">2</span>
        <span> Dashboards </span>
      </a>
        <div class="collapse" id="sidebarDashboards">
        <ul class="side-nav-second-level">
        <li>
          <a href="caja.php"><i class="ri-cash-line"></i> Caja</a>
        </li>
        <li>
          <a href="ventas.php"><i class="ri-bar-chart-2-line"></i> Ventas</a>
        </li>
        </ul>
      </div>
      <?php } ?>
      </li>

      <li class="side-nav-title">Gestion</li>
      
      <?php if ($_SESSION['user_role'] === 'Administrador' && !isSuperAdmin()) { ?>
      <!-- Gestión de Suscripción (solo para empresas, no Super Admin) -->
      <li class="side-nav-item">
        <a href="subscription-manage.php" class="side-nav-link">
          <i class="mdi mdi-credit-card-outline"></i>
          <span> Mi Suscripción </span>
        </a>
      </li>
      <?php } ?>
      
      <?php if ($_SESSION['user_role'] === 'Administrador') { ?>
      <li class="side-nav-item">
      <a href="productos.php" class="side-nav-link">
        <i class="ri-shopping-basket-2-line"></i>
        <span> Productos </span>
      </a>
      </li>

      <li class="side-nav-item">
      <a href="personal.php" class="side-nav-link">
        <i class="ri-user-3-line"></i>
        <span> Personal </span>
      </a>
      </li>
      <li class="side-nav-item">
      <a href="inventario.php" class="side-nav-link">
        <i class="ri-archive-line"></i>
        <span> Inventario </span>
      </a>
      </li>
      <?php } ?>
      <li class="side-nav-item">
      <a href="ordenes.php" class="side-nav-link">
        <i class="ri-file-list-3-line"></i>
        <span> Ordenes </span>
      </a>
      </li>
      <?php if ($_SESSION['user_role'] === 'Administrador') { ?>
      <li class="side-nav-item">
      <a href="categorias.php" class="side-nav-link">
        <i class="ri-price-tag-3-line"></i>
        <span> Categorias </span>
      </a>
      </li>
      <li class="side-nav-item">
      <a href="mesas.php" class="side-nav-link">
        <i class="ri-table-line"></i>
        <span> Mesas </span>
      </a>
      </li>
      <?php } ?>
      <li class="side-nav-item">
      <a href="all.php" class="side-nav-link">
        <i class="ri-list-check-2"></i>
        <span> Lista de Ordenes </span>
      </a>
      </li>
      <li class="side-nav-item">
      <a href="menu.php" class="side-nav-link">
        <i class="ri-restaurant-2-line"></i>
        <span> Menú </span>
      </a>
      </li>
    </ul>
    <!--- End Sidemenu -->

    <div class="clearfix"></div>
  </div>
</div>
<!-- ========== Left Sidebar End ========== -->
