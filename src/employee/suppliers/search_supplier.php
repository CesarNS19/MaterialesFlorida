<?php
require '../../../mysql/connection.php';

$searchQuery = "";
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $searchTerm = $conn->real_escape_string($_GET['search']);
    $searchQuery = " WHERE 
        p.nombre LIKE '%$searchTerm%' OR 
        pro.nombre LIKE '%$searchTerm%' OR 
        d.ciudad LIKE '%$searchTerm%'";
}

$sql = "SELECT p.nombre, p.id_producto, p.telefono, p.email, p.fecha_ingreso, p.id_proveedor, p.estatus, d.id_direccion, d.ciudad, pro.nombre AS nombre_producto
        FROM proveedores p
        JOIN productos pro ON p.id_producto = pro.id_producto
        JOIN direcciones d ON p.id_direccion = d.id_direccion
        $searchQuery";

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
    echo "<tr><td colspan='7' class='text-center text-muted'>No hay proveedores disponibles</td></tr>";
}

$conn->close();
?>