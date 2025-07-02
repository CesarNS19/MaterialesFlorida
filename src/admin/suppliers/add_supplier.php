<?php
require '../../../mysql/connection.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = $_POST['nombre'];
    $producto = $_POST['producto'];
    $telefono = $_POST['telefono'];
    $email = $_POST['email'];
    $direccion = $_POST['direccion'];
    $estatus = 'activo';
    $fecha = date('Y-m-d');

    $sql = "INSERT INTO proveedores (nombre, producto, telefono, email, direccion, estatus, fecha_ingreso) VALUES (?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssss", $nombre, $producto, $telefono, $email, $direccion, $estatus, $fecha);

    if ($stmt->execute()) {
        $_SESSION['status_message'] = "Proveedor agregado exitosamente.";
        $_SESSION['status_type'] = "success";
    } else {
        $_SESSION['status_message'] = "Error al agregar el producto: " . $stmt->error;
        $_SESSION['status_type'] = "danger";
    }

    $stmt->close();
    header("Location: ../suppliers.php");
    exit();
}

$conn->close();
?>
