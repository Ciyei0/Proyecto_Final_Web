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

<div class="row">
    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-header">
                <h5>Información de la Empresa</h5>
            </div>
            <div class="card-body">
                <?php if ($company && $company['logo']): ?>
                    <img src="<?php echo BASE_URL . '/assets/uploads/' . htmlspecialchars($company['logo']); ?>" 
                         class="img-fluid rounded mb-3" style="max-height: 150px;">
                <?php endif; ?>
                
                <h4><?php echo htmlspecialchars($company['nombre_empresa'] ?? 'Nombre no definido'); ?></h4>
                <p class="text-muted"><?php echo htmlspecialchars($user['email']); ?></p>
                
                <?php if ($company): ?>
                    <p><i class="fas fa-phone"></i> <?php echo htmlspecialchars($company['telefono'] ?? 'No especificado'); ?></p>
                    <p><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($company['direccion'] ?? 'Dirección no especificada'); ?></p>
                    <?php if ($company['sitio_web']): ?>
                        <p><i class="fas fa-globe"></i> <a href="<?php echo htmlspecialchars($company['sitio_web']); ?>" target="_blank">Sitio web</a></p>
                    <?php endif; ?>
                <?php endif; ?>
                
                <a href="publicar.php" class="btn btn-primary btn-sm mt-2">Publicar Nueva Oferta</a>
            </div>
        </div>
    </div>
    
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header">
                <h5>Estadísticas</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4 text-center">
                        <div class="p-3 bg-light rounded">
                            <h3><?php echo $stats['total_ofertas'] ?? 0; ?></h3>
                            <p class="mb-0">Ofertas Totales</p>
                        </div>
                    </div>
                    <div class="col-md-4 text-center">
                        <div class="p-3 bg-light rounded">
                            <h3><?php echo $stats['ofertas_activas'] ?? 0; ?></h3>
                            <p class="mb-0">Ofertas Activas</p>
                        </div>
                    </div>
                    <div class="col-md-4 text-center">
                        <div class="p-3 bg-light rounded">
                            <h3><?php echo $stats['ofertas_inactivas'] ?? 0; ?></h3>
                            <p class="mb-0">Ofertas Inactivas</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5>Últimas Ofertas</h5>
                <a href="ofertas.php" class="btn btn-sm btn-primary">Ver Todas</a>
            </div>
            <div class="card-body">
                <?php if ($offers): ?>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Título</th>
                                    <th>Publicación</th>
                                    <th>Estado</th>
                                    <th>Postulantes</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($offers as $offer): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($offer['titulo']); ?></td>
                                        <td><?php echo date('d/m/Y', strtotime($offer['fecha_publicacion'])); ?></td>
                                        <td>
                                            <span class="badge <?php echo $offer['activa'] ? 'bg-success' : 'bg-secondary'; ?>">
                                                <?php echo $offer['activa'] ? 'Activa' : 'Inactiva'; ?>
                                            </span>
                                        </td>
                                        <td><?php echo $offer['aplicaciones']; ?></td>
                                        <td>
                                            <a href="postulantes.php?oferta_id=<?php echo $offer['id']; ?>" 
                                               class="btn btn-sm btn-outline-primary">
                                                Ver Postulantes
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info">
                        Aún no has publicado ninguna oferta. <a href="publicar.php">Publica tu primera oferta</a>.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>