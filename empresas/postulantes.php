<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth_functions.php';

if (!isLoggedIn() || !isCompany()) {
    redirect(BASE_URL . '/auth/login.php');
}

$pageTitle = "Postulantes a Oferta";
require_once __DIR__ . '/../includes/header.php';

$user = getCurrentUser();
$companyId = $user['id'];
$offerId = $_GET['oferta_id'] ?? 0;

// Verificar que la oferta pertenezca a la empresa
$stmt = $pdo->prepare("SELECT id, titulo FROM ofertas_empleo WHERE id = ? AND empresa_id = ?");
$stmt->execute([$offerId, $companyId]);
$offer = $stmt->fetch();

if (!$offer) {
    echo "<div class='alert alert-danger'>Oferta no encontrada o no tienes permisos para verla</div>";
    require_once __DIR__ . '/../../includes/footer.php';
    exit;
}

// Obtener aplicaciones para esta oferta
$stmt = $pdo->prepare("
    SELECT a.*, c.usuario_id, u.nombre, u.email, cand.foto_perfil, cv.objetivo_profesional
    FROM aplicaciones a
    JOIN candidatos c ON a.candidato_id = c.usuario_id
    JOIN usuarios u ON c.usuario_id = u.id
    LEFT JOIN cvs cv ON a.cv_id = cv.id
    LEFT JOIN candidatos cand ON c.usuario_id = cand.usuario_id
    WHERE a.oferta_id = ?
    ORDER BY a.fecha_aplicacion DESC
");
$stmt->execute([$offerId]);
$applications = $stmt->fetchAll();
?>

<div class="card">
    <div class="card-header">
        <h5>Postulantes para: <?php echo htmlspecialchars($offer['titulo']); ?></h5>
    </div>
    <div class="card-body">
        <?php if ($applications): ?>
            <div class="list-group">
                <?php foreach ($applications as $app): ?>
                    <div class="list-group-item">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="d-flex gap-3">
                                <?php if ($app['foto_perfil']): ?>
                                    <img src="<?php echo BASE_URL . '/assets/photos/' . htmlspecialchars($app['foto_perfil']); ?>" 
                                         class="rounded-circle" width="60" height="60" style="object-fit: cover;">
                                <?php else: ?>
                                    <div class="rounded-circle bg-light d-flex align-items-center justify-content-center" 
                                         style="width: 60px; height: 60px;">
                                        <i class="fas fa-user fa-2x text-secondary"></i>
                                    </div>
                                <?php endif; ?>
                                
                                <div>
                                    <h6><?php echo htmlspecialchars($app['nombre']); ?></h6>
                                    <p class="mb-1 text-muted"><?php echo htmlspecialchars($app['email']); ?></p>
                                    <?php if ($app['objetivo_profesional']): ?>
                                        <p class="mb-1"><?php echo nl2br(htmlspecialchars(substr($app['objetivo_profesional'], 0, 150) . '...')); ?></p>
                                    <?php endif; ?>
                                    <small class="text-muted">Postuló el <?php echo date('d/m/Y H:i', strtotime($app['fecha_aplicacion'])); ?></small>
                                </div>
                            </div>
                            
                            <div class="dropdown">
                                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" 
                                        data-bs-toggle="dropdown">
                                    Acciones
                                </button>
                                <ul class="dropdown-menu">
                                    <li>
                                        <a class="dropdown-item" href="ver_candidato.php?candidato_id=<?php echo $app['usuario_id']; ?>&oferta_id=<?php echo $offerId; ?>">
                                            Ver perfil completo
                                        </a>
                                    </li>

                                    
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <form method="post" action="cambiar_estado.php" class="d-inline">
                                            <input type="hidden" name="aplicacion_id" value="<?php echo $app['id']; ?>">
                                            <input type="hidden" name="oferta_id" value="<?php echo $offerId; ?>">
                                            <button type="submit" name="estado" value="seleccionado" 
                                                    class="dropdown-item text-success">
                                                Marcar como seleccionado
                                            </button>
                                            <button type="submit" name="estado" value="rechazado" 
                                                    class="dropdown-item text-danger">
                                                Rechazar candidato
                                            </button>
                                        </form>
                                    </li>
                                </ul>
                            </div>


                            
                        </div>
                        <?php if ($app['estado'] != 'pendiente'): ?>
                            <div class="mt-2">
                                <span class="badge <?php 
                                    echo $app['estado'] == 'seleccionado' ? 'bg-success' : 
                                         ($app['estado'] == 'rechazado' ? 'bg-danger' : 'bg-info'); 
                                ?>">
                                    <?php echo ucfirst($app['estado']); ?>
                                </span>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="alert alert-info">
                Aún no hay postulantes para esta oferta.
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>