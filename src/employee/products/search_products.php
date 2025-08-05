<?php
require '../../../mysql/connection.php';

$searchQuery = "";
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $searchTerm = $conn->real_escape_string($_GET['search']);
    $searchQuery = " WHERE 
        p.nombre LIKE '%$searchTerm%' 
        OR c.nombre LIKE '%$searchTerm%' 
        OR m.nombre LIKE '%$searchTerm%'";
}

$sql = "SELECT p.id_producto, p.id_unidad_medida, u.nombre AS unidad_medida,
               p.id_marca, m.nombre AS marca, p.nombre, 
               p.precio, p.stock, p.ubicacion, p.fecha_ingreso, 
               p.estado, p.imagen, c.nombre AS categoria, c.id_categoria
        FROM productos p
        JOIN marcas m ON p.id_marca = m.id_marca
        JOIN categorias c ON p.id_categoria = c.id_categoria
        JOIN unidades_medida u ON p.id_unidad_medida = u.id_unidad_medida
        $searchQuery";

$result = $conn->query($sql);

if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($row['categoria']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['marca']) . "</td>";
                        echo "<td class='text-start'>" . htmlspecialchars($row['nombre']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['unidad_medida']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['stock']) . "</td>";
                        echo "<td class='text-success fw-bold'>$" . htmlspecialchars($row['precio']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['fecha_ingreso']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['ubicacion']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['estado']) . "</td>";
                        echo "<td><img src='../../img/" . htmlspecialchars($row['imagen']) . "' class='rounded' width='100px' height='60px' alt='Imágen Producto'></td>";
                        echo "<td>";

                        if ($row['estado'] === 'activo') {
                            echo "<a href='products/status_product.php?id=" . $row['id_producto'] . "&estatus=inactivo' class='btn btn-warning btn-sm me-2 rounded-pill shadow-sm'>
                                    <i class='fas fa-ban'></i> 
                                  </a>";
                        } else {
                            echo "<a href='products/status_product.php?id=" . $row['id_producto'] . "&estatus=activo' class='btn btn-success btn-sm me-2 rounded-pill shadow-sm'>
                                    <i class='fas fa-check-circle'></i> 
                                  </a>";
                        }

                        echo "<button class='btn btn-sm btn-outline-primary me-2 rounded-pill shadow-sm' onclick='openEditModal(" . json_encode($row) . ")'>
                                <i class='fas fa-edit'></i>
                              </button>
                              <button class='btn btn-sm btn-outline-danger me-2 rounded-pill shadow-sm' onclick='openDeleteModal(" . json_encode($row) . ")'>
                                <i class='fas fa-trash-alt'></i>
                              </button>
                        </td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='12' class='text-center text-muted'>No hay productos disponibles</td></tr>";
                }

$conn->close();
?>
