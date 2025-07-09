<?php
require '../../../mysql/connection.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_direccion = $_POST['id_direccion'];
    $nombre = $_POST['nombre'];
    $producto = $_POST['producto'];
    $telefono = $_POST['telefono'];
    $email = $_POST['email'];
    $estatus = 'activo';
    $fecha = date('Y-m-d');

    $sql = "INSERT INTO proveedores (id_direccion, nombre, producto, telefono, email, estatus, fecha_ingreso) VALUES (?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
        $stmt->bind_param("issssss", $id_direccion,$nombre, $producto, $telefono, $email, $estatus, $fecha);

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
