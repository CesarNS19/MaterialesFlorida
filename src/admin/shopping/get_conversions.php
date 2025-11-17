<?php
require '../../../mysql/connection.php';

if (!isset($_GET['id_producto'])) {
    echo json_encode([]);
    exit;
}

$id = intval($_GET['id_producto']);

$sql = "SELECT id_conversion, unidad_medida, factor 
        FROM unidades_conversion 
        WHERE id_producto = $id";

$result = $conn->query($sql);

$unidades = [];

while ($row = $result->fetch_assoc()) {
    $unidades[] = $row;
}

echo json_encode($unidades);
