<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth_functions.php';

$pageTitle = "Ofertas de Empleo";
require_once __DIR__ . '/../includes/header.php';

// Parámetros de búsqueda
$search = $_GET['search'] ?? '';
$location = $_GET['location'] ?? '';
$contractType = $_GET['contract_type'] ?? '';

// Construir consulta con filtros
$query = "SELECT o.*, e.nombre_empresa 
          FROM ofertas_empleo o
          JOIN empresas e ON o.empresa_id = e.usuario_id
          WHERE o.activa = 1";

$params = [];

if (!empty($search)) {
    $query .= " AND (o.titulo LIKE ? OR o.descripcion LIKE ? OR e.nombre_empresa LIKE ?)";
    $searchTerm = "%$search%";
    $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm]);
}

if (!empty($location)) {
    $query .= " AND o.ubicacion LIKE ?";
    $params[] = "%$location%";
}

if (!empty($contractType)) {
    $query .= " AND o.tipo_contrato = ?";
    $params[] = $contractType;
}

$query .= " ORDER BY o.fecha_publicacion DESC";

// Obtener ofertas
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$offers = $stmt->fetchAll();

// Obtener tipos de contrato únicos para el filtro
$stmt = $pdo->query("SELECT DISTINCT tipo_contrato FROM ofertas_empleo WHERE tipo_contrato IS NOT NULL");
$contractTypes = $stmt->fetchAll(PDO::FETCH_COLUMN);
?>

<div class="row mb-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
                <form method="get" class="row g-3">
                    <div class="col-md-4">
                        <input type="text" name="search" class="form-control" placeholder="Puesto, empresa o palabras clave" 
                               value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                    <div class="col-md-3">
                        <input type="text" name="location" class="form-control" placeholder="Ubicación" 
                               value="<?php echo htmlspecialchars($location); ?>">
                    </div>
                    <div class="col-md-3">
                        <select name="contract_type" class="form-select">
                            <option value="">Todos los tipos</option>
                            <?php foreach ($contractTypes as $type): ?>
                                <option value="<?php echo htmlspecialchars($type); ?>" <?php echo $contractType == $type ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($type); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">Buscar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <h2 class="mb-4">Ofertas de Empleo Disponibles</h2>
        
        <?php if ($offers): ?>
            <div class="list-group">
                <?php foreach ($offers as $offer): ?>
                    <a href="ver.php?id=<?php echo $offer['id']; ?>" class="list-group-item list-group-item-action">
                        <div class="d-flex w-100 justify-content-between">
                            <h5 class="mb-1"><?php echo htmlspecialchars($offer['titulo']); ?></h5>
                            <small><?php echo date('d/m/Y', strtotime($offer['fecha_publicacion'])); ?></small>
                        </div>
                        <p class="mb-1"><strong><?php echo htmlspecialchars($offer['nombre_empresa']); ?></strong> - 
                            <?php echo htmlspecialchars($offer['ubicacion'] ?? 'Ubicación no especificada'); ?></p>
                        <small class="text-muted"><?php echo htmlspecialchars($offer['tipo_contrato'] ?? 'Tipo de contrato no especificado'); ?></small>
                        <?php if ($offer['salario']): ?>
                            <span class="badge bg-success ms-2"><?php echo htmlspecialchars($offer['salario']); ?></span>
                        <?php endif; ?>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="alert alert-info">
                No se encontraron ofertas que coincidan con tu búsqueda.
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>