<?php
require '../../../mysql/connection.php';
require '../../../vendor/fpdf/fpdf.php';

if (!isset($_GET['id_venta'])) {
    die('ID de venta no proporcionado.');
}

$id_venta = intval($_GET['id_venta']);

$sqlVenta = "SELECT v.*, CONCAT(u.nombre, ' ', u.apellido_paterno, ' ', u.apellido_materno) AS cliente
             FROM ventas v
             JOIN usuarios u ON v.id_usuario = u.id_usuario
             WHERE v.id_venta = $id_venta";
$resultVenta = $conn->query($sqlVenta);
$venta = $resultVenta->fetch_assoc();

if (!$venta) {
    die('Venta no encontrada.');
}

$sqlDetalles = "SELECT dv.*, p.nombre AS producto
                FROM detalle_venta dv
                JOIN productos p ON dv.id_producto = p.id_producto
                WHERE dv.id_venta = $id_venta";
$resultDetalles = $conn->query($sqlDetalles);

$pdf = new FPDF();
$pdf->AddPage();

$pdf->SetFont('Arial', 'B', 16);
$pdf->Cell(0, 10, 'Comprobante de Venta', 0, 1, 'C');

$pdf->SetFont('Arial', '', 12);
$pdf->Ln(5);
$pdf->Cell(100, 8, "Cliente: " . $venta['cliente'], 0, 1);
$pdf->Cell(100, 8, "Fecha: " . $venta['fecha'], 0, 1);
$pdf->Cell(100, 8, "No. Venta: " . $venta['id_venta'], 0, 1);

$pdf->Ln(8);

$pdf->SetFont('Arial', 'B', 11);
$pdf->Cell(70, 8, 'Producto', 1);
$pdf->Cell(30, 8, 'Cantidad', 1);
$pdf->Cell(30, 8, 'P. Unitario', 1);
$pdf->Cell(30, 8, 'Subtotal', 1);
$pdf->Ln();

$pdf->SetFont('Arial', '', 11);
while ($row = $resultDetalles->fetch_assoc()) {
    $pdf->Cell(70, 8, $row['producto'], 1);
    $pdf->Cell(30, 8, $row['cantidad'], 1);
    $pdf->Cell(30, 8, '$' . number_format($row['precio_unitario'], 2), 1);
    $pdf->Cell(30, 8, '$' . number_format($row['subtotal'], 2), 1);
    $pdf->Ln();
}

$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(130, 10, 'Total:', 1);
$pdf->Cell(30, 10, '$' . number_format($venta['total'], 2), 1);

$pdf->Output("I", "venta_$id_venta.pdf");
