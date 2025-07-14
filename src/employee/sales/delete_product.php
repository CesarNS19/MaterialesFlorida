<?php
session_start();
require '../../../mysql/connection.php';

if (isset($_GET['id']) && isset($_GET['id_usuario'])) {
    $id_carrito = intval($_GET['id']);
    $id_usuario = intval($_GET['id_usuario']);

    $sql = "DELETE FROM carrito WHERE id_carrito = $id_carrito";

    if ($conn->query($sql) === TRUE) {
        $_SESSION['status_message'] = "Producto eliminado del carrito correctamente.";
        $_SESSION['status_type'] = "success";
    } else {
        $_SESSION['status_message'] = "Error al eliminar el producto: " . $conn->error;
        $_SESSION['status_type'] = "danger";
    }
} else {
    $_SESSION['status_message'] = "Faltan parámetros para eliminar el producto.";
    $_SESSION['status_type'] = "warning";
}

header("Location: ../sales.php?id_usuario=" . $id_usuario);
exit;
