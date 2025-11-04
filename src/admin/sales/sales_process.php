<?php
session_start();
require '../../../mysql/connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_usuario'], $_POST['id_caja'])) {
    $id_usuario = intval($_POST['id_usuario']);
    $id_caja = intval($_POST['id_caja']);

    $sqlCaja = "SELECT estado FROM cajas WHERE id_caja = ?";
    $stmtCaja = $conn->prepare($sqlCaja);
    $stmtCaja->bind_param('i', $id_caja);
    $stmtCaja->execute();
    $resultCaja = $stmtCaja->get_result();
    $caja = $resultCaja->fetch_assoc();
    $stmtCaja->close();

    if (!$caja) {
        $_SESSION['status_message'] = "No se encontró la caja especificada.";
        $_SESSION['status_type'] = "warning";
        header("Location: ../sales.php?id_usuario=$id_usuario");
        exit;
    }

    if ($caja['estado'] !== 'abierta') {
        $_SESSION['status_message'] = "No se puede realizar la venta, la caja está cerrada.";
        $_SESSION['status_type'] = "warning";
        header("Location: ../sales.php?id_usuario=$id_usuario");
        exit;
    }

    $carrito_sql = "SELECT c.id_producto, c.cantidad, c.unidad_seleccionada, c.precio, c.subtotal
                    FROM carrito c
                    WHERE c.id_usuario = ?";
    $stmtCarrito = $conn->prepare($carrito_sql);
    $stmtCarrito->bind_param('i', $id_usuario);
    $stmtCarrito->execute();
    $result = $stmtCarrito->get_result();

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

    $stmtCarrito->close();

    date_default_timezone_set('America/Mexico_City');
    $fecha = date('Y-m-d H:i:s');

    $insertVenta = "INSERT INTO ventas (id_usuario, id_caja, fecha, total) VALUES (?, ?, ?, ?)";
    $stmtVenta = $conn->prepare($insertVenta);
    $stmtVenta->bind_param('iisd', $id_usuario, $id_caja, $fecha, $total);

    if ($stmtVenta->execute()) {
        $id_venta = $stmtVenta->insert_id;
        $stmtVenta->close();

        $ok = true;

        foreach ($productos as $p) {
            $id_producto = $p['id_producto'];
            $cantidad = $p['cantidad'];
            $unidad_seleccionada = $conn->real_escape_string($p['unidad_seleccionada']);
            $precio_unitario = $p['precio'];
            $subtotal = $p['subtotal'];

            $insertDetalle = "INSERT INTO detalle_venta (id_producto, id_venta, cantidad, unidad_seleccionada, precio_unitario, subtotal)
                              VALUES (?, ?, ?, ?, ?, ?)";
            $stmtDetalle = $conn->prepare($insertDetalle);
            $stmtDetalle->bind_param('iiisdd', $id_producto, $id_venta, $cantidad, $unidad_seleccionada, $precio_unitario, $subtotal);

            if (!$stmtDetalle->execute()) {
                $ok = false;
                $stmtDetalle->close();
                break;
            }

            $stmtDetalle->close();
            $conn->query("UPDATE productos SET stock = stock - $cantidad WHERE id_producto = $id_producto");
        }

        if ($ok) {
            $deleteCarrito = $conn->prepare("DELETE FROM carrito WHERE id_usuario = ?");
            $deleteCarrito->bind_param('i', $id_usuario);
            $deleteCarrito->execute();
            $deleteCarrito->close();

            $_SESSION['status_message'] = "Venta realizada exitosamente. Total: $" . number_format($total, 2);
            $_SESSION['status_type'] = "success";
            header("Location: ../sales.php?id_usuario=$id_usuario&id_venta=$id_venta");
        } else {
            $_SESSION['status_message'] = "Error al guardar los detalles de la venta.";
            $_SESSION['status_type'] = "error";
            header("Location: ../sales.php?id_usuario=$id_usuario");
        }
    } else {
        $_SESSION['status_message'] = "Error al registrar la venta: " . $stmtVenta->error;
        $_SESSION['status_type'] = "error";
        header("Location: ../sales.php?id_usuario=$id_usuario");
    }
    exit;
} else {
    $_SESSION['status_message'] = "Datos inválidos para procesar la venta.";
    $_SESSION['status_type'] = "error";
    header("Location: ../sales.php");
    exit;
}

$conn->close();
?>
