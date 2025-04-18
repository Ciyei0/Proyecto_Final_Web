<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' | ' . SITE_NAME : SITE_NAME; ?></title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        /* Custom styles for the navbar */
        .navbar {
            background: linear-gradient(90deg, #007bff, #6610f2);
        }
        .navbar-brand {
            font-size: 1.5rem;
            color: #fff !important;
        }
        .navbar-nav .nav-link {
            color: #fff !important;
            transition: color 0.3s ease, transform 0.3s ease;
        }
        .navbar-nav .nav-link:hover {
            color: #ffd700 !important;
            transform: scale(1.1);
        }
        .navbar-toggler {
            border-color: #fff;
        }
        .navbar-toggler-icon {
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 30 30'%3E%3Cpath stroke='rgba%28255, 255, 255, 1%29' stroke-width='2' d='M4 7h22M4 15h22M4 23h22'/%3E%3C/svg%3E");
        }
        .dropdown-menu {
            background-color: #6610f2;
        }
        .dropdown-menu .dropdown-item {
            color: #fff;
        }
        .dropdown-menu .dropdown-item:hover {
            background-color: #007bff;
            color: #fff;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold" href="<?php echo BASE_URL; ?>">
                <i class="bi bi-briefcase-fill me-2"></i><?php echo SITE_NAME; ?>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <!-- Enlaces principales -->
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo BASE_URL; ?>/ofertas/">
                            <i class="bi bi-clipboard-data me-1"></i>Ofertas de Trabajo
                        </a>
                    </li>
                </ul>

                <!-- Enlaces de autenticación -->
                <ul class="navbar-nav">
                    <?php if (isLoggedIn()): ?>
                        <?php if (isCandidate()): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="<?php echo BASE_URL; ?>/candidates/dashboard.php">
                                    <i class="bi bi-person-circle me-1"></i>Mi Panel
                                </a>
                            </li>
                        <?php elseif (isCompany()): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="<?php echo BASE_URL; ?>/empresas/dashboard.php">
                                    <i class="bi bi-building me-1"></i>Panel Empresa
                                </a>
                            </li>
                        <?php endif; ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo BASE_URL; ?>/auth/logout.php">
                                <i class="bi bi-box-arrow-right me-1"></i>Cerrar Sesión
                            </a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo BASE_URL; ?>/auth/login.php">
                                <i class="bi bi-box-arrow-in-right me-1"></i>Iniciar Sesión
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo BASE_URL; ?>/auth/register.php">
                                <i class="bi bi-person-plus me-1"></i>Registrarse
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
