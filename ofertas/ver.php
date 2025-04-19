<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth_functions.php';

$offerId = $_GET['id'] ?? 0;

// Obtener información de la oferta
$stmt = $pdo->prepare("
    SELECT o.*, e.nombre_empresa, e.descripcion as descripcion_empresa, e.sitio_web
    FROM ofertas_empleo o
    JOIN empresas e ON o.empresa_id = e.usuario_id
    WHERE o.id = ?
");
$stmt->execute([$offerId]);
$offer = $stmt->fetch();

if (!$offer) {
    header("HTTP/1.0 404 Not Found");
    echo "Oferta no encontrada";
    exit;
}

$pageTitle = $offer['titulo'];
require_once __DIR__ . '/../includes/header.php';

// Verificar si el usuario actual ya aplicó a esta oferta
$hasApplied = false;
if (isLoggedIn() && isCandidate()) {
    $userId = $_SESSION['usuario_id'];
    $stmt = $pdo->prepare("SELECT id FROM aplicaciones WHERE oferta_id = ? AND candidato_id = ?");
    $stmt->execute([$offerId, $userId]);
    $hasApplied = (bool)$stmt->fetch();
}
?>

<div class="row">
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-body">
                <h2><?php echo htmlspecialchars($offer['titulo']); ?></h2>
                <h4 class="text-muted"><?php echo htmlspecialchars($offer['nombre_empresa']); ?></h4>
                
                <div class="d-flex gap-3 my-3">
                    <?php if ($offer['ubicacion']): ?>
                        <span><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($offer['ubicacion']); ?></span>
                    <?php endif; ?>
                    
                    <?php if ($offer['tipo_contrato']): ?>
                        <span><i class="fas fa-file-contract"></i> <?php echo htmlspecialchars($offer['tipo_contrato']); ?></span>
                    <?php endif; ?>
                    
                    <?php if ($offer['salario']): ?>
                        <span><i class="fas fa-money-bill-wave"></i> <?php echo htmlspecialchars($offer['salario']); ?></span>
                    <?php endif; ?>
                </div>
                
                <hr>
                
                <h4>Descripción del Puesto</h4>
                <p><?php echo nl2br(htmlspecialchars($offer['descripcion'])); ?></p>
                
                <h4 class="mt-4">Requisitos</h4>
                <p><?php echo nl2br(htmlspecialchars($offer['requisitos'])); ?></p>
                
                <?php if ($offer['fecha_cierre']): ?>
                    <div class="alert alert-warning mt-4">
                        <i class="fas fa-clock"></i> Esta oferta cierra el <?php echo date('d/m/Y', strtotime($offer['fecha_cierre'])); ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h5>Sobre la Empresa</h5>
            </div>
            <div class="card-body">
                <p><?php echo nl2br(htmlspecialchars($offer['descripcion_empresa'])); ?></p>
                
                <?php if ($offer['sitio_web']): ?>
                    <a href="<?php echo htmlspecialchars($offer['sitio_web']); ?>" target="_blank" class="btn btn-outline-primary">
                        <i class="fas fa-globe"></i> Sitio web
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card sticky-top" style="top: 20px;">
            <div class="card-body">
                <h5 class="card-title">¿Interesado en esta oferta?</h5>
                
                <?php if (isLoggedIn()): ?>
                    <?php if (isCandidate()): ?>
                        <?php if ($hasApplied): ?>
                            <div class="alert alert-success">
                                Ya has aplicado a esta oferta. Revisa tu <a href="<?php echo BASE_URL; ?>/candidates/aplicaciones.php">panel de candidato</a> para ver el estado.
                            </div>
                        <?php else: ?>
                            <form method="post" action="../candidates/aplicar.php">
                                <input type="hidden" name="oferta_id" value="<?php echo $offerId; ?>">
                                
                                <div class="mb-3">
                                    <label for="mensaje" class="form-label">Mensaje (opcional)</label>
                                    <textarea class="form-control" id="mensaje" name="mensaje" rows="3" 
                                              placeholder="¿Por qué eres el candidato ideal para este puesto?"></textarea>
                                </div>
                                
                                <button type="submit" class="btn btn-primary w-100">Aplicar a esta oferta</button>
                            </form>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="alert alert-info">
                            Esta función está disponible solo para candidatos. <a href="<?php echo BASE_URL; ?>/auth/login.php">Inicia sesión</a> como candidato para aplicar.
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="alert alert-info">
                        <p>Para aplicar a esta oferta, necesitas:</p>
                        <a href="<?php echo BASE_URL; ?>/auth/register.php?tipo=candidato" class="btn btn-primary mb-2 w-100">Registrarte como candidato</a>
                        <p class="text-center mb-0">o</p>
                        <a href="<?php echo BASE_URL; ?>/auth/login.php" class="btn btn-outline-primary mt-2 w-100">Iniciar sesión</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php';