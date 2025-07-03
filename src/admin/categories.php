<?php
session_start();
require '../../mysql/connection.php';
require 'slidebar.php';
$title = "La Florida ┃ Categorias";
?>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
<title><?php echo $title; ?></title>

<div id="Alert" class="container"></div>

<!-- Modal para añadir categoria -->
<div class="modal fade" id="addCategoriesModal" tabindex="-1" role="dialog" aria-labelledby="addCategoriesModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="addCategoriesModalLabel">Agregar Nueva Categoría</h5>
            </div>
            <form action="categories/add_categories.php" method="POST">
                <div class="modal-body">
                    <div class="form-group mb-3">
                        <label for="">Categoría</label>
                        <input type="text" name="nombre" class="form-control" required>
                    </div>
                    <div class="form-group mb-3">
                        <label for="">Descripción</label>
                        <textarea name="descripcion" class="form-control" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-primary">Agregar Categoría</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal para editar categorias -->
<div class="modal fade" id="editCategoriesModal" tabindex="-1" role="dialog" aria-labelledby="editCategoriesLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="editCategoriesLabel">Editar Categoría</h5>
            </div>
            <form action="categories/edit_categories.php" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="id_categoria" id="edit_id_categoria">

                    <div class="form-group mb-3">
                        <label for="edit_nombre">Nombre de la Categoría</label>
                        <input type="text" name="nombre" id="edit_nombre" class="form-control" required>
                    </div>

                    <div class="form-group mb-3">
                        <label for="edit_descripcion">Descripción</label>
                        <input type="text" name="descripcion" id="edit_descripcion" class="form-control" required>
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

<!-- Modal para eliminar categorias -->
<div class="modal fade" id="deleteCategoriesModal" tabindex="-1" aria-labelledby="deleteCategoriesModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title" id="deleteCategoriesModalLabel">Confirmar Eliminación</h5>
      </div>
      <form action="categories/delete_categories.php" method="POST">
      <div class="modal-body">
      <input type="hidden" name="id_categoria" id="delete_id_categoria">
        <p>¿Estás seguro de que deseas eliminar esta categoría?, Esta acción no se puede deshacer.</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-danger">Eliminar</button>
      </div>
      </form>
    </div>
  </div>
</div>

<div class="container-fluid d-flex">
    <main class="flex-fill p-4 overflow-auto" id="main-content">
        <h2 class="fw-bold text-primary text-center">Administrar Categorías</h2>
        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addCategoriesModal" style="float: right; margin: 10px;">
            Agregar Categoria
        </button>
        <div class="table-responsive">
        <table class="table table-hover table-bordered text-center align-middle shadow-sm rounded-3">
            <thead class="bg-primary text-white">
                <tr>
                    <th class="text-start">Nombre de Categoría</th>
                    <th class="text-start">Descripción</th>
                    <th class="text-middle">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $sql = "SELECT * FROM categorias";
                $result = $conn->query($sql);

                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td class='text-start'>" . htmlspecialchars($row['nombre']) . "</td>";
                        echo "<td class='text-start text-muted'>" . htmlspecialchars($row['descripcion']) . "</td>";
                        echo "<td class='text-middle'>
                            <button class='btn btn-sm btn-outline-primary me-2 rounded-pill shadow-sm' onclick='openEditModal(" . json_encode($row) . ")'>
                                <i class='fas fa-edit'></i> Editar
                            </button>
                            <button class='btn btn-sm btn-outline-danger me-2 rounded-pill shadow-sm' onclick='openDeleteModal(" . json_encode($row) . ")'>
                                <i class='fas fa-trash-alt'></i> Eliminar
                              </button>
                        </td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='3' class='text-center text-muted'>No hay categorías disponibles</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </main>
</div>

<script>
    function openEditModal(categoriesData) {
        $('#edit_id_categoria').val(categoriesData.id_categoria);
        $('#edit_nombre').val(categoriesData.nombre);
        $('#edit_descripcion').val(categoriesData.descripcion);    
        $('#editCategoriesModal').modal('show');
    }

    function openDeleteModal(Data) {
        $('#delete_id_categoria').val(Data.id_categoria);
        $('#deleteCategoriesModal').modal('show');
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
                <?php elseif ($_SESSION["status_type"] === "danger"): ?>
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