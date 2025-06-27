<?php
require '../../../mysql/connection.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_producto = $_POST['id_producto'];
    $id_categoria = $_POST['id_categoria'];
    $id_marca = $_POST['id_marca'];
    $nombre = $_POST['nombre'];
    $descripcion = $_POST['descripcion'];
    $precio = $_POST['precio'];
    $stock = $_POST['stock'];
    $color = $_POST['color'];

    $target_dir = "../../../img/";

    $imagen_ruta = null;

    if (isset($_FILES['imagen_producto']) && $_FILES['imagen_producto']['error'] == 0) {
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0755, true);
        }

        $target_file = $target_dir . basename($_FILES["imagen_producto"]["name"]);
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        $check = getimagesize($_FILES["imagen_producto"]["tmp_name"]);
        if ($check !== false) {
            if (move_uploaded_file($_FILES["imagen_producto"]["tmp_name"], $target_file)) {
                $imagen_ruta = $target_file;
            } else {
                $_SESSION['status_message'] = "Error al subir la imágen";
                $_SESSION['status_type'] = "error";
                header("Location: ../products.php");
                exit();
            }
        } else {
            $_SESSION['status_message'] = "El archivo no es una imágen válida";
            $_SESSION['status_type'] = "warning";
            header("Location: ../products.php");
            exit();
        }
    }

    if ($imagen_ruta) {
        $sql = "UPDATE productos SET id_categoria='$id_categoria', id_marca='$id_marca', nombre='$nombre', descripcion='$descripcion', precio='$precio', stock='$stock', color='$color', imagen='$imagen_ruta' WHERE id_producto='$id_producto'";
    } else {
        $sql = "UPDATE productos SET id_categoria='$id_categoria', id_marca='$id_marca', nombre='$nombre', descripcion='$descripcion', precio='$precio', stock='$stock', color='$color'  WHERE id_producto='$id_producto'";
    }

    if ($conn->query($sql) === TRUE) {
        $_SESSION['status_message'] = "Producto actualizado exitosamente";
        $_SESSION['status_type'] = "success";
    } else {
        $_SESSION['status_message'] = "Error al actualizar el producto";
        $_SESSION['status_type'] = "danger";
    }

    header("Location: ../products.php");
    exit();
}

?>