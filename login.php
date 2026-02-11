<?php 
session_start();

if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    header("Location: index.php");
    exit();
}

include 'layouts/main.php'; 
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Iniciar Sesion | Estudiovioleta</title>
    <?php include 'layouts/title-meta.php'; ?>
    <?php include 'layouts/head-css.php'; ?>
</head>

<body class="authentication-bg position-relative">

<?php include 'layouts/background.php'; ?>

    <div class="account-pages pt-2 pt-sm-5 pb-4 pb-sm-5 position-relative">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-xxl-4 col-lg-5">
                    <div class="card">

                        <div class="card-header py-4 text-center bg-primary">
                            <a href="index.php">
                                <span><img src="assets/images/logo.png" alt="logo" height="52"></span>
                            </a>
                        </div>

                        <div class="card-body p-4">
                            <div class="text-center w-75 m-auto">
                                <h4 class="text-dark-50 text-center pb-0 fw-bold">Iniciar Sesion</h4>
                                <p class="text-muted mb-4">Ingresa las credenciales proporcionadas por el administrador.</p>
                            </div>

                            <div id="alert-message" class="alert" style="display: none;"></div>

                            <form id="loginForm">
                                <div class="mb-3">
                                    <label for="emailaddress" class="form-label">Correo Electronico</label>
                                    <input class="form-control" type="email" id="emailaddress" name="email" 
                                           placeholder="Ingresa tu correo electronico">
                                </div>

                                <div class="mb-3">
                                    <label for="password" class="form-label">Contraseña</label>
                                    <div class="input-group input-group-merge">
                                        <input type="password" id="password" name="password" class="form-control" 
                                               placeholder="Ingresa tu contraseña">
                                        <div class="input-group-text" data-password="false">
                                            <span class="password-eye"></span>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3 mb-0 text-center">
                                    <button class="btn btn-primary" type="submit" id="loginBtn">
                                        <span id="loginText">Iniciar Sesion</span>
                                        <span id="loginSpinner" class="spinner-border spinner-border-sm ms-2" style="display: none;"></span>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-12 text-center">
                            <p class="text-muted bg-body">No tienes cuenta? <b>Solicitala a tu administrador.</b></p>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <footer class="footer footer-alt fw-medium">
        <span class="bg-body">
            <script>document.write(new Date().getFullYear())</script> © Estudiovioleta - Software de Restaurante
        </span>
    </footer>

    <?php include 'layouts/footer-scripts.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const loginForm = document.getElementById('loginForm');
    const loginBtn = document.getElementById('loginBtn');
    const loginText = document.getElementById('loginText');
    const loginSpinner = document.getElementById('loginSpinner');
    const alertMessage = document.getElementById('alert-message');

    loginForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        loginBtn.disabled = true;
        loginText.textContent = 'Iniciando...';
        loginSpinner.style.display = 'inline-block';
        hideAlert();

        const formData = new FormData(loginForm);

        fetch('./presentation/auth-login.php', {
            method: 'POST',
            body: formData,
            credentials: 'same-origin'
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.text();
        })
        .then(text => {
            try {
                const data = JSON.parse(text);
                if (data.success) {
                    showAlert('success', data.message);
                    setTimeout(() => {
                        window.location.href = data.redirect || 'index.php';
                    }, 1000);
                } else {
                    showAlert('danger', data.message);
                    resetButton();
                }
            } catch (parseError) {
                console.error('JSON Parse Error:', parseError);
                console.error('Response text:', text);
                showAlert('danger', 'Error en la respuesta del servidor');
                resetButton();
            }
        })
        .catch(error => {
            console.error('Fetch Error:', error);
            showAlert('danger', 'Error de conexión. Intenta nuevamente.');
            resetButton();
        });
    });

    function resetButton() {
        loginBtn.disabled = false;
        loginText.textContent = 'Iniciar Sesion';
        loginSpinner.style.display = 'none';
    }

    function showAlert(type, message) {
        alertMessage.className = `alert alert-${type}`;
        alertMessage.innerHTML = `<i class="${type === 'success' ? 'fas fa-check-circle' : 'fas fa-exclamation-circle'}"></i>  ${message}`;
        alertMessage.style.display = 'block';
    }

    function hideAlert() {
        alertMessage.style.display = 'none';
    }

    document.querySelector('[data-password]').addEventListener('click', function() {
        const passwordField = document.getElementById('password');
        const eyeIcon = this.querySelector('.password-eye');
        
        if (passwordField.type === 'password') {
            passwordField.type = 'text';
        } else {
            passwordField.type = 'password';
        }
    });
});
</script>
    <script src="assets/js/app.min.js"></script>
</body>
</html>