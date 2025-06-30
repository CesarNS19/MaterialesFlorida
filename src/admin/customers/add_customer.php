<?php
require '../../../mysql/connection.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = $_POST['nombre'];
    $apellido_paterno = $_POST['apellido_paterno'];
    $apellido_materno = $_POST['apellido_materno'];
    $email = $_POST['email'];
    $contrasena = $_POST['contrasena'];

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
        $hashed_password = password_hash($contrasena, PASSWORD_DEFAULT);

        $sql = "INSERT INTO usuarios (id_perfil, nombre, apellido_paterno, apellido_materno, email, contrasena) 
                VALUES (?, ?, ?, ?, ?, ?)";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("isssss", $id_perfil, $nombre, $apellido_paterno, $apellido_materno, $email, $hashed_password);

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
