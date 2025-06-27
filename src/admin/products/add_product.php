<?php
require '../../../mysql/connection.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_categoria = $_POST["id_categoria"];
    $id_marca = $_POST["id_marca"];
    $nombre_producto = $_POST['nombre'];
    $descripcion = $_POST['descripcion'];
    $precio = $_POST['precio'];
    $stock = $_POST['stock'];
    $color = $_POST['color'];

    if (isset($_FILES['imagen_producto']) && $_FILES['imagen_producto']['error'] === UPLOAD_ERR_OK) {

        $target_dir = "../../../img/";
        $target_file = $target_dir . basename($_FILES["imagen_producto"]["name"]);
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        $check = getimagesize($_FILES["imagen_producto"]["tmp_name"]);
        if ($check !== false) {
            if (move_uploaded_file($_FILES["imagen_producto"]["tmp_name"], $target_file)) {
                $relative_path = "../../../img/" . basename($_FILES["imagen_producto"]["name"]);
                
                $sql = "INSERT INTO productos (id_categoria, id_marca, nombre, descripcion, precio, stock, color, imagen) 
                        VALUES ('$id_categoria', '$id_marca', '$nombre_producto', '$descripcion', '$precio', '$stock', '$color', '$relative_path')";

                if ($conn->query($sql) === TRUE) {
                    $_SESSION['status_message'] = "Producto agregado exitosamente";
                    $_SESSION['status_type'] = "success";
                } else {
                    $_SESSION['status_message'] = "Error al agregar el producto en la base de datos";
                    $_SESSION['status_type'] = "error";
                }
            } else {
                $_SESSION['status_message'] = "Error al mover la imagen a la carpeta Img";
                $_SESSION['status_type'] = "error";
            }
        } else {
            $_SESSION['status_message'] = "El archivo seleccionado no es una imagen válida";
            $_SESSION['status_type'] = "warning";
        }
    } else {
        $_SESSION['status_message'] = "Por favor, selecciona una imagen";
        $_SESSION['status_type'] = "warning";
    }

    header("Location: ../products.php");
    exit();
}
