<?php
session_start();
require '../../mysql/connection.php';
require 'slidebar.php';
$title = "La Florida ┃ Corte de Caja";

$id_usuario = $_SESSION['id_usuario'] ?? 1;

$queryCaja = $conn->query("SELECT * FROM cajas WHERE estado='abierta' ORDER BY id_caja DESC LIMIT 1");
$caja = $queryCaja->fetch_assoc();

if ($caja) {
    $totalVentas = $conn->query("
        SELECT SUM(total) AS total_ventas
        FROM ventas
        WHERE id_caja = {$caja['id_caja']}
    ")->fetch_assoc()['total_ventas'] ?? 0;

    $totalRetiros = $conn->query("
        SELECT SUM(monto) AS total_retiros
        FROM retiros_caja
        WHERE id_caja = {$caja['id_caja']}
    ")->fetch_assoc()['total_retiros'] ?? 0;

    $saldoActual = $caja['saldo_inicial'] + $totalVentas - $totalRetiros;
}
?>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
<title><?= $title ?></title>

<div id="Alert" class="container"></div>

<div class="container-fluid d-flex">
    <main class="flex-fill p-4 overflow-auto" id="main-content">
        <h2 class="fw-bold custom-orange-text text-center mb-4"><i class="fas fa-cash-register"></i> Gestión de Cajas</h2>

        <?php if ($caja): ?>
            <div class="card mb-4 shadow-sm">
                <div class="card-body">
                    <h5 class="card-title custom-orange-text"><i class="fas fa-door-open"></i> Caja abierta</h5>
                    <p><strong>Fecha apertura:</strong> <?= $caja['fecha_apertura'] ?></p>
                    <p><strong>Saldo inicial:</strong> $<?= number_format($caja['saldo_inicial'], 2) ?></p>
                    <p><strong>Saldo actual:</strong> $<span id="saldoActual"><?= number_format($saldoActual, 2) ?></span></p>
                    <form id="cutForm" method="POST" action="cash_register/process_cut.php" class="mt-3">
                        <input type="hidden" name="id_caja" value="<?= $caja['id_caja'] ?>">
                        <input type="hidden" name="saldo_real" id="inputSaldoReal">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Saldo real contado:</label>
                                <input type="number" step="0.01" id="saldo_real" class="form-control" required>
                            </div>
                            <div class="col-md-4 d-flex align-items-end">
                                <button type="button" class="btn custom-orange-btn text-white w-100" data-bs-toggle="modal" data-bs-target="#passwordCajaModal">
                                    <i class="fas fa-lock"></i> Realizar Corte
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Modal password_caja -->
            <div class="modal fade" id="passwordCajaModal" tabindex="-1" aria-labelledby="passwordCajaModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <form id="formPasswordCaja" method="POST" action="cash_register/process_cut.php">
                            <div class="modal-header bg-custom-orange text-white">
                                <h5 class="modal-title" id="passwordCajaModalLabel"><i class="fas fa-key"></i> Contraseña de Administrador</h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                            </div>
                            <div class="modal-body">
                                <p>Ingrese la contraseña de caja de cualquier administrador para realizar el corte:</p>
                                <div class="mb-3">
                                    <input type="password" class="form-control" id="password_caja" name="password_caja" placeholder="Contraseña" required>
                                </div>
                                <input type="hidden" name="id_caja" value="<?= $caja['id_caja'] ?>">
                                <input type="hidden" name="saldo_real" id="modalSaldoReal">
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                <button type="submit" class="btn custom-orange-btn text-white">Confirmar Corte</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <script>
                const saldoInput = document.getElementById('saldo_real');
                const modalSaldoInput = document.getElementById('modalSaldoReal');
                const inputSaldoReal = document.getElementById('inputSaldoReal');

                saldoInput.addEventListener('input', function() {
                    modalSaldoInput.value = saldoInput.value;
                });

                document.getElementById('formPasswordCaja').addEventListener('submit', function() {
                    inputSaldoReal.value = modalSaldoInput.value;
                });
            </script>
            
        <!-- Registrar retiro -->
            <div class="card mb-4 shadow-sm">
                <div class="card-body">
                    <h5 class="card-title custom-orange-text"><i class="fas fa-minus-circle"></i> Registrar Retiro</h5>
                    <form method="POST" action="cash_register/register_withdrawal.php" class="row g-3">
                        <input type="hidden" name="id_caja" value="<?= $caja['id_caja'] ?>">
                        <input type="hidden" name="id_usuario" value="<?= $id_usuario ?>">
                        <div class="col-md-4">
                            <label>Monto:</label>
                            <input type="number" step="0.01" name="monto" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label>Motivo:</label>
                            <input type="text" name="motivo" class="form-control" required>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn custom-orange-btn text-white w-100">
                                <i class="fas fa-save"></i> Guardar
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Lista de retiros -->
            <div class="card mb-4 shadow-sm">
                <div class="card-body">
                    <h5 class="card-title custom-orange-text"><i class="fas fa-list"></i> Retiros del día</h5>
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered text-center align-middle shadow-sm rounded-3 mt-3">
                            <thead class="bg-primary text-white">
                                <tr>
                                    <th>Monto</th>
                                    <th>Motivo</th>
                                    <th>Usuario</th>
                                    <th>Fecha</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $retiros = $conn->query("
                                    SELECT r.*, 
                                        CONCAT(u.nombre, ' ', u.apellido_paterno, ' ', u.apellido_materno) AS usuario
                                    FROM retiros_caja r
                                    JOIN usuarios u ON r.id_usuario = u.id_usuario
                                    WHERE r.id_caja = {$caja['id_caja']}
                                    ORDER BY r.fecha_retiro DESC
                                ");
                                if ($retiros->num_rows > 0) {
                                    while ($row = $retiros->fetch_assoc()) {
                                        echo "<tr>
                                                <td>$".number_format($row['monto'],2)."</td>
                                                <td class='text-start'>{$row['motivo']}</td>
                                                <td>{$row['usuario']}</td>
                                                <td>{$row['fecha_retiro']}</td>
                                              </tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='4' class='text-center text-muted'>No hay retiros registrados</td></tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        <?php else: ?>
            <div class="card shadow-sm">
                <div class="card-body">
                    <h5 class="card-title custom-orange-text"><i class="fas fa-plus-circle"></i> Abrir nueva caja</h5>
                    <form method="POST" action="cash_register/open_box.php">
                        <div class="mb-3">
                            <label>Saldo inicial:</label>
                            <input type="number" step="0.01" name="saldo_inicial" class="form-control" required>
                        </div>
                        <input type="hidden" name="id_usuario" value="<?= $id_usuario ?>">
                        <button type="submit" class="btn custom-orange-btn text-white">
                            <i class="fas fa-door-open"></i> Abrir Caja
                        </button>
                    </form>
                </div>
            </div>
        <?php endif; ?>

        <!-- Historial de cortes -->
        <h4 class="mt-4 custom-orange-text"><i class="fas fa-history"></i> Historial de Cortes</h4>
        <div class="table-responsive">
            <table class="table table-hover table-bordered text-center align-middle shadow-sm rounded-3 mt-3">
                <thead class="bg-primary text-white">
                    <tr>
                        <th>Fecha</th>
                        <th>Total Ventas</th>
                        <th>Total Retiros</th>
                        <th>Saldo Sistema</th>
                        <th>Saldo Real</th>
                        <th>Diferencia</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $cortes = $conn->query("SELECT * FROM cortes_caja ORDER BY fecha_corte DESC");
                    if ($cortes->num_rows > 0) {
                        while ($row = $cortes->fetch_assoc()) {
                            $color = ($row['diferencia'] != 0) ? "text-danger" : "text-success";
                            echo "<tr>
                                    <td>{$row['fecha_corte']}</td>
                                    <td>$".number_format($row['total_ventas'],2)."</td>
                                    <td>$".number_format($row['total_retiros'],2)."</td>
                                    <td>$".number_format($row['saldo_sistema'],2)."</td>
                                    <td>$".number_format($row['saldo_real'],2)."</td>
                                    <td class='$color'>$".number_format($row['diferencia'],2)."</td>
                                  </tr>";
                        }
                    } else {
                        echo "<tr><td colspan='7' class='text-center text-muted'>No hay cortes registrados</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </main>
</div>

<script>
    function actualizarSaldo(montoRetiro) {
        let saldo = parseFloat($("#saldoActual").text().replace(/,/g, ''));
        saldo -= parseFloat(montoRetiro);
        $("#saldoActual").text(saldo.toFixed(2));
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