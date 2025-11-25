<?php
require '../../../mysql/connection.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] === "POST" && (isset($_POST["product_code"]) || isset($_POST["id_producto"]))) {
    $id_usuario = isset($_POST["id_usuario"]) ? intval($_POST["id_usuario"]) : null;
    $codigo = isset($_POST["product_code"]) ? trim($_POST["product_code"]) : null;
    $id_producto = isset($_POST["id_producto"]) ? intval($_POST["id_producto"]) : null;

    if (!$id_usuario) {
        $_SESSION['status_message'] = "Error: ID de usuario no proporcionado.";
        $_SESSION['status_type'] = "error";
        header("Location: ../sales.php");
        exit;
    }

    if ($id_producto) {
        $stmt = $conn->prepare("SELECT id_producto, precio, stock, estado FROM productos WHERE id_producto = ?");
        $stmt->bind_param("i", $id_producto);
    } elseif ($codigo) {
        $stmt = $conn->prepare("SELECT id_producto, precio, stock, estado FROM productos WHERE codigo = ?");
        $stmt->bind_param("s", $codigo);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    if (!$row) {
        $_SESSION['status_message'] = "Producto no encontrado.";
        $_SESSION['status_type'] = "warning";
        header("Location: ../sales.php?id_usuario=" . $id_usuario);
        exit;
    }

    if (strtolower($row['estado']) === 'inactivo') {
        $_SESSION['status_message'] = "Producto no disponible.";
        $_SESSION['status_type'] = "warning";
        header("Location: ../sales.php?id_usuario=" . $id_usuario);
        exit;
    }

    $id_producto = $row['id_producto'];
    $precio = floatval($row['precio']);
    $stock = floatval($row['stock']);

    if ($stock <= 0) {
        $_SESSION['status_message'] = "Producto agotado.";
        $_SESSION['status_type'] = "warning";
        header("Location: ../sales.php?id_usuario=" . $id_usuario);
        exit;
    }

    $queryUnidadBase = $conn->prepare("
        SELECT um.nombre 
        FROM productos p
        JOIN unidades_medida um ON p.id_unidad_medida = um.id_unidad_medida
        WHERE p.id_producto = ?
        LIMIT 1
    ");
    $queryUnidadBase->bind_param("i", $id_producto);
    $queryUnidadBase->execute();
    $resultUnidadBase = $queryUnidadBase->get_result();
    $unidad_seleccionada = ($rowUnidadBase = $resultUnidadBase->fetch_assoc())
        ? $rowUnidadBase['nombre']
        : 'unidad';

    $check = $conn->prepare("SELECT cantidad FROM carrito WHERE id_producto = ? AND id_usuario = ?");
    $check->bind_param("ii", $id_producto, $id_usuario);
    $check->execute();
    $res = $check->get_result();

    if ($res->num_rows > 0) {
        $row_cart = $res->fetch_assoc();
        $cantidad_actual_carrito = floatval($row_cart['cantidad']);
        $nueva_cantidad = $cantidad_actual_carrito + 1;

        if ($nueva_cantidad > $stock) {
            $_SESSION['status_message'] = "No hay suficiente stock disponible.";
            $_SESSION['status_type'] = "warning";
            header("Location: ../sales.php?id_usuario=" . $id_usuario);
            exit;
        }

        $subtotal = $precio * $nueva_cantidad;

        $update = $conn->prepare("
            UPDATE carrito 
            SET cantidad = ?, subtotal = ?, unidad_seleccionada = ?
            WHERE id_producto = ? AND id_usuario = ?
        ");
        $update->bind_param("idsii", $nueva_cantidad, $subtotal, $unidad_seleccionada, $id_producto, $id_usuario);
        $update->execute();

        $_SESSION['status_message'] = "Producto actualizado en el carrito.";
        $_SESSION['status_type'] = "info";
    } else {
        $cantidad = 1;
        $subtotal = $precio;

        $insert = $conn->prepare("
            INSERT INTO carrito (id_producto, id_usuario, cantidad, unidad_seleccionada)
            VALUES (?, ?, ?, ?)
        ");
        $insert->bind_param("iiis", $id_producto, $id_usuario, $cantidad, $unidad_seleccionada);
        $insert->execute();

        $_SESSION['status_message'] = "Producto agregado al carrito.";
        $_SESSION['status_type'] = "success";
    }

    header("Location: ../sales.php?id_usuario=" . $id_usuario);
    exit;
}
?>
