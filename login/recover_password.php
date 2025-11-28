<?php
session_start();
if (isset($_SESSION['status_message'])) {
    echo "<script>console.log('Status: " . $_SESSION['status_message'] . "');</script>";
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Mueblería ┃ Login</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@300;400;600&display=swap" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Nunito', sans-serif;
        }

        .login-container {
            background-color: #ffffff;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
            border-radius: 15px;
            overflow: hidden;
            height: 600px;
        }

        .animation-container {
            color: white;
            display: flex;
            justify-content: center;
            flex-direction: column;
            text-align: center;
            padding: 20px;
        }

        .furniture-icon {
            font-size: 4rem;
            opacity: 0;
            animation: fadeIn 1.5s forwards;
        }

        .furniture-icon:nth-child(1) { animation-delay: 0.3s; }
        .furniture-icon:nth-child(2) { animation-delay: 0.6s; }
        .furniture-icon:nth-child(3) { animation-delay: 0.9s; }
        .furniture-icon:nth-child(4) { animation-delay: 1.2s; }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .form-label {
            color: #6c757d;
        }

        .login-title {
            font-weight: 700;
        }

        .texto {
            font-weight: bold;
            font-size: 36px;
        }

        .custom-orange-bg {
            background-color: #ff8c00;
        }

        .custom-orange-text {
            color: #ff8c00;
        }

        .custom-orange-btn {
            background-color: #ff8c00;
            border-color: #ff8c00;
            color: white;
        }

        .custom-orange-btn:hover {
            background-color: #e67600;
            border-color: #e67600;
        }

        .toggle-pass {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #6c757d;
            font-size: 1rem;
            transition: color 0.3s;
        }

        .toggle-pass:hover {
            color: #000;
        }

        .form-control.is-valid,
        .form-control.is-invalid {
            background-image: none !important;
            padding-right: 0.75rem !important;
        }
    </style>
</head>

<body>
<div class="container d-flex align-items-center justify-content-center min-vh-100">
    <div class="row w-100">
        <div class="col-md-8 mx-auto">
            <div class="row login-container">

                <div class="col-md-6 d-none d-md-flex animation-container custom-orange-bg">
                    <h3 class="mb-3 texto">MATERIALES LA FLORIDA</h3>
                    <div class="d-flex justify-content-around w-100 mt-5">
                            <i class="fas fa-hard-hat furniture-icon"></i>
                            <i class="fas fa-tools furniture-icon"></i>
                            <i class="fas fa-hammer furniture-icon"></i>
                            <i class="fas fa-truck-loading furniture-icon"></i>
                        </div>
                </div>

                <div class="col-md-6 p-5" id="form-container">
                    <h2 class="text-center login-title mb-4 custom-orange-text mt-4">Reestablecer Contraseña</h2>
                    <form action="reset_password.php" method="POST" onsubmit="return confirmSubmit()">
                        <div id="Alert"></div>
                        <div class="mb-3">
                            <label for="code" class="form-label">Código de Recuperación</label>
                            <input id="code" type="text" name="code" class="form-control" placeholder="Ingrese el código" required>
                        </div>
                        <div class="form-group mb-3">
                        <label>Contraseña (8 caracteres mínimo)</label>
                        <div class="position-relative">
                            <input type="password" name="contrasena" id="contrasena" class="form-control" required data-bs-toggle="tooltip">
                            <i class="fas fa-eye toggle-pass" onclick="togglePassword('contrasena', this)"></i>
                        </div>
                    </div>

                    <div class="form-group mb-3">
                        <label>Confirmar Contraseña</label>
                        <div class="position-relative">
                            <input type="password" name="confirm_password" id="confirm_password" class="form-control" required data-bs-toggle="tooltip">
                            <i class="fas fa-eye toggle-pass" onclick="togglePassword('confirm_password', this)"></i>
                        </div>
                    </div>
                        <button id="btnSubmit" type="submit" class="btn custom-orange-btn w-100 mb-3">Restablecer Contraseña</button>
                        <div class="text-center mt-2">
                            <a href="login.php" class="link-secondary text-decoration-none">¿Ya tienes una cuenta? Inicia sesión aquí</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function validarPasswordsEmpleado() {
        const pass = document.getElementById("contrasena");
        const confirm = document.getElementById("confirm_password");
        const btn = document.getElementById("btnSubmit");

        const longitudValida = pass.value.length >= 8;
        const coinciden = pass.value === confirm.value && pass.value !== "";

        pass.classList.toggle("is-valid", longitudValida);
        pass.classList.toggle("is-invalid", !longitudValida);
        pass.setAttribute("data-bs-original-title", longitudValida ? "" : "Debe tener mínimo 8 caracteres");

        confirm.classList.toggle("is-valid", coinciden);
        confirm.classList.toggle("is-invalid", !coinciden);
        confirm.setAttribute("data-bs-original-title", coinciden ? "" : "Las contraseñas no coinciden");

        btn.disabled = !(longitudValida && coinciden);

        bootstrap.Tooltip.getInstance(pass)?.dispose();
        bootstrap.Tooltip.getInstance(confirm)?.dispose();

        if (!longitudValida) new bootstrap.Tooltip(pass).show();
        if (!coinciden && confirm.value !== "") new bootstrap.Tooltip(confirm).show();
    }

    document.getElementById("contrasena").addEventListener("input", validarPasswordsEmpleado);
    document.getElementById("confirm_password").addEventListener("input", validarPasswordsEmpleado);

    document.addEventListener("DOMContentLoaded", () => {
        document.getElementById("btnSubmit").disabled = true;
        new bootstrap.Tooltip(document.getElementById("contrasena"));
        new bootstrap.Tooltip(document.getElementById("confirm_password"));
    });

    function togglePassword(inputId, icon) {
        const input = document.getElementById(inputId);
        if (input.type === "password") {
            input.type = "text";
            icon.classList.replace('fa-eye', 'fa-eye-slash');
        } else {
            input.type = "password";
            icon.classList.replace('fa-eye-slash', 'fa-eye');
        }
    }

function mostrarToast(titulo, mensaje, tipo) {
            let icon = '';
            let alertClass = '';

            switch (tipo) {
                case 'success':
                    icon = '<span class="fas fa-check-circle text-white fs-6"></span>';
                    alertClass = 'alert-success';
                    break;
                case 'error':
                    icon = '<span class="fas fa-times-circle text-white fs-6"></span>';
                    alertClass = 'alert-danger';
                    break;
                case 'warning':
                    icon = '<span class="fas fa-exclamation-circle text-white fs-6"></span>';
                    alertClass = 'alert-warning';
                    break;
                case 'info':
                    icon = '<span class="fas fa-info-circle text-white fs-6"></span>';
                    alertClass = 'alert-info';
                    break;
                default:
                    icon = '<span class="fas fa-info-circle text-white fs-6"></span>';
                    alertClass = 'alert-info';
                    break;
            }

            const alert = `
            <div class="alert ${alertClass} d-flex align-items-center alert-dismissible fade show" role="alert">
                <div class="me-2">${icon}</div>
                <div>${titulo}: ${mensaje}</div>
                <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>`;

            $("#Alert").html(alert);

            setTimeout(() => {
                $(".alert").alert('close');
            }, 4000);
        }

        document.addEventListener('DOMContentLoaded', function() {
            <?php if (isset($_SESSION['status_message']) && isset($_SESSION['status_type'])): ?>
                <?php if ($_SESSION["status_type"] === "warning"): ?>
                    mostrarToast("Advertencia", '<?= $_SESSION["status_message"] ?>', '<?= $_SESSION["status_type"] ?>');
                <?php elseif ($_SESSION["status_type"] === "error"): ?>
                    mostrarToast("Error", '<?= $_SESSION["status_message"] ?>', '<?= $_SESSION["status_type"] ?>');
                <?php elseif ($_SESSION["status_type"] === "info"): ?>
                    mostrarToast("Info", '<?= $_SESSION["status_message"] ?>', '<?= $_SESSION["status_type"] ?>');
                <?php else: ?>
                    mostrarToast("Éxito", '<?= $_SESSION["status_message"] ?>', '<?= $_SESSION["status_type"] ?>');
                <?php endif; ?>
                <?php unset($_SESSION['status_message'], $_SESSION['status_type']); ?>
            <?php endif; ?>
        });
</script>
</body>
</html>