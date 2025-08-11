<?php
require '../../../mysql/connection.php';
session_start();

if (isset($_POST['id_unidad_medida'])) {
    $id = $_POST['id_unidad_medida'];

    $sql = "SELECT * FROM unidades_medida";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $result = $stmt->get_result();
    $cliente = $result->fetch_assoc();

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $unidad = $_POST['nombre'];
        $abreviatura = $_POST['abreviatura'];
    
        $sql = "UPDATE unidades_medida SET nombre = ?, abreviatura = ? WHERE id_unidad_medida = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssi", $unidad, $abreviatura, $id);
    
        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                $_SESSION['status_message'] = 'Unidad de medida actualizada exitosamente.';
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
        header("Location: ../units_of_measure.php");
        exit();
    }
}
?>
