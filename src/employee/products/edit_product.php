<?php
require '../../../mysql/connection.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_producto = $_POST['id_producto'];
    $codigo = $_POST['codigo'];
    $id_unidad_medida = $_POST["id_unidad_medida"];
    $id_marca = $_POST["id_marca"];
    $id_categoria = $_POST["id_categoria"];
    $nombre = $_POST['nombre'];
    $precio = $_POST['precio'];
    $ubicacion = $_POST['ubicacion'];

    $imagen_actual = $_POST['imagen_actual'] ?? '';
    $imagen_ruta = $imagen_actual;

    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] == UPLOAD_ERR_OK) {
        $target_dir = "../../../img/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0755, true);
        }

        $filename = basename($_FILES["imagen"]["name"]);
        $target_file = $target_dir . $filename;
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        $check = getimagesize($_FILES["imagen"]["tmp_name"]);
        if ($check !== false) {
            if (move_uploaded_file($_FILES["imagen"]["tmp_name"], $target_file)) {
                $imagen_ruta = $filename;
            } else {
                $_SESSION['status_message'] = "Error al subir la imagen";
                $_SESSION['status_type'] = "error";
                header("Location: ../products.php");
                exit();
            }
        } else {
            $_SESSION['status_message'] = "El archivo no es una imagen válida";
            $_SESSION['status_type'] = "warning";
            header("Location: ../products.php");
            exit();
        }
    }

    $sql = "UPDATE productos SET id_unidad_medida = ?, id_marca = ?, id_categoria = ?, codigo = ?, nombre = ?, precio = ?, ubicacion = ?, imagen = ? WHERE id_producto = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iiissdssi", $id_unidad_medida, $id_marca, $id_categoria, $codigo, $nombre, $precio, $ubicacion, $imagen_ruta, $id_producto);

    if ($stmt->execute()) {
        $_SESSION['status_message'] = "Producto actualizado exitosamente";
        $_SESSION['status_type'] = "success";
    } else {
        $_SESSION['status_message'] = "Error al actualizar el producto: " . $stmt->error;
        $_SESSION['status_type'] = "danger";
    }

    $stmt->close();
    header("Location: ../products.php");
    exit();
}
?>
