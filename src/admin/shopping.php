<?php 
session_start();
require '../../mysql/connection.php';
require 'slidebar.php'; 
$title = "La Florida ┃ Compras";

$sql = "SELECT p.id_producto, p.id_unidad_medida, u.nombre AS unidad_medida,
               p.id_marca, m.nombre AS marca, p.nombre, 
               dc.precio_unitario, 
               p.imagen, c.nombre AS categoria, c.id_categoria,
               co.total, dc.cantidad, co.fecha, co.hora, pro.nombre AS nombre_proveedor, dir.ciudad
        FROM detalle_compra dc
        JOIN compras co ON dc.id_compra = co.id_compra
        JOIN proveedores pro ON co.id_proveedor = pro.id_proveedor
        JOIN direcciones dir ON pro.id_direccion = dir.id_direccion
        JOIN productos p ON dc.id_producto = p.id_producto
        JOIN marcas m ON p.id_marca = m.id_marca
        JOIN categorias c ON p.id_categoria = c.id_categoria
        JOIN unidades_medida u ON p.id_unidad_medida = u.id_unidad_medida";
?>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
<title><?php echo $title; ?></title>

<!-- Modal para añadir compras -->
<div class="modal fade" id="addModal" tabindex="-1" role="dialog" aria-labelledby="addModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-custom-orange text-white">
                <h5 class="modal-title" id="addModalLabel">Agregar Nueva Compra</h5>
            </div>
            <form action="shopping/add_shopping.php" method="POST">
                <div class="modal-body">

                    <div class="form-group mb-3">
                        <label for="id_provedor">Proveedor</label>
                        <select name="id_provedor" id="id_provedor" class="form-control" required>
                            <option value="">Seleccione un proveedor</option>
                            <?php
                            $pro_sql = "SELECT p.id_producto, p.nombre AS nombre_producto, prov.id_proveedor, prov.nombre 
                                        FROM proveedores prov 
                                        JOIN productos p ON prov.id_producto = p.id_producto";
                            $pro_result = $conn->query($pro_sql);
                            while ($pro = $pro_result->fetch_assoc()) {
                                echo "<option value='" . $pro['id_proveedor'] . "' 
                                            data-producto-id='" . $pro['id_producto'] . "' 
                                            data-producto-nombre='" . htmlspecialchars($pro['nombre_producto']) . "'>
                                            " . htmlspecialchars($pro['nombre']) . "
                                      </option>";
                            }
                            ?>
                        </select>
                    </div>

                    <div class="form-group mb-3">
                        <label for="nombre_producto">Producto</label>
                        <input type="text" id="nombre_producto" class="form-control" readonly>
                        <input type="hidden" name="id_producto" id="id_producto">
                    </div>

                    <div class="form-group mb-3">
                        <label for="cantidad">Cantidad</label>
                        <input type="number" name="cantidad" id="cantidad" class="form-control" step="0.01" required>
                    </div>

                    <div class="form-group mb-3">
                        <label for="precio">Precio</label>
                        <input type="number" name="precio" id="precio" class="form-control" step="0.01" required>
                    </div>

                    <div class="form-group mb-3">
                        <label for="subtotal">Total</label>
                        <input type="number" name="subtotal" id="subtotal" class="form-control" readonly required>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn custom-orange-btn text-white">Realizar Compra</button>
                </div>
            </form>
        </div>
    </div>
</div>

    <main class="flex-fill p-4 overflow-auto" id="main-content">
        <div id="Alert" class="container"></div>
        <h2 class="fw-bold custom-orange-text text-center">Mis Compras</h2>
        <button class="btn custom-orange-btn text-white" data-bs-toggle="modal" data-bs-target="#addModal" style="float: right; margin: 10px;">
            Nueva Compra
        </button>
    <div class="table-responsive">
        <table class="table table-hover table-bordered text-center align-middle shadow-sm rounded-3">
            <thead class="bg-primary text-white">
                <tr>
                    <th>Proveedor</th>
                    <th>Dirección Proveedor</th>
                    <th class="text-start">Producto</th>
                    <th>Categoría</th>
                    <th>Marca</th>
                    <th>U. Medida</th>
                    <th>Cantidad</th>
                    <th>Precio</th>
                    <th>Fecha Compra</th>
                    <th>Hora Compra</th>
                    <th>Imagen</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody id="products-container">
                <?php
                $result = $conn->query($sql);
                
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($row['nombre_proveedor']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['ciudad']) . "</td>";
                        echo "<td class='text-start'>" . htmlspecialchars($row['nombre']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['categoria']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['marca']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['unidad_medida']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['cantidad']) . "</td>";
                        echo "<td class='text-success fw-bold'>$" . htmlspecialchars($row['precio_unitario']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['fecha']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['hora']) . "</td>";
                        echo "<td><img src='../../img/" . htmlspecialchars($row['imagen']) . "' class='rounded' width='100px' height='60px' alt='Imágen Producto'></td>";
                        echo "<td>" . htmlspecialchars($row['total']) . "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='10' class='text-center text-muted'>No hay compras realizadas</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </main>

<script>
   $(document).ready(function() {
    $('#id_provedor').on('change', function() {
        const selected = $(this).find(':selected');
        const productoId = selected.data('producto-id');
        const productoNombre = selected.data('producto-nombre');

        $('#id_producto').val(productoId);
        $('#nombre_producto').val(productoNombre);
    });

    $('#cantidad, #precio').on('input', function() {
        const cantidad = parseFloat($('#cantidad').val()) || 0;
        const precio = parseFloat($('#precio').val()) || 0;
        $('#subtotal').val((cantidad * precio).toFixed(2));
    });
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
</div>