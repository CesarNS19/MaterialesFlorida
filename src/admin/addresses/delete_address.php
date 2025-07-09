<?php
require '../../../mysql/connection.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_direccion = $_POST['id_direccion'];

    $sql = "DELETE FROM direcciones WHERE id_direccion = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id_direccion);

    if ($stmt->execute()) {
        $_SESSION['status_message'] = "Dirección eliminada correctamente.";
        $_SESSION['status_type'] = "success";
    } else {
        $_SESSION['status_message'] = "Error al eliminar la dirección: " . $stmt->error;
        $_SESSION['status_type'] = "danger";
    }

    $stmt->close();
    header("Location: ../addresses.php");
    exit();
}

$conn->close();
?>
