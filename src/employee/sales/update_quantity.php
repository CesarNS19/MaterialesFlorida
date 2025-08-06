<?php
session_start();
require '../../../mysql/connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_carrito = isset($_POST['id_carrito']) ? intval($_POST['id_carrito']) : 0;
    $id_usuario = isset($_POST['id_usuario']) ? intval($_POST['id_usuario']) : 0;
    $nueva_cantidad = isset($_POST['cantidad']) ? floatval($_POST['cantidad']) : 0;

    if ($id_carrito <= 0 || $id_usuario <= 0) {
        $_SESSION['status_message'] = "Datos inválidos.";
        $_SESSION['status_type'] = "error";
        header("Location: ../sales.php?id_usuario=$id_usuario");
        exit;
    }

    if ($nueva_cantidad <= 0) {
        $_SESSION['status_message'] = "La cantidad debe ser mayor a cero.";
        $_SESSION['status_type'] = "warning";
        header("Location: ../sales.php?id_usuario=$id_usuario");
        exit;
    }

    $sql = "SELECT c.id_producto, c.cantidad AS cantidad_actual, p.stock, p.precio
            FROM carrito c
            JOIN productos p ON c.id_producto = p.id_producto
            WHERE c.id_carrito = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id_carrito);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $id_producto = $row['id_producto'];
        $cantidad_actual = floatval($row['cantidad_actual']);
        $stock = floatval($row['stock']);
        $precio = floatval($row['precio']);

        if ($nueva_cantidad > ($stock + $cantidad_actual)) {
            $_SESSION['status_message'] = "No hay suficiente stock disponible.";
            $_SESSION['status_type'] = "warning";
            header("Location: ../sales.php?id_usuario=$id_usuario");
            exit;
        }

        $nuevo_subtotal = $precio * $nueva_cantidad;
        $update_cart = $conn->prepare("UPDATE carrito SET cantidad = ?, subtotal = ? WHERE id_carrito = ?");
        $update_cart->bind_param("ddi", $nueva_cantidad, $nuevo_subtotal, $id_carrito);
        $update_cart->execute();

        $nuevo_stock = ($stock + $cantidad_actual) - $nueva_cantidad;
        $update_stock = $conn->prepare("UPDATE productos SET stock = ? WHERE id_producto = ?");
        $update_stock->bind_param("di", $nuevo_stock, $id_producto);
        $update_stock->execute();

        $_SESSION['status_message'] = "Cantidad actualizada correctamente.";
        $_SESSION['status_type'] = "success";
    } else {
        $_SESSION['status_message'] = "Producto no encontrado en el carrito.";
        $_SESSION['status_type'] = "error";
    }

    header("Location: ../sales.php?id_usuario=$id_usuario");
    exit;
}
?>
