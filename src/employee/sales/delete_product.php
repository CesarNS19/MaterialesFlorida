<?php
session_start();
require '../../../mysql/connection.php';

if (isset($_GET['id']) && isset($_GET['id_usuario'])) {
    $id_carrito = intval($_GET['id']);
    $id_usuario = intval($_GET['id_usuario']);

    $query = "SELECT id_producto, cantidad FROM carrito WHERE id_carrito = $id_carrito";
    $result = $conn->query($query);

    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $id_producto = $row['id_producto'];
        $cantidad = $row['cantidad'];

        $update_stock = "UPDATE productos SET stock = stock + $cantidad WHERE id_producto = $id_producto";
        if ($conn->query($update_stock) === TRUE) {

            $delete_sql = "DELETE FROM carrito WHERE id_carrito = $id_carrito";
            if ($conn->query($delete_sql) === TRUE) {
                $_SESSION['status_message'] = "Producto eliminado del carrito exitosamente.";
                $_SESSION['status_type'] = "success";
            } else {
                $_SESSION['status_message'] = "Error al eliminar el producto del carrito: " . $conn->error;
                $_SESSION['status_type'] = "danger";
            }

        } else {
            $_SESSION['status_message'] = "Error al actualizar el stock: " . $conn->error;
            $_SESSION['status_type'] = "danger";
        }

    } else {
        $_SESSION['status_message'] = "Producto no encontrado en el carrito.";
        $_SESSION['status_type'] = "warning";
    }

} else {
    $_SESSION['status_message'] = "Faltan parámetros para eliminar el producto.";
    $_SESSION['status_type'] = "warning";
}

header("Location: ../sales.php?id_usuario=" . $id_usuario);
exit;
