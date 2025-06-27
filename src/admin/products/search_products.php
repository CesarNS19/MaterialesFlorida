<?php
require '../../../mysql/connection.php';

$searchQuery = "";
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $searchTerm = $_GET['search'];
    $searchQuery = " WHERE p.nombre LIKE '%" . $conn->real_escape_string($searchTerm) . "%' ";
}

$sql = "SELECT p.id_producto, c.nombre AS categoria, m.nombre AS marca, p.nombre, p.descripcion, p.color, p.precio, p.imagen, p.stock
        FROM productos p
        JOIN categorias c ON p.id_categoria = c.id_categoria
        JOIN marcas m ON p.id_marca = m.id_marca" . $searchQuery;

$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td class='text-start'>" . htmlspecialchars($row['nombre']) . "</td>";
        echo "<td class='text-start text-muted'>" . htmlspecialchars($row['descripcion']) . "</td>";
        echo "<td class='text-success fw-bold'>$" . htmlspecialchars($row['precio']) . "</td>";
        echo "<td>" . htmlspecialchars($row['stock']) . "</td>";
        echo "<td>" . htmlspecialchars($row['color']) . "</td>";
        echo "<td><img src='img/" . htmlspecialchars($row['imagen']) . "' class='rounded' width='100px' height='60px' alt='Imágen Producto'></td>";
        echo "<td>
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
    echo "<tr><td colspan='7' class='text-center text-muted'>No se encontraron productos</td></tr>";
}

$conn->close();
?>
