<?php
session_start();
require '../../../mysql/connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_proveedor = isset($_POST['id_provedor']) ? intval($_POST['id_provedor']) : null;
    $id_producto = isset($_POST['id_producto']) ? intval($_POST['id_producto']) : null;
    $cantidad = isset($_POST['cantidad']) ? floatval($_POST['cantidad']) : 0;
    $precio = isset($_POST['precio']) ? floatval($_POST['precio']) : 0;
    $subtotal = isset($_POST['subtotal']) ? floatval($_POST['subtotal']) : 0;

    if ($id_proveedor && $id_producto && $cantidad > 0 && $precio > 0 && $subtotal > 0) {
        $conn->begin_transaction();

        try {
            $fecha = date("Y-m-d");
            $sqlCompra = "INSERT INTO compras (id_proveedor, fecha, total) VALUES (?, ?, ?)";
            $stmtCompra = $conn->prepare($sqlCompra);
            $stmtCompra->bind_param("isi", $id_proveedor, $fecha, $subtotal);
            $stmtCompra->execute();
            $id_compra = $stmtCompra->insert_id;

            $sqlDetalle = "INSERT INTO detalle_compra (id_producto, id_compra, precio_unitario, cantidad, subtotal) VALUES (?, ?, ?, ?, ?)";
            $stmtDetalle = $conn->prepare($sqlDetalle);
            $stmtDetalle->bind_param("iiddi", $id_producto, $id_compra, $precio, $cantidad, $subtotal);
            $stmtDetalle->execute();

            $sqlStock = "UPDATE productos SET stock = stock + ? WHERE id_producto = ?";
            $stmtStock = $conn->prepare($sqlStock);
            $stmtStock->bind_param("di", $cantidad, $id_producto);
            $stmtStock->execute();

            $sqlPrecioCompra = "UPDATE productos SET precio_compra = ? WHERE id_producto = ?";
            $stmtPrecioCompra = $conn->prepare($sqlPrecioCompra);
            $stmtPrecioCompra->bind_param("di", $precio, $id_producto);
            $stmtPrecioCompra->execute();

            $conn->commit();

            $_SESSION['status_message'] = "Compra registrada correctamente.";
            $_SESSION['status_type'] = "success";
        } catch (Exception $e) {
            $conn->rollback();
            $_SESSION['status_message'] = "Error al registrar la compra: " . $e->getMessage();
            $_SESSION['status_type'] = "error";
        }

    } else {
        $_SESSION['status_message'] = "Por favor, complete todos los campos correctamente.";
        $_SESSION['status_type'] = "warning";
    }
} else {
    $_SESSION['status_message'] = "Solicitud inválida.";
    $_SESSION['status_type'] = "error";
}

header("Location: ../add_shopping.php");
exit;
?>
