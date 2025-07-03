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

<style>
    #accordionSidebar {
        position: fixed;
        top: 0;
        left: 0;
        height: 100vh;
        z-index: 1030;
        overflow-y: auto;
    }

    #content-wrapper {
        margin-left: 220px;
    }

    #main-content {
        margin-top: 1px;
        overflow-y: auto;
        max-height: calc(100vh - 90px);
    }

    .custom-orange-bg {
        background-color: #ff8c00 !important;
    }

    .custom-orange {
        background-color: #ff8c00 !important;
    }
</style>

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
</head>

<body id="page-top">

        <ul class="navbar-nav sidebar sidebar-dark accordion custom-orange-bg" id="accordionSidebar">
            <a class="sidebar-brand d-flex align-items-center justify-content-center" href="index_admin.php">
                <div class="sidebar-brand-icon rotate-n-15">
                    <i class="fas fa-truck-loading"></i>
                </div>
                <div class="sidebar-brand-text mx-3">La Florida</div>
            </a>

            <hr class="sidebar-divider my-0">
            <li class="nav-item active">
                <a class="nav-link" href="index_admin.php">
                    <i class="fas fa-home"></i>
                    <span>Inicio</span></a>
            </li>

            <hr class="sidebar-divider">
            <div class="sidebar-heading">
                Productos
            </div>
              <li class="nav-item">
            <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseTwo"
                aria-expanded="true" aria-controls="collapseTwo">
                <i class="fas fa-layer-group"></i>
                <span>Categorías</span>
            </a>
            <div id="collapseTwo" class="collapse" aria-labelledby="headingTwo" data-parent="#accordionSidebar">
                <div class="bg-white py-2 collapse-inner rounded">
                    <a class="collapse-item" href="categories.php">
                        <i class="fas fa-layer-group"></i>Categorias
                    </a>
                </div>
            </div>
        </li>
        <li class="nav-item">
            <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapsePages"
                aria-expanded="true" aria-controls="collapsePages">
                <i class="fas fa-globe"></i>
                <span>Marcas</span>
            </a>
            <div id="collapsePages" class="collapse" aria-labelledby="headingTwo" data-parent="#accordionSidebar">
                <div class="bg-white py-2 collapse-inner rounded">
                    <a class="collapse-item" href="brands.php">
                        <i class="fas fa-globe"></i> Marcas
                    </a>
                </div>
            </div>
        </li>

            <li class="nav-item">
                <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseUtilities"
                    aria-expanded="true" aria-controls="collapseUtilities">
                    <i class="fas fa-box"></i>
                    <span>Productos</span>
                </a>
                <div id="collapseUtilities" class="collapse" aria-labelledby="headingUtilities"
                    data-parent="#accordionSidebar">
                    <div class="bg-white py-2 collapse-inner rounded">
                        <a class="collapse-item" href="products.php">
                            <i class="fas fa-box"></i> Productos
                        </a>
                    </div>
                </div>
            </li>

            <hr class="sidebar-divider">

            <div class="sidebar-heading">
                Ventas
            </div>

            <li class="nav-item">
                <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapse"
                    aria-expanded="true" aria-controls="collapse">
                    <i class="fas fa-dollar-sign"></i>
                    <span>Ventas</span>
                </a>
                <div id="collapse" class="collapse" aria-labelledby="headingPages" data-parent="#accordionSidebar">
                    <div class="bg-white py-2 collapse-inner rounded">
                        <a class="collapse-item" href="sales.php">
                            <i class="fas fa-chart-line"></i> Mis Ventas
                        </a>
                    </div>
                </div>
            </li>
            <hr class="sidebar-divider">
            <div class="sidebar-heading">
                Usuarios
            </div>
            <li class="nav-item">
                <a class="nav-link" href="employees.php">
                    <i class="fas fa-users"></i>
                    <span>Empleados</span></a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="customers.php">
                    <i class="fas fa-user"></i>
                    <span>Clientes</span></a>
            </li>
            <hr class="sidebar-divider">
            <div class="sidebar-heading">
                Compras
            </div>
            <li class="nav-item">
                <a class="nav-link" href="shopping.php">
                    <i class="fas fa-basket-shopping"></i>
                    <span>Compras</span>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link" href="suppliers.php">
                    <i class="fas fa-truck"></i> 
                    <span>Proveedores</span>
                </a>
            </li>
        </ul>

        <div id="content-wrapper" class="d-flex flex-column">

                <nav class="navbar navbar-expand topbar mb-4 static-top shadow">

                    <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
                        <i class="fa fa-bars"></i>
                    </button>

                    <div class="input-group col-4">
                        <input id="search" type="text" class="form-control border-0 small" placeholder="Buscar" aria-label="Search" aria-describedby="basic-addon2">
                        <div class="input-group-append">
                            <span class="input-group-text custom-orange border-0 text-white">
                                <i class="fas fa-search fa-sm"></i>
                            </span>
                        </div>
                    </div>

                    <ul class="navbar-nav ml-auto">

                    <?php
                        if (isset($_SESSION['nombre'], $_SESSION['apellido_paterno'], $_SESSION['apellido_materno'])) {
                            $fullName = $_SESSION['nombre'] . ' ' . $_SESSION['apellido_paterno'] . ' ' . $_SESSION['apellido_materno'];

                            echo "
                            <div class='nav-item' style='display: flex; align-items: center; margin-left: auto;'>
                                <a class='nav-link' font-size: 15px; text-decoration: none; font-weight: normal;'>
                                    $greeting $fullName
                                </a>
                            </div>";
                        } else {
                            echo "<div class='nav-item' style='display: flex; align-items: center; margin-left: auto;'>
                            <a class='nav-link' href='../../login/login.php' font-size: 15px; text-decoration: none; font-weight: normal;'>Iniciar Sesión</a>
                             </div>";
                        }
                    ?>

                    <li class="nav-item ps-2 pe-0">
                            <div class="dropdown theme-control-dropdown">
                                <a class="nav-link d-flex align-items-center dropdown-toggle fs-9 pe-1 py-0" href="#" 
                                role="button" id="themeSwitchDropdown" data-bs-toggle="dropdown" aria-haspopup="true"
                                aria-expanded="false">
                                    <span class="fas fa-sun fs-7" data-theme-dropdown-toggle-icon="light"></span>
                                    <span class="fas fa-moon fs-7" data-theme-dropdown-toggle-icon="dark"></span>
                                    <span class="fas fa-adjust fs-7" data-theme-dropdown-toggle-icon="auto"></span>
                                </a>
                                <div class="dropdown-menu dropdown-menu-end dropdown-caret border py-0 mt-3"
                                    aria-labelledby="themeSwitchDropdown">
                                    <div class="rounded-2 py-2">
                                        <button class="dropdown-item d-flex align-items-center gap-2" type="button" value="light" data-theme-control="theme">
                                            <span class="fas fa-sun"></span> Light
                                            <span class=" ms-auto text-600"></span>
                                        </button>
                                        <button class="dropdown-item d-flex align-items-center gap-2" type="button" value="dark" data-theme-control="theme">
                                            <span class="fas fa-moon"></span> Dark
                                            <span class=" ms-auto text-600"></span>
                                        </button>
                                        <button class="dropdown-item d-flex align-items-center gap-2" type="button" value="auto" data-theme-control="theme">
                                            <span class="fas fa-adjust"></span> Auto
                                            <span class=" ms-auto text-600"></span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </li>

                        <div class="topbar-divider d-none d-sm-block"></div>

                        <li class="nav-item dropdown no-arrow">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button"
                            data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fas fa-user fa-lg text-gray-600"></i> 
                        </a>
                        <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in" aria-labelledby="userDropdown"> 
                            <a class="dropdown-item" href="../../login/logout.php">
                                <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                                Cerrar Sesión
                            </a>
                        </div>
                    </li>
                </ul>
            </nav>

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