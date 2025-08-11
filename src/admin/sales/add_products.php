<?php
require '../../../mysql/connection.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["product_code"])) {
    $codigo = trim($_POST["product_code"]);
    $id_usuario = isset($_POST["id_usuario"]) ? intval($_POST["id_usuario"]) : null;

    if (!$id_usuario) {
        $_SESSION['status_message'] = "Error: ID de usuario no proporcionado.";
        $_SESSION['status_type'] = "error";
        header("Location: ../sales.php");
        exit;
    }

    $stmt = $conn->prepare("SELECT id_producto, precio, stock FROM productos WHERE codigo = ?");
    $stmt->bind_param("s", $codigo);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $id_producto = $row["id_producto"];
        $precio = floatval($row["precio"]);
        $stock = intval($row["stock"]);

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

        if ($rowUnidadBase = $resultUnidadBase->fetch_assoc()) {
            $unidad_seleccionada = $rowUnidadBase['nombre'];
        } else {
            $unidad_seleccionada = 'unidad';
        }

        $check = $conn->prepare("SELECT cantidad FROM carrito WHERE id_producto = ? AND id_usuario = ?");
        $check->bind_param("ii", $id_producto, $id_usuario);
        $check->execute();
        $res = $check->get_result();

        if ($res->num_rows > 0) {
            $row_cart = $res->fetch_assoc();
            $cantidad_actual_carrito = intval($row_cart['cantidad']);
            $nueva_cantidad = $cantidad_actual_carrito + 1;

            if ($nueva_cantidad > ($stock + $cantidad_actual_carrito)) {
                $_SESSION['status_message'] = "No hay suficiente stock disponible.";
                $_SESSION['status_type'] = "warning";
                header("Location: ../sales.php?id_usuario=" . $id_usuario);
                exit;
            }

            $subtotal = $precio * $nueva_cantidad;

            $update = $conn->prepare("UPDATE carrito SET cantidad = ?, subtotal = ?, unidad_seleccionada = ? WHERE id_producto = ? AND id_usuario = ?");
            $update->bind_param("idsii", $nueva_cantidad, $subtotal, $unidad_seleccionada, $id_producto, $id_usuario);
            $update->execute();

            $new_stock = $stock - 1;
            $update_stock = $conn->prepare("UPDATE productos SET stock = ? WHERE id_producto = ?");
            $update_stock->bind_param("ii", $new_stock, $id_producto);
            $update_stock->execute();

            $_SESSION['status_message'] = "Producto actualizado en el carrito.";
            $_SESSION['status_type'] = "info";
        } else {
            $cantidad = 1;
            $subtotal = $precio;

            $insert = $conn->prepare("INSERT INTO carrito (id_producto, id_usuario, cantidad, precio, subtotal, unidad_seleccionada) VALUES (?, ?, ?, ?, ?, ?)");
            $insert->bind_param("iiidds", $id_producto, $id_usuario, $cantidad, $precio, $subtotal, $unidad_seleccionada);
            $insert->execute();

            $new_stock = $stock - 1;
            $update_stock = $conn->prepare("UPDATE productos SET stock = ? WHERE id_producto = ?");
            $update_stock->bind_param("ii", $new_stock, $id_producto);
            $update_stock->execute();

            $_SESSION['status_message'] = "Producto agregado al carrito.";
            $_SESSION['status_type'] = "success";
        }
    } else {
        $_SESSION['status_message'] = "Producto no encontrado.";
        $_SESSION['status_type'] = "warning";
    }

    header("Location: ../sales.php?id_usuario=" . $id_usuario);
    exit;
}
?>
