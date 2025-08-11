<?php
require '../../../mysql/connection.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $unidad = $_POST['nombre'];
    $abreviatura = $_POST['abreviatura'];

    $sql = "INSERT INTO unidades_medida (nombre, abreviatura)
            VALUES (?, ?)";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $unidad, $abreviatura);

    if ($stmt->execute()) {
        $_SESSION['status_message'] = "Unidad de Medida agregada exitosamente.";
        $_SESSION['status_type'] = "success";
    } else {
        $_SESSION['status_message'] = "Error al agregar la unidad de medida: " . $stmt->error;
        $_SESSION['status_type'] = "danger";
    }

    $stmt->close();
    header("Location: ../units_of_measure.php");
    exit();
}

$conn->close();
?>