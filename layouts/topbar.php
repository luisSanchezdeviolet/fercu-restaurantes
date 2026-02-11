<?php
require_once 'layouts/session.php';

$userData = getUserData();
?>

<div class="navbar-custom">
    <div class="topbar container-fluid">
        <div class="d-flex align-items-center gap-lg-2 gap-1">

           
            <div class="logo-topbar">
                
                <a href="index.php" class="logo-light">
                    <span class="logo-lg">
                        <img src="assets/images/logo.png" alt="logo">
                    </span>
                    <span class="logo-sm">
                        <img src="assets/images/logo-sm.png" alt="small logo">
                    </span>
                </a>

                
                <a href="index.php" class="logo-dark">
                    <span class="logo-lg">
                        <img src="assets/images/logo-dark.png" alt="dark logo">
                    </span>
                    <span class="logo-sm">
                        <img src="assets/images/logo-sm.png" alt="small logo">
                    </span>
                </a>
            </div>

            
            <button class="button-toggle-menu">
                <i class="ri-menu-2-fill"></i>
            </button>

            
            <button class="navbar-toggle" data-bs-toggle="collapse" data-bs-target="#topnav-menu-content">
                <div class="lines">
                    <span></span>
                    <span></span>
                    <span></span>
                </div>
            </button>
        </div>

        <ul class="topbar-menu d-flex align-items-center gap-3">

            <li class="d-none d-sm-inline-block">
                <div class="nav-link" id="light-dark-mode" data-bs-toggle="tooltip" data-bs-placement="left" title="Theme Mode">
                    <i class="ri-moon-line fs-22"></i>
                </div>
            </li>


            <li class="d-none d-md-inline-block">
                <a class="nav-link" href="" data-toggle="fullscreen">
                    <i class="ri-fullscreen-line fs-22"></i>
                </a>
            </li>

            <li class="dropdown">
                <a class="nav-link dropdown-toggle arrow-none nav-user px-2" data-bs-toggle="dropdown" href="#" role="button" aria-haspopup="false" aria-expanded="false">
                    <span class="account-user-avatar">
                        <img src="assets/images/users/usuario.png" alt="user-image" width="32" class="rounded-circle">
                    </span>
                    <span class="d-lg-flex flex-column gap-1 d-none">
                        <h5 class="my-0">
                            <?php echo htmlspecialchars($userData['name']); ?>
                        </h5>
                        <h6 class="my-0 fw-normal"><?php echo htmlspecialchars($userData['role']); ?></h6>
                    </span>
                </a>
                <div class="dropdown-menu dropdown-menu-end dropdown-menu-animated profile-dropdown">
                    
                    <div class=" dropdown-header noti-title">
                        <h6 class="text-overflow m-0">Hola, Bienvenido, <?php echo htmlspecialchars($userData['name']); ?>!</h6>
                    </div>

                    <a href="login.php" class="dropdown-item" id="btnLogout">
                        <i class="ri-logout-box-line fs-18 align-middle me-1"></i>
                        <span>Logout</span>
                    </a>
                </div>
            </li>
        </ul>
    </div>

</div>
<div id="myModal" class="modal fade">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-body">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <div class="text-center">
                    <h4 class="mt-0">¿Estás seguro que quieres cerrar sesión?</h4>
                    <p class="text-muted">Al hacer clic en "Cerrar sesión", se cerrará tu sesión actual.</p>
                </div>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-primary">Cerrar sesión</button></div>
        </div>
    </div>
</div>
<script>
        var myModal = document.getElementById("myModal");
        var okBtn = myModal.querySelector(".modal-footer .btn");
        var closeBtn = myModal.querySelector(".close");
        const btnLogout = document.getElementById("btnLogout");

        function showModal() {
            myModal.classList.add("show");
            myModal.style.display = "block";
            myModal.removeAttribute("aria-hidden");
            myModal.setAttribute("aria-modal", "true");
            myModal.style.backgroundColor = "rgba(0,0,0,0.5)";
            document.body.classList.add("modal-open");
        }

        function hideModal() {
            myModal.classList.remove("show");
            myModal.style.display = "none";
            myModal.setAttribute("aria-hidden", "true");
            myModal.removeAttribute("aria-modal");
            myModal.style.backgroundColor = "";
            document.body.classList.remove("modal-open");
        }

        okBtn.addEventListener("click", function() {
            window.location.href = "layouts/logout.php";
        });

        closeBtn.addEventListener("click", function() {
            hideModal();
        });

        myModal.addEventListener("click", function(e) {
            if (e.target === myModal) {
                hideModal();
            }
        });

        btnLogout.addEventListener("click", function(e) {
            e.preventDefault();
            showModal();
        });

</script>