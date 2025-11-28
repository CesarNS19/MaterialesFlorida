<?php
session_start();
include "../../mysql/connection.php";
include "slidebar.php";
$title = "Materiales Florida ┃ Perfil Administrador";
$id_usuario = $_SESSION['id_usuario'];

$sql = "SELECT nombre, apellido_paterno, apellido_materno, telefono, email, password_caja
        FROM usuarios WHERE id_usuario = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$result = $stmt->get_result();
$userData = $result->fetch_assoc();
?>
<style>
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

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

<title><?php echo $title; ?></title>

<!-- Card para mostrar información del cliente -->
<div class="container mt-5">
    <div class="card mx-auto shadow-lg" style="max-width: 500px; border-radius: 15px;">
        <div class="card-body text-center">
            <div id="Alert" class="container"></div>
            <div class="mb-4">
                <img src="https://cdn-icons-png.flaticon.com/512/3135/3135715.png" alt="Perfil Admin" class="rounded-circle" width="120">
            </div>
            <h3 class="card-title custom-orange-text fw-bold">Mi Perfil</h3>
            <ul class="list-group list-group-flush text-start">
                <li class="list-group-item"><strong>Nombre:</strong> <?php echo $userData['nombre']; ?></li>
                <li class="list-group-item"><strong>Apellido Paterno:</strong> <?php echo $userData['apellido_paterno']; ?></li>
                <li class="list-group-item"><strong>Apellido Materno:</strong> <?php echo $userData['apellido_materno']; ?></li>
                <li class="list-group-item"><strong>Teléfono:</strong> <?php echo $userData['telefono']; ?></li>
                <li class="list-group-item"><strong>Email:</strong> <?php echo $userData['email']; ?></li>
            </ul>

            <button class="btn custom-orange-btn mt-4 px-4 text-white" data-bs-toggle="modal" data-bs-target="#editProfileModal">
                <i class="fas fa-user-edit me-1"></i> Editar Perfil
            </button>

            <?php if (empty($userData['password_caja'])): ?>
                <button class="btn custom-orange-btn text-white mt-4 px-4" data-bs-toggle="modal" data-bs-target="#addCajaPasswordModal">
                    <i class="fas fa-key me-1"></i> Agregar Contraseña de Caja
                </button>
            <?php else: ?>
                <button class="btn custom-orange-btn text-white mt-4 px-4" data-bs-toggle="modal" data-bs-target="#addCajaPasswordModal">
                    <i class="fas fa-key me-1"></i> Actualizar Contraseña de Caja
                </button>
            <?php endif; ?>

        </div>
    </div>
</div>

<!-- Modal para editar perfil -->
<div class="modal fade" id="editProfileModal" tabindex="-1" aria-labelledby="editProfileModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="profile/edit_profile.php" method="POST">
                <div class="modal-header bg-custom-orange text-white">
                    <h5 class="modal-title" id="editProfileModalLabel">Editar Perfil</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="nombre" class="form-label">Nombre</label>
                        <input type="text" class="form-control" id="nombre" name="nombre" value="<?php echo $userData['nombre']; ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="apellido_paterno" class="form-label">Apellido Paterno</label>
                        <input type="text" class="form-control" id="apellido_paterno" name="apellido_paterno" value="<?php echo $userData['apellido_paterno']; ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="apellido_materno" class="form-label">Apellido Materno</label>
                        <input type="text" class="form-control" id="apellido_materno" name="apellido_materno" value="<?php echo $userData['apellido_materno']; ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="telefono" class="form-label">Teléfono</label>
                        <input type="tel" class="form-control" id="telefono" name="telefono" value="<?php echo $userData['telefono']; ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" value="<?php echo $userData['email']; ?>" required>
                    </div>

                    <h6 class="custom-orange-text">Cambiar Contraseña (Opcional)</h6>
                    <div class="form-group mb-3">
                        <label>Contraseña (8 caracteres mínimo)</label>
                        <div class="position-relative">
                            <input type="password" class="form-control" id="new_password" name="new_password" required data-bs-toggle="tooltip">
                            <i class="fas fa-eye toggle-pass" onclick="togglePassword('new_password', this)"></i>
                        </div>
                    </div>

                    <div class="form-group mb-3">
                        <label>Confirmar Contraseña</label>
                        <div class="position-relative">
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required data-bs-toggle="tooltip">
                            <i class="fas fa-eye toggle-pass" onclick="togglePassword('confirm_password', this)"></i>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn custom-orange-btn text-white">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal para agregar/actualizar contraseña de caja -->
<div class="modal fade" id="addCajaPasswordModal" tabindex="-1" aria-labelledby="addCajaPasswordModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="profile/add_password.php" method="POST">
                <div class="modal-header bg-custom-orange text-white">
                    <h5 class="modal-title" id="addCajaPasswordModalLabel">
                        <?php echo empty($userData['password_caja']) ? 'Agregar Contraseña de Caja' : 'Actualizar Contraseña de Caja'; ?>
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <?php if (!empty($userData['password_caja'])): ?>
                        <p class="text-muted">Actualmente ya tienes una contraseña de caja. Ingresa la nueva para actualizarla.</p>
                    <?php else: ?>
                        <p class="text-muted">No tienes contraseña de caja. Ingresa una nueva para agregarla.</p>
                    <?php endif; ?>
                    <div class="form-group mb-3">
                        <label>Contraseña</label>
                        <div class="position-relative">
                            <input type="password" name="contrasena" id="contrasena" class="form-control" required data-bs-toggle="tooltip">
                            <i class="fas fa-eye toggle-pass" onclick="togglePassword('contrasena', this)"></i>
                        </div>
                    </div>

                    <div class="form-group mb-3">
                        <label>Confirmar Contraseña</label>
                        <div class="position-relative">
                            <input type="password" name="confirmar_contrasena" id="confirmar_contrasena" class="form-control" required data-bs-toggle="tooltip">
                            <i class="fas fa-eye toggle-pass" onclick="togglePassword('confirmar_contrasena', this)"></i>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" id="btnGuardarCaja" class="btn custom-orange-btn text-white" disabled>Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function validarPasswordCaja() {
        const pass = document.getElementById("contrasena");
        const confirm = document.getElementById("confirmar_contrasena");
        const btn = document.getElementById("btnGuardarCaja");

        const coinciden = pass.value === confirm.value && pass.value !== "";

        pass.classList.toggle("is-valid", coinciden);
        pass.classList.toggle("is-invalid", !coinciden && confirm.value !== "");

        confirm.classList.toggle("is-valid", coinciden);
        confirm.classList.toggle("is-invalid", !coinciden && confirm.value !== "");

        confirm.setAttribute("data-bs-original-title", coinciden ? "" : "Las contraseñas no coinciden");

        btn.disabled = !coinciden;

        bootstrap.Tooltip.getInstance(confirm)?.dispose();
        if (!coinciden && confirm.value !== "") new bootstrap.Tooltip(confirm).show();
    }

    document.getElementById("contrasena").addEventListener("input", validarPasswordCaja);
    document.getElementById("confirmar_contrasena").addEventListener("input", validarPasswordCaja);

    document.addEventListener("DOMContentLoaded", () => {
        document.getElementById("btnGuardarCaja").disabled = true;
        new bootstrap.Tooltip(document.getElementById("contrasena"));
        new bootstrap.Tooltip(document.getElementById("confirmar_contrasena"));
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

    function validarPasswordUser() {
        const pass = document.getElementById("new_password");
        const confirm = document.getElementById("confirm_password");
        const btn = document.getElementById("btnUser");

        const longitudValida = pass.value.length >= 8;
        const coinciden = pass.value === confirm.value && pass.value !== "";

        pass.classList.toggle("is-valid", longitudValida);
        pass.classList.toggle("is-invalid", !longitudValida);
        pass.setAttribute("data-bs-original-title", longitudValida ? "" : "Debe tener mínimo 8 caracteres");

        confirm.classList.toggle("is-valid", coinciden);
        confirm.classList.toggle("is-invalid", !coinciden);
        confirm.setAttribute("data-bs-original-title", coinciden ? "" : "Las contraseñas no coinciden");

        bootstrap.Tooltip.getInstance(pass)?.dispose();
        bootstrap.Tooltip.getInstance(confirm)?.dispose();

        if (!longitudValida) new bootstrap.Tooltip(pass).show();
        if (!coinciden && confirm.value !== "") new bootstrap.Tooltip(confirm).show();
    }

    document.getElementById("new_password").addEventListener("input", validarPasswordUser);
    document.getElementById("confirm_password").addEventListener("input", validarPasswordUser);

    document.addEventListener("DOMContentLoaded", () => {
        new bootstrap.Tooltip(document.getElementById("new_password"));
        new bootstrap.Tooltip(document.getElementById("confirm_password"));
    });

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