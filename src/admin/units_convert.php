<?php
session_start();
require '../../mysql/connection.php';
require 'slidebar.php';
$title = "La Florida ┃ Unidades de Conversión";

$searchQuery = "";
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $searchTerm = $_GET['search'];
    $searchQuery = " WHERE unidad_medida LIKE '%" . $conn->real_escape_string($searchTerm) . "%' ";
}

$sql = "SELECT u.id_conversion, u.id_producto,  u.unidad_medida, u.factor, p.nombre
        FROM unidades_conversion u 
        JOIN productos p ON u.id_producto = p.id_producto". $searchQuery;
?>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
<title><?php echo $title; ?></title>

<div id="Alert" class="container"></div>

<!-- Modal para añadir Unidades de conversión -->
<div class="modal fade" id="addModal" tabindex="-1" role="dialog" aria-labelledby="addModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-custom-orange text-white">
                <h5 class="modal-title" id="addModalLabel">Agregar Nueva Unidad de Conversión</h5>
            </div>
            <form action="unit/add_unit_convert.php" method="POST">
                <div class="modal-body">
                    <div class="form-group mb-3">
                        <label for="id_producto">Seleccionar un producto</label>
                        <select name="id_producto" id="id_producto" class="form-control" required>
                            <option value="">Seleccione un producto</option>
                            <?php
                            $prod_sql = "SELECT * FROM productos";
                            $prod_result = $conn->query($prod_sql);
                            while ($prod = $prod_result->fetch_assoc()) {
                                echo "<option value='" . $prod['id_producto'] . "'>" . htmlspecialchars($prod['nombre']) . "</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="form-group mb-3">
                        <label for="">Unidad de Conversion</label>
                        <input type="text" name="unidad_medida" class="form-control" required placeholder="Nombre de la Unidad de conversión">
                    </div>
                    <div class="form-group mb-3">
                        <label for="">Factor</label>
                        <input type="text" name="factor" class="form-control" required placeholder="Factor de la Unidad de conversión"></input>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn custom-orange-btn text-white">Agregar Unidad de Conversión</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal para editar Unidades de Conversión -->
<div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-labelledby="editLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-custom-orange text-white">
                <h5 class="modal-title" id="editLabel">Editar Unidad de Conversión</h5>
            </div>
            <form action="unit/edit_unit_convert.php" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="id_conversion" id="edit_id_conversion">

                    <div class="form-group mb-3">
                        <label for="edit_producto">Producto</label>
                        <select name="id_producto" id="edit_producto" class="form-control" required>
                            <?php
                            $prod_sql = "SELECT * FROM productos";
                            $prod_result = $conn->query($prod_sql);
                            while ($prod = $prod_result->fetch_assoc()) {
                                echo "<option value='" . $prod['id_producto'] . "'>" . htmlspecialchars($prod['nombre']) . "</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <div class="form-group mb-3">
                        <label for="edit_unidad_medida">Nombre de la Unidad de Conversión</label>
                        <input type="text" name="unidad_medida" id="edit_unidad_medida" class="form-control" required>
                    </div>

                    <div class="form-group mb-3">
                        <label for="edit_factor">Factor de la Unidad de Conversion</label>
                        <input type="text" name="factor" id="edit_factor" class="form-control" required>
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

<!-- Modal para eliminar Unidades de Conversión -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-custom-orange text-white">
        <h5 class="modal-title" id="deleteModalLabel">Confirmar Eliminación</h5>
      </div>
      <form action="unit/delete_unit_convert.php" method="POST">
      <div class="modal-body">
      <input type="hidden" name="id_conversion" id="delete_id_conversion">
        <p>¿Estás seguro de que deseas eliminar esta Unidad de Conversión?, Esta acción no se puede deshacer.</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn custom-orange-btn text-white">Eliminar</button>
      </div>
      </form>
    </div>
  </div>
</div>

 <!-- Tabla de Unidades de Conversión -->
 <div class="container-fluid d-flex">
    <main class="flex-fill p-4 overflow-auto" id="main-content">
    <h2 class="fw-bold custom-orange-text text-center">Administrar Unidades de Conversión</h2>
    <button class="btn custom-orange-btn text-white" data-bs-toggle="modal" data-bs-target="#addModal" style="float: right; margin: 10px;">
            Agregar Unidad de Conversión
        </button>
    <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
    <table class="table table-hover table-bordered text-center align-middle shadow-sm rounded-3">
            <thead class="bg-primary text-white">
                <tr>
                    <th>Producto</th>
                    <th class="text-start">Nombre de la Unidad de Conversión</th>
                    <th class="text-start">Factor</th>
                    <th class="text-middle">Acciones</th>
                </tr>
            </thead>
            <tbody id="units-container">
                <?php
                $result = $conn->query($sql);

                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td class='text-start'>" . htmlspecialchars($row['nombre']) . "</td>";
                        echo "<td class='text-start'>" . htmlspecialchars($row['unidad_medida']) . "</td>";
                        echo "<td class='text-start text-muted'>" . htmlspecialchars($row['factor']) . "</td>";
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
                    echo "<tr><td colspan='3' class='text-center text-muted'>No hay marcas disponibles</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </main>
</div>

<script>
    $(document).ready(function() {
        $('#search').on('input', function() {
            let searchTerm = $(this).val();
            
            $.ajax({
                url: "unit/search_unit_convert.php",
                type: "GET",
                data: { search: searchTerm },
                success: function(response) {
                    $('#units-container').html(response);
                }
            });
        });
    });

    function openEditModal(Data) {
        $('#edit_id_conversion').val(Data.id_conversion);
        $('#edit_producto').val(Data.id_producto);
        $('#edit_unidad_medida').val(Data.unidad_medida);
        $('#edit_factor').val(Data.factor);
        $('#editModal').modal('show');
    }

    function openDeleteModal(Data) {
        $('#delete_id_conversion').val(Data.id_conversion);
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