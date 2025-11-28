<?php
session_start();
require '../../../mysql/connection.php';

function getFactor($conn, $id_producto, $unidad) {
    if (empty($unidad)) return 1;
    $stmt = $conn->prepare("SELECT factor FROM unidades_conversion WHERE id_producto=? AND unidad_medida=?");
    $stmt->bind_param("is", $id_producto, $unidad);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result && $result->num_rows > 0) {
        return floatval($result->fetch_assoc()['factor']);
    }
    return 1;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $id_carrito = intval($_POST['id_carrito']);
    $cantidad = floatval($_POST['cantidad']);
    $unidad_seleccionada = $_POST['unidad_seleccionada'];
    $id_usuario = intval($_POST['id_usuario']);

    $stmt = $conn->prepare("SELECT id_producto, cantidad, unidad_seleccionada FROM carrito WHERE id_carrito=?");
    $stmt->bind_param("i", $id_carrito);
    $stmt->execute();
    $res = $stmt->get_result();
    if (!$res || $res->num_rows === 0) {
        $_SESSION['status_message'] = "Producto no encontrado en el carrito.";
        $_SESSION['status_type'] = "error";
        header("Location: ../sales.php?id_usuario=$id_usuario");
        exit;
    }
    $rowCarrito = $res->fetch_assoc();
    $id_producto = $rowCarrito['id_producto'];
    $cantidadAnterior = floatval($rowCarrito['cantidad']);
    $unidadAnterior = $rowCarrito['unidad_seleccionada'];

    $factorAnterior = getFactor($conn, $id_producto, $unidadAnterior);
    $factorNuevo = getFactor($conn, $id_producto, $unidad_seleccionada);
    $cantidadBaseAnterior = $cantidadAnterior * $factorAnterior;
    $cantidadBaseNueva = $cantidad * $factorNuevo;

    $stmt = $conn->prepare("SELECT precio AS precio_normal, precio_pieza FROM productos WHERE id_producto=?");
    $stmt->bind_param("i", $id_producto);
    $stmt->execute();
    $res = $stmt->get_result();
    if (!$res || $res->num_rows === 0) {
        $_SESSION['status_message'] = "No se encontraron precios para el producto.";
        $_SESSION['status_type'] = "error";
        header("Location: ../sales.php?id_usuario=$id_usuario");
        exit;
    }
    $precios = $res->fetch_assoc();

    $precio = ($factorNuevo == 1) ? $precios['precio_normal'] : $precios['precio_pieza'];
    $subtotal = $cantidad * $precio;

    $stmt = $conn->prepare("SELECT stock FROM productos WHERE id_producto=?");
    $stmt->bind_param("i", $id_producto);
    $stmt->execute();
    $res = $stmt->get_result();
    $stockActual = floatval($res->fetch_assoc()['stock']);
    $stockNuevo = $stockActual + $cantidadBaseAnterior - $cantidadBaseNueva;

    if ($stockNuevo < 0) {
        $_SESSION['status_message'] = "No hay suficiente stock para esta cantidad y unidad seleccionada.";
        $_SESSION['status_type'] = "warning";
        header("Location: ../sales.php?id_usuario=$id_usuario");
        exit;
    }

    $stmtCarrito = $conn->prepare("UPDATE carrito SET cantidad=?, unidad_seleccionada=?, precio=?, subtotal=? WHERE id_carrito=?");
    $stmtCarrito->bind_param("isdii", $cantidad, $unidad_seleccionada, $precio, $subtotal, $id_carrito);

    $stmtStock = $conn->prepare("UPDATE productos SET stock=? WHERE id_producto=?");
    $stmtStock->bind_param("di", $stockNuevo, $id_producto);

    if ($stmtCarrito->execute() && $stmtStock->execute()) {
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
?>
