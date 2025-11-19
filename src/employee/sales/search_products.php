<?php
require '../../../mysql/connection.php';
header('Content-Type: application/json');

$query = isset($_GET['query']) ? trim($_GET['query']) : '';
$id_usuario = isset($_GET['id_usuario']) ? intval($_GET['id_usuario']) : null;

if (!$query || !$id_usuario) {
    echo json_encode([]);
    exit;
}

$stmt = $conn->prepare("
    SELECT id_producto, codigo, nombre, stock, imagen 
    FROM productos
    WHERE (codigo LIKE CONCAT('%', ?, '%') OR nombre LIKE CONCAT('%', ?, '%'))
      AND stock > 0
      AND LOWER(estado) = 'activo'
    LIMIT 10
");
$stmt->bind_param("ss", $query, $query);
$stmt->execute();
$result = $stmt->get_result();

$productos = [];
while ($row = $result->fetch_assoc()) {
    $productos[] = $row;
}

echo json_encode($productos);
