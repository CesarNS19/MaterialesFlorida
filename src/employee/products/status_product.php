<?php
require '../../../mysql/connection.php';
session_start();

if (isset($_GET['id']) && isset($_GET['estatus'])) {
    $id_producto = intval($_GET['id']);
    $nuevo_estatus = $_GET['estatus'];

    if ($nuevo_estatus === 'activo' || $nuevo_estatus === 'inactivo') {
        $sql = "UPDATE productos SET estado = ? WHERE id_producto = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('si', $nuevo_estatus, $id_producto);

        if ($stmt->execute()) {
            $_SESSION['status_message'] = $nuevo_estatus === 'activo' ? 'Producto activado correctamente.' : 'Producto desactivado correctamente.';
            $_SESSION['status_type'] = 'success';
        } else {
            $_SESSION['status_message'] = 'Error al actualizar el estado del producto: ' . $stmt->error;
            $_SESSION['status_type'] = 'danger';
        }

        $stmt->close();
    } else {
        $_SESSION['status_message'] = 'Estado inválido.';
        $_SESSION['status_type'] = 'warning';
    }
} else {
    $_SESSION['status_message'] = 'ID o estado no especificado.';
    $_SESSION['status_type'] = 'warning';
}

header('Location: ' . $_SERVER['HTTP_REFERER']);
exit();

$conn->close();
?>
