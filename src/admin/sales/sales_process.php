<?php
session_start();
require '../../../mysql/connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_usuario'])) {
    $id_usuario = intval($_POST['id_usuario']);

    $carrito_sql = "SELECT c.id_producto, c.cantidad, c.precio, c.subtotal
                    FROM carrito c
                    WHERE c.id_usuario = $id_usuario";
    $result = $conn->query($carrito_sql);

    if ($result->num_rows === 0) {
        $_SESSION['status_message'] = "El carrito está vacío.";
        $_SESSION['status_type'] = "warning";
        header("Location: ../sales.php?id_usuario=$id_usuario");
        exit;
    }

    $productos = [];
    $total = 0;

    while ($row = $result->fetch_assoc()) {
        $productos[] = $row;
        $total += $row['subtotal'];
    }

    $fecha = date('Y-m-d H:i:s');
    $insertVenta = "INSERT INTO ventas (id_usuario, fecha, total) VALUES ($id_usuario, '$fecha', $total)";

    if ($conn->query($insertVenta)) {
        $id_venta = $conn->insert_id;

        $ok = true;
        foreach ($productos as $p) {
            $id_producto = $p['id_producto'];
            $cantidad = $p['cantidad'];
            $precio_unitario = $p['precio'];
            $subtotal = $p['subtotal'];

            $insertDetalle = "INSERT INTO detalle_venta (id_producto, id_venta, cantidad, precio_unitario, subtotal)
                              VALUES ($id_producto, $id_venta, $cantidad, $precio_unitario, $subtotal)";

            if (!$conn->query($insertDetalle)) {
                $ok = false;
                break;
            }

            $conn->query("UPDATE productos SET stock = stock - $cantidad WHERE id_producto = $id_producto");
        }

        if ($ok) {
            $conn->query("DELETE FROM carrito WHERE id_usuario = $id_usuario");

            $_SESSION['status_message'] = "Venta realizada exitosamente.";
            $_SESSION['status_type'] = "success";
        } else {
            $_SESSION['status_message'] = "Error al guardar detalles de la venta.";
            $_SESSION['status_type'] = "error";
        }
    } else {
        $_SESSION['status_message'] = "Error al registrar la venta.";
        $_SESSION['status_type'] = "error";
    }

    header("Location: ../sales.php?id_usuario=$id_usuario");
    exit;
} else {
    $_SESSION['status_message'] = "Datos inválidos para procesar la venta.";
    $_SESSION['status_type'] = "error";
    header("Location: ../sales.php");
    exit;
}
