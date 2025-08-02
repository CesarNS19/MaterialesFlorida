<?php
session_start();
require '../../mysql/connection.php';
require 'slidebar.php';
$title = "La Florida ┃ Empleados";

$searchQuery = "";
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $searchTerm = $_GET['search'];
    $searchQuery = " WHERE 
        nombre LIKE '%$searchTerm%' OR 
        producto LIKE '%$searchTerm%'";
}

 $sql = "SELECT p.nombre, p.id_producto, p.telefono, p.email, p.fecha_ingreso, p.id_proveedor, p.estatus, d.id_direccion, d.ciudad, pro.nombre AS nombre_producto
        FROM proveedores p
        JOIN productos pro ON p.id_producto = pro.id_producto
        JOIN direcciones d ON p.id_direccion = d.id_direccion". $searchQuery;
?>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
<title><?php echo $title; ?></title>

<div id="Alert" class="container"></div>

    <div class="modal fade" id="addModal" tabindex="-1" role="dialog" aria-labelledby="addModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-custom-orange text-white">
                <h5 class="modal-title" id="addModalLabel">Agregar Proveedor</h5>
            </div>
            <form action="suppliers/add_supplier.php" method="POST">
                <div class="modal-body">
                    <div class="form-group mb-3">
                        <label>Nombre del Proveedor</label>
                        <input type="text" name="nombre" class="form-control" required>
                    </div>
                    <div class="form-group mb-3">
                        <label for="id_producto">Producto que provee</label>
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
                        <label>Teléfono</label>
                        <input type="number" name="telefono" class="form-control" required>
                    </div>
                    <div class="form-group mb-3">
                        <label>Email</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div class="form-group mb-3">
                        <label for="id_direccion">Direccion</label>
                        <select name="id_direccion" id="id_direccion" class="form-control" required>
                            <option value="">Seleccione una direccción</option>
                            <?php
                            $direcciones_sql = "SELECT * FROM direcciones";
                            $direcciones_result = $conn->query($direcciones_sql);
                            while ($direccion = $direcciones_result->fetch_assoc()) {
                                echo "<option value='" . $direccion['id_direccion'] . "'>" . htmlspecialchars($direccion['ciudad']) . "</option>";
                            }
                            ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn custom-orange-btn text-white">Agregar Proveedor</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal para editar proveedor -->
<div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-custom-orange text-white">
                <h5 class="modal-title" id="editLabel">Editar Proveedor</h5>
            </div>
            <form action="suppliers/edit_supplier.php" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="id_proveedor" id="edit_id_proveedor">
                    
                    <div class="form-group mb-3">
                        <label for="edit_nombre">Nombre del proveedor</label>
                        <input type="text" name="nombre" id="edit_nombre" class="form-control" required>
                    </div>
                    
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
                        <label for="edit_telefono">Teléfono</label>
                        <input type="number" name="telefono" id="edit_telefono" class="form-control" required>
                    </div>
                    
                    <div class="form-group mb-3">
                        <label for="edit_email">Email</label>
                        <input type="email" name="email" id="edit_email" class="form-control" required>
                    </div>

                    <div class="form-group mb-3">
                        <label for="edit_id_direccion">Dirección</label>
                        <select name="id_direccion" id="edit_id_direccion" class="form-control" required>
                            <?php
                            $direcciones_sql = "SELECT * FROM direcciones";
                            $direcciones_result = $conn->query($direcciones_sql);
                            while ($direccion = $direcciones_result->fetch_assoc()) {
                                echo "<option value='" . $direccion['id_direccion'] . "'>" . htmlspecialchars($direccion['ciudad']) . "</option>";
                            }
                            ?>
                        </select>
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

<!-- Modal para eliminar empleados -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title" id="deleteModalLabel">Confirmar Eliminación</h5>
      </div>
      <form action="suppliers/delete_supplier.php" method="POST">
      <div class="modal-body">
      <input type="hidden" name="id_proveedor" id="delete_id_proveedor">
        <p>¿Estás seguro de que deseas eliminar este proveedor?, Esta acción no se puede deshacer.</p>
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
    <h2 class="fw-bold custom-orange-text text-center">Administrar Proveedores</h2>
    <button class="btn custom-orange-btn text-white" data-bs-toggle="modal" data-bs-target="#addModal" style="float: right; margin: 10px;">
            Agregar Proveedor
        </button>
    <div class="table-responsive">
        <table class="table table-hover table-bordered text-center align-middle shadow-sm rounded-3">
            <thead class="bg-primary text-white">
                <tr>
                    <th>Proveedor</th>
                    <th>Producto que provee</th>
                    <th>Teléfono</th>
                    <th>Email</th>
                    <th>Dirección</th>
                    <th>Fecha Ingreso</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody id="suppliers-container">
                <?php
                $result = $conn->query($sql);

                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $nombre_completo = htmlspecialchars($row['nombre']);
                        echo "<tr>";
                        echo "<td>" . $nombre_completo . "</td>";
                        echo "<td>" . htmlspecialchars($row['nombre_producto']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['telefono']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['email']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['ciudad']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['fecha_ingreso']) . "</td>";
                        echo "<td>";
                    
                        if ($row['estatus'] === 'activo') {
                            echo "<a href='suppliers/status_supplier.php?id=" . $row['id_proveedor'] . "&estatus=inactivo' class='btn btn-warning btn-sm me-2 rounded-pill shadow-sm'>
                                    <i class='fas fa-ban'></i> Desactivar
                                  </a>";
                        } else {
                            echo "<a href='suppliers/status_supplier.php?id=" . $row['id_proveedor'] . "&estatus=activo' class='btn btn-success btn-sm me-2 rounded-pill shadow-sm'>
                                    <i class='fas fa-check-circle'></i> Activar
                                  </a>";
                        }

                        echo "<button class='btn btn-sm btn-outline-primary me-2 rounded-pill shadow-sm' onclick='openEditModal(" . json_encode($row) . ")'>
                                <i class='fas fa-edit'></i> Editar
                              </button>
                              <button class='btn btn-sm btn-outline-danger me-2 rounded-pill shadow-sm' onclick='openDeleteModal(" . json_encode($row) . ")'>
                                <i class='fas fa-trash-alt'></i> Eliminar
                              </button>
                        </td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='6' class='text-center text-muted'>No hay proveedores disponibles</td></tr>";
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
                url: "suppliers/search_supplier.php",
                type: "GET",
                data: { search: searchTerm },
                success: function(response) {
                    $('#suppliers-container').html(response);
                }
            });
        });
    });

    function openEditModal(data) {
        $('#edit_id_proveedor').val(data.id_proveedor);
        $('#edit_id_direccion').val(data.id_direccion);
        $('#edit_nombre').val(data.nombre);
        $('#edit_producto').val(data.producto);
        $('#edit_telefono').val(data.telefono);
        $('#edit_email').val(data.email);
        $('#editModal').modal('show');
    }

    function openDeleteModal(Data) {
        $('#delete_id_proveedor').val(Data.id_proveedor);
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