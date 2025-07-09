<?php
require '../../../mysql/connection.php';

$searchQuery = "";
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $searchTerm = $_GET['search'];
    $searchQuery = " WHERE 
        nombre LIKE '%$searchTerm%' OR 
        producto LIKE '%$searchTerm%'";
}

$sql = "SELECT * FROM proveedores". $searchQuery;

$result = $conn->query($sql);

if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $nombre_completo = htmlspecialchars($row['nombre']);
                        echo "<tr>";
                        echo "<td>" . $nombre_completo . "</td>";
                        echo "<td>" . htmlspecialchars($row['producto']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['telefono']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['email']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['direccion']) . "</td>";
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

$conn->close();
?>
