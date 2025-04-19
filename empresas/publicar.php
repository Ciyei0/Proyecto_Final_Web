<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth_functions.php';

if (!isLoggedIn() || !isCompany()) {
    redirect(BASE_URL . '/auth/login.php');
}

$pageTitle = "Publicar Oferta";
require_once __DIR__ . '/../includes/header.php';

$user = getCurrentUser();
$companyId = $user['id'];

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo = trim($_POST['titulo'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $requisitos = trim($_POST['requisitos'] ?? '');
    $ubicacion = trim($_POST['ubicacion'] ?? '');
    $salario = trim($_POST['salario'] ?? '');
    $tipoContrato = trim($_POST['tipo_contrato'] ?? '');
    $fechaCierre = trim($_POST['fecha_cierre'] ?? '');
    $activa = isset($_POST['activa']) ? 1 : 0;
    
    // Validaciones
    if (empty($titulo)) $errors[] = "El título es requerido";
    if (empty($descripcion)) $errors[] = "La descripción es requerida";
    if (empty($requisitos)) $errors[] = "Los requisitos son requeridos";
    if ($fechaCierre && !strtotime($fechaCierre)) $errors[] = "Fecha de cierre inválida";
    
    if (empty($errors)) {
        try {
            $pdo->beginTransaction();
            
            $stmt = $pdo->prepare("
                INSERT INTO ofertas_empleo (
                    empresa_id, titulo, descripcion, requisitos, ubicacion, 
                    salario, tipo_contrato, fecha_cierre, activa
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $companyId, $titulo, $descripcion, $requisitos, $ubicacion,
                $salario, $tipoContrato, $fechaCierre ? date('Y-m-d', strtotime($fechaCierre)) : null, $activa
            ]);
            
            $pdo->commit();
            $success = true;
            $_POST = []; // Limpiar el formulario
        } catch (PDOException $e) {
            $pdo->rollBack();
            $errors[] = "Error al publicar la oferta: " . $e->getMessage();
        }
    }
}
?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <h2 class="mb-4">Publicar Nueva Oferta de Empleo</h2>
        
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success">
                Oferta publicada exitosamente. <a href="ofertas.php">Ver todas las ofertas</a>.
            </div>
        <?php endif; ?>
        
        <form method="post">
            <div class="mb-3">
                <label for="titulo" class="form-label">Título del Puesto*</label>
                <input type="text" class="form-control" id="titulo" name="titulo" required 
                       value="<?php echo htmlspecialchars($_POST['titulo'] ?? ''); ?>">
            </div>
            
            <div class="mb-3">
                <label for="descripcion" class="form-label">Descripción del Puesto*</label>
                <textarea class="form-control" id="descripcion" name="descripcion" rows="5" required><?php 
                    echo htmlspecialchars($_POST['descripcion'] ?? ''); 
                ?></textarea>
            </div>
            
            <div class="mb-3">
                <label for="requisitos" class="form-label">Requisitos*</label>
                <textarea class="form-control" id="requisitos" name="requisitos" rows="5" required><?php 
                    echo htmlspecialchars($_POST['requisitos'] ?? ''); 
                ?></textarea>
                <small class="text-muted">Separe cada requisito con un salto de línea</small>
            </div>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="ubicacion" class="form-label">Ubicación</label>
                    <input type="text" class="form-control" id="ubicacion" name="ubicacion" 
                           value="<?php echo htmlspecialchars($_POST['ubicacion'] ?? ''); ?>">
                </div>
                <div class="col-md-6 mb-3">
                    <label for="salario" class="form-label">Salario</label>
                    <input type="text" class="form-control" id="salario" name="salario" 
                           value="<?php echo htmlspecialchars($_POST['salario'] ?? ''); ?>">
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="tipo_contrato" class="form-label">Tipo de Contrato</label>
                    <select class="form-select" id="tipo_contrato" name="tipo_contrato">
                        <option value="">Seleccione...</option>
                        <option value="Tiempo completo" <?php echo ($_POST['tipo_contrato'] ?? '') == 'Tiempo completo' ? 'selected' : ''; ?>>Tiempo completo</option>
                        <option value="Medio tiempo" <?php echo ($_POST['tipo_contrato'] ?? '') == 'Medio tiempo' ? 'selected' : ''; ?>>Medio tiempo</option>
                        <option value="Por proyecto" <?php echo ($_POST['tipo_contrato'] ?? '') == 'Por proyecto' ? 'selected' : ''; ?>>Por proyecto</option>
                        <option value="Prácticas" <?php echo ($_POST['tipo_contrato'] ?? '') == 'Prácticas' ? 'selected' : ''; ?>>Prácticas</option>
                        <option value="Freelance" <?php echo ($_POST['tipo_contrato'] ?? '') == 'Freelance' ? 'selected' : ''; ?>>Freelance</option>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="fecha_cierre" class="form-label">Fecha de Cierre (opcional)</label>
                    <input type="date" class="form-control" id="fecha_cierre" name="fecha_cierre" 
                           value="<?php echo htmlspecialchars($_POST['fecha_cierre'] ?? ''); ?>">
                </div>
            </div>
            
            <div class="mb-3 form-check">
                <input type="checkbox" class="form-check-input" id="activa" name="activa" checked>
                <label class="form-check-label" for="activa">Oferta activa (visible para candidatos)</label>
            </div>
            
            <button type="submit" class="btn btn-primary">Publicar Oferta</button>
            <a href="dashboard.php" class="btn btn-secondary">Cancelar</a>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>