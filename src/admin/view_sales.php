<?php
session_start();
include "../../mysql/connection.php";
include "slidebar.php";
$title = "La Florida │ Administrar Ventas";

if ((isset($_GET['mes']) && $_GET['mes'] !== '' && (empty($_GET['año']) || !isset($_GET['año']))) ||
    (isset($_GET['año']) && $_GET['año'] !== '' && (empty($_GET['mes']) || !isset($_GET['mes'])))) {
    echo "<script>alert('Para filtrar por mes, también debes seleccionar el año'); window.location.href = 'view_sales.php';</script>";
    exit;
}

$whereClauses = [];

if (!empty($_GET['fecha_inicio']) && !empty($_GET['fecha_fin'])) {
    $fecha_inicio = $conn->real_escape_string($_GET['fecha_inicio']);
    $fecha_fin = $conn->real_escape_string($_GET['fecha_fin']);
    $whereClauses[] = "(v.fecha BETWEEN '$fecha_inicio 00:00:00' AND '$fecha_fin 23:59:59')";
} elseif (!empty($_GET['fecha_inicio'])) {
    $fecha_inicio = $conn->real_escape_string($_GET['fecha_inicio']);
    $whereClauses[] = "(v.fecha >= '$fecha_inicio 00:00:00')";
} elseif (!empty($_GET['fecha_fin'])) {
    $fecha_fin = $conn->real_escape_string($_GET['fecha_fin']);
    $whereClauses[] = "(v.fecha <= '$fecha_fin 23:59:59')";
}

if (!empty($_GET['mes']) && !empty($_GET['año'])) {
    $mes = $conn->real_escape_string($_GET['mes']);
    $año = $conn->real_escape_string($_GET['año']);
    $whereClauses[] = "MONTH(v.fecha) = '$mes' AND YEAR(v.fecha) = '$año'";
}

$searchQuery = "";
if (!empty($whereClauses)) {
    $searchQuery = " WHERE " . implode(" AND ", $whereClauses);
}

$ventasPorPagina = 6;
$paginaActual = isset($_GET['pagina']) ? max(1, intval($_GET['pagina'])) : 1;
$offset = ($paginaActual - 1) * $ventasPorPagina;

$sqlTotal = "SELECT COUNT(DISTINCT v.id_venta) AS total
             FROM ventas v
             JOIN detalle_venta d ON v.id_venta = d.id_venta
             JOIN usuarios u ON v.id_usuario = u.id_usuario
             $searchQuery";

$totalResult = $conn->query($sqlTotal);
$totalVentas = $totalResult->fetch_assoc()['total'];
$totalPaginas = ceil($totalVentas / $ventasPorPagina);

$sql = "SELECT v.id_venta, v.fecha, 
               SUM(d.cantidad) AS total_articulos, 
               SUM(d.subtotal) AS total_compra,
               CONCAT(u.nombre, ' ', u.apellido_paterno, ' ', u.apellido_materno) AS nombre_completo
        FROM ventas v
        JOIN detalle_venta d ON v.id_venta = d.id_venta
        JOIN usuarios u ON v.id_usuario = u.id_usuario
        $searchQuery
        GROUP BY v.id_venta
        ORDER BY v.fecha DESC
        LIMIT $ventasPorPagina OFFSET $offset";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="es" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <title><?php echo $title; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://kit.fontawesome.com/a076d05399.js"></script>
</head>
<body>

<div class="container-fluid d-flex" id="products-container">
    <main class="flex-fill p-4 overflow-auto" id="main-content">
        <h2 class="text-center custom-orange-text fw-bold mt-2">Ventas</h2>

        <form class="row g-3 align-items-center mb-4" method="GET">
            <div class="col-auto">
                <label for="fecha_inicio" class="col-form-label">Desde:</label>
            </div>
            <div class="col-auto">
                <input type="date" class="form-control" name="fecha_inicio" id="fecha_inicio" value="<?= $_GET['fecha_inicio'] ?? '' ?>">
            </div>
            <div class="col-auto">
                <label for="fecha_fin" class="col-form-label">Hasta:</label>
            </div>
            <div class="col-auto">
                <input type="date" class="form-control" name="fecha_fin" id="fecha_fin" value="<?= $_GET['fecha_fin'] ?? '' ?>">
            </div>
            <div class="col-auto">
                <label for="mes" class="col-form-label">Mes:</label>
            </div>
            <div class="col-auto">
                <select class="form-select" name="mes" id="mes">
                    <option value="">-- Mes --</option>
                    <?php
                    $meses = [
                        '01' => 'Enero', '02' => 'Febrero', '03' => 'Marzo',
                        '04' => 'Abril', '05' => 'Mayo', '06' => 'Junio',
                        '07' => 'Julio', '08' => 'Agosto', '09' => 'Septiembre',
                        '10' => 'Octubre', '11' => 'Noviembre', '12' => 'Diciembre'
                    ];
                    foreach ($meses as $num => $nombre) {
                        $selected = (isset($_GET['mes']) && $_GET['mes'] == $num) ? 'selected' : '';
                        echo "<option value='$num' $selected>$nombre</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="col-auto">
                <label for="año" class="col-form-label">Año:</label>
            </div>
            <div class="col-auto">
                <select class="form-select" name="año" id="año">
                    <option value="">-- Año --</option>
                    <?php
                    $añoActual = date('Y');
                    for ($i = $añoActual; $i >= 2020; $i--) {
                        $selected = (isset($_GET['año']) && $_GET['año'] == $i) ? 'selected' : '';
                        echo "<option value='$i' $selected>$i</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn custom-orange-btn text-white">Filtrar</button>
            </div>
            <div class="col-auto">
                <a href="view_sales.php" class="btn btn-secondary">Limpiar</a>
            </div>
        </form>

        <?php
        if (!empty($_GET['fecha_inicio']) || !empty($_GET['fecha_fin']) || !empty($_GET['mes']) || !empty($_GET['año'])) {
            echo "<p class='text-center text-muted'>";
            if (!empty($_GET['fecha_inicio'])) {
                echo "Desde <strong>" . date('d/m/Y', strtotime($_GET['fecha_inicio'])) . "</strong> ";
            }
            if (!empty($_GET['fecha_fin'])) {
                echo "hasta <strong>" . date('d/m/Y', strtotime($_GET['fecha_fin'])) . "</strong> ";
            }
            if (!empty($_GET['mes'])) {
                echo "mes: <strong>" . $meses[$_GET['mes']] . "</strong> ";
            }
            if (!empty($_GET['año'])) {
                echo "año: <strong>" . $_GET['año'] . "</strong>";
            }
            echo "</p>";
        }
        ?>

        <?php
        $meses_en = [
            'January' => 'enero', 'February' => 'febrero', 'March' => 'marzo', 'April' => 'abril',
            'May' => 'mayo', 'June' => 'junio', 'July' => 'julio', 'August' => 'agosto',
            'September' => 'septiembre', 'October' => 'octubre', 'November' => 'noviembre', 'December' => 'diciembre'
        ];

        if ($result->num_rows > 0) {
            echo "<div class='row row-cols-1 row-cols-md-2 g-4 mt-2'>";
            while ($venta = $result->fetch_assoc()) {
                $fecha = new DateTime($venta['fecha']);
                $fecha_formateada = $fecha->format('d') . " de " . $meses_en[$fecha->format('F')] . " de " . $fecha->format('Y');

                echo "<div class='col'>";
                echo "<div class='card border border-light shadow-sm rounded-4 h-100'>";
                echo "<div class='card-header text-center fw-semibold rounded-top-4'><i class='fas fa-receipt me-2'></i> Venta Realizada</div>";
                echo "<div class='card-body px-4 py-3 d-flex flex-column justify-content-between h-100'>";
                echo "<div class='d-flex justify-content-between mb-3 text-muted small'><div><i class='fas fa-calendar-alt me-1'></i> $fecha_formateada</div></div>";
                echo "<div class='mb-3'><div class='fw-semibold text-body mb-1'><i class='fas fa-user me-2'></i>Cliente</div><div class='text-muted'>{$venta['nombre_completo']}</div></div>";
                echo "<div class='row mb-3'><div class='col-6'><div class='fw-semibold text-body'><i class='fas fa-box me-2'></i>Artículos: {$venta['total_articulos']}</div></div>";
                echo "<div class='col-6 text-end'><div class='fw-bold'>Total: \$" . number_format($venta['total_compra'], 2) . "</div></div></div>";
                echo "<div class='mt-auto text-end'><button class='btn custom-orange-btn text-white btn-sm rounded-pill px-4' onclick='verTicket({$venta['id_venta']})' data-bs-toggle='tooltip' data-bs-placement='top' title='Ver ticket de venta'><i class='fas fa-eye me-1'></i></button></div>";
                echo "</div></div></div>";
            }
            echo "</div>";

            // PAGINACION
            if ($totalPaginas > 1) {
                echo '<nav class="mt-4">';
                echo '<ul class="pagination justify-content-center">';
                for ($i = 1; $i <= $totalPaginas; $i++) {
                    $isActive = ($i == $paginaActual) ? 'active' : '';
                    $queryString = $_GET;
                    $queryString['pagina'] = $i;
                    $queryUrl = http_build_query($queryString);
                    echo "<li class='page-item $isActive'><a class='page-link' href='?{$queryUrl}'>$i</a></li>";
                }
                echo '</ul>';
                echo '</nav>';
            }

        } else {
            echo "<p class='text-center text-muted'>No se han encontrado ventas con los filtros aplicados.</p>";
        }
        ?>
    </main>
</div>

<script>
    function verTicket(idVenta) {
        window.location.href = 'sales/ticket_generate.php?id_venta=' + idVenta;
    }

    document.addEventListener('DOMContentLoaded', function () {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.forEach(function (tooltipTriggerEl) {
            new bootstrap.Tooltip(tooltipTriggerEl);
        });
    });
</script>

</body>
</html>
