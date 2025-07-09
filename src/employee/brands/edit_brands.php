<?php
require '../../../mysql/connection.php';
session_start();

if (isset($_POST['id_marca'])) {
    $id = $_POST['id_marca'];

    $sql = "SELECT * FROM marcas";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $result = $stmt->get_result();
    $cliente = $result->fetch_assoc();

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $marca = $_POST['nombre'];
        $descripcion = $_POST['descripcion'];
    
        $sql = "UPDATE marcas SET nombre = ?, descripcion = ? WHERE id_marca = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssi", $marca, $descripcion, $id);
    
        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                $_SESSION['status_message'] = 'Marca actualizada exitosamente.';
                $_SESSION['status_type'] = 'success';
            } else {
                $_SESSION['status_message'] = 'No se realizaron cambios. Verifica los datos.';
                $_SESSION['status_type'] = 'warning';
            }
        } else {
            $_SESSION['status_message'] = 'Error al actualizar los datos: ' . $stmt->error;
            $_SESSION['status_type'] = 'danger';
        }

        $stmt->close();
        header("Location: ../brands.php");
        exit();
    }
}
?>
