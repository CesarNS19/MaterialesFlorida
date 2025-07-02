<?php
require '../../../mysql/connection.php';
session_start();

if (isset($_POST['id_proveedor'])) {
    $id = intval($_POST['id_proveedor']);

    $sql = "DELETE FROM proveedores WHERE id_proveedor = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        $_SESSION['status_message'] = 'Proveedor eliminado correctamente.';
        $_SESSION['status_type'] = 'success';
    } else {
        $_SESSION['status_message'] = 'Error al eliminar el proveedor: ' . $stmt->error;
        $_SESSION['status_type'] = 'danger';
    }

    $stmt->close();
} else {
    $_SESSION['status_message'] = 'ID de provedor no especificado.';
    $_SESSION['status_type'] = 'warning';
}

header('Location: ../suppliers.php');
exit();

$conn->close();
?>