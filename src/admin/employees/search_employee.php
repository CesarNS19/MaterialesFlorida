<?php
require '../../../mysql/connection.php';

$searchQuery = "";
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $searchTerm = $conn->real_escape_string($_GET['search']);
    $searchQuery = " AND (u.nombre LIKE '%" . $searchTerm . "%' OR u.email LIKE '%" . $searchTerm . "%') ";
}

$sql = "SELECT u.id_usuario, u.nombre, u.apellido_paterno, u.apellido_materno, u.email, u.estatus AS estado, 
               p.nombre AS perfil, p.id_perfil, u.id_direccion, 
               d.calle, d.num_ext, d.num_int, d.ciudad, d.estado AS estado_dir, d.codigo_postal
        FROM usuarios u
        JOIN perfil p ON u.id_perfil = p.id_perfil
        JOIN direcciones d ON u.id_direccion = d.id_direccion
        WHERE p.nombre = 'Empleado'" . $searchQuery;

$result = $conn->query($sql);

if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $nombre_completo = htmlspecialchars($row['nombre'] . ' ' . $row['apellido_paterno'] . ' ' . $row['apellido_materno']);
                        $direccion_completa = htmlspecialchars(
                            $row['calle'] . ' ' . $row['num_ext'] .
                            ($row['num_int'] ? ' Int ' . $row['num_int'] : '') . ', ' .
                            $row['ciudad'] . ', ' . $row['estado_dir'] . ' C.P. ' . $row['codigo_postal']
                        );
                        echo "<tr>";
                        echo "<td>" . $nombre_completo . "</td>";
                        echo "<td>" . $direccion_completa . "</td>";
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
