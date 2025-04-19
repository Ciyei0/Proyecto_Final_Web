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
        /* Enhanced navbar styles */
        .navbar {
            background: linear-gradient(135deg, #4b6cb7 0%, #182848 100%);
            padding: 0.8rem 1rem;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        
        .navbar-brand {
            font-size: 1.6rem;
            color: #fff !important;
            font-weight: 700;
            letter-spacing: 0.5px;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.2);
        }
        
        .navbar-brand i {
            color: #ffd700;
            margin-right: 0.5rem;
            filter: drop-shadow(0 1px 1px rgba(0, 0, 0, 0.3));
        }
        
        .navbar-nav .nav-link {
            color: rgba(255, 255, 255, 0.9) !important;
            font-weight: 500;
            padding: 0.5rem 1rem;
            border-radius: 4px;
            margin: 0 0.2rem;
            transition: all 0.3s ease;
        }
        
        .navbar-nav .nav-link:hover {
            color: #ffffff !important;
            background-color: rgba(255, 255, 255, 0.1);
            transform: translateY(-2px);
        }
        
        .navbar-nav .nav-link:active {
            transform: translateY(0);
        }
        
        .navbar-nav .nav-link i {
            margin-right: 0.4rem;
            font-size: 1.1rem;
        }
        
        .navbar-toggler {
            border: none;
            padding: 0.5rem;
            background-color: rgba(255, 255, 255, 0.1);
            border-radius: 4px;
            transition: background-color 0.3s ease;
        }
        
        .navbar-toggler:hover {
            background-color: rgba(255, 255, 255, 0.2);
        }
        
        .navbar-toggler:focus {
            box-shadow: 0 0 0 0.2rem rgba(255, 255, 255, 0.25);
        }
        
        .navbar-toggler-icon {
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 30 30'%3E%3Cpath stroke='rgba%28255, 255, 255, 1%29' stroke-width='2' d='M4 7h22M4 15h22M4 23h22'/%3E%3C/svg%3E");
        }
        
        .dropdown-menu {
            background-color: #182848;
            border: none;
            border-radius: 8px;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
            padding: 0.5rem;
            margin-top: 0.5rem;
        }
        
        .dropdown-menu .dropdown-item {
            color: rgba(255, 255, 255, 0.9);
            padding: 0.6rem 1rem;
            border-radius: 4px;
            transition: all 0.2s ease;
        }
        
        .dropdown-menu .dropdown-item:hover {
            background-color: rgba(75, 108, 183, 0.7);
            color: #fff;
            transform: translateX(4px);
        }
        
        /* Special styling for active nav items */
        .navbar-nav .active {
            background-color: rgba(255, 255, 255, 0.15);
            font-weight: 600;
        }
        
        /* Container padding adjustments */
        .container.mt-4 {
            padding-top: 1rem;
        }
        
        /* Auth buttons special styling */
        .auth-buttons .nav-link {
            margin-left: 0.5rem;
        }
        
        .login-btn {
            border: 1px solid rgba(255, 255, 255, 0.5);
        }
        
        .register-btn {
            background-color: #ffd700;
            color: #182848 !important;
            font-weight: 600;
        }
        
        .register-btn:hover {
            background-color: #ffcc00;
            color: #182848 !important;
            transform: translateY(-2px);
        }
        
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark sticky-top">
        <div class="container">
            <a class="navbar-brand" href="<?php echo BASE_URL; ?>">
                <i class="bi bi-briefcase-fill"></i><?php echo SITE_NAME; ?>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <!-- Main links -->
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo BASE_URL; ?>/ofertas/">
                            <i class="bi bi-clipboard-data"></i>Ofertas de Trabajo
                        </a>
                    </li>
                </ul>

                <!-- Authentication links -->
                <ul class="navbar-nav auth-buttons">
                    <?php if (isLoggedIn()): ?>
                        <?php if (isCandidate()): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="<?php echo BASE_URL; ?>/candidates/dashboard.php">
                                    <i class="bi bi-person-circle"></i>Mi Panel
                                </a>
                            </li>
                        <?php elseif (isCompany()): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="<?php echo BASE_URL; ?>/empresas/dashboard.php">
                                    <i class="bi bi-building"></i>Panel Empresa
                                </a>
                            </li>
                        <?php endif; ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo BASE_URL; ?>/auth/logout.php">
                                <i class="bi bi-box-arrow-right"></i>Cerrar Sesión
                            </a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link login-btn" href="<?php echo BASE_URL; ?>/auth/login.php">
                                <i class="bi bi-box-arrow-in-right"></i>Iniciar Sesión
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link register-btn" href="<?php echo BASE_URL; ?>/auth/register.php">
                                <i class="bi bi-person-plus"></i>Registrarse
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">