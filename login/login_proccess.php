<?php
session_start();
include '../mysql/connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['contrasena'];

    $stmt = $conn->prepare("
        SELECT u.id_usuario, u.email, u.contrasena, u.nombre AS nombre_usuario,
               u.apellido_paterno, u.apellido_materno,
               p.nombre AS perfil_nombre, p.estatus
        FROM usuarios u
        INNER JOIN perfil p ON u.id_perfil = p.id_perfil
        WHERE u.email = ?
    ");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();

        if (password_verify($password, $row['contrasena'])) {
            if (strtolower($row['estatus']) === 'Inactivo') {
                $_SESSION['status_message'] = "Tu cuenta está inactiva. Contacta al administrador.";
                $_SESSION['status_type'] = "warning";
                header("Location: login.php");
                exit();
            }

            $_SESSION['user'] = $row['email'];
            $_SESSION['id_usuario'] = $row['id_usuario'];
            $_SESSION['nombre'] = $row['nombre_usuario'];
            $_SESSION['apellido_paterno'] = $row['apellido_paterno'];
            $_SESSION['apellido_materno'] = $row['apellido_materno'];
            $_SESSION['perfil'] = $row['perfil_nombre'];
            $_SESSION['last_activity'] = time();
            $_SESSION['expire_time'] = 50000;

            switch ($row['perfil_nombre']) {
                case 'Administrador':
                    header("Location: ../src/admin/index_admin.php");
                    break;
                case 'Empleado':
                    header("Location: ../src/employee/index_employee.php");
                    break;
                default:
                    $_SESSION['status_message'] = "Tipo de perfil no reconocido.";
                    $_SESSION['status_type'] = "error";
                    header("Location: login.php");
                    break;
            }
            exit();
        } else {
            $_SESSION['status_message'] = "Contraseña incorrecta.";
            $_SESSION['status_type'] = "error";
            header("Location: login.php");
            exit();
        }
    } else {
        $_SESSION['status_message'] = "Correo electrónico no encontrado.";
        $_SESSION['status_type'] = "error";
        header("Location: login.php");
        exit();
    }

    $stmt->close();
}
$conn->close();
?>
