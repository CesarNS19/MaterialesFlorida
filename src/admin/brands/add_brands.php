<?php
require '../../../mysql/connection.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $marca = $_POST['nombre'];
    $descripcion = $_POST['descripcion'];

    $sql = "INSERT INTO marcas (nombre, descripcion)
            VALUES (?, ?)";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $marca, $descripcion);

    if ($stmt->execute()) {
        $_SESSION['status_message'] = "Marca agregada exitosamente.";
        $_SESSION['status_type'] = "success";
    } else {
        $_SESSION['status_message'] = "Error al agregar la marca: " . $stmt->error;
        $_SESSION['status_type'] = "danger";
    }

    $stmt->close();
    header("Location: ../brands.php");
    exit();
}

$conn->close();
?>