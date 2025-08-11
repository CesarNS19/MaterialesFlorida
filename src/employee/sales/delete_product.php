<?php
session_start();
require '../../../mysql/connection.php';

if (isset($_GET['id']) && isset($_GET['id_usuario'])) {
    $id_carrito = intval($_GET['id']);
    $id_usuario = intval($_GET['id_usuario']);

    $query = "SELECT id_producto, cantidad, unidad_seleccionada FROM carrito WHERE id_carrito = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id_carrito);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $id_producto = $row['id_producto'];
        $cantidad = floatval($row['cantidad']);
        $unidad_seleccionada = $row['unidad_seleccionada'];

        $factor = 1;
        $factorQuery = "SELECT factor FROM unidades_conversion WHERE id_producto = ? AND unidad_medida = ? LIMIT 1";
        $stmtFactor = $conn->prepare($factorQuery);
        $stmtFactor->bind_param("is", $id_producto, $unidad_seleccionada);
        $stmtFactor->execute();
        $resultFactor = $stmtFactor->get_result();
        if ($resultFactor && $resultFactor->num_rows > 0) {
            $rowFactor = $resultFactor->fetch_assoc();
            $factor = floatval($rowFactor['factor']);
        }
        $stmtFactor->close();

        $stock_a_sumar = $cantidad * $factor;

        $update_stock = "UPDATE productos SET stock = stock + ? WHERE id_producto = ?";
        $stmtUpdate = $conn->prepare($update_stock);
        $stmtUpdate->bind_param("di", $stock_a_sumar, $id_producto);

        if ($stmtUpdate->execute()) {

            $delete_sql = "DELETE FROM carrito WHERE id_carrito = ?";
            $stmtDelete = $conn->prepare($delete_sql);
            $stmtDelete->bind_param("i", $id_carrito);

            if ($stmtDelete->execute()) {
                $_SESSION['status_message'] = "Producto eliminado del carrito exitosamente.";
                $_SESSION['status_type'] = "success";
            } else {
                $_SESSION['status_message'] = "Error al eliminar el producto del carrito: " . $conn->error;
                $_SESSION['status_type'] = "danger";
            }
            $stmtDelete->close();

        } else {
            $_SESSION['status_message'] = "Error al actualizar el stock: " . $conn->error;
            $_SESSION['status_type'] = "danger";
        }
        $stmtUpdate->close();

    } else {
        $_SESSION['status_message'] = "Producto no encontrado en el carrito.";
        $_SESSION['status_type'] = "warning";
    }
    $stmt->close();

} else {
    $_SESSION['status_message'] = "Faltan parámetros para eliminar el producto.";
    $_SESSION['status_type'] = "warning";
}

header("Location: ../sales.php?id_usuario=" . $id_usuario);
exit;
?>
