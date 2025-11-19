<?php 
session_start();
require '../../mysql/connection.php';
require 'slidebar.php'; 
$title = "La Florida ┃ Compras";

$conn->query("SET lc_time_names = 'es_ES'");

$sql = "SELECT 
            p.id_producto, 
            p.id_unidad_medida, 
            u.nombre AS unidad_medida,
            p.id_marca, 
            m.nombre AS marca, 
            p.nombre, 
            dc.precio_unitario AS precio_compra, 
            p.imagen, 
            p.precio AS precio_venta_real, 
            p.precio_pieza,
            c.nombre AS categoria, 
            c.id_categoria,

            CASE 
                WHEN uc.factor = 1 
                    THEN p.precio
                ELSE p.precio_pieza
            END AS precio_mostrar,

            CASE 
                WHEN uc.factor = 1 
                    THEN p.precio
                ELSE p.precio_pieza
            END AS precio_venta_mostrar,

            co.total, 
            dc.cantidad, 
            dc.unidad_medida AS unidad,
            co.fecha, 
            co.hora,

            DATE_FORMAT(
                STR_TO_DATE(CONCAT(co.fecha, ' ', co.hora), '%Y-%m-%d %H:%i:%s'),
                '%e de %M de %Y a las %l:%i %p'
            ) AS fecha_hora,

            pro.nombre AS nombre_proveedor, 
            dir.id_direccion, dir.calle, dir.num_ext, dir.num_int, 
            dir.ciudad, dir.estado AS estado_dir, dir.codigo_postal,

            uc.factor,

            CASE
                WHEN uc.factor = 1 
                    THEN (p.precio - dc.precio_unitario)
                ELSE (p.precio_pieza - dc.precio_unitario)
            END AS ganancia_unidad,

            CASE
                WHEN uc.factor = 1 
                    THEN (p.precio - dc.precio_unitario) * dc.cantidad
                ELSE (p.precio_pieza - dc.precio_unitario) * dc.cantidad
            END AS ganancia_total

        FROM detalle_compra dc
        JOIN compras co ON dc.id_compra = co.id_compra
        JOIN proveedores pro ON co.id_proveedor = pro.id_proveedor
        JOIN direcciones dir ON pro.id_direccion = dir.id_direccion
        JOIN productos p ON dc.id_producto = p.id_producto
        JOIN marcas m ON p.id_marca = m.id_marca
        JOIN categorias c ON p.id_categoria = c.id_categoria
        JOIN unidades_medida u ON p.id_unidad_medida = u.id_unidad_medida
        JOIN unidades_conversion uc 
            ON uc.id_producto = p.id_producto 
            AND uc.unidad_medida = dc.unidad_medida
        ORDER BY co.fecha DESC, co.hora DESC";
?>

<style>
    .purchase-img-small {
        height: 100px;
        width: auto;
        max-width: 100%;
        object-fit: contain;
        border-radius: 12px;
    }
    .stylish-card {
        transition: transform 0.25s ease, box-shadow 0.25s ease;
    }
    .stylish-card:hover {
        transform: translateY(-6px);
        box-shadow: 0 10px 30px rgba(0,0,0,0.15);
    }
    .custom-orange-btn {
        background-color: #f68b1f !important;
        border: none;
    }
    .custom-orange-btn:hover {
        background-color: #e67a12 !important;
    }
    .card-hover {
        transition: 0.3s ease-in-out;
    }
    .card-hover:hover {
        transform: scale(1.02);
    }
</style>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<title><?php echo $title; ?></title>

<div class="modal fade" id="addModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-custom-orange text-white">
                <h5 class="modal-title">Agregar Nueva Compra</h5>
            </div>

            <form id="formShopping">
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
                                echo "<option value='{$pro['id_proveedor']}'
                                        data-producto-id='{$pro['id_producto']}'
                                        data-producto-nombre='" . htmlspecialchars($pro['nombre_producto']) . "'>
                                        {$pro['nombre']}
                                    </option>";
                            }
                            ?>
                        </select>
                    </div>

                    <div class="form-group mb-3">
                        <label>Producto</label>
                        <input type="text" id="nombre_producto" class="form-control" readonly>
                        <input type="hidden" name="id_producto" id="id_producto">
                    </div>

                    <div class="form-group mb-3">
                        <label>Unidad de Medida</label>
                        <select name="id_conversion" id="id_conversion" class="form-control" required>
                            <option value="">Seleccione una unidad de medida</option>
                        </select>
                    </div>

                    <div class="form-group mb-3">
                        <label>Cantidad</label>
                        <input type="number" name="cantidad" id="cantidad" class="form-control" step="0.01" required>
                    </div>

                    <div class="form-group mb-3">
                        <label>Precio</label>
                        <input type="number" name="precio" id="precio" class="form-control" step="0.01" required>
                    </div>

                    <div class="form-group mb-3">
                        <label>Total</label>
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

<main class="flex-fill p-4 overflow-auto vh-100" id="main-content">
<div id="Alert" class="container"></div>
    <h2 class="fw-bold custom-orange-text text-center mb-4">Mis Compras</h2>

    <div class="d-flex justify-content-end mb-4">
        <button class="btn custom-orange-btn text-white px-4 py-2 rounded-pill"
            data-bs-toggle="modal" data-bs-target="#addModal">
            Nueva Compra
        </button>
    </div>

    <div class="container">
        <div class="row row-cols-1 row-cols-md-3 g-4">

        <?php
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {

                $direccion_completa =
                    htmlspecialchars(
                        $row['calle'] . ' ' . $row['num_ext'] .
                        ($row['num_int'] ? ' Int ' . $row['num_int'] : '') .
                        ', ' . $row['ciudad'] . ', ' . $row['estado_dir'] .
                        ' C.P. ' . $row['codigo_postal']
                    );
        ?>

            <div class="col">
                <div class="card shadow-lg rounded-4 h-100 border-0 card-hover stylish-card">
                    <div class="card-header text-center fw-semibold rounded-top-4 bg-custom-orange text-white">
                        <i class="fas fa-cart-arrow-down me-2"></i> Compra Realizada
                    </div>

                    <div class="card-body px-4 py-3 d-flex flex-column">
                        <div class="text-muted small mb-2 text-center">
                            <i class="fas fa-calendar-alt me-1"></i>
                            <strong>Fecha:</strong> <?= $row['fecha_hora'] ?>
                        </div>

                        <div class="text-center mb-3">
                            <img src="../../img/<?= htmlspecialchars($row['imagen']) ?>" 
                                class="purchase-img-small">
                        </div>

                        <h5 class="fw-bold text-dark mb-2">
                            <i class="fas fa-tag me-2"></i><?= htmlspecialchars($row['nombre']) ?>
                        </h5>

                        <div class="text-muted small mb-3">
                            <strong><i class="fas fa-layer-group"></i> Categoría:</strong> <?= htmlspecialchars($row['categoria']) ?><br>
                            <strong><i class="fas fa-tag"></i> Marca:</strong> <?= htmlspecialchars($row['marca']) ?><br>
                            <strong><i class="fas fa-ruler"></i> Unidad Medida:</strong> 
                            <span class="text-primary fw-bold"><?= $row['unidad'] ?></span><br><br>
                            <strong><i class="fas fa-user-tie"></i> Proveedor:</strong> <?= htmlspecialchars($row['nombre_proveedor']) ?><br>
                            <strong><i class="fas fa-map-marked-alt"></i> Dirección:</strong> <?= $direccion_completa ?>
                        </div>

                        <div class="text-muted small mb-2">
                            <strong><i class="fas fa-dollar"></i> Precio Venta:</strong>
                            <span class="text-primary fw-bold">
                                $<?= number_format($row['precio_venta_mostrar'], 2) ?>
                            </span>
                        </div>

                        <div class="d-flex justify-content-between mb-3">
                            <span class="fw-semibold text-secondary">
                                <i class="fas fa-box me-1"></i>
                                Cantidad: <span class="text-primary fw-bold"><?= $row['cantidad'] ?></span>
                            </span>

                            <span class="fw-semibold text-secondary">
                                <i class="fas fa-dollar me-1"></i>
                                Precio Compra: <span class="text-primary fw-bold">$<?= number_format($row['precio_compra'], 2) ?></span>
                            </span>

                            <span class="fw-semibold text-secondary">
                                <i class="fas fa-dollar me-1"></i>
                                Total: <span class="text-success fw-bold">$<?= number_format($row['total'], 2) ?></span>
                            </span>
                        </div>
                        <div class="mt-3 p-2 bg-light rounded-3 border">
                            <h6 class="fw-bold text-success mb-1">
                                <i class="fas fa-money-bill-wave"></i> Utilidad del Producto
                            </h6>
                            <div class="small">
                                <strong>Ganancia por Unidad:</strong> 
                                <span class="text-primary fw-bold">$<?= number_format($row['ganancia_unidad'], 2) ?></span><br>
                                <strong>Ganancia Total:</strong> 
                                <span class="text-success fw-bold">$<?= number_format($row['ganancia_total'], 2) ?></span>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

        <?php
            }
        } else {
            echo "<p class='text-center text-muted'>No hay compras realizadas.</p>";
        }
        ?>

        </div>
    </div>
</main>

<script>
    $('#id_provedor').on('change', function() {
        const selected = $(this).find(':selected');
        const productoId = selected.data('producto-id');
        const nombreProducto = selected.data('producto-nombre');

        $("#nombre_producto").val(nombreProducto);
        $("#id_producto").val(productoId);

        if (!productoId) return;

        $.ajax({
            url: "shopping/get_conversions.php",
            type: "GET",
            data: { id_producto: productoId },
            dataType: "json",
            success: function(data) {
                $('#id_conversion').empty()
                                  .append('<option value="">Seleccione una unidad</option>');

                data.forEach(u => {
                    $('#id_conversion').append(`
                        <option value="${u.id_conversion}">
                            ${u.unidad_medida}
                        </option>
                    `);
                });
            }
        });
    });

    $('#cantidad, #precio').on('input', function() {
        const cantidad = parseFloat($('#cantidad').val()) || 0;
        const precio = parseFloat($('#precio').val()) || 0;
        $('#subtotal').val((cantidad * precio).toFixed(2));
    });

    $('#formShopping').on('submit', function(e) {
        e.preventDefault();

        let unidad_nombre = $("#id_conversion option:selected").text();
        let formData = new FormData(this);
        formData.append("unidad_nombre", unidad_nombre);

        $.ajax({
            url: "shopping/add_shopping.php",
            type: 'POST',
            data: formData,
            dataType: "json",
            processData: false,
            contentType: false,
            success: function(resp) {
                if (resp.status === "success") {
                    mostrarToast("Éxito", resp.message, "success");
                    $('#addModal').modal('hide');
                    $('#formShopping')[0].reset();
                    setTimeout(() => location.reload(), 1200);
                }
            }
        });
    });

    function mostrarToast(titulo, mensaje, tipo) {
        let icon = '';
        let alertClass = '';

        switch (tipo) {
            case 'success': icon = '<span class="fas fa-check-circle text-white fs-6"></span>'; alertClass = 'alert-success'; break;
            case 'error': icon = '<span class="fas fa-times-circle text-white fs-6"></span>'; alertClass = 'alert-danger'; break;
            case 'warning': icon = '<span class="fas fa-exclamation-circle text-white fs-6"></span>'; alertClass = 'alert-warning'; break;
            case 'info': icon = '<span class="fas fa-info-circle text-white fs-6"></span>'; alertClass = 'alert-info'; break;
            default: icon = '<span class="fas fa-info-circle text-white fs-6"></span>'; alertClass = 'alert-info';
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
</script>