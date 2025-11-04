<?php
require '../../../mysql/connection.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_caja = intval($_POST['id_caja']);
    $id_usuario = intval($_POST['id_usuario']);
    $monto = floatval($_POST['monto']);
    $motivo = trim($_POST['motivo']);

    if ($monto <= 0) {
        $_SESSION['status_message'] = "El monto debe ser mayor que cero.";
        $_SESSION['status_type'] = "warning";
        header("Location: ../cash_register.php");
        exit();
    }

    $sql_caja = "SELECT saldo_inicial, estado FROM cajas WHERE id_caja = ?";
    $stmt_caja = $conn->prepare($sql_caja);
    $stmt_caja->bind_param('i', $id_caja);
    $stmt_caja->execute();
    $caja = $stmt_caja->get_result()->fetch_assoc();
    $stmt_caja->close();

    if (!$caja) {
        $_SESSION['status_message'] = "La caja no existe.";
        $_SESSION['status_type'] = "error";
        header("Location: ../cash_register.php");
        exit();
    }

    if ($caja['estado'] !== 'abierta') {
        $_SESSION['status_message'] = "No se puede hacer un retiro, la caja está cerrada.";
        $_SESSION['status_type'] = "warning";
        header("Location: ../cash_register.php");
        exit();
    }

    $sql_ventas = "
        SELECT IFNULL(SUM(total), 0) AS total_ventas 
        FROM ventas 
        WHERE id_caja = ? AND DATE(fecha) = CURDATE()
    ";
    $stmt_ventas = $conn->prepare($sql_ventas);
    $stmt_ventas->bind_param('i', $id_caja);
    $stmt_ventas->execute();
    $ventas = floatval($stmt_ventas->get_result()->fetch_assoc()['total_ventas']);
    $stmt_ventas->close();

    $sql_retiros = "
        SELECT IFNULL(SUM(monto), 0) AS total_retiros 
        FROM retiros_caja 
        WHERE id_caja = ? AND DATE(fecha_retiro) = CURDATE()
    ";
    $stmt_retiros = $conn->prepare($sql_retiros);
    $stmt_retiros->bind_param('i', $id_caja);
    $stmt_retiros->execute();
    $retiros = floatval($stmt_retiros->get_result()->fetch_assoc()['total_retiros']);
    $stmt_retiros->close();

    $saldo_inicial = floatval($caja['saldo_inicial']);
    $disponible = $saldo_inicial + $ventas - $retiros;

    if ($monto > $disponible) {
        $_SESSION['status_message'] = "No puedes retirar $" . number_format($monto, 2) . ". Solo hay disponible $" . number_format($disponible, 2) . ".";
        $_SESSION['status_type'] = "error";
        header("Location: ../cash_register.php");
        exit();
    }

    $sql_insert = "INSERT INTO retiros_caja (id_caja, id_usuario, monto, motivo) VALUES (?, ?, ?, ?)";
    $stmt_insert = $conn->prepare($sql_insert);
    $stmt_insert->bind_param('iids', $id_caja, $id_usuario, $monto, $motivo);

    if ($stmt_insert->execute()) {
        $_SESSION['status_message'] = "Retiro de $" . number_format($monto, 2) . " registrado correctamente.";
        $_SESSION['status_type'] = "success";
    } else {
        $_SESSION['status_message'] = "Error al registrar el retiro: " . $stmt_insert->error;
        $_SESSION['status_type'] = "error";
    }

    $stmt_insert->close();
    header("Location: ../cash_register.php");
    exit();
}

$conn->close();
?>
