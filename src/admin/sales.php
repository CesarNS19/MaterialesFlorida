<?php 
session_start();
require '../../mysql/connection.php';
require 'slidebar.php'; 
$title = "La Florida ┃ Ventas";

$sql = "SELECT 
            p.id_producto,
            p.nombre AS nombre_producto,
            p.precio,
            um.nombre AS nombre_unidad,
            u.id_usuario,
            u.nombre AS nombre_usuario,
            u.apellido_paterno,
            u.apellido_materno,
            d.ciudad,
            v.id_venta,
            v.fecha,
            v.total,
            dv.cantidad,
            dv.subtotal
        FROM productos p
        JOIN unidades_medida um ON p.id_unidad_medida = um.id_unidad_medida
        JOIN detalle_venta dv ON p.id_producto = dv.id_producto
        JOIN ventas v ON dv.id_venta = v.id_venta
        JOIN usuarios u ON v.id_usuario = u.id_usuario
        JOIN direcciones d ON u.id_direccion = d.id_direccion
        WHERE p.nombre = 'Cliente'";
?>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
<title><?php echo $title; ?></title>
<div class="container">
    
<div id="Alert" class="container"></div>

 <div class="container">
    <div class="text-end my-3">
        <a href="index_admin.php" class="btn custom-orange-btn text-white">
            Ver Ventas
        </a>
    </div>

    <form id="productSearchForm" class="row g-2" action="sales/add_products.php" method="POST">
        <div class="col-md-8">
            <div class="input-group">
                <span class="input-group-text bg-custom-orange text-white">
                    <i class="fas fa-search"></i>
                </span>
                <input type="text" name="search" id="search" class="form-control" placeholder="Agregar producto por nombre...">
            </div>
        </div>
    </form>
</div>


<div class="container-fluid d-flex">
    <main class="flex-fill p-4 overflow-auto" id="main-content">
        <h2 class="fw-bold custom-orange-text text-center">Productos</h2>
    <div class="table-responsive">
        <table class="table table-hover table-bordered text-center align-middle shadow-sm rounded-3">
            <thead class="bg-primary text-white">
                <tr>
                    <th>Producto</th>
                    <th>Unidad de Medida</th>
                    <th>Cantidad</th>
                    <th>Precio</th>
                    <th>Subtotal</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody id="products-container">
                <?php
                $result = $conn->query($sql);
                
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($row['nombre']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['unidad_medida']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['cantidad']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['precio']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['subtotal']) . "</td>";
                        echo "<button class='btn btn-sm btn-outline-primary me-2 rounded-pill shadow-sm' onclick='openEditModal(" . json_encode($row) . ")'>
                                <i class='fas fa-edit'></i>
                              </button>
                              <button class='btn btn-sm btn-outline-danger me-2 rounded-pill shadow-sm' onclick='openDeleteModal(" . json_encode($row) . ")'>
                                <i class='fas fa-trash-alt'></i>
                              </button>
                        </td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='12' class='text-center text-muted'>No hay productos disponibles</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </main>    
</div>

<script>

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
