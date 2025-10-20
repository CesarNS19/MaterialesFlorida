<?php
require '../../../mysql/connection.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_usuario = intval($_POST['id_usuario']);
    $saldo_inicial = floatval($_POST['saldo_inicial']);

    $check = $conn->query("SELECT id_caja FROM cajas WHERE estado='abierta' LIMIT 1");
    if ($check->num_rows > 0) {
        $_SESSION['status_message'] = "Ya existe una caja abierta. Debes cerrarla antes de abrir una nueva.";
        $_SESSION['status_type'] = "warning";
        header("Location: ../cash_register.php");
        exit();
    }

    $sql = "INSERT INTO cajas (id_usuario, saldo_inicial, estado) VALUES (?, ?, 'abierta')";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('id', $id_usuario, $saldo_inicial);

    if ($stmt->execute()) {
        $_SESSION['status_message'] = "Caja abierta correctamente.";
        $_SESSION['status_type'] = "success";
    } else {
        $_SESSION['status_message'] = "Error al abrir la caja: " . $stmt->error;
        $_SESSION['status_type'] = "danger";
    }

    $stmt->close();
     header("Location: ../cash_register.php");
    exit();
}

$conn->close();
