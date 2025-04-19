<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth_functions.php';

if (!isLoggedIn() || !isCompany()) {
    redirect(BASE_URL . '/auth/login.php');
}

$pageTitle = "Panel de Empresa";
require_once __DIR__ . '/../includes/header.php';

$user = getCurrentUser();
$companyId = $user['id'];

// Obtener información de la empresa
$stmt = $pdo->prepare("SELECT * FROM empresas WHERE usuario_id = ?");
$stmt->execute([$companyId]);
$company = $stmt->fetch();

// Obtener estadísticas de la empresa
$stmt = $pdo->prepare("
    SELECT 
        COUNT(*) as total_ofertas,
        SUM(CASE WHEN activa = 1 THEN 1 ELSE 0 END) as ofertas_activas,
        SUM(CASE WHEN activa = 0 THEN 1 ELSE 0 END) as ofertas_inactivas
    FROM ofertas_empleo 
    WHERE empresa_id = ?
");
$stmt->execute([$companyId]);
$stats = $stmt->fetch();

// Obtener ofertas con conteo de aplicaciones
$stmt = $pdo->prepare("
    SELECT o.*, COUNT(a.id) as aplicaciones
    FROM ofertas_empleo o
    LEFT JOIN aplicaciones a ON o.id = a.oferta_id
    WHERE o.empresa_id = ?
    GROUP BY o.id
    ORDER BY o.fecha_publicacion DESC
    LIMIT 5
");
$stmt->execute([$companyId]);
$offers = $stmt->fetchAll();
?>

<div class="container py-4">
    <div class="row g-4">
        <!-- Sidebar con información de la empresa -->
        <div class="col-lg-4">
            <div class="card border shadow-sm rounded-4 h-100">
                <div class="card-body p-4">
                    <div class="text-center mb-4">
                        <?php if ($company && $company['logo']): ?>
                            <div class="avatar-container mb-3 mx-auto" style="width: 150px; height: 150px; overflow: hidden; border-radius: 50%; background-color: #1a73e8;">
                                <img src="<?php echo BASE_URL . '/assets/uploads/' . htmlspecialchars($company['logo']); ?>" 
                                     class="img-fluid" alt="Logo de la empresa" style="object-fit: cover; width: 100%; height: 100%;">
                            </div>
                        <?php else: ?>
                            <div class="avatar-placeholder mb-3 mx-auto d-flex align-items-center justify-content-center" 
                                 style="width: 150px; height: 150px; border-radius: 50%; background-color: #1a73e8;">
                            </div>
                        <?php endif; ?>
                        
                        <h3 class="fw-bold"><?php echo htmlspecialchars($company['nombre_empresa'] ?? 'empresa'); ?></h3>
                        <p class="text-muted"><?php echo htmlspecialchars($user['email']); ?></p>
                    </div>
                    
                    <hr class="my-4">
                    
                    <?php if ($company): ?>
                        <div class="company-info">
                            <div class="mb-3">
                                <small class="text-muted d-block">Teléfono</small>
                                <span><?php echo htmlspecialchars($company['telefono'] ?? 'No especificado'); ?></span>
                            </div>
                            
                            <div class="mb-3">
                                <small class="text-muted d-block">Dirección</small>
                                <span><?php echo htmlspecialchars($company['direccion'] ?? 'No especificada'); ?></span>
                            </div>
                            
                            <?php if (isset($company['sitio_web']) && $company['sitio_web']): ?>
                                <div class="mb-3">
                                    <small class="text-muted d-block">Sitio Web</small>
                                    <a href="<?php echo htmlspecialchars($company['sitio_web']); ?>" class="text-decoration-none" target="_blank">
                                        <?php echo htmlspecialchars(preg_replace('(^https?://)', '', $company['sitio_web'])); ?>
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                    
                    <div class="mt-4 d-grid">
                        <a href="publicar.php" class="btn btn-primary rounded-pill py-3" style="background-color: #1a73e8; border: none;">
                            Publicar Nueva Oferta
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Contenido principal -->
        <div class="col-lg-8">
            <!-- Tarjetas de estadísticas -->
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <div class="card border shadow-sm rounded-4 h-100">
                        <div class="card-body p-3">
                            <div class="d-flex align-items-center">
                                <div class="rounded-4 bg-primary bg-opacity-10 me-3" 
                                     style="width: 50px; height: 50px;">
                                </div>
                                <div>
                                    <h2 class="h2 fw-bold mb-0"><?php echo $stats['total_ofertas'] ?? 0; ?></h2>
                                    <p class="text-muted mb-0">Ofertas Totales</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="card border shadow-sm rounded-4 h-100">
                        <div class="card-body p-3">
                            <div class="d-flex align-items-center">
                                <div class="rounded-4 bg-success bg-opacity-10 me-3" 
                                     style="width: 50px; height: 50px;">
                                </div>
                                <div>
                                    <h2 class="h2 fw-bold mb-0"><?php echo $stats['ofertas_activas'] ?? 0; ?></h2>
                                    <p class="text-muted mb-0">Ofertas Activas</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="card border shadow-sm rounded-4 h-100">
                        <div class="card-body p-3">
                            <div class="d-flex align-items-center">
                                <div class="rounded-4 bg-secondary bg-opacity-10 me-3" 
                                     style="width: 50px; height: 50px;">
                                </div>
                                <div>
                                    <h2 class="h2 fw-bold mb-0"><?php echo $stats['ofertas_inactivas'] ?? 0; ?></h2>
                                    <p class="text-muted mb-0">Ofertas Inactivas</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Últimas ofertas -->
            <div class="card border shadow-sm rounded-4">
                <div class="card-header bg-white d-flex justify-content-between align-items-center py-3 px-4">
                    <h5 class="fw-bold mb-0">Últimas Ofertas</h5>
                    <a href="ofertas.php" class="btn btn-outline-primary rounded-pill btn-sm px-3" style="border-color: #1a73e8; color: #1a73e8;">
                        Ver Todas
                    </a>
                </div>
                <div class="card-body p-0">
                    <?php if ($offers): ?>
                        <div class="table-responsive">
                            <table class="table align-middle mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="px-4 py-3">Título</th>
                                        <th class="py-3">Publicación</th>
                                        <th class="py-3">Estado</th>
                                        <th class="py-3">Postulantes</th>
                                        <th class="py-3 text-end pe-4">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($offers as $offer): ?>
                                        <tr>
                                            <td class="px-4 py-3 fw-semibold"><?php echo htmlspecialchars($offer['titulo']); ?></td>
                                            <td class="py-3 text-muted"><?php echo date('d/m/Y', strtotime($offer['fecha_publicacion'])); ?></td>
                                            <td class="py-3">
                                                <?php if ($offer['activa']): ?>
                                                    <span class="badge rounded-pill bg-success py-2 px-3" style="background-color: #34A853 !important;">
                                                        Activa
                                                    </span>
                                                <?php else: ?>
                                                    <span class="badge rounded-pill bg-secondary py-2 px-3">
                                                        Inactiva
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="py-3">
                                                <div class="d-flex align-items-center">
                                                    <div class="rounded-pill bg-info bg-opacity-10 d-flex align-items-center justify-content-center me-2" 
                                                         style="width: 28px; height: 28px;">
                                                    </div>
                                                    <?php echo $offer['aplicaciones']; ?>
                                                </div>
                                            </td>
                                            <td class="py-3 text-end pe-4">
                                                <a href="postulantes.php?oferta_id=<?php echo $offer['id']; ?>" 
                                                   class="btn btn-primary rounded-pill px-3" style="background-color: #1a73e8; border: none;">
                                                    Ver Postulantes
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="p-4">
                            <div class="alert alert-info border-0 shadow-sm rounded-4 p-4">
                                <div class="d-flex">
                                    <div>
                                        <h5 class="fw-bold mb-1">Aún no has publicado ofertas</h5>
                                        <p class="mb-3">Comienza a recibir aplicaciones publicando tu primera oferta de empleo.</p>
                                        <a href="publicar.php" class="btn btn-primary rounded-pill px-4" style="background-color: #1a73e8; border: none;">
                                            Publicar oferta
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>