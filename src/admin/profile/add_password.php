<?php
session_start();
include "../../../mysql/connection.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['contrasena'])) {
    
    $id_usuario = $_SESSION['id_usuario'];
    $password_caja = trim($_POST['contrasena']);

    if (empty($password_caja)) {
        $_SESSION['status_message'] = "La contraseña no puede estar vacía.";
        $_SESSION['status_type'] = "warning";
        header("Location: ../profile.php");
        exit();
    }

    $hashed_password = password_hash($password_caja, PASSWORD_DEFAULT);

    $sql_check = "SELECT password_caja FROM usuarios WHERE id_usuario = ?";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bind_param("i", $id_usuario);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();
    $user = $result_check->fetch_assoc();
    $stmt_check->close();

    $accion = empty($user['password_caja']) ? "agregada" : "actualizada";

    $sql = "UPDATE usuarios SET password_caja = ? WHERE id_usuario = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $hashed_password, $id_usuario);

    if ($stmt->execute()) {
        $_SESSION['status_message'] = "Contraseña de caja $accion correctamente.";
        $_SESSION['status_type'] = "success";
    } else {
        $_SESSION['status_message'] = "Error al $accion la contraseña de caja.";
        $_SESSION['status_type'] = "error";
    }

    $stmt->close();
    $conn->close();

    header("Location: ../profile.php");
    exit();
} else {
    header("Location: ../profile.php");
    exit();
}
