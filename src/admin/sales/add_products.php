<?php 
require '../../../mysql/connection.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["product_name"])) {
    $nombre = trim($_POST["product_name"]);
    $id_usuario = isset($_POST["id_usuario"]) ? intval($_POST["id_usuario"]) : null;

    if (!$id_usuario) {
        $_SESSION['status_message'] = "Error: ID de usuario no proporcionado.";
        $_SESSION['status_type'] = "error";
        header("Location: ../sales.php");
        exit;
    }

    $stmt = $conn->prepare("SELECT id_producto, precio, stock FROM productos WHERE nombre = ?");
    $stmt->bind_param("s", $nombre);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $id_producto = $row["id_producto"];
        $precio = floatval($row["precio"]);
        $stock = floatval($row["stock"]);

        if ($stock <= 0) {
            $_SESSION['status_message'] = "Producto agotado.";
            $_SESSION['status_type'] = "warning";
            header("Location: ../sales.php?id_usuario=" . $id_usuario);
            exit;
        }

        $check = $conn->prepare("SELECT cantidad FROM carrito WHERE id_producto = ? AND id_usuario = ?");
        $check->bind_param("ii", $id_producto, $id_usuario);
        $check->execute();
        $res = $check->get_result();

        if ($res->num_rows > 0) {
            $row_cart = $res->fetch_assoc();
            $nueva_cantidad = $row_cart['cantidad'] + 1;

            if ($nueva_cantidad > $stock) {
                $_SESSION['status_message'] = "No hay suficiente stock disponible.";
                $_SESSION['status_type'] = "warning";
                header("Location: ../sales.php?id_usuario=" . $id_usuario);
                exit;
            }

            $subtotal = $precio * $nueva_cantidad;

            $update = $conn->prepare("UPDATE carrito SET cantidad = ?, subtotal = ? WHERE id_producto = ? AND id_usuario = ?");
            $update->bind_param("idii", $nueva_cantidad, $subtotal, $id_producto, $id_usuario);
            $update->execute();

            $new_stock = $stock - 1;
            $update_stock = $conn->prepare("UPDATE productos SET stock = ? WHERE id_producto = ?");
            $update_stock->bind_param("di", $new_stock, $id_producto);
            $update_stock->execute();

            $_SESSION['status_message'] = "Producto actualizado en el carrito.";
            $_SESSION['status_type'] = "info";
        } else {
            $cantidad = 1;
            $subtotal = $precio;

            $insert = $conn->prepare("INSERT INTO carrito (id_producto, id_usuario, cantidad, precio, subtotal) VALUES (?, ?, ?, ?, ?)");
            $insert->bind_param("iiddd", $id_producto, $id_usuario, $cantidad, $precio, $subtotal);
            $insert->execute();

            $new_stock = $stock - 1;
            $update_stock = $conn->prepare("UPDATE productos SET stock = ? WHERE id_producto = ?");
            $update_stock->bind_param("di", $new_stock, $id_producto);
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
