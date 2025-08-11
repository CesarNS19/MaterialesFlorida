<?php
require '../../../mysql/connection.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_unidad = intval($_POST['id_conversion']);
    $id_producto = intval($_POST['id_producto']);
    $unidad_medida = trim($_POST['unidad_medida']);
    $factor = trim($_POST['factor']);

    $checkSql = "SELECT COUNT(*) as total FROM unidades_conversion WHERE id_producto = ? AND unidad_medida = ? AND id_conversion != ?";
    $stmtCheck = $conn->prepare($checkSql);
    $stmtCheck->bind_param("isi", $id_producto, $unidad_medida, $id_unidad);
    $stmtCheck->execute();
    $resultCheck = $stmtCheck->get_result();
    $rowCheck = $resultCheck->fetch_assoc();
    $stmtCheck->close();

    if ($rowCheck['total'] > 0) {
        $_SESSION['status_message'] = "Error: El producto ya tiene asignada la unidad de medida '$unidad_medida'.";
        $_SESSION['status_type'] = "warning";
        header("Location: ../units_convert.php");
        exit();
    }

    $updateSql = "UPDATE unidades_conversion SET id_producto = ?, unidad_medida = ?, factor = ? WHERE id_conversion = ?";
    $stmtUpdate = $conn->prepare($updateSql);
    $stmtUpdate->bind_param("issi", $id_producto, $unidad_medida, $factor, $id_unidad);

    if ($stmtUpdate->execute()) {
        if ($stmtUpdate->affected_rows > 0) {
            $_SESSION['status_message'] = "Unidad de conversión actualizada correctamente.";
            $_SESSION['status_type'] = "success";
        } else {
            $_SESSION['status_message'] = "No se realizaron cambios.";
            $_SESSION['status_type'] = "info";
        }
    } else {
        $_SESSION['status_message'] = "Error al actualizar: " . $stmtUpdate->error;
        $_SESSION['status_type'] = "danger";
    }

    $stmtUpdate->close();

    header("Location: ../units_convert.php");
    exit();
}

header("Location: ../units_convert.php");
exit();
?>
