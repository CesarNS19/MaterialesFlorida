<?php
require '../../../mysql/connection.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_caja = intval($_POST['id_caja']);
    $saldo_real = floatval($_POST['saldo_real']);
    $tipo_corte = $_POST['tipo_corte'] ?? 'diario';

    $ventas = $conn->query("SELECT SUM(total) AS total FROM ventas WHERE DATE(fecha) = CURDATE()");
    $totalVentas = $ventas->fetch_assoc()['total'] ?? 0;

    $retiros = $conn->query("SELECT SUM(monto) AS total FROM retiros_caja WHERE id_caja = $id_caja");
    $totalRetiros = $retiros->fetch_assoc()['total'] ?? 0;

    $caja = $conn->query("SELECT saldo_inicial FROM cajas WHERE id_caja = $id_caja")->fetch_assoc();
    $saldoSistema = ($caja['saldo_inicial'] + $totalVentas) - $totalRetiros;

    $diferencia = $saldo_real - $saldoSistema;

    $stmt = $conn->prepare("INSERT INTO cortes_caja (id_caja, total_ventas, total_retiros, saldo_sistema, saldo_real, diferencia, tipo_corte)
                            VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param('iddddds', $id_caja, $totalVentas, $totalRetiros, $saldoSistema, $saldo_real, $diferencia, $tipo_corte);

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
