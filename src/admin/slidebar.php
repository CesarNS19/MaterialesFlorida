<?php
if (isset($_COOKIE['timezone'])) {
    date_default_timezone_set($_COOKIE['timezone']);
}

$hour = date('H');
if ($hour >= 5 && $hour < 12) {
    $greeting = 'Buenos Días';
} elseif ($hour >= 12 && $hour < 19) {
    $greeting = 'Buenas Tardes';
} else {
    $greeting = 'Buenas Noches';
}

if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $_SESSION['expire_time']) {
    session_unset();
    session_destroy();
    header("Location: ../../login/login.php");
    exit();
}

$_SESSION['last_activity'] = time();
?>
<!DOCTYPE html>
<html lang="es" data-bs-theme="light">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <link href="../../vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link
        href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i"
        rel="stylesheet">
    <link href="../../css/sb-admin-2.min.css" rel="stylesheet">
    <link href="../../css/style.css" rel="stylesheet">
    
    <style>
        .custom-orange-bg { background-color: #ff8c00 !important; }
        .custom-orange { background-color: #ff8c00 !important; }

        html[data-bs-theme="light"] .navbar {
        background-color: #f8f9fa !important;
        }
        html[data-bs-theme="light"] .navbar .nav-link,
        html[data-bs-theme="light"] .navbar .navbar-brand {
            color: #000 !important;
        }

        html[data-bs-theme="dark"] .navbar {
            background-color: #212529 !important;
        }
        html[data-bs-theme="dark"] .navbar .nav-link,
        html[data-bs-theme="dark"] .navbar .navbar-brand {
            color: #fff !important;
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-light bg-light shadow-sm">
    <div class="container-fluid">
        <button class="btn custom-orange text-white me-2" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebarMenu">
            <i class="fa fa-bars"></i>
        </button>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarContent">

            <form class="d-flex flex-grow-1 my-2 my-lg-0">
                <div class="input-group w-100">
                    <input id="search" type="text" class="form-control border-0 small" placeholder="Buscar" aria-label="Search">
                    <button class="input-group-text custom-orange border-0 text-white">
                        <i class="fas fa-search fa-sm"></i>
                    </button>
                </div>
            </form>

            <ul class="navbar-nav ms-auto align-items-center mt-2 mt-lg-0">

                <li class="nav-item me-3">
                    <?php
                        if (isset($_SESSION['nombre'], $_SESSION['apellido_paterno'], $_SESSION['apellido_materno'])) {
                            $fullName = $_SESSION['nombre'] . ' ' . $_SESSION['apellido_paterno'] . ' ' . $_SESSION['apellido_materno'];
                            echo "<span class='nav-link'>$greeting $fullName</span>";
                        } else {
                            echo "<a class='nav-link' href='../../login/login.php'>Iniciar Sesión</a>";
                        }
                    ?>
                </li>

                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="themeSwitchDropdown" data-bs-toggle="dropdown">
                        <span class="fas fa-sun" data-theme-dropdown-toggle-icon="light"></span>
                        <span class="fas fa-moon" data-theme-dropdown-toggle-icon="dark"></span>
                        <span class="fas fa-adjust" data-theme-dropdown-toggle-icon="auto"></span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="themeSwitchDropdown">
                        <li><button class="dropdown-item" type="button" value="light" data-theme-control="theme"><span class="fas fa-sun"></span> Light</button></li>
                        <li><button class="dropdown-item" type="button" value="dark" data-theme-control="theme"><span class="fas fa-moon"></span> Dark</button></li>
                        <li><button class="dropdown-item" type="button" value="auto" data-theme-control="theme"><span class="fas fa-adjust"></span> Auto</button></li>
                    </ul>
                </li>

                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="userDropdown" data-bs-toggle="dropdown">
                        <i class="fas fa-user fa-lg text-gray-600"></i>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                        <li><a class="dropdown-item" href="../../login/logout.php"><i class="fas fa-sign-out-alt me-2"></i> Cerrar Sesión</a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>


<div class="offcanvas offcanvas-start custom-orange-bg text-white" tabindex="-1" id="sidebarMenu">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title"><i class="fas fa-truck-loading"></i> La Florida</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body">
        <h6>Productos</h6>
        <ul class="nav flex-column mb-3">
            <li class="nav-item"><a href="categories.php" class="nav-link text-white"><i class="fas fa-tags"></i> Categorías</a></li>
            <li class="nav-item"><a href="brands.php" class="nav-link text-white"><i class="fas fa-industry"></i> Marcas</a></li>
            <li class="nav-item"><a href="products.php" class="nav-link text-white"><i class="fas fa-box-open"></i> Productos</a></li>
        </ul>

        <h6>Ventas</h6>
        <ul class="nav flex-column mb-3">
            <li class="nav-item"><a href="sales.php" class="nav-link text-white"><i class="fas fa-cash-register"></i> Ventas</a></li>
        </ul>

        <h6>Gestión de Cajas</h6>
        <ul class="nav flex-column mb-3">
            <li class="nav-item"><a href="cash_register.php" class="nav-link text-white"><i class="fas fa-money-bill-wave"></i> Caja</a></li>
        </ul>

        <h6>Usuarios</h6>
        <ul class="nav flex-column mb-3">
            <li class="nav-item"><a href="employees.php" class="nav-link text-white"><i class="fas fa-users"></i> Empleados</a></li>
            <li class="nav-item"><a href="customers.php" class="nav-link text-white"><i class="fas fa-user"></i> Clientes</a></li>
            <li class="nav-item"><a href="addresses.php" class="nav-link text-white"><i class="fas fa-map-marker-alt"></i> Direcciones</a></li>
        </ul>

        <h6>Compras</h6>
        <ul class="nav flex-column">
            <li class="nav-item"><a href="shopping.php" class="nav-link text-white"><i class="fas fa-basket-shopping"></i> Compras</a></li>
            <li class="nav-item"><a href="suppliers.php" class="nav-link text-white"><i class="fas fa-truck"></i> Proveedores</a></li>
        </ul>
    </div>
</div>

<main class="container my-4">
</main>
     <script src="../../vendor/jquery/jquery.min.js"></script>
    <script src="../../vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="../../vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="../../js/sb-admin-2.min.js"></script>
    <script src="../../vendor/chart.js/Chart.min.js"></script>
    <script src="../../js/demo/chart-area-demo.js"></script>
    <script src="../../js/demo/chart-pie-demo.js"></script>

<script>
    if (!document.cookie.includes("timezone")) {
        document.cookie = "timezone=" + Intl.DateTimeFormat().resolvedOptions().timeZone;
    }

    const themeButtons = document.querySelectorAll('[data-theme-control="theme"]');
    const savedTheme = localStorage.getItem('theme') || 'light';
    applyTheme(savedTheme);
    updateDropdown(savedTheme);

    themeButtons.forEach(button => {
        button.addEventListener('click', () => {
            const theme = button.value;
            localStorage.setItem('theme', theme);
            applyTheme(theme);
            updateDropdown(theme);
        });
    });

    function applyTheme(theme) {
        const html = document.documentElement;
        if (theme === 'auto') {
            const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            html.setAttribute('data-bs-theme', prefersDark ? 'dark' : 'light');
        } else {
            html.setAttribute('data-bs-theme', theme);
        }
    }

    function updateDropdown(theme) {
        const icons = document.querySelectorAll('[data-theme-dropdown-toggle-icon]');
        icons.forEach(icon => {
            icon.style.display = icon.dataset.themeDropdownToggleIcon === theme ? 'inline-block' : 'none';
        });
    }
</script>
</body>
</html>