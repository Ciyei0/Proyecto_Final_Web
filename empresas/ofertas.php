<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth_functions.php';

if (!isLoggedIn() || !isCompany()) {
    redirect(BASE_URL . '/auth/login.php');
}

$pageTitle = "Mis Ofertas de Empleo";
require_once __DIR__ . '/../includes/header.php';

$user = getCurrentUser();
$companyId = $user['id'];

// Obtener ofertas con conteo de aplicaciones
$stmt = $pdo->prepare("
    SELECT o.*, COUNT(a.id) as aplicaciones
    FROM ofertas_empleo o
    LEFT JOIN aplicaciones a ON o.id = a.oferta_id
    WHERE o.empresa_id = ?
    GROUP BY o.id
    ORDER BY o.fecha_publicacion DESC
");
$stmt->execute([$companyId]);
$offers = $stmt->fetchAll();
?>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5>Mis Ofertas de Empleo</h5>
        <a href="publicar.php" class="btn btn-primary btn-sm">Publicar Nueva Oferta</a>
    </div>
    <div class="card-body">
        <?php if ($offers): ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Título</th>
                            <th>Publicación</th>
                            <th>Cierre</th>
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
                                <td><?php echo $offer['fecha_cierre'] ? date('d/m/Y', strtotime($offer['fecha_cierre'])) : '--'; ?></td>
                                <td>
                                    <span class="badge <?php echo $offer['activa'] ? 'bg-success' : 'bg-secondary'; ?>">
                                        <?php echo $offer['activa'] ? 'Activa' : 'Inactiva'; ?>
                                    </span>
                                </td>
                                <td><?php echo $offer['aplicaciones']; ?></td>
                                <td>
                                    <div class="d-flex gap-2">
                                        <a href="postulantes.php?oferta_id=<?php echo $offer['id']; ?>" 
                                           class="btn btn-sm btn-outline-primary" title="Ver postulantes">
                                            <i class="fas fa-users"></i>
                                        </a>
                                        <a href="editar_oferta.php?id=<?php echo $offer['id']; ?>" 
                                           class="btn btn-sm btn-outline-secondary" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form method="post" action="eliminar_oferta.php" class="d-inline">
                                            <input type="hidden" name="id" value="<?php echo $offer['id']; ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-danger" 
                                                    title="Eliminar" onclick="return confirm('¿Eliminar esta oferta?')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
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

<?php require_once __DIR__ . '/../includes/footer.php'; ?>