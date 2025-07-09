<?php
require '../../../mysql/connection.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_direccion = $_POST['id_direccion'];
    $num_int = $_POST['num_int'];
    $num_ext = $_POST['num_ext'];
    $calle = $_POST['calle'];
    $ciudad = $_POST['ciudad'];
    $estado = $_POST['estado'];
    $codigo_postal = $_POST['codigo_postal'];

    $sql = "UPDATE direcciones SET num_int = ?, num_ext = ?, calle = ?, ciudad = ?, estado = ?, codigo_postal = ? WHERE id_direccion = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iisssii", $num_int, $num_ext, $calle, $ciudad, $estado, $codigo_postal, $id_direccion);

    if ($stmt->execute()) {
        $_SESSION['status_message'] = "Dirección actualizada exitosamente.";
        $_SESSION['status_type'] = "success";
    } else {
        $_SESSION['status_message'] = "Error al actualizar la dirección: " . $stmt->error;
        $_SESSION['status_type'] = "danger";
    }

    $stmt->close();
    header("Location: ../addresses.php");
    exit();
}

$conn->close();
?>
