<?php
session_start();
require '../../../mysql/connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_carrito = intval($_POST['id_carrito']);
    $cantidad = floatval($_POST['cantidad']);
    $unidad_seleccionada = $_POST['unidad_seleccionada'];
    $id_usuario = intval($_POST['id_usuario']);

    $queryProducto = $conn->query("SELECT id_producto FROM carrito WHERE id_carrito = $id_carrito");
    if (!$queryProducto || $queryProducto->num_rows === 0) {
        $_SESSION['status_message'] = "Producto no encontrado en el carrito.";
        $_SESSION['status_type'] = "error";
        header("Location: ../sales.php?id_usuario=$id_usuario");
        exit;
    }
    $rowProducto = $queryProducto->fetch_assoc();
    $id_producto = $rowProducto['id_producto'];

    $queryPrecios = $conn->query("SELECT precio AS precio_normal, precio_pieza FROM productos WHERE id_producto = $id_producto");
    if (!$queryPrecios || $queryPrecios->num_rows === 0) {
        $_SESSION['status_message'] = "No se encontraron precios para el producto.";
        $_SESSION['status_type'] = "error";
        header("Location: ../sales.php?id_usuario=$id_usuario");
        exit;
    }
    $precios = $queryPrecios->fetch_assoc();

    $precio = $precios['precio_normal'];
    $unidadesPrecioPieza = ['pieza', 'carretilla', 'bulto'];
    if (in_array($unidad_seleccionada, $unidadesPrecioPieza)) {
        $precio = $precios['precio_pieza'];
    }

    if ($unidad_seleccionada === 'normal') {
        $factor = 1;
    } else {
        $queryFactor = $conn->query("SELECT factor FROM unidades_conversion WHERE id_producto = $id_producto AND unidad_medida = '" . $conn->real_escape_string($unidad_seleccionada) . "'");
        if ($queryFactor && $queryFactor->num_rows > 0) {
            $rowFactor = $queryFactor->fetch_assoc();
            $factor = floatval($rowFactor['factor']);
        } else {
            $factor = 1;
        }
    }

    $subtotal = $cantidad * $precio;

    $queryCantidadActual = $conn->query("SELECT cantidad, unidad_seleccionada FROM carrito WHERE id_carrito = $id_carrito");
    if (!$queryCantidadActual || $queryCantidadActual->num_rows === 0) {
        $_SESSION['status_message'] = "Error al obtener cantidad actual del carrito.";
        $_SESSION['status_type'] = "error";
        header("Location: ../sales.php?id_usuario=$id_usuario");
        exit;
    }
    $rowCantActual = $queryCantidadActual->fetch_assoc();

    if ($rowCantActual['unidad_seleccionada'] === 'normal') {
        $factorActual = 1;
    } else {
        $queryFactorActual = $conn->query("SELECT factor FROM unidades_conversion WHERE id_producto = $id_producto AND unidad_medida = '" . $conn->real_escape_string($rowCantActual['unidad_seleccionada']) . "'");
        if ($queryFactorActual && $queryFactorActual->num_rows > 0) {
            $rowFactorActual = $queryFactorActual->fetch_assoc();
            $factorActual = floatval($rowFactorActual['factor']);
        } else {
            $factorActual = 1;
        }
    }

    $cantidadBaseActual = $rowCantActual['cantidad'] * $factorActual;
    $cantidadBaseNueva = $cantidad * $factor;

    $queryStock = $conn->query("SELECT stock FROM productos WHERE id_producto = $id_producto");
    if (!$queryStock || $queryStock->num_rows === 0) {
        $_SESSION['status_message'] = "Producto no encontrado.";
        $_SESSION['status_type'] = "error";
        header("Location: ../sales.php?id_usuario=$id_usuario");
        exit;
    }
    $rowStock = $queryStock->fetch_assoc();
    $stockActual = floatval($rowStock['stock']);

    $stockNuevo = $stockActual + $cantidadBaseActual - $cantidadBaseNueva;

    if ($stockNuevo < 0) {
        $_SESSION['status_message'] = "No hay suficiente stock para esta cantidad.";
        $_SESSION['status_type'] = "warning";
        header("Location: ../sales.php?id_usuario=$id_usuario");
        exit;
    }

    if ($unidad_seleccionada === 'normal') {
        $queryUnidadBase = $conn->query("
            SELECT um.nombre 
            FROM productos p
            JOIN unidades_medida um ON p.id_unidad_medida = um.id_unidad_medida
            WHERE p.id_producto = $id_producto
            LIMIT 1
        ");
        if ($queryUnidadBase && $queryUnidadBase->num_rows > 0) {
            $rowUnidadBase = $queryUnidadBase->fetch_assoc();
            $unidad_seleccionada = $conn->real_escape_string($rowUnidadBase['nombre']);
        } else {
            $unidad_seleccionada = 'unidad';
        }
    }

    $updateCarrito = "UPDATE carrito SET cantidad = $cantidad, unidad_seleccionada = '$unidad_seleccionada', precio = $precio, subtotal = $subtotal WHERE id_carrito = $id_carrito";

    $updateStock = "UPDATE productos SET stock = $stockNuevo WHERE id_producto = $id_producto";

    if ($conn->query($updateCarrito) && $conn->query($updateStock)) {
        $_SESSION['status_message'] = "Cantidad y stock actualizados correctamente.";
        $_SESSION['status_type'] = "success";
    } else {
        $_SESSION['status_message'] = "Error al actualizar cantidad o stock.";
        $_SESSION['status_type'] = "error";
    }

    header("Location: ../sales.php?id_usuario=$id_usuario");
    exit;
} else {
    $_SESSION['status_message'] = "Método no permitido.";
    $_SESSION['status_type'] = "error";
    header("Location: ../sales.php");
    exit;
}
