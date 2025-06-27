<?php
require '../../../mysql/connection.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = $_POST['nombre'];
    $apellido_paterno = $_POST['apellido_paterno'];
    $apellido_materno = $_POST['apellido_materno'];
    $telefono = $_POST['telefono'];
    $email = $_POST['email'];
    $contrasena = $_POST['contrasena'];
    $estatus = 'activo';
    $rol = 'empleado';

    $checkEmailSql = "SELECT id_empleado FROM empleados WHERE email = ?";
    $checkEmailStmt = $conn->prepare($checkEmailSql);
    $checkEmailStmt->bind_param("s", $email);
    $checkEmailStmt->execute();
    $checkEmailResult = $checkEmailStmt->get_result();

    if ($checkEmailResult->num_rows > 0) {
        $_SESSION['status_message'] = "El correo electrónico ya está registrado, intente con otro";
        $_SESSION['status_type'] = "danger";
    } else {
        $hashed_password = password_hash($contrasena, PASSWORD_DEFAULT);

        $sql = "INSERT INTO empleados (nombre, apellido_paterno, apellido_materno, telefono, email, contrasena, rol, estatus) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssssss", $nombre, $apellido_paterno, $apellido_materno, $telefono, $email, $hashed_password, $rol, $estatus);

        if ($stmt->execute()) {
            $_SESSION['status_message'] = "Empleado agregado exitosamente.";
            $_SESSION['status_type'] = "success";
        } else {
            $_SESSION['status_message'] = "Error al agregar el empleado: " . $stmt->error;
            $_SESSION['status_type'] = "danger";
        }

        $stmt->close();
    }

    $checkEmailStmt->close();
    $conn->close();

    header("Location: ../employees.php");
    exit();
}
?>
