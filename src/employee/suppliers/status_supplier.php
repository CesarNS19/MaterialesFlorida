<?php
require '../../../mysql/connection.php';
session_start();

if (isset($_GET['id']) && isset($_GET['estatus'])) {
    $id_proveedor = intval($_GET['id']);
    $nuevo_estatus = $_GET['estatus'];

    if ($nuevo_estatus === 'activo' || $nuevo_estatus === 'inactivo') {
        $sql = "UPDATE proveedores SET estatus = ? WHERE id_proveedor = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('si', $nuevo_estatus, $id_proveedor);

        if ($stmt->execute()) {
            $_SESSION['status_message'] = $nuevo_estatus === 'activo' ? 'Proveedor activado correctamente.' : 'Proveedor desactivado correctamente.';
            $_SESSION['status_type'] = 'success';
        } else {
            $_SESSION['status_message'] = 'Error al actualizar el estado del proveedor: ' . $stmt->error;
            $_SESSION['status_type'] = 'danger';
        }

        $stmt->close();
    } else {
        $_SESSION['status_message'] = 'Estatus inválido.';
        $_SESSION['status_type'] = 'warning';
    }
} else {
    $_SESSION['status_message'] = 'ID o estatus no especificado.';
    $_SESSION['status_type'] = 'warning';
}

header('Location: ' . $_SERVER['HTTP_REFERER']);
exit();

$conn->close();
?>
