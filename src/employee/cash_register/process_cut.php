<?php
require '../../../mysql/connection.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_caja = intval($_POST['id_caja']);
    $saldo_real_input = trim($_POST['saldo_real']);
    $password_caja = trim($_POST['password_caja'] ?? '');
    $tipo_corte = $_POST['tipo_corte'] ?? 'diario';

    if ($saldo_real_input === '' || !is_numeric($saldo_real_input)) {
        $_SESSION['status_message'] = "Debe ingresar un saldo real válido para procesar el corte.";
        $_SESSION['status_type'] = "warning";
        header("Location: ../cash_register.php");
        exit();
    }
    $saldo_real = floatval($saldo_real_input);

    if ($password_caja === '') {
        $_SESSION['status_message'] = "Debe ingresar la contraseña de caja de un administrador.";
        $_SESSION['status_type'] = "warning";
        header("Location: ../cash_register.php");
        exit();
    }

    $stmt_admins = $conn->prepare("SELECT password_caja FROM usuarios WHERE id_perfil = 1 AND password_caja IS NOT NULL");
    $stmt_admins->execute();
    $result_admins = $stmt_admins->get_result();

    $passwordValida = false;
    while ($admin = $result_admins->fetch_assoc()) {
        if (password_verify($password_caja, $admin['password_caja'])) {
            $passwordValida = true;
            break;
        }
    }
    $stmt_admins->close();

    if (!$passwordValida) {
        $_SESSION['status_message'] = "Contraseña de caja incorrecta o no pertenece a ningún administrador.";
        $_SESSION['status_type'] = "error";
        header("Location: ../cash_register.php");
        exit();
    }

    $totalVentas = $conn->query("SELECT SUM(total) AS total FROM ventas WHERE id_caja = $id_caja")->fetch_assoc()['total'] ?? 0;
    $totalRetiros = $conn->query("SELECT SUM(monto) AS total FROM retiros_caja WHERE id_caja = $id_caja")->fetch_assoc()['total'] ?? 0;

    $caja = $conn->query("SELECT saldo_inicial FROM cajas WHERE id_caja = $id_caja")->fetch_assoc();
    $saldoSistema = ($caja['saldo_inicial'] + $totalVentas) - $totalRetiros;

    $diferencia = $saldo_real - $saldoSistema;

    $stmt = $conn->prepare("INSERT INTO cortes_caja (id_caja, total_ventas, total_retiros, saldo_sistema, saldo_real, diferencia, fecha_corte)
                            VALUES (?, ?, ?, ?, ?, ?, NOW())");
    $stmt->bind_param('iddddd', $id_caja, $totalVentas, $totalRetiros, $saldoSistema, $saldo_real, $diferencia);

    if ($stmt->execute()) {
        $conn->query("UPDATE cajas SET saldo_final = $saldo_real, estado = 'cerrada', fecha_cierre = NOW() WHERE id_caja = $id_caja");

        $_SESSION['status_message'] = "Corte realizado correctamente. Diferencia: $" . number_format($diferencia, 2);
        $_SESSION['status_type'] = ($diferencia == 0) ? "success" : "warning";
    } else {
        $_SESSION['status_message'] = "Error al realizar el corte: " . $stmt->error;
        $_SESSION['status_type'] = "danger";
    }

    $stmt->close();
    header("Location: ../cash_register.php");
    exit();
}

$conn->close();
?>
