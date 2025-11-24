<?php
require '../../../mysql/connection.php';
$conn->query("SET lc_time_names = 'es_ES'");

$itemsPorPagina = 6;
$paginaActual = isset($_GET['pagina']) ? max(1, intval($_GET['pagina'])) : 1;
$offset = ($paginaActual - 1) * $itemsPorPagina;

$totalComprasResult = $conn->query("SELECT COUNT(*) AS total FROM detalle_compra dc
    JOIN compras co ON dc.id_compra = co.id_compra");
$totalComprasRow = $totalComprasResult->fetch_assoc();
$totalCompras = $totalComprasRow['total'];
$totalPaginas = ceil($totalCompras / $itemsPorPagina);

$sql = "SELECT 
            p.id_producto, 
            p.id_unidad_medida, 
            u.nombre AS unidad_medida,
            p.id_marca, 
            m.nombre AS marca, 
            p.nombre, 
            dc.precio_unitario AS precio_compra, 
            p.imagen, 
            p.precio AS precio_venta_real, 
            p.precio_pieza,
            c.nombre AS categoria, 
            c.id_categoria,
            CASE WHEN uc.factor = 1 THEN p.precio ELSE p.precio_pieza END AS precio_venta_mostrar,
            co.total, 
            dc.cantidad, 
            dc.unidad_medida AS unidad,
            co.fecha, 
            co.hora,
            DATE_FORMAT(
                STR_TO_DATE(CONCAT(co.fecha, ' ', co.hora), '%Y-%m-%d %H:%i:%s'),
                '%e de %M de %Y a las %l:%i %p'
            ) AS fecha_hora,
            pro.nombre AS nombre_proveedor, 
            dir.id_direccion, dir.calle, dir.num_ext, dir.num_int, 
            dir.ciudad, dir.estado AS estado_dir, dir.codigo_postal,
            uc.factor,
            CASE WHEN uc.factor = 1 THEN (p.precio - dc.precio_unitario) ELSE (p.precio_pieza - dc.precio_unitario) END AS ganancia_unidad,
            CASE WHEN uc.factor = 1 THEN (p.precio - dc.precio_unitario) * dc.cantidad ELSE (p.precio_pieza - dc.precio_unitario) * dc.cantidad END AS ganancia_total
        FROM detalle_compra dc
        JOIN compras co ON dc.id_compra = co.id_compra
        JOIN proveedores pro ON co.id_proveedor = pro.id_proveedor
        JOIN direcciones dir ON pro.id_direccion = dir.id_direccion
        JOIN productos p ON dc.id_producto = p.id_producto
        JOIN marcas m ON p.id_marca = m.id_marca
        JOIN categorias c ON p.id_categoria = c.id_categoria
        JOIN unidades_medida u ON p.id_unidad_medida = u.id_unidad_medida
        JOIN unidades_conversion uc ON uc.id_producto = p.id_producto AND uc.unidad_medida = dc.unidad_medida
        ORDER BY co.fecha DESC, co.hora DESC
        LIMIT $itemsPorPagina OFFSET $offset";

$result = $conn->query($sql);

$cardsHtml = "<div class='row row-cols-1 row-cols-md-3 g-4'>";
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()){
        $direccion_completa =
            htmlspecialchars(
                $row['calle'] . ' ' . $row['num_ext'] .
                ($row['num_int'] ? ' Int ' . $row['num_int'] : '') .
                ', ' . $row['ciudad'] . ', ' . $row['estado_dir'] .
                ' C.P. ' . $row['codigo_postal']
            );

        $cardsHtml .= "
        <div class='col'>
            <div class='card shadow-lg rounded-4 h-100 border-0 card-hover stylish-card'>
                <div class='card-header text-center fw-semibold rounded-top-4 bg-custom-orange text-white'>
                    <i class='fas fa-cart-arrow-down me-2'></i> Compra Realizada
                </div>
                <div class='card-body px-4 py-3 d-flex flex-column'>
                    <div class='text-muted small mb-2 text-center'>
                        <i class='fas fa-calendar-alt me-1'></i><strong>Fecha:</strong> {$row['fecha_hora']}
                    </div>
                    <div class='text-center mb-3'>
                        <img src='../../img/{$row['imagen']}' class='purchase-img-small'>
                    </div>
                    <h5 class='fw-bold text-dark mb-2'>
                        <i class='fas fa-tag me-2'></i>{$row['nombre']}
                    </h5>
                    <div class='text-muted small mb-3'>
                        <strong><i class='fas fa-layer-group'></i> Categoría:</strong> {$row['categoria']}<br>
                        <strong><i class='fas fa-tag'></i> Marca:</strong> {$row['marca']}<br>
                        <strong><i class='fas fa-ruler'></i> Unidad Medida:</strong> 
                        <span class='text-primary fw-bold'>{$row['unidad']}</span><br><br>
                        <strong><i class='fas fa-user-tie'></i> Proveedor:</strong> {$row['nombre_proveedor']}<br>
                        <strong><i class='fas fa-map-marked-alt'></i> Dirección:</strong> {$direccion_completa}
                    </div>
                    <div class='text-muted small mb-2'>
                        <strong><i class='fas fa-dollar'></i> Precio Venta:</strong>
                        <span class='text-primary fw-bold'>$".number_format($row['precio_venta_mostrar'],2)."</span>
                    </div>
                    <div class='d-flex justify-content-between mb-3'>
                        <span class='fw-semibold text-secondary'><i class='fas fa-box me-1'></i> Cantidad: <span class='text-primary fw-bold'>{$row['cantidad']}</span></span>
                        <span class='fw-semibold text-secondary'><i class='fas fa-dollar me-1'></i> Precio Compra: <span class='text-primary fw-bold'>$".number_format($row['precio_compra'],2)."</span></span>
                        <span class='fw-semibold text-secondary'><i class='fas fa-dollar me-1'></i> Total: <span class='text-success fw-bold'>$".number_format($row['total'],2)."</span></span>
                    </div>
                    <div class='mt-3 p-2 bg-light rounded-3 border'>
                        <h6 class='fw-bold text-success mb-1'><i class='fas fa-money-bill-wave'></i> Utilidad del Producto</h6>
                        <div class='small'>
                            <strong>Ganancia por Unidad:</strong> <span class='text-primary fw-bold'>$".number_format($row['ganancia_unidad'],2)."</span><br>
                            <strong>Ganancia Total:</strong> <span class='text-success fw-bold'>$".number_format($row['ganancia_total'],2)."</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>";
    }
} else {
    $cardsHtml .= "<p class='text-center text-muted'>No hay compras realizadas.</p>";
}
$cardsHtml .= "</div>";

$paginationHtml = "<ul class='pagination justify-content-center'>";
if($paginaActual > 1){
    $paginationHtml .= "<li class='page-item'><a class='page-link' href='#' data-page='".($paginaActual-1)."'>Anterior</a></li>";
}
for($i=1;$i<=$totalPaginas;$i++){
    $active = ($i == $paginaActual) ? "active" : "";
    $paginationHtml .= "<li class='page-item $active'><a class='page-link' href='#' data-page='$i'>$i</a></li>";
}
if($paginaActual < $totalPaginas){
    $paginationHtml .= "<li class='page-item'><a class='page-link' href='#' data-page='".($paginaActual+1)."'>Siguiente</a></li>";
}
$paginationHtml .= "</ul>";

echo json_encode(["cards"=>$cardsHtml,"pagination"=>$paginationHtml]);
