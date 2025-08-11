<?php
require '../../../mysql/connection.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $num_int = $_POST['num_int'];
    $num_ext = $_POST['num_ext'];
    $calle = $_POST['calle'];
    $ciudad = $_POST['ciudad'];
    $estado = $_POST['estado'];
    $codigo_postal = $_POST['codigo_postal'];
    $id_usuario = intval($_POST['id_usuario']);

    $sql = "INSERT INTO direcciones (num_int, num_ext, calle, ciudad, estado, codigo_postal) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iisssi", $num_int, $num_ext, $calle, $ciudad, $estado, $codigo_postal);

    if ($stmt->execute()) {
        $id_direccion_nueva = $conn->insert_id;

        $sql_update_usuario = "UPDATE usuarios SET id_direccion = ? WHERE id_usuario = ?";
        $stmt_update = $conn->prepare($sql_update_usuario);
        $stmt_update->bind_param("ii", $id_direccion_nueva, $id_usuario);

        if ($stmt_update->execute()) {
            $_SESSION['status_message'] = "Dirección agregada y asignada exitosamente.";
            $_SESSION['status_type'] = "success";
        } else {
            $_SESSION['status_message'] = "Dirección agregada pero error al asignarla al usuario: " . $stmt_update->error;
            $_SESSION['status_type'] = "danger";
        }

        $stmt_update->close();

    } else {
        $_SESSION['status_message'] = "Error al agregar la dirección: " . $stmt->error;
        $_SESSION['status_type'] = "danger";
    }

    $stmt->close();

    header("Location: ../sales.php?id_usuario=$id_usuario&id_direccion=$id_direccion_nueva");
    exit();
}

$conn->close();
