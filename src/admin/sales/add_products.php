<?php
require '../../../mysql/connection.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['search'])) {
    $nombreProducto = $conn->real_escape_string($_POST['search']);

    $query = "SELECT p.id_producto, p.nombre, p.precio, p.stock, u.nombre AS unidad_medida
              FROM productos p
              JOIN unidades_medida u ON p.id_unidad_medida = u.id_unidad_medida
              WHERE p.nombre LIKE '%$nombreProducto%'
              LIMIT 1";

    $result = $conn->query($query);

    if ($result && $result->num_rows > 0) {
        $producto = $result->fetch_assoc();
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'producto' => $producto
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Producto no encontrado']);
    }
    exit;
}
?>
