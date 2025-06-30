<?php
require '../../../mysql/connection.php';

$searchQuery = "";
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $searchTerm = $conn->real_escape_string($_GET['search']);
    $searchQuery = " AND u.nombre LIKE '%" . $searchTerm . "%' ";
}

 $sql = "SELECT u.id_usuario, u.nombre, u.apellido_paterno, u.apellido_materno, u.email, p.estatus AS estado, p.nombre AS perfil, p.id_perfil
                FROM usuarios u
                JOIN perfil p ON u.id_perfil = p.id_perfil
                WHERE p.nombre = 'Empleado'". $searchQuery;

$result = $conn->query($sql);

if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $nombre_completo = htmlspecialchars($row['nombre'] . ' ' . $row['apellido_paterno'] . ' ' . $row['apellido_materno']);
                        echo "<tr>";
                        echo "<td>" . $nombre_completo . "</td>";
                        echo "<td>" . htmlspecialchars($row['email']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['perfil']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['estado']) . "</td>";
                        echo "<td>";
                    
                        if ($row['estado'] === 'activo') {
                            echo "<a href='employees/status_employee.php?id=" . $row['id_perfil'] . "&estatus=inactivo' class='btn btn-warning btn-sm me-2 rounded-pill shadow-sm'>
                                    <i class='fas fa-ban'></i> Desactivar
                                  </a>";
                        } else {
                            echo "<a href='employees/status_employee.php?id=" . $row['id_perfil'] . "&estatus=activo' class='btn btn-success btn-sm me-2 rounded-pill shadow-sm'>
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
                    echo "<tr><td colspan='6' class='text-center text-muted'>No hay empleados disponibles</td></tr>";
                }

$conn->close();
?>
