<?php
session_start();
include '../mysql/connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['password'], $_POST['confirm_password'], $_POST['code'])) {
    $newPassword = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];
    $verificationCode = $_POST['code'];

    if ($newPassword === $confirmPassword) {
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

        $stmt = $conn->prepare("
            SELECT email FROM usuarios WHERE codigo_recuperacion = ? 
        ");
        $stmt->bind_param("s", $verificationCode);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows > 0) {
            $stmt1 = $conn->prepare("UPDATE usuarios SET contrasena = ?, codigo_recuperacion = NULL WHERE codigo_recuperacion = ?");
            $stmt1->bind_param("ss", $hashedPassword, $verificationCode);
            $stmt1->execute();

            if ($stmt1->affected_rows > 0 || $stmt2->affected_rows > 0) {
                $_SESSION['status_message'] = "Contraseña restablecida con éxito, ahora puede iniciar sesión.";
                $_SESSION['status_type'] = "success";
                header("Location: login.php");
                exit();
            } else {
                $_SESSION['status_message'] = "Hubo un problema al restablecer la contraseña.";
                $_SESSION['status_type'] = "error";
                header("Location: recover_password.php?code=" . $verificationCode);
                exit();
            }

            $stmt1->close();
            $stmt2->close();
        } else {
            $_SESSION['status_message'] = "Código de verificación inválido o expirado.";
            $_SESSION['status_type'] = "error";
            header("Location: recover_password.php?code=" . $verificationCode);
            exit();
        }

        $stmt->close();
    } else {
        $_SESSION['status_message'] = "Las contraseñas no coinciden.";
        $_SESSION['status_type'] = "error";
        header("Location: recover_password.php?code=" . $verificationCode);
        exit();
    }
}

$conn->close();
?>
