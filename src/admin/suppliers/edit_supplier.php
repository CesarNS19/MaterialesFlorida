<?php
require '../../../mysql/connection.php';
session_start();

if (isset($_POST['id_proveedor'])) {
    $id = $_POST['id_proveedor'];

    $sql = "SELECT * FROM proveedores";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $result = $stmt->get_result();
    $cliente = $result->fetch_assoc();

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $nombre = $_POST['nombre'];
        $producto = $_POST['producto'];
        $telefono = $_POST['telefono'];
        $email = $_POST['email'];
        $direccion = $_POST['direccion'];
    
        $sql = "UPDATE proveedores SET nombre = ?, producto = ?, telefono = ?, email = ?, direccion = ? WHERE id_proveedor = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssi", $nombre, $producto, $telefono, $email, $direccion, $id);
    
        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                $_SESSION['status_message'] = 'Proveedor actualizado exitosamente.';
                $_SESSION['status_type'] = 'success';
            } else {
                $_SESSION['status_message'] = 'No se realizaron cambios. Verifica los datos.';
                $_SESSION['status_type'] = 'warning';
            }
        } else {
            $_SESSION['status_message'] = 'Error al actualizar los datos: ' . $stmt->error;
            $_SESSION['status_type'] = 'danger';
        }

        $stmt->close();
        header("Location: ../suppliers.php");
        exit();
    }
}
?>
