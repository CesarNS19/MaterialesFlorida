<?php
session_start();
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '../../vendor/autoload.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];

    $conn = new mysqli("localhost", "root", "", "materiales");

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $conn->set_charset('utf8');

    $sql = "
        SELECT id_usuario, email 
        FROM usuarios 
        WHERE email = ?;
    ";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        $verification_code = bin2hex(random_bytes(4));

        $user = $result->fetch_assoc();
        $user_id = $user['id_usuario'];
        
        if (isset($user['id_usuario'])) {
            $sql = "UPDATE usuarios SET codigo_recuperacion = ? WHERE id_usuario = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("si", $verification_code, $user_id);
            $stmt->execute();
        }

        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'cesarneri803@gmail.com';
            $mail->Password = 'kyoi thod ximj mipk';
            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;

            $mail->setFrom('cesarneri803@gmail.com', 'Materiales La Florida');
            $mail->addAddress($email);

            $mail->isHTML(true);
            $mail->Subject = 'Recuperación de Contraseña';
            $mail->Body = "Tu código para reestablecer tu contraseña es: $verification_code";

            $mail->send();

            $_SESSION['status_message'] = "Te hemos enviado un correo con el código para reestablecer la contraseña.";
            $_SESSION['status_type'] = "success";
            header("Location: recover_password.php");
            exit();
        } catch (Exception $e) {
            $_SESSION['status_message'] = "Error al enviar el correo: {$mail->ErrorInfo}";
            $_SESSION['status_type'] = "error";
            header("Location: login.php?form=forgot_password");
            exit();
        }
    } else {
        $_SESSION['status_message'] = "No se encontró ningún usuario con ese correo electrónico.";
        $_SESSION['status_type'] = "error";
        header("Location: login.php");
        exit();
    }

    $conn->close();
}
?>
