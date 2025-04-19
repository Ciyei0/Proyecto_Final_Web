<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/auth_functions.php';

$pageTitle = "Plataforma de Empleo";
require_once __DIR__ . '/includes/header.php';

// Obtener ofertas destacadas (las más recientes y activas)
$stmt = $pdo->query("
    SELECT o.*, e.nombre_empresa, e.logo 
    FROM ofertas_empleo o
    JOIN empresas e ON o.empresa_id = e.usuario_id
    WHERE o.activa = 1 AND (o.fecha_cierre IS NULL OR o.fecha_cierre >= CURDATE())
    ORDER BY o.fecha_publicacion DESC
    LIMIT 6
");
$featuredOffers = $stmt->fetchAll();

// Obtener estadísticas generales
$stats = [
    'ofertas' => $pdo->query("SELECT COUNT(*) FROM ofertas_empleo WHERE activa = 1")->fetchColumn(),
    'empresas' => $pdo->query("SELECT COUNT(*) FROM empresas")->fetchColumn(),
    'candidatos' => $pdo->query("SELECT COUNT(*) FROM candidatos")->fetchColumn()
];
?>

<!-- Importación de Iconos Modernos y Google Fonts -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<!-- Añadimos Material Icons como alternativa para íconos -->
<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
<!-- Importamos Bootstrap Icons para tener más opciones -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

<!-- Estilos personalizados mejorados -->

<style>
    /* Estilos generales */
    :root {
        --primary-color: #4361ee;
        --primary-dark: #3a56d4;
        --secondary-color: #7209b7;
        --success-color: #06d6a0;
        --warning-color: #ffd166;
        --danger-color: #ef476f;
        --light-bg: #f8f9fc;
        --dark-text: #2b2d42;
        --medium-text: #495057;
        --light-text: #8d99ae;
        --card-shadow: 0 10px 25px rgba(50, 50, 93, 0.1), 0 5px 15px rgba(0, 0, 0, 0.07);
        --hover-shadow: 0 14px 30px rgba(50, 50, 93, 0.15), 0 8px 20px rgba(0, 0, 0, 0.1);
        --border-radius: 12px;
    }
    
    body {
        color: var(--dark-text);
        background-color: #fcfcfc;
    }
    
    .section-title {
        position: relative;
        margin-bottom: 3rem;
        text-align: center;
        font-weight: 700;
        color: var(--dark-text);
    }
    
    .section-title:after {
        content: '';
        position: absolute;
        bottom: -15px;
        left: 50%;
        width: 70px;
        height: 4px;
        transform: translateX(-50%);
        background: linear-gradient(90deg, var(--primary-color), var(--secondary-color));
        border-radius: 2px;
    }
    
    /* Hero Section */
    .hero-section {
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        color: white;
        padding: 120px 0 100px;
        position: relative;
        overflow: hidden;
    }
    
    .hero-section::before {
        content: '';
        position: absolute;
        top: 0;
        right: 0;
        bottom: 0;
        left: 0;
        background-image: url("data:image/svg+xml,%3Csvg width='100' height='100' viewBox='0 0 100 100' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M11 18c3.866 0 7-3.134 7-7s-3.134-7-7-7-7-3.134-7-7 3.134-7 7-7 7-3.134 7-7-3.134-7-7-7-7-3.134-7-7 3.134-7 7-7 7-3.134 7-7-3.134-7-7-7-7-3.134-7-7 3.134-7 7-7 7-3.134 7-7-3.134-7-7-7-7-3.134-7-7 3.134-7 7-7 7-3.134 7-7-3.134-7-7-7-7-3.134-7-7 3.134-7 7-7 7-3.134 7-7-3.134-7-7-7-7-3.134-7-7 3.134-7 7-7 7-3.134 7-7-3.134-7-7-7z' fill='%23ffffff' fill-opacity='0.05' fill-rule='evenodd'/%3E%3C/svg%3E");
        z-index: 0;
    }
    
    .hero-content {
        position: relative;
        z-index: 1;
    }
    
    .hero-title {
        font-size: 3.5rem;
        font-weight: 800;
        line-height: 1.2;
        margin-bottom: 1.5rem;
    }
    
    .hero-subtitle {
        font-size: 1.25rem;
        font-weight: 400;
        margin-bottom: 2rem;
        opacity: 0.9;
    }
    
    .hero-image {
        transform: translateY(0);
        animation: float 6s ease-in-out infinite;
    }
    
    @keyframes float {
        0% { transform: translateY(0px) rotate(0deg); }
        50% { transform: translateY(-20px) rotate(1deg); }
        100% { transform: translateY(0px) rotate(0deg); }
    }
    
    .hero-btn {
        border-radius: 50px;
        padding: 12px 30px;
        font-weight: 600;
        transition: all 0.3s ease;
        box-shadow: 0 7px 15px rgba(0, 0, 0, 0.15);
        position: relative;
        overflow: hidden;
        z-index: 1;
    }
    
    .hero-btn::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: rgba(255, 255, 255, 0.2);
        transition: all 0.4s ease;
        z-index: -1;
    }
    
    .hero-btn:hover {
        transform: translateY(-5px);
        box-shadow: 0 12px 25px rgba(0, 0, 0, 0.2);
    }
    
    .hero-btn:hover::before {
        left: 0;
    }
    
    /* Stats Section */
    .stats-section {
        background-color: white;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
        position: relative;
        margin-top: -50px;
        z-index: 10;
        border-radius: var(--border-radius);
    }
    
    .stat-card {
        padding: 30px 20px;
        transition: all 0.3s ease;
        text-align: center;
    }
    
    .stat-card:hover {
        transform: translateY(-7px);
    }
    
    .stat-icon {
        font-size: 2.5rem;
        margin-bottom: 15px;
        display: inline-block;
        color: var(--primary-color);
    }
    
    .stat-number {
        display: block;
        font-size: 2.5rem;
        font-weight: 700;
        background: linear-gradient(to right, var(--primary-color), var(--secondary-color));
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        margin-bottom: 5px;
    }
    
    .stat-label {
        color: var(--medium-text);
        font-weight: 500;
        font-size: 1.1rem;
    }
    
    /* Featured Offers */
    .featured-offers {
        padding: 100px 0 80px;
    }
    
    .offer-card {
        border: none;
        border-radius: var(--border-radius);
        transition: all 0.4s ease;
        overflow: hidden;
        box-shadow: var(--card-shadow);
        height: 100%;
    }
    
    .offer-card:hover {
        transform: translateY(-12px);
        box-shadow: var(--hover-shadow);
    }
    
    .card-img-top {
        height: 120px;
        position: relative;
        overflow: hidden;
    }
    
    .card-img-top::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: linear-gradient(45deg, rgba(0, 0, 0, 0.2), rgba(0, 0, 0, 0));
    }
    
    .company-logo {
        width: 70px;
        height: 70px;
        border-radius: 12px;
        overflow: hidden;
        border: 3px solid white;
        margin-top: -35px;
        position: relative;
        background-color: white;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .company-logo img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    
    .offer-badge {
        display: inline-flex;
        align-items: center;
        padding: 6px 12px;
        border-radius: 50px;
        font-size: 0.75rem;
        font-weight: 600;
        margin-right: 5px;
        margin-bottom: 5px;
    }
    
    .badge-location {
        background-color: rgba(67, 97, 238, 0.1);
        color: var(--primary-color);
    }
    
    .badge-contract {
        background-color: rgba(114, 9, 183, 0.1);
        color: var(--secondary-color);
    }
    
    .badge-salary {
        background-color: rgba(6, 214, 160, 0.1);
        color: var(--success-color);
    }
    
    .view-all-btn {
        border-radius: 50px;
        padding: 10px 24px;
        font-weight: 600;
        transition: all 0.3s ease;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    }
    
    .view-all-btn:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 15px rgba(0, 0, 0, 0.15);
    }
    
    /* How It Works */
    .how-it-works {
        padding: 100px 0;
        background-color: var(--light-bg);
        position: relative;
    }
    
    .how-it-works::before {
        content: '';
        position: absolute;
        width: 100%;
        height: 100%;
        top: 0;
        left: 0;
        background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23000000' fill-opacity='0.02'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
    }
    
    .step-card {
        border-radius: var(--border-radius);
        transition: all 0.4s ease;
        border: none;
        box-shadow: var(--card-shadow);
        overflow: hidden;
        position: relative;
    }
    
    .step-card:hover {
        transform: translateY(-12px);
        box-shadow: var(--hover-shadow);
    }
    
    .step-icon {
        width: 100px;
        height: 100px;
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 30px;
        color: white;
        font-size: 2.2rem;
        box-shadow: 0 10px 25px rgba(67, 97, 238, 0.3);
        position: relative;
        z-index: 1;
    }
    
    .step-icon::before {
        content: '';
        position: absolute;
        width: 100%;
        height: 100%;
        border-radius: 50%;
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        top: 0;
        left: 0;
        z-index: -1;
        opacity: 0.5;
        animation: pulse 2s infinite;
    }
    
    @keyframes pulse {
        0% {
            transform: scale(1);
            opacity: 0.5;
        }
        50% {
            transform: scale(1.2);
            opacity: 0;
        }
        100% {
            transform: scale(1);
            opacity: 0.5;
        }
    }
    
    .step-title {
        font-weight: 700;
        font-size: 1.5rem;
        margin-bottom: 1rem;
        color: var(--dark-text);
    }
    
    .step-btn {
        padding: 8px 20px;
        border-radius: 50px;
        font-weight: 600;
        transition: all 0.3s ease;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    }
    
    .step-btn:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 15px rgba(0, 0, 0, 0.15);
    }
    
    /* Testimonials */
    .testimonials {
        padding: 100px 0;
        background-color: #fff;
    }
    
    .testimonial-card {
        border: none;
        border-radius: var(--border-radius);
        overflow: hidden;
        transition: all 0.4s ease;
        box-shadow: var(--card-shadow);
        height: 100%;
    }
    
    .testimonial-card:hover {
        transform: translateY(-12px);
        box-shadow: var(--hover-shadow);
    }
    
    .testimonial-avatar {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        border: 4px solid white;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        object-fit: cover;
    }
    
    .star-rating {
        color: #ffc107;
        font-size: 1.1rem;
    }
    
    .testimonial-quote {
        position: relative;
        padding-left: 2rem;
        padding-right: 1rem;
        font-style: italic;
        line-height: 1.8;
    }
    
    .testimonial-quote::before {
        content: '\f10d';
        font-family: 'Font Awesome 6 Free';
        font-weight: 900;
        position: absolute;
        left: 0;
        top: 0;
        color: var(--primary-color);
        opacity: 0.3;
        font-size: 1.5rem;
    }
    
    /* Call to Action */
    .cta-section {
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        padding: 100px 0;
        position: relative;
        overflow: hidden;
    }
    
    .cta-section::before {
        content: '';
        position: absolute;
        top: 0;
        right: 0;
        bottom: 0;
        left: 0;
        background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.05'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
    }
    
    .cta-title {
        font-size: 2.8rem;
        font-weight: 800;
        margin-bottom: 1.5rem;
        color: white;
    }
    
    .cta-subtitle {
        font-size: 1.2rem;
        margin-bottom: 2.5rem;
        opacity: 0.9;
        color: white;
    }
    
    .cta-btn {
        border-radius: 50px;
        padding: 16px 36px;
        font-weight: 700;
        transition: all 0.3s ease;
        background: #fff;
        color: var(--primary-color);
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        position: relative;
        overflow: hidden;
        z-index: 1;
    }
    
    .cta-btn::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
        transition: all 0.4s ease;
        z-index: -1;
    }
    
    .cta-btn:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.25);
        color: var(--primary-dark);
    }
    
    .cta-btn:hover::before {
        left: 100%;
    }
    
    /* Animaciones */
    .fade-in-up {
        opacity: 0;
        transform: translateY(40px);
        transition: opacity 0.6s ease-out, transform 0.6s ease-out;
    }
    
    .fade-in-up.active {
        opacity: 1;
        transform: translateY(0);
    }
    
    /* Efectos adicionales */
    .icon-tada {
        animation: tada 2s infinite;
        animation-play-state: paused;
        display: inline-block;
    }
    
    .card:hover .icon-tada {
        animation-play-state: running;
    }
    
    @keyframes tada {
        0% { transform: scale(1); }
        10%, 20% { transform: scale(0.9) rotate(-3deg); }
        30%, 50%, 70%, 90% { transform: scale(1.1) rotate(3deg); }
        40%, 60%, 80% { transform: scale(1.1) rotate(-3deg); }
        100% { transform: scale(1) rotate(0); }
    }

    /* Responsive adjustments */
    @media (max-width: 992px) {
        .hero-title {
            font-size: 2.8rem;
        }
    }
    
    @media (max-width: 768px) {
        .hero-section {
            padding: 80px 0 60px;
            text-align: center;
        }
        
        .hero-title {
            font-size: 2.5rem;
        }
        
        .hero-image {
            margin-top: 50px;
            max-width: 80%;
            margin-left: auto;
            margin-right: auto;
        }
        
        .stats-section {
            margin-top: 0;
            border-radius: 0;
        }
        
        .stat-card, .step-card, .testimonial-card {
            margin-bottom: 30px;
        }
        
        .cta-title {
            font-size: 2.2rem;
        }
    }
    
    @media (max-width: 576px) {
        .hero-title {
            font-size: 2rem;
        }
        
        .hero-subtitle {
            font-size: 1.1rem;
        }
        
        .cta-title {
            font-size: 1.8rem;
        }
        
        .cta-btn {
            padding: 12px 30px;
        }
        
        .step-icon {
            width: 80px;
            height: 80px;
            font-size: 1.8rem;
        }
    }
</style>
<!-- Script para inicializar animaciones -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Activar animaciones de fade-in-up cuando elementos entran en viewport
    const fadeElements = document.querySelectorAll('.fade-in-up');
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                setTimeout(() => {
                    entry.target.classList.add('active');
                }, entry.target.dataset.delay || 0);
            }
        });
    }, { threshold: 0.1 });
    
    fadeElements.forEach(element => {
        observer.observe(element);
    });
    
    // Contador de animación para estadísticas
    const counters = document.querySelectorAll('.counter');
    
    counters.forEach(counter => {
        const target = +counter.innerText;
        const duration = 2000;
        const increment = target / (duration / 16);
        let current = 0;
        
        const updateCounter = () => {
            current = Math.min(current + increment, target);
            counter.innerText = Math.floor(current).toLocaleString();
            
            if (current < target) {
                requestAnimationFrame(updateCounter);
            } else {
                counter.innerText = target.toLocaleString();
            }
        };
        
        const counterObserver = new IntersectionObserver((entries) => {
            if (entries[0].isIntersecting) {
                updateCounter();
                counterObserver.unobserve(counter);
            }
        }, { threshold: 0.5 });
        
        counterObserver.observe(counter);
    });
});
</script>

<!-- Hero Section Mejorado -->
<section class="hero-section">
    <div class="container position-relative">
        <div class="row align-items-center">
            <div class="col-lg-6 hero-content">
                <h1 class="hero-title animate__animated animate__fadeInUp">
                    Descubre Tu <span style="color: var(--warning-color);">Próxima Oportunidad</span> Profesional
                </h1>
                <p class="hero-subtitle animate__animated animate__fadeInUp animate__delay-1s">
                    Conectamos talento excepcional con empresas innovadoras. Encuentra el trabajo perfecto que impulse tu carrera y transforme tu futuro.
                </p>
                <div class="d-flex gap-3 flex-wrap animate__animated animate__fadeInUp animate__delay-2s">
                    <a href="<?php echo BASE_URL; ?>/auth/register.php?tipo=candidato" class="btn btn-light hero-btn">
                        <i class="bi bi-person-badge me-2"></i>Busco Empleo
                    </a>
                    <a href="<?php echo BASE_URL; ?>/auth/register.php?tipo=empresa" class="btn btn-outline-light hero-btn">
                        <i class="bi bi-buildings me-2"></i>Busco Talento
                    </a>
                </div>
            </div>
            <div class="col-lg-6 d-none d-lg-block">
                <img src="<?php echo BASE_URL; ?>/assets/images/hero-image.svg" alt="Personas trabajando" class="img-fluid hero-image animate__animated animate__fadeIn animate__delay-1s">
            </div>
        </div>
    </div>
</section>

<!-- Stats Section Mejorado -->
<section class="py-5">
    <div class="container">
        <div class="stats-section py-5 px-4">
            <div class="row text-center">
                <div class="col-md-4">
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="bi bi-briefcase-fill"></i>
                        </div>
                        <span class="stat-number counter"><?php echo number_format($stats['ofertas']); ?></span>
                        <span class="stat-label">Ofertas Activas</span>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="bi bi-building"></i>
                        </div>
                        <span class="stat-number counter"><?php echo number_format($stats['empresas']); ?></span>
                        <span class="stat-label">Empresas Registradas</span>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="bi bi-people-fill"></i>
                        </div>
                        <span class="stat-number counter"><?php echo number_format($stats['candidatos']); ?></span>
                        <span class="stat-label">Candidatos Activos</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Featured Offers Mejorado -->
<section class="featured-offers">
    <div class="container">
        <h2 class="section-title">Ofertas Destacadas</h2>
        
        <div class="d-flex justify-content-end mb-4">
            <a href="<?php echo BASE_URL; ?>/ofertas/" class="btn btn-primary view-all-btn">
                <i class="bi bi-list-ul me-2"></i>Ver todas las ofertas
            </a>
        </div>
        
        <?php if ($featuredOffers): ?>
            <div class="row">
                <?php 
                $delay = 1;
                // Array de gradientes modernos para las tarjetas
                $gradients = [
                    'linear-gradient(45deg, #4361ee, #3a0ca3)',
                    'linear-gradient(45deg, #7209b7, #560bad)',
                    'linear-gradient(45deg, #f72585, #b5179e)',
                    'linear-gradient(45deg, #4cc9f0, #4361ee)',
                    'linear-gradient(45deg, #6a00f4, #8900f2)',
                    'linear-gradient(45deg, #ff006e, #fb5607)'
                ];
                
                foreach ($featuredOffers as $offer): 
                    // Preparar la ruta del logo
                    $logoPath = !empty($offer['logo']) 
                        ? BASE_URL . '/assets/uploads/photos/' . htmlspecialchars($offer['logo']) 
                        : BASE_URL . '/assets/img/default-logo.png';
                    
                    // Seleccionar un gradiente aleatorio para el banner
                    $randomGradient = $gradients[array_rand($gradients)];
                ?>
                    <div class="col-lg-4 col-md-6 mb-4">
                        <div class="card offer-card h-100 fade-in-up" data-delay="<?php echo $delay * 150; ?>">
                            <div class="card-img-top" style="background: <?php echo $randomGradient; ?>"></div>
                            <div class="card-body position-relative pt-4">
                                <div class="d-flex justify-content-between">
                                    <div class="company-logo">
                                        <?php if (!empty($offer['logo']) && file_exists(__DIR__ . '/assets/uploads/photos/' . $offer['logo'])): ?>
                                            <img src="<?php echo $logoPath; ?>" 
                                                alt="Logo de <?php echo htmlspecialchars($offer['nombre_empresa']); ?>"
                                                onerror="this.src='<?php echo BASE_URL; ?>/assets/img/default-logo.png'">
                                        <?php else: ?>
                                            <div class="d-flex align-items-center justify-content-center h-100 bg-light">
                                                <i class="bi bi-building text-primary"></i>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <p class="text-muted small mb-0">
                                        <i class="bi bi-calendar-date me-1"></i>
                                        <?php 
                                            $date = new DateTime($offer['fecha_publicacion']);
                                            echo $date->format('d/m/Y');
                                        ?>
                                    </p>
                                </div>
                                
                                <h5 class="card-title mt-3 mb-2 fw-bold"><?php echo htmlspecialchars($offer['titulo']); ?></h5>
                                <h6 class="text-muted mb-3"><?php echo htmlspecialchars($offer['nombre_empresa']); ?></h6>
                                
                                <div class="mb-3">
                                    <?php if ($offer['ubicacion']): ?>
                                        <span class="offer-badge badge-location">
                                            <i class="bi bi-geo-alt me-1"></i><?php echo htmlspecialchars($offer['ubicacion']); ?>
                                        </span>
                                    <?php endif; ?>
                                    
                                    <?php if ($offer['tipo_contrato']): ?>
                                        <span class="offer-badge badge-contract">
                                            <i class="bi bi-file-earmark-text me-1"></i><?php echo htmlspecialchars($offer['tipo_contrato']); ?>
                                        </span>
                                    <?php endif; ?>
                                    
                                    <?php if ($offer['salario']): ?>
                                        <span class="offer-badge badge-salary">
                                            <i class="bi bi-cash-coin me-1"></i><?php echo htmlspecialchars($offer['salario']); ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                                
                                <p class="card-text mb-4">
                                    <?php 
                                    $description = $offer['descripcion'];
                                    echo strlen($description) > 100 ? substr(htmlspecialchars($description), 0, 100) . '...' : htmlspecialchars($description);
                                    ?>
                                </p>
                                
                                <a href="<?php echo BASE_URL; ?>/ofertas/ver.php?id=<?php echo $offer['id']; ?>" class="btn btn-primary btn-sm stretched-link mt-auto">
                                    Ver detalle <i class="bi bi-arrow-right ms-1"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                <?php 
                    $delay++;
                    endforeach; 
                ?>
            </div>
        <?php else: ?>
            <div class="alert alert-info">
                <i class="bi bi-info-circle me-2"></i> No hay ofertas disponibles en este momento.
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- How It Works Section -->
<section class="how-it-works">
    <div class="container">
        <h2 class="section-title text-center">¿Cómo Funciona?</h2>
        
        <div class="row mt-5">
            <div class="col-lg-4 mb-4">
                <div class="card step-card text-center p-4 h-100 fade-in-up" data-delay="150">
                    <div class="card-body">
                        <div class="step-icon">
                            <i class="bi bi-person-plus"></i>
                        </div>
                        <h3 class="step-title">Crea tu perfil</h3>
                        <p class="card-text mb-4">
                            Regístrate como candidato o empresa. Completa tu perfil con tu experiencia, habilidades o necesidades de contratación.
                        </p>
                        <a href="<?php echo BASE_URL; ?>/auth/register.php" class="btn btn-outline-primary step-btn">
                            Comenzar ahora <i class="bi bi-arrow-right ms-1"></i>
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4 mb-4">
                <div class="card step-card text-center p-4 h-100 fade-in-up" data-delay="300">
                    <div class="card-body">
                        <div class="step-icon">
                            <i class="bi bi-search"></i>
                        </div>
                        <h3 class="step-title">Encuentra oportunidades</h3>
                        <p class="card-text mb-4">
                            Explora ofertas de trabajo que coincidan con tu perfil profesional o encuentra candidatos ideales para tu empresa.
                        </p>
                        <a href="<?php echo BASE_URL; ?>/ofertas/" class="btn btn-outline-primary step-btn">
                            Explorar ofertas <i class="bi bi-arrow-right ms-1"></i>
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4 mb-4">
                <div class="card step-card text-center p-4 h-100 fade-in-up" data-delay="450">
                    <div class="card-body">
                        <div class="step-icon">
                            <i class="bi bi-check2-circle"></i>
                        </div>
                        <h3 class="step-title">Conecta y crece</h3>
                        <p class="card-text mb-4">
                            Postúlate a ofertas o contacta con candidatos interesantes. Inicia el proceso de selección y desarrolla tu carrera o equipo.
                        </p>
                        <a href="<?php echo BASE_URL; ?>/contacto/" class="btn btn-outline-primary step-btn">
                            Más información <i class="bi bi-arrow-right ms-1"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Testimonials Section -->
<section class="testimonials">
    <div class="container">
        <h2 class="section-title">Lo Que Dicen Nuestros Usuarios</h2>
        
        <div class="row">
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="testimonial-card fade-in-up" data-delay="150">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center mb-4">
                            <img src="<?php echo BASE_URL; ?>/assets/img/testimonial-1.jpg" alt="Foto de usuario" class="testimonial-avatar me-3">
                            <div>
                                <h5 class="mb-1">Laura Méndez</h5>
                                <p class="mb-1 text-muted">Desarrolladora Web</p>
                                <div class="star-rating">
                                    <i class="bi bi-star-fill"></i>
                                    <i class="bi bi-star-fill"></i>
                                    <i class="bi bi-star-fill"></i>
                                    <i class="bi bi-star-fill"></i>
                                    <i class="bi bi-star-fill"></i>
                                </div>
                            </div>
                        </div>
                        <div class="testimonial-quote">
                            Gracias a esta plataforma encontré mi trabajo soñado en menos de un mes. El proceso fue muy sencillo y la interfaz muy intuitiva.
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="testimonial-card fade-in-up" data-delay="300">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center mb-4">
                            <img src="<?php echo BASE_URL; ?>/assets/img/testimonial-2.jpg" alt="Foto de usuario" class="testimonial-avatar me-3">
                            <div>
                                <h5 class="mb-1">Carlos Jiménez</h5>
                                <p class="mb-1 text-muted">CEO, TechSolutions</p>
                                <div class="star-rating">
                                    <i class="bi bi-star-fill"></i>
                                    <i class="bi bi-star-fill"></i>
                                    <i class="bi bi-star-fill"></i>
                                    <i class="bi bi-star-fill"></i>
                                    <i class="bi bi-star-half"></i>
                                </div>
                            </div>
                        </div>
                        <div class="testimonial-quote">
                            Hemos conseguido ampliar nuestro equipo con profesionales de alto nivel. La calidad de los candidatos es excelente y el sistema de filtrado muy eficiente.
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="testimonial-card fade-in-up" data-delay="450">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center mb-4">
                            <img src="<?php echo BASE_URL; ?>/assets/img/testimonial-3.jpg" alt="Foto de usuario" class="testimonial-avatar me-3">
                            <div>
                                <h5 class="mb-1">Marta Gutiérrez</h5>
                                <p class="mb-1 text-muted">Diseñadora UX/UI</p>
                                <div class="star-rating">
                                    <i class="bi bi-star-fill"></i>
                                    <i class="bi bi-star-fill"></i>
                                    <i class="bi bi-star-fill"></i>
                                    <i class="bi bi-star-fill"></i>
                                    <i class="bi bi-star"></i>
                                </div>
                            </div>
                        </div>
                        <div class="testimonial-quote">
                            Lo que más me gusta es lo fácil que es mantener actualizado mi perfil y recibir notificaciones de ofertas que realmente se ajustan a lo que busco.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Call to Action Section -->
<section class="cta-section">
    <div class="container text-center position-relative">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <h2 class="cta-title fade-in-up" data-delay="150">¿Listo para avanzar en tu carrera?</h2>
                <p class="cta-subtitle fade-in-up" data-delay="300">
                    Únete a miles de profesionales y empresas que ya están aprovechando nuestra plataforma para crecer.
                </p>
                <div class="fade-in-up" data-delay="450">
                    <a href="<?php echo BASE_URL; ?>/auth/register.php" class="btn cta-btn">
                        <i class="bi bi-person-plus-fill me-2"></i>Crear mi cuenta ahora
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Footer actualizado con Bootstrap Icons en lugar de Font Awesome -->
<?php require_once __DIR__ . '/includes/footer.php'; ?>