<?php 
session_start();
require '../../mysql/connection.php';
require 'slidebar.php'; 
$title = "La Florida ┃ Direcciones";
?>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
<title><?php echo $title; ?></title>

<div id="Alert" class="container"></div>

<!-- Modal para añadir direcciones -->
<div class="modal fade" id="addModal" tabindex="-1" role="dialog" aria-labelledby="addModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header bg-custom-orange text-white">
        <h5 class="modal-title" id="addModalLabel">Agregar Nueva Dirección</h5>
      </div>
      <form action="addresses/add_address.php" method="POST">
        <div class="modal-body">

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

<!-- Modal para editar direcciones -->
<div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-labelledby="editLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header bg-custom-orange text-white">
        <h5 class="modal-title" id="editLabel">Editar Dirección</h5>
      </div>
      <form action="addresses/edit_address.php" method="POST">
        <div class="modal-body">
          <input type="hidden" name="id_direccion" id="edit_id_direccion">

          <div class="form-group mb-3">
            <label for="edit_num_int">Número Interior</label>
            <input type="number" name="num_int" id="edit_num_int" class="form-control" required>
          </div>

          <div class="form-group mb-3">
            <label for="edit_num_ext">Número Exterior</label>
            <input type="number" name="num_ext" id="edit_num_ext" class="form-control" required>
          </div>

          <div class="form-group mb-3">
            <label for="edit_calle">Calle</label>
            <input type="text" name="calle" id="edit_calle" class="form-control" required>
          </div>

          <div class="form-group mb-3">
            <label for="edit_ciudad">Ciudad</label>
            <input type="text" name="ciudad" id="edit_ciudad" class="form-control" required>
          </div>

          <div class="form-group mb-3">
            <label for="edit_estado">Estado</label>
            <input type="text" name="estado" id="edit_estado" class="form-control" required>
          </div>

          <div class="form-group mb-3">
            <label for="edit_codigo_postal">Código Postal</label>
            <input type="number" name="codigo_postal" id="edit_codigo_postal" class="form-control" required>
          </div>

        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
          <button type="submit" class="btn custom-orange-btn text-white">Guardar Cambios</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal para eliminar direcciones -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title" id="deleteModalLabel">Confirmar Eliminación</h5>
      </div>
      <form action="addresses/delete_address.php" method="POST">
        <div class="modal-body">
          <input type="hidden" name="id_direccion" id="delete_id_direccion">
          <p>¿Estás seguro de que deseas eliminar esta dirección? Esta acción no se puede deshacer.</p>
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
        <h2 class="fw-bold custom-orange-text text-center">Administrar Direcciones</h2>
        <button class="btn custom-orange-btn text-white" data-bs-toggle="modal" data-bs-target="#addModal" style="float: right; margin: 10px;">
            Agregar Dirección
        </button>
        <div class="table-responsive">
            <table class="table table-hover table-bordered text-center align-middle shadow-sm rounded-3">
                <thead class="bg-primary text-white">
                    <tr>
                        <th class="text-start">Num. Int</th>
                        <th class="text-start">Num. Ext</th>
                        <th class="text-start">Calle</th>
                        <th class="text-start">Ciudad</th>
                        <th class="text-start">Estado</th>
                        <th class="text-start">Código Postal</th>
                        <th class="text-middle">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $sql = "SELECT * FROM direcciones";
                    $result = $conn->query($sql);

                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td class='text-start'>" . htmlspecialchars($row['num_int']) . "</td>";
                            echo "<td class='text-start'>" . htmlspecialchars($row['num_ext']) . "</td>";
                            echo "<td class='text-start'>" . htmlspecialchars($row['calle']) . "</td>";
                            echo "<td class='text-start'>" . htmlspecialchars($row['ciudad']) . "</td>";
                            echo "<td class='text-start'>" . htmlspecialchars($row['estado']) . "</td>";
                            echo "<td class='text-start'>" . htmlspecialchars($row['codigo_postal']) . "</td>";
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
                        echo "<tr><td colspan='7' class='text-center text-muted'>No hay direcciones registradas</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </main>
</div>

<script>
    function openEditModal(data) {
        $('#edit_id_direccion').val(data.id_direccion);
        $('#edit_num_int').val(data.num_int);
        $('#edit_num_ext').val(data.num_ext);
        $('#edit_calle').val(data.calle);
        $('#edit_ciudad').val(data.ciudad);
        $('#edit_estado').val(data.estado);
        $('#edit_codigo_postal').val(data.codigo_postal);
        $('#editModal').modal('show');
    }

    function openDeleteModal(data) {
        $('#delete_id_direccion').val(data.id_direccion);
        $('#deleteModal').modal('show');
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