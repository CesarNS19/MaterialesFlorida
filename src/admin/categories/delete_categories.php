<?php
require '../../../mysql/connection.php';
session_start();

if (isset($_POST['id_categoria'])) {
    $id = intval($_POST['id_categoria']);

    $sql = "DELETE FROM categorias WHERE id_categoria = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        $_SESSION['status_message'] = 'Categoria eliminada correctamente.';
        $_SESSION['status_type'] = 'success';
    } else {
        $_SESSION['status_message'] = 'Error al eliminar la categoria: ' . $stmt->error;
        $_SESSION['status_type'] = 'danger';
    }

    $stmt->close();
} else {
    $_SESSION['status_message'] = 'ID de categoria no especificado.';
    $_SESSION['status_type'] = 'warning';
}

header('Location: ../categories.php');
exit();

$conn->close();
?>