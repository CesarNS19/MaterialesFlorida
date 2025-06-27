<?php
require '../../../mysql/connection.php';
session_start();

if (isset($_POST['id_empleado'])) {
    $id = intval($_POST['id_empleado']);

    $sql = "DELETE FROM empleados WHERE id_empleado = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        $_SESSION['status_message'] = 'Empleado eliminado correctamente.';
        $_SESSION['status_type'] = 'success';
    } else {
        $_SESSION['status_message'] = 'Error al eliminar el empleado: ' . $stmt->error;
        $_SESSION['status_type'] = 'danger';
    }

    $stmt->close();
} else {
    $_SESSION['status_message'] = 'ID de empleado no especificado.';
    $_SESSION['status_type'] = 'warning';
}

header('Location: ../employees.php');
exit();

$conn->close();
?>