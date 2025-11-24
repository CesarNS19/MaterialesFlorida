<?php 
session_start();
require '../../mysql/connection.php';
require 'slidebar.php'; 
$title = "La Florida ┃ Compras";

$conn->query("SET lc_time_names = 'es_ES'");
?>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

<style>
    .purchase-img-small { height: 100px; width: auto; max-width: 100%; object-fit: contain; border-radius: 12px; }
    .stylish-card { transition: transform 0.25s ease, box-shadow 0.25s ease; }
    .stylish-card:hover { transform: translateY(-6px); box-shadow: 0 10px 30px rgba(0,0,0,0.15); }
    .custom-orange-btn { background-color: #f68b1f !important; border: none; }
    .custom-orange-btn:hover { background-color: #e67a12 !important; }
    .card-hover { transition: 0.3s ease-in-out; }
    .card-hover:hover { transform: scale(1.02); }

    .pagination .page-link {
        color: #f68b1f;
        border: 1px solid #f68b1f;
    }

    .pagination .page-item.active .page-link {
        background-color: #f68b1f;
        color: #fff;
        border-color: #f68b1f;
    }

    .pagination .page-link:hover {
        background-color: #e67a12;
        color: #fff;
        border-color: #e67a12;
    }
</style>

<title><?php echo $title; ?></title>

<main class="flex-fill p-4 overflow-auto vh-100" id="main-content">
<div id="Alert" class="container"></div>
<h2 class="fw-bold custom-orange-text text-center mb-4">Mis Compras</h2>

<div class="d-flex justify-content-end mb-4">
    <button class="btn custom-orange-btn text-white px-4 py-2 rounded-pill"
        data-bs-toggle="modal" data-bs-target="#addModal">
        Nueva Compra
    </button>
</div>

<div class="container" id="cards-container">
</div>

<div id="pagination-container" class="mt-4"></div>
</main>

<!-- Modal Nueva Compra-->
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
                            $pro_sql = "SELECT p.id_producto, p.nombre AS nombre_producto, prov.id_proveedor, prov.nombre, prov.estatus
                                        FROM proveedores prov 
                                        JOIN productos p ON prov.id_producto = p.id_producto
                                        WHERE prov.estatus = 'activo'";
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

<script>
let currentPage = 1;

function loadCards(page = 1) {
    $.ajax({
        url: "../admin/shopping/shopping_data.php",
        type: "GET",
        data: { pagina: page },
        success: function(data) {
            $("#cards-container").html(data.cards);
            $("#pagination-container").html(data.pagination);
        },
        dataType: "json"
    });
}

$(document).ready(function() {
    loadCards();

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
                    setTimeout(() => loadCards(currentPage), 1200);
                }
            }
        });
    });
});

function mostrarToast(titulo, mensaje, tipo) {
    let icon = '';
    let alertClass = '';
    switch (tipo) {
        case 'success': icon = '<span class="fas fa-check-circle text-white fs-6"></span>'; alertClass = 'alert-success'; break;
        case 'error': icon = '<span class="fas fa-times-circle text-white fs-6"></span>'; alertClass = 'alert-danger'; break;
        case 'warning': icon = '<span class="fas fa-exclamation-circle text-white fs-6"></span>'; alertClass = 'alert-warning'; break;
        default: icon = '<span class="fas fa-info-circle text-white fs-6"></span>'; alertClass = 'alert-info';
    }

    const alert = `
    <div class="alert ${alertClass} d-flex align-items-center alert-dismissible fade show" role="alert">
        <div class="me-2">${icon}</div>
        <div>${titulo}: ${mensaje}</div>
        <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>`;

    $("#Alert").html(alert);
    setTimeout(() => { $(".alert").alert('close'); }, 4000);
}

$(document).on("click", ".page-link", function(e){
    e.preventDefault();
    const page = $(this).data("page");
    if(page){
        currentPage = page;
        loadCards(currentPage);
    }
});
</script>
