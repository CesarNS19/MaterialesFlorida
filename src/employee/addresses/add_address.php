<?php
require '../../../mysql/connection.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $num_int = $_POST['num_int'];
    $num_ext = $_POST['num_ext'];
    $calle = $_POST['calle'];
    $ciudad = $_POST['ciudad'];
    $estado = $_POST['estado'];
    $codigo_postal = $_POST['codigo_postal'];

    $sql = "INSERT INTO direcciones (num_int, num_ext, calle, ciudad, estado, codigo_postal) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iisssi", $num_int, $num_ext, $calle, $ciudad, $estado, $codigo_postal);

    if ($stmt->execute()) {
        $_SESSION['status_message'] = "Dirección agregada exitosamente.";
        $_SESSION['status_type'] = "success";
    } else {
        $_SESSION['status_message'] = "Error al agregar la dirección: " . $stmt->error;
        $_SESSION['status_type'] = "danger";
    }

    $stmt->close();
    header("Location: ../addresses.php");
    exit();
}

$conn->close();
?>
