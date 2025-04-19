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

<!-- Hero Section -->
<section class="hero-section bg-primary text-white py-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <h1 class="display-4 fw-bold mb-4">Encuentra el trabajo de tus sueños</h1>
                <p class="lead mb-4">Conectamos a los mejores talentos con las empresas más innovadoras. Regístrate y descubre oportunidades que se ajusten a tu perfil.</p>
                <div class="d-flex gap-3">
                    <a href="<?php echo BASE_URL; ?>/auth/register.php?tipo=candidato" class="btn btn-light btn-lg px-4">
                        Soy Candidato
                    </a>
                    <a href="<?php echo BASE_URL; ?>/auth/register.php?tipo=empresa" class="btn btn-outline-light btn-lg px-4">
                        Soy Empresa
                    </a>
                </div>
            </div>
            <div class="col-lg-6 d-none d-lg-block">
                <img src="<?php echo BASE_URL; ?>/assets/images/hero-image.svg" alt="Personas trabajando" class="img-fluid">
            </div>
        </div>
    </div>
</section>

<!-- Stats Section -->
<section class="stats-section py-4 bg-light">
    <div class="container">
        <div class="row text-center">
            <div class="col-md-4">
                <div class="p-3">
                    <h2 class="fw-bold text-primary"><?php echo number_format($stats['ofertas']); ?></h2>
                    <p class="mb-0">Ofertas Activas</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="p-3">
                    <h2 class="fw-bold text-primary"><?php echo number_format($stats['empresas']); ?></h2>
                    <p class="mb-0">Empresas Registradas</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="p-3">
                    <h2 class="fw-bold text-primary"><?php echo number_format($stats['candidatos']); ?></h2>
                    <p class="mb-0">Candidatos Activos</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Featured Offers -->
<section class="featured-offers py-5">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold">Ofertas Destacadas</h2>
            <a href="<?php echo BASE_URL; ?>/ofertas/" class="btn btn-outline-primary">Ver todas las ofertas</a>
        </div>
        
        <?php if ($featuredOffers): ?>
            <div class="row">
                <?php foreach ($featuredOffers as $offer): ?>
                    <div class="col-lg-4 col-md-6 mb-4">
                        <div class="card h-100">
                            <div class="card-body">
                                <div class="d-flex align-items-center mb-3">
                                    <?php if ($offer['logo']): ?>
                                        <img src="<?php echo BASE_URL . '/assets/uploads/' . htmlspecialchars($offer['logo']); ?>" 
                                             class="rounded me-3" width="50" height="50" style="object-fit: cover;">
                                    <?php else: ?>
                                        <div class="rounded bg-light d-flex align-items-center justify-content-center me-3" 
                                             style="width: 50px; height: 50px;">
                                            <i class="fas fa-building text-secondary"></i>
                                        </div>
                                    <?php endif; ?>
                                    <h5 class="mb-0"><?php echo htmlspecialchars($offer['nombre_empresa']); ?></h5>
                                </div>
                                
                                <h4 class="card-title"><?php echo htmlspecialchars($offer['titulo']); ?></h4>
                                
                                <div class="d-flex gap-2 mb-3">
                                    <?php if ($offer['ubicacion']): ?>
                                        <span class="text-muted"><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($offer['ubicacion']); ?></span>
                                    <?php endif; ?>
                                    
                                    <?php if ($offer['tipo_contrato']): ?>
                                        <span class="text-muted"><i class="fas fa-file-contract"></i> <?php echo htmlspecialchars($offer['tipo_contrato']); ?></span>
                                    <?php endif; ?>
                                </div>
                                
                                <p class="card-text"><?php echo nl2br(htmlspecialchars(substr($offer['descripcion'], 0, 120)) . '...'); ?></p>
                                
                                <?php if ($offer['salario']): ?>
                                    <p class="fw-bold text-success mb-3"><?php echo htmlspecialchars($offer['salario']); ?></p>
                                <?php endif; ?>
                            </div>
                            <div class="card-footer bg-transparent">
                                <a href="<?php echo BASE_URL; ?>/ofertas/ver.php?id=<?php echo $offer['id']; ?>" class="btn btn-primary w-100">
                                    Ver Detalles
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="alert alert-info">
                Actualmente no hay ofertas destacadas disponibles.
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- How It Works -->
<section class="how-it-works py-5 bg-light">
    <div class="container">
        <h2 class="text-center fw-bold mb-5">¿Cómo funciona?</h2>
        
        <div class="row text-center">
            <div class="col-md-4 mb-4">
                <div class="p-4 bg-white rounded shadow-sm h-100">
                    <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3" 
                         style="width: 80px; height: 80px;">
                        <i class="fas fa-user-plus fa-2x"></i>
                    </div>
                    <h4 class="fw-bold">1. Regístrate</h4>
                    <p>Crea tu perfil como candidato o empresa en minutos.</p>
                </div>
            </div>
            
            <div class="col-md-4 mb-4">
                <div class="p-4 bg-white rounded shadow-sm h-100">
                    <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3" 
                         style="width: 80px; height: 80px;">
                        <i class="fas fa-file-alt fa-2x"></i>
                    </div>
                    <h4 class="fw-bold">2. Completa tu perfil</h4>
                    <p>Candidatos: Crea tu CV digital. Empresas: Publica tus ofertas.</p>
                </div>
            </div>
            
            <div class="col-md-4 mb-4">
                <div class="p-4 bg-white rounded shadow-sm h-100">
                    <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3" 
                         style="width: 80px; height: 80px;">
                        <i class="fas fa-handshake fa-2x"></i>
                    </div>
                    <h4 class="fw-bold">3. Conecta</h4>
                    <p>Candidatos: Postula a ofertas. Empresas: Encuentra talento.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Testimonials -->
<section class="testimonials py-5">
    <div class="container">
        <h2 class="text-center fw-bold mb-5">Lo que dicen nuestros usuarios</h2>
        
        <div class="row">
            <div class="col-lg-6 mb-4">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <img src="<?php echo BASE_URL; ?>/assets/images/testimonial1.jpg" 
                                 class="rounded-circle me-3" width="60" height="60" style="object-fit: cover;">
                            <div>
                                <h5 class="mb-0">María González</h5>
                                <small class="text-muted">Desarrolladora Frontend</small>
                            </div>
                        </div>
                        <p class="card-text">"Encontré mi trabajo actual en esta plataforma. El proceso fue sencillo y en menos de dos semanas ya estaba contratada."</p>
                        <div class="text-warning">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-6 mb-4">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <img src="<?php echo BASE_URL; ?>/assets/images/testimonial2.jpg" 
                                 class="rounded-circle me-3" width="60" height="60" style="object-fit: cover;">
                            <div>
                                <h5 class="mb-0">Tech Solutions Inc.</h5>
                                <small class="text-muted">Empresa de tecnología</small>
                            </div>
                        </div>
                        <p class="card-text">"Hemos contratado a 3 desarrolladores talentosos a través de esta plataforma. La calidad de los candidatos es excelente."</p>
                        <div class="text-warning">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star-half-alt"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Call to Action -->
<section class="cta-section bg-primary text-white py-5">
    <div class="container text-center">
        <h2 class="fw-bold mb-4">¿Listo para empezar?</h2>
        <p class="lead mb-4">Regístrate ahora y descubre cómo podemos ayudarte a alcanzar tus metas profesionales.</p>
        <a href="<?php echo BASE_URL; ?>/auth/register.php" class="btn btn-light btn-lg px-5">Regístrate Gratis</a>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>