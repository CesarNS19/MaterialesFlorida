<?php
session_start();
require '../../mysql/connection.php';
require 'slidebar.php';
$title = "Muebleria ┃ Admin Products";

$searchQuery = "";
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $searchTerm = $_GET['search'];
    $searchQuery = " WHERE p.nombre LIKE '%" . $conn->real_escape_string($searchTerm) . "%' ";
}
?>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
<title><?php echo $title; ?></title>

<div id="Alert" class="container"></div>

<button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addProductsModal" style="float: right; margin: 10px;">
            Agregar Producto
        </button>
<!-- Modal para añadir productos -->
<div class="modal fade" id="addProductsModal" tabindex="-1" role="dialog" aria-labelledby="addProductsModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="addProductsModalLabel">Agregar Nuevo Producto</h5>
            </div>
            <form action="products/add_product.php" method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                <div class="form-group mb-3">  
                    <div class="form-group mb-3">
                        <label for="">Nombre del producto</label>
                        <input type="text" name="nombre" class="form-control" required>
                    </div>
                    <div class="form-group mb-3">
                        <label for="">Descripción</label>
                        <textarea name="descripcion" class="form-control" required></textarea>
                    </div>
                    <div class="form-group mb-3">
                        <label for="">Precio</label>
                        <input type="number" name="precio" class="form-control" required>
                    </div>
                    <div class="form-group mb-3">
                        <label for="">Stock</label>
                        <input type="number" name="stock" class="form-control" required>
                    </div>
                    <div class="form-group mb-3">
                        <label for="">Color</label>
                        <input type="text" name="color" class="form-control" required>
                    </div>
                    <div class="form-group mb-3">
                        <input type="file" name="imagen_producto" class="form-control" placeholder="Imágen del producto">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-primary">Agregar Producto</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal para editar productos -->
<div class="modal fade" id="editProductsModal" tabindex="-1" role="dialog" aria-labelledby="editProductsLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="editProductsLabel">Editar Producto</h5>
            </div>
            <form action="products/edit_product.php" method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" name="id_producto" id="edit_id_producto">
                

                    <div class="form-group mb-3">
                        <label for="edit_nombre">Nombre del producto</label>
                        <input type="text" name="nombre" id="edit_nombre" class="form-control" required>
                    </div>

                    <div class="form-group mb-3">
                        <label for="edit_descripcion">Descripción del producto</label>
                        <textarea name="descripcion" class="form-control" id="edit_descripcion" required></textarea>
                    </div>

                    <div class="form-group mb-3">
                        <label for="edit_precio">Precio del Producto</label>
                        <input type="number" name="precio" id="edit_precio" class="form-control" required>
                    </div>

                    <div class="form-group mb-3">
                        <label for="edit_stock">Stock</label>
                        <input type="number" name="stock" id="edit_stock" class="form-control" required>
                    </div>

                    <div class="form-group mb-3">
                        <label for="edit_color">Color</label>
                        <input type="text" name="color" id="edit_color" class="form-control" required>
                    </div>

                    <div class="form-group mb-3">
                        <label>Imágen Actual</label>
                        <div>
                            <img id="current_image" src="" width="100" alt="Imágen del producto">
                        </div>
                    </div>

                    <div class="form-group mb-3">
                        <label for="edit_imagen">Actualizar Imágen del Producto</label>
                        <input type="file" name="imagen_producto" id="edit_imagen" class="form-control">
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal para eliminar productos -->
<div class="modal fade" id="deleteProductsModal" tabindex="-1" aria-labelledby="deleteProductsModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title" id="deleteProductsModalLabel">Confirmar Eliminación</h5>
      </div>
      <form action="products/delete_product.php" method="POST">
      <div class="modal-body">
      <input type="hidden" name="id_producto" id="delete_id_producto">
        <p>¿Estás seguro de que deseas eliminar este producto?, Esta acción no se puede deshacer.</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-danger">Eliminar</button>
      </div>
      </form>
    </div>
  </div>
</div>

<script>
    $(document).ready(function() {
        $('#search').on('input', function() {
            let searchTerm = $(this).val();
            
            $.ajax({
                url: "products/search_products.php",
                type: "GET",
                data: { search: searchTerm },
                success: function(response) {
                    $('#products-container').html(response);
                }
            });
        });
    });

    function openEditModal(productsData) {
        $('#edit_id_producto').val(productsData.id_producto);
        $('#edit_id_categoria').val(productsData.id_categoria);
        $('#edit_id_marca').val(productsData.id_marca);
        $('#edit_nombre').val(productsData.nombre);
        $('#edit_descripcion').val(productsData.descripcion);  
        $('#edit_precio').val(productsData.precio);
        $('#edit_stock').val(productsData.stock);
        $('#edit_color').val(productsData.color);
        $('#current_image').attr('src', 'img/' + productsData.imagen);
        $('#editProductsModal').modal('show');
    }

    function openDeleteModal(Data) {
        $('#delete_id_producto').val(Data.id_producto);
        $('#deleteProductsModal').modal('show');
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