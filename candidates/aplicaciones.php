<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth_functions.php';

if (!isLoggedIn() || !isCandidate()) {
    redirect(BASE_URL . '/auth/login.php');
}

$pageTitle = "Mis Postulaciones";
require_once __DIR__ . '/../includes/header.php';

$user = getCurrentUser();
$candidateId = $user['id'];

// Obtener todas las aplicaciones del candidato con información de las ofertas
$stmt = $pdo->prepare("
    SELECT a.*, o.titulo, o.descripcion, o.ubicacion, o.tipo_contrato, o.salario,
           e.nombre_empresa, e.logo,
           CASE 
               WHEN o.fecha_cierre < CURDATE() THEN 'cerrada'
               WHEN o.activa = 0 THEN 'inactiva'
               ELSE 'activa'
           END as estado_oferta
    FROM aplicaciones a
    JOIN ofertas_empleo o ON a.oferta_id = o.id
    JOIN empresas e ON o.empresa_id = e.usuario_id
    WHERE a.candidato_id = ?
    ORDER BY a.fecha_aplicacion DESC
");
$stmt->execute([$candidateId]);
$applications = $stmt->fetchAll();
?>

<div class="row">
    <div class="col-md-12">
        <h2 class="mb-4">Mis Postulaciones</h2>
        
        <?php if (empty($applications)): ?>
            <div class="alert alert-info">
                No te has postulado a ninguna oferta aún. <a href="<?php echo BASE_URL; ?>/ofertas/">Explora las ofertas disponibles</a>.
            </div>
        <?php else: ?>
            <div class="accordion" id="applicationsAccordion">
                <?php foreach ($applications as $index => $app): ?>
                    <div class="accordion-item mb-3">
                        <h2 class="accordion-header" id="heading<?php echo $index; ?>">
                            <button class="accordion-button <?php echo $index !== 0 ? 'collapsed' : ''; ?>" 
                                    type="button" data-bs-toggle="collapse" 
                                    data-bs-target="#collapse<?php echo $index; ?>" 
                                    aria-expanded="<?php echo $index === 0 ? 'true' : 'false'; ?>" 
                                    aria-controls="collapse<?php echo $index; ?>">
                                <div class="d-flex w-100 align-items-center">
                                    <?php if ($app['logo']): ?>
                                        <img src="<?php echo BASE_URL . '/assets/uploads/' . htmlspecialchars($app['logo']); ?>" 
                                             class="rounded me-3" width="40" height="40" style="object-fit: cover;">
                                    <?php else: ?>
                                        <div class="rounded bg-light d-flex align-items-center justify-content-center me-3" 
                                             style="width: 40px; height: 40px;">
                                            <i class="fas fa-building text-secondary"></i>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <div>
                                        <h5 class="mb-0"><?php echo htmlspecialchars($app['titulo']); ?></h5>
                                        <small class="text-muted">
                                            <?php echo htmlspecialchars($app['nombre_empresa']); ?> - 
                                            Postulada el <?php echo date('d/m/Y', strtotime($app['fecha_aplicacion'])); ?>
                                        </small>
                                    </div>
                                    
                                    <div class="ms-auto">
                                        <span class="badge 
                                            <?php echo $app['estado'] == 'pendiente' ? 'bg-warning' : 
                                                  ($app['estado'] == 'revisado' ? 'bg-info' : 
                                                  ($app['estado'] == 'seleccionado' ? 'bg-success' : 'bg-danger')); ?>">
                                            <?php echo ucfirst($app['estado']); ?>
                                        </span>
                                        
                                        <span class="badge ms-1 
                                            <?php echo $app['estado_oferta'] == 'activa' ? 'bg-success' : 
                                                  ($app['estado_oferta'] == 'cerrada' ? 'bg-secondary' : 'bg-warning'); ?>">
                                            <?php echo ucfirst($app['estado_oferta']); ?>
                                        </span>
                                    </div>
                                </div>
                            </button>
                        </h2>
                        
                        <div id="collapse<?php echo $index; ?>" 
                             class="accordion-collapse collapse <?php echo $index === 0 ? 'show' : ''; ?>" 
                             aria-labelledby="heading<?php echo $index; ?>" 
                             data-bs-parent="#applicationsAccordion">
                            <div class="accordion-body">
                                <div class="row">
                                    <div class="col-md-8">
                                        <h5>Detalles de la Oferta</h5>
                                        <p><strong>Empresa:</strong> <?php echo htmlspecialchars($app['nombre_empresa']); ?></p>
                                        <p><strong>Ubicación:</strong> <?php echo htmlspecialchars($app['ubicacion'] ?? 'No especificada'); ?></p>
                                        <p><strong>Tipo de contrato:</strong> <?php echo htmlspecialchars($app['tipo_contrato'] ?? 'No especificado'); ?></p>
                                        <?php if ($app['salario']): ?>
                                            <p><strong>Salario:</strong> <?php echo htmlspecialchars($app['salario']); ?></p>
                                        <?php endif; ?>
                                        
                                        <h5 class="mt-4">Descripción del Puesto</h5>
                                        <p><?php echo nl2br(htmlspecialchars($app['descripcion'])); ?></p>
                                        
                                        <?php if ($app['mensaje']): ?>
                                            <h5 class="mt-4">Tu Mensaje</h5>
                                            <div class="alert alert-light">
                                                <?php echo nl2br(htmlspecialchars($app['mensaje'])); ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="col-md-4">
                                        <div class="card">
                                            <div class="card-header">
                                                <h5>Estado de tu Postulación</h5>
                                            </div>
                                            <div class="card-body">
                                                <div class="mb-3">
                                                    <p><strong>Fecha de postulación:</strong><br>
                                                    <?php echo date('d/m/Y H:i', strtotime($app['fecha_aplicacion'])); ?></p>
                                                    
                                                    <p><strong>Estado actual:</strong><br>
                                                    <span class="badge 
                                                        <?php echo $app['estado'] == 'pendiente' ? 'bg-warning' : 
                                                              ($app['estado'] == 'revisado' ? 'bg-info' : 
                                                              ($app['estado'] == 'seleccionado' ? 'bg-success' : 'bg-danger')); ?>">
                                                        <?php echo ucfirst($app['estado']); ?>
                                                    </span></p>
                                                    
                                                    <p><strong>Estado de la oferta:</strong><br>
                                                    <span class="badge 
                                                        <?php echo $app['estado_oferta'] == 'activa' ? 'bg-success' : 
                                                              ($app['estado_oferta'] == 'cerrada' ? 'bg-secondary' : 'bg-warning'); ?>">
                                                        <?php echo ucfirst($app['estado_oferta']); ?>
                                                    </span></p>
                                                </div>
                                                
                                                <div class="d-grid gap-2">
                                                    <a href="<?php echo BASE_URL; ?>/ofertas/ver.php?id=<?php echo $app['oferta_id']; ?>" 
                                                       class="btn btn-outline-primary">
                                                        Ver oferta completa
                                                    </a>
                                                    
                                                    <?php if ($app['estado_oferta'] === 'activa'): ?>
                                                        <a href="<?php echo BASE_URL; ?>/candidates/perfil.php" 
                                                           class="btn btn-outline-secondary">
                                                            Actualizar mi CV
                                                        </a>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>