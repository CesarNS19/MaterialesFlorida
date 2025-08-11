<?php
require '../../../mysql/connection.php';
session_start();

if (isset($_POST['id_unidad_medida'])) {
    $id = intval($_POST['id_unidad_medida']);

    $sql = "DELETE FROM unidades_medida WHERE id_unidad_medida = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        $_SESSION['status_message'] = 'Unidad de medida eliminada correctamente.';
        $_SESSION['status_type'] = 'success';
    } else {
        $_SESSION['status_message'] = 'Error al eliminar la unidad de medida: ' . $stmt->error;
        $_SESSION['status_type'] = 'danger';
    }

    $stmt->close();
} else {
    $_SESSION['status_message'] = 'ID de unidad de medida no especificado.';
    $_SESSION['status_type'] = 'warning';
}

header("Location: ../units_of_measure.php");
exit();

$conn->close();
?>