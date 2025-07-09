<?php
require '../../../mysql/connection.php';
session_start();

if (isset($_POST['id_producto'])) {
    $id = intval($_POST['id_producto']);

    $sql = "DELETE FROM productos WHERE id_producto = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        $_SESSION['status_message'] = 'Producto eliminado correctamente.';
        $_SESSION['status_type'] = 'success';
    } else {
        $_SESSION['status_message'] = 'Error al eliminar el producto: ' . $stmt->error;
        $_SESSION['status_type'] = 'danger';
    }

    $stmt->close();
} else {
    $_SESSION['status_message'] = 'ID de producto no especificado.';
    $_SESSION['status_type'] = 'warning';
}

header('Location: ../products.php');
exit();

$conn->close();
?>