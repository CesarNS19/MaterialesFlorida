<?php
require '../../../mysql/connection.php';
session_start();

if (isset($_POST['id_usuario'])) {
    $id = intval($_POST['id_usuario']);

    $sql = "DELETE FROM usuarios WHERE id_usuario = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        $_SESSION['status_message'] = 'Cliente eliminado correctamente.';
        $_SESSION['status_type'] = 'success';
    } else {
        $_SESSION['status_message'] = 'Error al eliminar el cliente: ' . $stmt->error;
        $_SESSION['status_type'] = 'danger';
    }

    $stmt->close();
} else {
    $_SESSION['status_message'] = 'ID de cliente no especificado.';
    $_SESSION['status_type'] = 'warning';
}

header('Location: ../customers.php');
exit();

$conn->close();
?>