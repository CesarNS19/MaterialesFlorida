<?php
require '../../../mysql/connection.php';
require '../../../vendor/fpdf/fpdf.php';

ob_start();

function toIso($text) {
    return iconv("UTF-8", "ISO-8859-1//TRANSLIT", $text);
}

if (!isset($_GET['id_venta'])) {
    die('ID de venta no proporcionado.');
}

$id_venta = intval($_GET['id_venta']);

$sqlVenta = "SELECT v.*, CONCAT(u.nombre, ' ', u.apellido_paterno, ' ', u.apellido_materno) AS cliente, u.id_direccion, u.telefono
             FROM ventas v
             JOIN usuarios u ON v.id_usuario = u.id_usuario
             WHERE v.id_venta = $id_venta";
$resultVenta = $conn->query($sqlVenta);

if (!$resultVenta) {
    die('Error en la consulta: ' . $conn->error);
}

$venta = $resultVenta->fetch_assoc();
if (!$venta) {
    die('Venta no encontrada.');
}

$id_caja = intval($venta['id_caja']);

$sqlCaja = "SELECT c.id_caja, c.id_usuario, CONCAT(u.nombre, ' ', u.apellido_paterno, ' ', u.apellido_materno) AS cajero
            FROM cajas c
            JOIN usuarios u ON c.id_usuario = u.id_usuario
            JOIN ventas v ON v.id_caja = c.id_caja  
            WHERE c.id_caja = $id_caja";
$resultCaja = $conn->query($sqlCaja);

if (!$resultCaja) {
    die('Error en la consulta de caja: ' . $conn->error);
}
$caja = $resultCaja->fetch_assoc();
if (!$caja) {
    die('Caja no encontrada.');
}

$id_direccion = intval($venta['id_direccion']);

$sql_dir = "SELECT 
                CONCAT_WS(', ',
                    d.calle,
                    CONCAT('No. Ext ', d.num_ext),
                    IF(d.num_int IS NOT NULL AND d.num_int != '', CONCAT('No. Int ', d.num_int), NULL),
                    d.ciudad,
                    d.estado,
                    CONCAT('C.P. ', d.codigo_postal)
                ) AS direccion_completa
            FROM direcciones d
            WHERE d.id_direccion = $id_direccion";
$resultDireccion = $conn->query($sql_dir);

if (!$resultDireccion) {
    die('Error en la consulta de dirección: ' . $conn->error);
}

$direccion = $resultDireccion->fetch_assoc();
$direccionCompleta = $direccion ? $direccion['direccion_completa'] : 'No disponible';

$sqlDetalles = "SELECT dv.*, p.nombre AS producto, dv.unidad_seleccionada
                FROM detalle_venta dv
                JOIN productos p ON dv.id_producto = p.id_producto
                WHERE dv.id_venta = $id_venta";
$resultDetalles = $conn->query($sqlDetalles);

if (!$resultDetalles) {
    die('Error en la consulta de detalles: ' . $conn->error);
}

$pdf = new FPDF('P','mm','A4');
$pdf->AddPage();

$pdf->Image('../../../img/cemex_logo.png', 10, 8, 30);
$pdf->Ln(25);

$pdf->SetFont('Arial','B',14);
$pdf->Cell(0,6, toIso('MATERIALES PARA CONSTRUCCIÓN'), 0,1,'C');

$pdf->SetFont('Arial','B',18);
$pdf->Cell(0,8, toIso('"LA FLORIDA"'), 0,1,'C');

$pdf->SetFont('Arial','',10);
$pdf->Cell(0,5,toIso('CEMENTO, MORTERO, VARILLA SAN LUIS, ALAMBRE, ALAMBRÓN,'),0,1,'C');
$pdf->Cell(0,5,toIso('BLOCK, ARENA, GRAVA Y PROYECTOS PRODUCTIVOS'),0,1,'C');

$pdf->SetFont('Arial','B',10);
$pdf->Cell(0,5,toIso('JOSE ALFREDO SALAZAR DUARTE'),0,1,'C');

$pdf->SetFont('Arial','',9);
$pdf->Cell(0,5,toIso('R.F.C. SADA-740714-QU4    CURP: SADA740714HMCLRL05'),0,1,'C');
$pdf->Cell(0,5,toIso('LIBRAMIENTO TEJUPILCO-ALTAMIRANO KM. 2 COLONIA FLORIDA'),0,1,'C');
$pdf->Cell(0,5,toIso('A 200 MTS. DE GASOLINERA TABACHINES MEX. (SOBRE EL LIBRAMIENTO) TEJUPILCO-MEX.'),0,1,'C');

$pdf->SetFont('Arial','B',10);
$pdf->Cell(0,5,toIso('TELS: 01 (724) 267-7169   722 350 5063   722 474 8048'),0,1,'C');

$pdf->Ln(2);
$pdf->Line(10,$pdf->GetY(),200,$pdf->GetY());
$pdf->Ln(4);

$pdf->SetFont('Arial','',10);
$pdf->Cell(100,6, toIso('Tejupilco México, a: ') . date('d/m/Y', strtotime($venta['fecha'])),0,0);
$pdf->Cell(0,6, toIso('PEDIDO No: ') . str_pad($venta['id_venta'], 4, '0', STR_PAD_LEFT),0,1);

$pdf->Cell(0,6, toIso('VENDEDOR: ') . toIso($caja['cajero']),0,1);

$y = $pdf->GetY(); 
$pdf->SetLineWidth(0.3);
$pdf->Line(10, $y, 200, $y);

$pdf->Ln(2);
$pdf->Cell(0,6, toIso('CLIENTE: ') . toIso($venta['cliente']),0,1);
$pdf->Cell(0,6, toIso('DIRECCIÓN: ') . toIso($direccionCompleta),0,1);
$pdf->Cell(0,6, toIso('TELÉFONO: ') . toIso($venta['telefono']),0,1);

$pdf->Ln(4);



$pdf->SetFont('Arial','B',10);
$pdf->Cell(70,8,toIso('DESCRIPCIÓN'),1,0,'C');
$pdf->Cell(40,8,toIso('UNIDAD MEDIDA'),1,0,'C');
$pdf->Cell(20,8,toIso('CANTIDAD'),1,0,'C');
$pdf->Cell(30,8,toIso('PRECIO U.'),1,0,'C');
$pdf->Cell(30,8,toIso('IMPORTE'),1,1,'C');

$pdf->SetFont('Arial','',10);
while($row = $resultDetalles->fetch_assoc()){
    $unidad = !empty($row['unidad_seleccionada']) ? $row['unidad_seleccionada'] : '-';

    $pdf->Cell(70,8,toIso($row['producto']),1);
    $pdf->Cell(40,8,toIso($unidad),1,0,'C');
    $pdf->Cell(20,8,$row['cantidad'],1,0,'C');
    $pdf->Cell(30,8,'$'.number_format($row['precio_unitario'],2),1,0,'R');
    $pdf->Cell(30,8,'$'.number_format($row['subtotal'],2),1,1,'R');
}

$pdf->SetFont('Arial','B',10);
$pdf->Cell(160,8,toIso('TOTAL'),1,0,'R');
$pdf->Cell(30,8,'$'.number_format($venta['total'],2),1,1,'R');

$pdf->Ln(8);
$pdf->SetFont('Arial','',9);
$pdf->MultiCell(0,5,toIso(
"Debo (emos) y pagare (mos) incondicionalmente a la orden de JOSE ALFREDO SALAZAR DUARTE, en esta ciudad, la cantidad de \$__________________________ el día ____________________________. Importe de la mercancía recibida a mi entera satisfacción. En caso de incumplimiento, este pagaré causará intereses moratorios del ________ % mensual."
));

$pdf->Ln(8);
$pdf->Cell(0,6,toIso('FIRMA DE CONFORMIDAD: ____________________________'),0,1,'R');

$pdfDir = realpath(__DIR__ . '/../../../pdf');
if (!is_dir($pdfDir)) {
    mkdir($pdfDir, 0777, true);
}
$pdfFile = $pdfDir . "/venta_$id_venta.pdf";
$pdf->Output("F", $pdfFile);

header('Content-Type: application/pdf');
header('Content-Disposition: inline; filename="venta_'.$id_venta.'.pdf"');
readfile($pdfFile);

ob_end_flush();
