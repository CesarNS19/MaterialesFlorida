<?php
require '../../../mysql/connection.php';
session_start();

if (isset($_POST['id_conversion'])) {
    $id = intval($_POST['id_conversion']);

    $sql = "DELETE FROM unidades_conversion WHERE id_conversion = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        $_SESSION['status_message'] = 'Unidad de conversión eliminada correctamente.';
        $_SESSION['status_type'] = 'success';
    } else {
        $_SESSION['status_message'] = 'Error al eliminar la unidad de conversión: ' . $stmt->error;
        $_SESSION['status_type'] = 'danger';
    }

    $stmt->close();
} else {
    $_SESSION['status_message'] = 'ID de unidad de conversión no especificado.';
    $_SESSION['status_type'] = 'warning';
}

header("Location: ../units_convert.php");
exit();
$conn->close();
?>