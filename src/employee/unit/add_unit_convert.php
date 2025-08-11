<?php
require '../../../mysql/connection.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_producto = $_POST['id_producto'];
    $unidad = trim($_POST['unidad_medida']);
    $factor = $_POST['factor'];

    $sql_check = "SELECT COUNT(*) as total FROM unidades_conversion WHERE id_producto = ? AND unidad_medida = ?";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bind_param("is", $id_producto, $unidad);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();
    $row_check = $result_check->fetch_assoc();
    $stmt_check->close();

    if ($row_check['total'] > 0) {
        $_SESSION['status_message'] = "Error: El producto ya tiene asignada la unidad de medida '$unidad'.";
        $_SESSION['status_type'] = "warning";
        header("Location: ../units_convert.php");
        exit();
    }

    $sql = "INSERT INTO unidades_conversion (id_producto, unidad_medida, factor) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iss", $id_producto, $unidad, $factor);

    if ($stmt->execute()) {
        $_SESSION['status_message'] = "Unidad de conversión agregada exitosamente.";
        $_SESSION['status_type'] = "success";
    } else {
        $_SESSION['status_message'] = "Error al agregar la unidad de conversión: " . $stmt->error;
        $_SESSION['status_type'] = "danger";
    }

    $stmt->close();
    header("Location: ../units_convert.php");
    exit();
}

$conn->close();
?>
