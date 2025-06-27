<?php
require '../../../mysql/connection.php';
session_start();

if (isset($_POST['id_marca'])) {
    $id = intval($_POST['id_marca']);

    $sql = "DELETE FROM marcas WHERE id_marca = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        $_SESSION['status_message'] = 'Marca eliminada correctamente.';
        $_SESSION['status_type'] = 'success';
    } else {
        $_SESSION['status_message'] = 'Error al eliminar la Marca: ' . $stmt->error;
        $_SESSION['status_type'] = 'danger';
    }

    $stmt->close();
} else {
    $_SESSION['status_message'] = 'ID de Marca no especificado.';
    $_SESSION['status_type'] = 'warning';
}

header('Location: ../brands.php');
exit();

$conn->close();
?>