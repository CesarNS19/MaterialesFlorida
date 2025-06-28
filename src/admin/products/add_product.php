<?php
session_start();
require '../../../mysql/connection.php';
date_default_timezone_set('America/Mexico_City');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_unidad_medida = $_POST["id_unidad_medida"];
    $id_marca = $_POST["id_marca"];
    $nombre = $_POST['nombre'];
    $precio = $_POST['precio'];
    $stock = $_POST['stock'];
    $ubicacion = $_POST['ubicacion'];
    $fecha_ingreso = date('Y-m-d');
    $estado = 'activo';

    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
        $target_dir = "../../../img/";
        $filename = basename($_FILES["imagen"]["name"]);
        $target_file = $target_dir . $filename;
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        $check = getimagesize($_FILES["imagen"]["tmp_name"]);
        if ($check !== false) {
            if (move_uploaded_file($_FILES["imagen"]["tmp_name"], $target_file)) {
                $relative_path = $filename;

                $sql = "INSERT INTO productos 
                        (id_unidad_medida, id_marca, nombre, precio, stock, ubicacion, fecha_ingreso, estado, imagen) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

                $stmt = $conn->prepare($sql);
                $stmt->bind_param("iisdissss", $id_unidad_medida, $id_marca, $nombre, $precio, $stock, $ubicacion, $fecha_ingreso, $estado, $relative_path);

                if ($stmt->execute()) {
                    $_SESSION['status_message'] = "Producto agregado exitosamente";
                    $_SESSION['status_type'] = "success";
                } else {
                    $_SESSION['status_message'] = "Error al insertar el producto: " . $stmt->error;
                    $_SESSION['status_type'] = "error";
                }

                $stmt->close();
            } else {
                $_SESSION['status_message'] = "Error al mover la imagen al directorio destino.";
                $_SESSION['status_type'] = "error";
            }
        } else {
            $_SESSION['status_message'] = "El archivo seleccionado no es una imagen válida.";
            $_SESSION['status_type'] = "warning";
        }
    } else {
        $_SESSION['status_message'] = "Por favor, selecciona una imagen.";
        $_SESSION['status_type'] = "warning";
    }

    header("Location: ../products.php");
    exit();
}
?>
