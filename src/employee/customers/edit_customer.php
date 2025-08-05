<?php
require '../../../mysql/connection.php';
session_start();

if (isset($_POST['id_usuario'])) {
    $id = $_POST['id_usuario'];

    $sql = "SELECT * FROM usuarios WHERE id_usuario = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $empleado = $result->fetch_assoc();

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
         $id_direccion = $_POST['id_direccion'];
        $nombre = $_POST['nombre'];
        $apellido_paterno = $_POST['apellido_paterno'];
        $apellido_materno = $_POST['apellido_materno'];
        $email = $_POST['email'];
        $telefono = $_POST['telefono'];

        $checkEmailSql = "SELECT id_usuario FROM usuarios WHERE email = ? AND id_usuario != ?";
        $checkEmailStmt = $conn->prepare($checkEmailSql);
        $checkEmailStmt->bind_param("si", $email, $id);
        $checkEmailStmt->execute();
        $checkEmailResult = $checkEmailStmt->get_result();

        if ($checkEmailResult->num_rows > 0) {
            $_SESSION['status_message'] = 'El correo electrónico ya está registrado en otro cliente.';
            $_SESSION['status_type'] = 'danger';
            $checkEmailStmt->close();
            header("Location: ../customers.php");
            exit();
        }

        $sql = "UPDATE usuarios SET id_direccion = ?, nombre = ?, apellido_paterno = ?, apellido_materno = ?, email = ?, telefono = ? WHERE id_usuario = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("isssssi", $id_direccion, $nombre, $apellido_paterno, $apellido_materno, $email, $telefono, $id);

        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                $_SESSION['status_message'] = 'Cliente actualizado exitosamente.';
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
        header("Location: ../customers.php");
        exit();
    }
}

$conn->close();
?>
