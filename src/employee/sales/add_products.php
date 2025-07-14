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

    $stmt = $conn->prepare("SELECT id_producto, precio FROM productos WHERE nombre = ?");
    $stmt->bind_param("s", $nombre);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $id_producto = $row["id_producto"];
        $precio = $row["precio"];
        $cantidad = 1;
        $subtotal = $precio * $cantidad;

        $check = $conn->prepare("SELECT * FROM carrito WHERE id_producto = ? AND id_usuario = ?");
        $check->bind_param("ii", $id_producto, $id_usuario);
        $check->execute();
        $res = $check->get_result();

        if ($res->num_rows > 0) {
            $conn->query("UPDATE carrito SET cantidad = cantidad + 1, subtotal = subtotal + $precio WHERE id_producto = $id_producto AND id_usuario = $id_usuario");
            $_SESSION['status_message'] = "Producto actualizado en el carrito.";
            $_SESSION['status_type'] = "info";
        } else {
            $insert = $conn->prepare("INSERT INTO carrito (id_producto, id_usuario, cantidad, precio, subtotal) VALUES (?, ?, ?, ?, ?)");

            $insert->bind_param("iiddd", $id_producto, $id_usuario, $cantidad, $precio, $subtotal);
            
            $insert->execute();

            $_SESSION['status_message'] = "Producto agregado al carrito.";
            $_SESSION['status_type'] = "success";
        }

    } else {
        $_SESSION['status_message'] = "Producto no encontrado.";
        $_SESSION['status_type'] = "warning";
    }

    header("Location: ../sales.php ? id_usuario=" . $id_usuario);
    exit();
}
