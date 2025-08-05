<?php
require '../../../mysql/connection.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_direccion = $_POST['id_direccion'];
    $nombre = $_POST['nombre'];
    $apellido_paterno = $_POST['apellido_paterno'];
    $apellido_materno = $_POST['apellido_materno'];
    $email = $_POST['email'];
    $telefono = $_POST['telefono'];

    $id_perfil = 3;

    $checkEmailSql = "SELECT id_usuario FROM usuarios WHERE email = ?";
    $checkEmailStmt = $conn->prepare($checkEmailSql);
    $checkEmailStmt->bind_param("s", $email);
    $checkEmailStmt->execute();
    $checkEmailResult = $checkEmailStmt->get_result();

    if ($checkEmailResult->num_rows > 0) {
        $_SESSION['status_message'] = "El correo electrónico ya está registrado, intente con otro.";
        $_SESSION['status_type'] = "danger";
    } else {

        $sql = "INSERT INTO usuarios (id_perfil, id_direccion, nombre, apellido_paterno, apellido_materno, email, telefono) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iisssss", $id_perfil, $id_direccion, $nombre, $apellido_paterno, $apellido_materno, $email, $telefono);

        if ($stmt->execute()) {
            $_SESSION['status_message'] = "Cliente agregado exitosamente.";
            $_SESSION['status_type'] = "success";
        } else {
            $_SESSION['status_message'] = "Error al agregar el cliente: " . $stmt->error;
            $_SESSION['status_type'] = "danger";
        }

        $stmt->close();
    }

    $checkEmailStmt->close();
    $conn->close();

    header("Location: ../customers.php");
    exit();
}
?>
