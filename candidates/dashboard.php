<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth_functions.php';

if (!isLoggedIn() || !isCandidate()) {
    redirect(BASE_URL . '/auth/login.php');
}

$pageTitle = "Panel del Candidato";
require_once __DIR__ . '/../includes/header.php';

$user = getCurrentUser();
$candidateId = $user['id'];

// Obtener información del candidato
$stmt = $pdo->prepare("SELECT * FROM candidatos WHERE usuario_id = ?");
$stmt->execute([$candidateId]);
$candidate = $stmt->fetch();

// Obtener CV del candidato
$stmt = $pdo->prepare("SELECT * FROM cvs WHERE candidato_id = ?");
$stmt->execute([$candidateId]);
$cv = $stmt->fetch();

// Obtener aplicaciones del candidato
$stmt = $pdo->prepare("
    SELECT a.*, o.titulo, o.empresa_id, e.nombre_empresa 
    FROM aplicaciones a
    JOIN ofertas_empleo o ON a.oferta_id = o.id
    JOIN empresas e ON o.empresa_id = e.usuario_id
    WHERE a.candidato_id = ?
    ORDER BY a.fecha_aplicacion DESC
");
$stmt->execute([$candidateId]);
$applications = $stmt->fetchAll();
?>

<div class="container mt-5">
    <div class="row">
        <!-- Perfil del candidato -->
        <div class="col-lg-4">
            <div class="card shadow-lg border-0 mb-4 transition-all card-hover">
                <div class="text-white text-center py-4" style="background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 50%, #a855f7 100%);">
                    <h5 class="mb-0">Mi Perfil</h5>
                </div>
                <div class="card-body text-center">
                    <div class="mb-4">
                        <?php if ($candidate && $candidate['foto_perfil']): ?>
                            <div class="rounded-circle overflow-hidden border border-4 border-white shadow-lg mx-auto" style="width: 150px; height: 150px;">
                                <img src="<?php echo BASE_URL . '/assets/photos/' . htmlspecialchars($candidate['foto_perfil']); ?>" 
                                     class="img-fluid w-100 h-100" style="object-fit: cover;">
                            </div>
                        <?php else: ?>
                            <div class="avatar-placeholder rounded-circle d-flex align-items-center justify-content-center mx-auto shadow-lg" 
                                 style="width: 150px; height: 150px; background: linear-gradient(135deg, #e0e7ff 0%, #c7d2fe 100%);">
                                <i class="fas fa-user fa-3x text-secondary"></i>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <h4 class="fw-bold text-gray-800"><?php echo htmlspecialchars($user['nombre']); ?></h4>
                    <p class="text-muted"><?php echo htmlspecialchars($user['email']); ?></p>
                    
                    <?php if ($candidate): ?>
                        <div class="d-flex align-items-center justify-content-center mb-2">
                            <i class="fas fa-phone text-primary me-2"></i>
                            <span><?php echo htmlspecialchars($candidate['telefono'] ?? 'No especificado'); ?></span>
                        </div>
                        <div class="d-flex align-items-center justify-content-center">
                            <i class="fas fa-map-marker-alt text-primary me-2"></i>
                            <span>
                                <?php 
                                $location = [];
                                if ($candidate['ciudad']) $location[] = $candidate['ciudad'];
                                if ($candidate['provincia']) $location[] = $candidate['provincia'];
                                echo $location ? htmlspecialchars(implode(', ', $location)) : 'Ubicación no especificada';
                                ?>
                            </span>
                        </div>
                    <?php endif; ?>
                    
                    <a href="perfil.php" class="btn btn-primary btn-sm mt-4 w-100">
                        <i class="fas fa-user-edit me-2"></i> Editar Perfil
                    </a>
                </div>
            </div>
        </div>
        
        <!-- CV y postulaciones -->
        <div class="col-lg-8">
            <!-- CV -->
            <div class="card shadow-lg border-0 mb-4 transition-all card-hover">
                <div class="gradient-bg text-white d-flex justify-content-between align-items-center px-4 py-3" style="background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 50%, #a855f7 100%);">
                    <h5 class="mb-0">Mi CV</h5>
                    <a href="perfil.php" class="btn btn-sm btn-light">
                        <i class="fas fa-sync-alt me-1"></i> <?php echo $cv ? 'Actualizar CV' : 'Crear CV'; ?>
                    </a>
                </div>
                <div class="card-body">
                    <?php if ($cv): ?>
                        <h6 class="fw-bold text-gray-800 mb-2">
                            <i class="fas fa-bullseye text-primary me-2"></i> Objetivo Profesional
                        </h6>
                        <p class="text-gray-600"><?php echo $cv['objetivo_profesional'] ? nl2br(htmlspecialchars($cv['objetivo_profesional'])) : 'No especificado'; ?></p>
                        
                        <h6 class="fw-bold text-gray-800 mt-4 mb-2">
                            <i class="fas fa-tools text-primary me-2"></i> Habilidades Clave
                        </h6>
                        <div class="d-flex flex-wrap gap-2">
                            <?php 
                            $skills = explode(',', $cv['habilidades_clave'] ?? '');
                            foreach ($skills as $skill): ?>
                                <span class="badge bg-primary text-white px-3 py-1"><?php echo htmlspecialchars(trim($skill)); ?></span>
                            <?php endforeach; ?>
                        </div>
                        
                        <?php if ($cv['cv_pdf']): ?>
                            <a href="<?php echo BASE_URL . '/assets/cv/' . htmlspecialchars($cv['cv_pdf']); ?>" 
                               target="_blank" class="btn btn-outline-primary mt-4">
                                <i class="fas fa-file-pdf me-2"></i> Ver CV en PDF
                            </a>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="alert alert-warning">
                            Aún no has creado tu CV digital. Completa tu perfil para aumentar tus oportunidades de empleo.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Postulaciones -->
            <div class="card shadow-lg border-0 transition-all card-hover">
                <div class="gradient-bg text-white px-4 py-3" style="background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 50%, #a855f7 100%);">
                    <h5 class="mb-0" >Mis Postulaciones</h5>
                </div>
                <div class="card-body">
                    <?php if ($applications): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Oferta</th>
                                        <th>Empresa</th>
                                        <th>Fecha</th>
                                        <th>Estado</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($applications as $app): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($app['titulo']); ?></td>
                                            <td><?php echo htmlspecialchars($app['nombre_empresa']); ?></td>
                                            <td><?php echo date('d/m/Y', strtotime($app['fecha_aplicacion'])); ?></td>
                                            <td>
                                                <span class="badge 
                                                    <?php echo $app['estado'] == 'pendiente' ? 'bg-warning' : 
                                                          ($app['estado'] == 'revisado' ? 'bg-info' : 
                                                          ($app['estado'] == 'seleccionado' ? 'bg-success' : 'bg-danger')); ?>">
                                                    <?php echo ucfirst($app['estado']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <a href="<?php echo BASE_URL; ?>/ofertas/ver.php?id=<?php echo $app['oferta_id']; ?>" 
                                                   class="btn btn-sm btn-outline-primary">
                                                    Ver Oferta
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info">
                            Aún no te has postulado a ninguna oferta. <a href="<?php echo BASE_URL; ?>/ofertas/">Explora las ofertas disponibles</a>.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>