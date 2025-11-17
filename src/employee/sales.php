<?php
session_start();
require '../../mysql/connection.php';
require 'slidebar.php'; 
$title = "La Florida ┃ Ventas";

$usuario_logueado = isset($_SESSION['id_usuario']) ? intval($_SESSION['id_usuario']) : null;

if (!$usuario_logueado) {
    $_SESSION['status_message'] = "Debes iniciar sesión para acceder a las ventas.";
    $_SESSION['status_type'] = "warning";
    header("Location: login.php");
    exit();
}

$id_usuario = isset($_GET["id_usuario"]) ? intval($_GET["id_usuario"]) : null;

$id_caja = null;
$sql_caja = "SELECT id_caja FROM cajas WHERE id_usuario = ? AND estado = 'abierta' LIMIT 1";
$stmt_caja = $conn->prepare($sql_caja);
$stmt_caja->bind_param('i', $usuario_logueado);
$stmt_caja->execute();
$resultado_caja = $stmt_caja->get_result();
if ($resultado_caja && $row_caja = $resultado_caja->fetch_assoc()) {
    $id_caja = $row_caja['id_caja'];
}
$stmt_caja->close();

$nombre_completo = "";
if ($id_usuario) {
    $resNombre = $conn->query("SELECT CONCAT(nombre, ' ', apellido_paterno, ' ', apellido_materno) AS nombre_completo FROM usuarios WHERE id_usuario = $id_usuario");
    if ($resNombre && $rowNombre = $resNombre->fetch_assoc()) {
        $nombre_completo = $rowNombre['nombre_completo'];
    }
}
$usuarios_result = $conn->query("
    SELECT u.id_usuario, CONCAT(u.nombre, ' ', u.apellido_paterno, ' ', u.apellido_materno) AS nombre_completo
    FROM usuarios u
    JOIN perfil p ON u.id_perfil = p.id_perfil
    WHERE p.nombre = 'Cliente'
");

$direcciones_result = null;
if (!empty($id_usuario)) {
    $direcciones_result = $conn->query("
        SELECT id_direccion, CONCAT(num_int, ', ', num_ext, ', ', calle, ', ', ciudad, ', ', estado, ', CP ', codigo_postal) AS direccion_completa
        FROM direcciones
        WHERE id_direccion = (SELECT id_direccion FROM usuarios WHERE id_usuario = $id_usuario)
    ");
}

$total = 0;
$productos_carrito = [];

if ($id_usuario) {
    $query = "SELECT 
                c.id_carrito,
                p.id_producto,
                p.nombre AS nombre_producto,
                p.stock,
                p.id_unidad_medida,
                um.nombre AS unidad_medida,
                c.cantidad,
                c.precio,
                p.precio AS precio_normal,
                p.precio_pieza,
                c.subtotal,
                p.imagen,
                p.codigo,
                c.unidad_seleccionada
            FROM carrito c
            JOIN productos p ON c.id_producto = p.id_producto
            JOIN unidades_medida um ON p.id_unidad_medida = um.id_unidad_medida
            WHERE c.id_usuario = $id_usuario";

    $result = $conn->query($query);

    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $unidadesAlternativas = [];
            $sqlUnidad = "SELECT unidad_medida, factor FROM unidades_conversion WHERE id_producto = " . intval($row['id_producto']);
            $resUnidades = $conn->query($sqlUnidad);
            if ($resUnidades) {
                while($u = $resUnidades->fetch_assoc()){
                    $unidadesAlternativas[$u['unidad_medida']] = $u['factor'];
                }
            }

            $row['unidades_alternativas'] = $unidadesAlternativas;
            $productos_carrito[] = $row;

            $total += $row['subtotal'];
        }
    }
}
?>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
<title><?php echo $title; ?></title>

<div class="container">

    <div id="Alert" class="container mt-3"></div>

    <h1 class = "text-center custom-orange-text">Realizar Venta</h1>

    <div class="text-end my-3">
        <a href="view_sales.php" class="btn custom-orange-btn text-white">
            Ver Ventas
        </a>
    </div>

<!-- Formulario para seleccionar cliente -->
    <form id="selectUserForm" method="GET" action="">
        <div class="row g-2 align-items-center">
            <div class="col-sm-4">
                <label for="id_usuario" class="form-label">Seleccionar Cliente:</label>
                <select class="form-select" id="id_usuario" name="id_usuario" onchange="document.getElementById('selectUserForm').submit();">
                    <option value="">-- Selecciona un usuario --</option>
                    <?php while ($usuario = $usuarios_result->fetch_assoc()): ?>
                        <option value="<?= $usuario['id_usuario'] ?>" <?= ($id_usuario == $usuario['id_usuario']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($usuario['nombre_completo']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="col-sm-4">
                <?php if (!empty($id_usuario)): ?>
                    <label for="id_direccion" class="form-label">Seleccionar Dirección:</label>
                    <select class="form-select" id="id_direccion" name="id_direccion">
                        <?php if ($direcciones_result && $direcciones_result->num_rows > 0): ?>
                            <?php while ($direccion = $direcciones_result->fetch_assoc()): ?>
                                <option value="<?= $direccion['id_direccion'] ?>">
                                    <?= htmlspecialchars($direccion['direccion_completa']) ?>
                                </option>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <option value="">No hay direcciones registradas</option>
                        <?php endif; ?>
                        <option value="nueva">➕ Elegir dirección nueva</option>
                    </select>
                <?php endif; ?>
            </div>
    </form>

<!-- Formulario de Búsqueda -->
    <form id="productSearchForm" class="row g-2">
        <input type="hidden" name="id_usuario" id="id_usuario_input" value="<?= $id_usuario ?>">
        <div class="col-md-8 position-relative">
            <div class="input-group">
                <span class="input-group-text bg-custom-orange text-white">
                    <i class="fas fa-barcode"></i>
                </span>
                <input type="text" id="search_query" class="form-control" placeholder="Buscar producto por código o nombre..." autocomplete="off">
            </div>
            <div id="suggestions" class="list-group position-absolute w-100" style="z-index:1000;"></div>
        </div>
    </form>

<!-- Modal para añadir direcciones -->
    <div class="modal fade" id="addModal" tabindex="-1" role="dialog" aria-labelledby="addModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
        <div class="modal-header bg-custom-orange text-white">
            <h5 class="modal-title" id="addModalLabel">Agregar Nueva Dirección</h5>
        </div>
        <form action="sales/add_address.php" method="POST">
            <div class="modal-body">
                <input type="hidden" name="id_direccion">
                <input type="hidden" name="id_usuario" value="<?= !empty($id_usuario) ? $id_usuario : '' ?>">

            <div class="form-group mb-3">
                <label for="">Número Interior</label>
                <input type="number" name="num_int" class="form-control" required>
            </div>

            <div class="form-group mb-3">
                <label for="">Número Exterior</label>
                <input type="number" name="num_ext" class="form-control" required>
            </div>

            <div class="form-group mb-3">
                <label for="">Calle</label>
                <input type="text" name="calle" class="form-control" required>
            </div>

            <div class="form-group mb-3">
                <label for="">Ciudad</label>
                <input type="text" name="ciudad" class="form-control" required>
            </div>

            <div class="form-group mb-3">
                <label for="">Estado</label>
                <input type="text" name="estado" class="form-control" required>
            </div>

            <div class="form-group mb-3">
                <label for="">Código Postal</label>
                <input type="number" name="codigo_postal" class="form-control" required>
            </div>

            </div>
            <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            <button type="submit" class="btn custom-orange-btn text-white">Agregar Dirección</button>
            </div>
        </form>
        </div>
    </div>
    </div>

<!-- Modal de confirmación de venta -->
    <div class="modal fade" id="confirmSaleModal" tabindex="-1" aria-labelledby="confirmSaleLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
        <div class="modal-header bg-custom-orange text-white">
            <h5 class="modal-title" id="confirmSaleLabel">Confirmar Venta</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>
        <div class="modal-body text-center">
            ¿Confirmas realizar la venta?
        </div>
        <div class="modal-footer justify-content-center">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
            <button type="button" class="btn custom-orange-btn text-white" id="btnConfirmSale">Confirmar</button>
        </div>
        </div>
    </div>
    </div>

<!-- Modal para Eliminar -->
    <div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-labelledby="confirmDeleteLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
        <div class="modal-header bg-custom-orange text-white">
            <h5 class="modal-title" id="confirmDeleteLabel">Confirmar eliminación</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>
        <div class="modal-body text-center">
            ¿Estás seguro de que deseas eliminar este producto del carrito?
        </div>
        <div class="modal-footer justify-content-center">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
            <a href="#" id="btnConfirmDelete" class="btn custom-orange-btn text-white">Eliminar</a>
        </div>
        </div>
    </div>
    </div>

    <h2 class="fw-bold custom-orange-text text-center">
        <?php if ($id_usuario): ?>
            Productos para <?= htmlspecialchars($nombre_completo) ?>
        <?php else: ?>
            Productos
        <?php endif; ?>
    </h2>

    <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
    <table class="table table-hover table-bordered text-center align-middle shadow-sm rounded-3">
            <thead class="bg-primary text-white">
                <tr>
                    <th>Código</th>
                    <th>Imagen</th>
                    <th>Producto</th>
                    <th>Existencia</th>
                    <th>Cantidad</th>
                    <th>Precio</th>
                    <th>Subtotal</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody id="products-container">
                <?php if ($id_usuario && count($productos_carrito) > 0): ?>
                    <?php foreach ($productos_carrito as $row): ?>
                        <?php
                            $unidadBase = $row['unidad_medida'];
                            $unidad_seleccionada = !empty($row['unidad_seleccionada']) ? $row['unidad_seleccionada'] : 'normal';
                            $unidadesAlternativas = $row['unidades_alternativas'];
                        ?>
                        <tr>
                            <td><?= htmlspecialchars($row['codigo']) ?></td>
                            <td><img src='../../img/<?= htmlspecialchars($row['imagen']) ?>' class='rounded' width='100px' height='60px'></td>
                            <td><?= htmlspecialchars($row['nombre_producto']) ?></td>
                            <td><?= htmlspecialchars($row['stock']) ?></td>
                            <td>
                                <form action="sales/update_quantity.php" method="POST" class="d-flex justify-content-center align-items-center">
                                    <input type="hidden" name="id_carrito" value="<?= $row['id_carrito'] ?>">
                                    <input type="hidden" name="id_usuario" value="<?= $id_usuario ?>">
                                    <input type="number" name="cantidad" value="<?= $row['cantidad'] ?>" class="form-control text-center" style="width:70px;" min="0.01" step="0.01" required>
                                    <select name="unidad_seleccionada" class="form-select ms-2" style="width:120px;">
                                        <?php foreach ($unidadesAlternativas as $unidad => $factor): ?>
                                            <option value="<?= htmlspecialchars($unidad) ?>" <?= ($unidad_seleccionada === $unidad) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($unidad) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <button type="submit" class="btn btn-sm btn-outline-primary ms-2"><i class="fas fa-sync-alt"></i></button>
                                </form>
                            </td>
                            <td>$<?= number_format($row['precio'], 2) ?></td>
                            <td>$<?= number_format($row['subtotal'], 2) ?></td>
                            <td>
                                <button class='btn btn-sm btn-outline-danger me-2'
                                    onclick='eliminarProducto(<?= $row['id_carrito'] ?>, <?= $id_usuario ?>); return false;'
                                    data-bs-toggle='modal' data-bs-target='#confirmDeleteModal'>
                                    <i class='fas fa-trash'></i>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="8">No hay productos en el carrito.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <p class="text-end" style="font-size: 20px;">Total: $<?= number_format($total, 2) ?></p>

    <?php if ($id_usuario): ?>
        <div class="text-end my-3">
            <form id="saleForm" action="sales/sales_process.php" method="POST">
                <input type="hidden" name="id_usuario" value="<?= $id_usuario ?>">
                <input type="hidden" name="id_caja" value="<?= $id_caja ?>">
                <button type="button" class="btn custom-orange-btn btn-sm text-white" id="btnOpenConfirm">
                    <i class="fas fa-cash-register me-2"></i> Realizar Venta
                </button>
            </form>
        </div>
    <?php endif; ?>


<script>
    function eliminarProducto(id_carrito, id_usuario) {
        const btnDelete = document.getElementById("btnConfirmDelete");
        btnDelete.href = "sales/delete_product.php?id=" + id_carrito + "&id_usuario=" + id_usuario;
    }

     const direccionSelect = document.getElementById('id_direccion');
        const usuarioSelect = document.getElementById('id_usuario');

        if (direccionSelect) {
            direccionSelect.addEventListener('change', function() {
                if (this.value === 'nueva') {
                    if (usuarioSelect.value === '') {
                        alert('Primero selecciona un cliente para agregar dirección.');
                        this.value = '';
                        return;
                    }
                    const modal = new bootstrap.Modal(document.getElementById('addModal'));
                    modal.show();
                    this.value = '';
                }
            });
        };

    document.addEventListener('DOMContentLoaded', function() {
        const btnOpenConfirm = document.getElementById('btnOpenConfirm');
        const btnConfirmSale = document.getElementById('btnConfirmSale');
        const saleForm = document.getElementById('saleForm');
        const confirmSaleModal = new bootstrap.Modal(document.getElementById('confirmSaleModal'));

        btnOpenConfirm.addEventListener('click', function() {
            confirmSaleModal.show();
        });

        btnConfirmSale.addEventListener('click', function() {
            saleForm.submit();
        });
    });

    document.querySelectorAll('form.update-quantity-form').forEach(form => {
        const cantidadInput = form.querySelector('input[name="cantidad"]');
        const unidadSelect = form.querySelector('select[name="unidad_seleccionada"]');
        const precioTd = form.closest('tr').querySelector('.precio');
        const subtotalTd = form.closest('tr').querySelector('.subtotal');

        function actualizarPrecio() {
            const cantidad = parseFloat(cantidadInput.value);
            const unidad = unidadSelect.value;
            const precioNormal = parseFloat(precioTd.dataset.precioNormal);
            const precioPieza = parseFloat(precioTd.dataset.precioPieza);

            let precio = precioNormal;

            if (unidad === 'pieza' || unidad === 'carretilla' || unidad === 'bulto' || unidad === 'kilogramos') {
            precio = precioPieza;
            }

            precioTd.textContent = `$${precio.toFixed(2)}`;
            const subtotal = cantidad * precio;
            subtotalTd.textContent = `$${subtotal.toFixed(2)}`;
        }

        cantidadInput.addEventListener('input', actualizarPrecio);
        unidadSelect.addEventListener('change', actualizarPrecio);
    });

    $(document).ready(function() {
        const $search = $('#search_query');
        const $suggestions = $('#suggestions');
        const id_usuario = $('#id_usuario_input').val();

        $search.on('input', function() {
            const query = $(this).val().trim();
            if (query.length === 0) {
                $suggestions.hide();
                return;
            }

            $.getJSON('sales/search_products.php', { query, id_usuario }, function(data) {
                $suggestions.empty();
                if (data.length > 0) {
                    data.forEach(prod => {
                        const item = $(`
                            <button type="button" class="list-group-item list-group-item-action d-flex align-items-center">
                                <img src='../../img/${prod.imagen}' width='50' class='me-2 rounded'>
                                <div>
                                    <strong>${prod.nombre}</strong><br>
                                    <small>Código: ${prod.codigo} | Stock: ${prod.stock}</small>
                                </div>
                            </button>
                        `);
                        item.on('click', function() {
                            $('<form>', {
                                method: 'POST',
                                action: 'sales/add_products.php'
                            }).append(
                                $('<input>', { type: 'hidden', name: 'id_usuario', value: id_usuario }),
                                $('<input>', { type: 'hidden', name: 'id_producto', value: prod.id_producto })
                            ).appendTo('body').submit();
                        });
                        $suggestions.append(item);
                    });
                    $suggestions.show();
                } else {
                    $suggestions.hide();
                }
            });
        });

        $(document).click(function(e) {
            if (!$(e.target).closest('#search_query, #suggestions').length) {
                $suggestions.hide();
            }
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

        <?php if (isset($_GET['id_venta'])): ?>
            setTimeout(() => {
                window.open('sales/ticket_generate.php?id_venta=<?= $_GET['id_venta'] ?>', '_blank');
            }, 1200);
        <?php endif; ?>
</script>