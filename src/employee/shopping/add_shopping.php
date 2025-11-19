<?php
require '../../../mysql/connection.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["status" => "error", "message" => "Solicitud inválida."]);
    exit;
}

$id_proveedor = intval($_POST['id_provedor'] ?? 0);
$id_producto = intval($_POST['id_producto'] ?? 0);
$id_conversion = intval($_POST['id_conversion'] ?? 0);
$cantidad = floatval($_POST['cantidad'] ?? 0);
$precio = floatval($_POST['precio'] ?? 0);
$subtotal = floatval($_POST['subtotal'] ?? 0);

if (!$id_proveedor || !$id_producto || !$id_conversion) {
    echo json_encode(["status" => "warning", "message" => "Complete todos los campos."]);
    exit;
}

$conn->begin_transaction();

try {

    $sqlUnidad = "SELECT unidad_medida, factor FROM unidades_conversion WHERE id_conversion = ? LIMIT 1";
    $stmtUnidad = $conn->prepare($sqlUnidad);
    $stmtUnidad->bind_param("i", $id_conversion);
    $stmtUnidad->execute();
    $resultUnidad = $stmtUnidad->get_result();

    $factor = 1;
    $unidad_medida = "";

    if ($resultUnidad->num_rows > 0) {
        $rowUnidad = $resultUnidad->fetch_assoc();
        $unidad_medida = trim($rowUnidad['unidad_medida']);
        $factor = floatval($rowUnidad['factor']);
    }

    $cantidad_convertida = $cantidad * $factor;

    date_default_timezone_set('America/Mexico_City');
    $fecha = date("Y-m-d");
    $hora = date("H:i:s");

    $sqlCompra = "INSERT INTO compras (id_proveedor, fecha, hora, total)
                  VALUES (?, ?, ?, ?)";
    $stmtCompra = $conn->prepare($sqlCompra);
    $stmtCompra->bind_param("issi", $id_proveedor, $fecha, $hora, $subtotal);
    $stmtCompra->execute();
    $id_compra = $stmtCompra->insert_id;

    $sqlDetalle = "INSERT INTO detalle_compra 
        (id_producto, id_compra, unidad_medida, precio_unitario, cantidad, subtotal)
        VALUES (?, ?, ?, ?, ?, ?)";

    $stmtDetalle = $conn->prepare($sqlDetalle);
    $stmtDetalle->bind_param("iisdid", 
        $id_producto, 
        $id_compra, 
        $unidad_medida, 
        $precio, 
        $cantidad, 
        $subtotal
    );
    $stmtDetalle->execute();

    $sqlStock = "UPDATE productos SET stock = stock + ? WHERE id_producto = ?";
    $stmtStock = $conn->prepare($sqlStock);
    $stmtStock->bind_param("di", $cantidad_convertida, $id_producto);
    $stmtStock->execute();
    
    $conn->commit();

    echo json_encode([
        "status" => "success",
        "message" => "Compra registrada correctamente."
    ]);
    exit;

} catch (Exception $e) {

    $conn->rollback();

    echo json_encode([
        "status" => "error",
        "message" => "Error al registrar la compra: " . $e->getMessage()
    ]);
    exit;
}
?>
