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

<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card border shadow-sm rounded-4 mb-4">
                <div class="card-body p-4 p-lg-5">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2 class="card-title fw-bold mb-0">Publicar Nueva Oferta de Empleo</h2>
                        <a href="dashboard.php" class="text-decoration-none" style="color: #1a73e8;">
                            <div class="d-flex align-items-center">
                                <span class="me-2">Volver al panel</span>
                                <div class="rounded-circle bg-primary bg-opacity-10" style="width: 28px; height: 28px;"></div>
                            </div>
                        </a>
                    </div>
                    
                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger rounded-3 border-0">
                            <div class="d-flex">
                                <div class="rounded-circle bg-danger bg-opacity-10 me-3 d-flex align-items-center justify-content-center" 
                                     style="width: 48px; height: 48px; min-width: 48px;">
                                </div>
                                <div>
                                    <h5 class="alert-heading fw-bold mb-1">Por favor corrija los siguientes errores:</h5>
                                    <ul class="mb-0 ps-3">
                                        <?php foreach ($errors as $error): ?>
                                            <li><?php echo htmlspecialchars($error); ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($success): ?>
                        <div class="alert alert-success rounded-3 border-0">
                            <div class="d-flex">
                                <div class="rounded-circle bg-success bg-opacity-10 me-3 d-flex align-items-center justify-content-center" 
                                     style="width: 48px; height: 48px; min-width: 48px;">
                                </div>
                                <div>
                                    <h5 class="alert-heading fw-bold mb-1">¡Oferta publicada con éxito!</h5>
                                    <p class="mb-0">Su oferta ha sido publicada correctamente. <a href="ofertas.php" class="fw-bold text-decoration-none" style="color: #34A853;">Ver todas las ofertas</a></p>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <form method="post" class="mt-4">
                        <div class="mb-4">
                            <label for="titulo" class="form-label fw-semibold">Título del Puesto*</label>
                            <input type="text" class="form-control form-control-lg rounded-3 py-2" id="titulo" name="titulo" required 
                                   value="<?php echo htmlspecialchars($_POST['titulo'] ?? ''); ?>"
                                   placeholder="Ej: Desarrollador Web Senior">
                        </div>
                        
                        <div class="mb-4">
                            <label for="descripcion" class="form-label fw-semibold">Descripción del Puesto*</label>
                            <textarea class="form-control rounded-3 py-2" id="descripcion" name="descripcion" rows="6" required
                                     placeholder="Describe las responsabilidades y funciones del puesto"><?php 
                                echo htmlspecialchars($_POST['descripcion'] ?? ''); 
                            ?></textarea>
                        </div>
                        
                        <div class="mb-4">
                            <label for="requisitos" class="form-label fw-semibold">Requisitos*</label>
                            <textarea class="form-control rounded-3 py-2" id="requisitos" name="requisitos" rows="6" required
                                     placeholder="Lista los requisitos necesarios para el puesto"><?php 
                                echo htmlspecialchars($_POST['requisitos'] ?? ''); 
                            ?></textarea>
                            <small class="text-muted">Separe cada requisito con un salto de línea</small>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <label for="ubicacion" class="form-label fw-semibold">Ubicación</label>
                                <input type="text" class="form-control rounded-3 py-2" id="ubicacion" name="ubicacion" 
                                       placeholder="Ej: Ciudad, País o Remoto"
                                       value="<?php echo htmlspecialchars($_POST['ubicacion'] ?? ''); ?>">
                            </div>
                            <div class="col-md-6 mb-4">
                                <label for="salario" class="form-label fw-semibold">Salario</label>
                                <input type="text" class="form-control rounded-3 py-2" id="salario" name="salario" 
                                       placeholder="Ej: $1000-$1500 mensual o A convenir"
                                       value="<?php echo htmlspecialchars($_POST['salario'] ?? ''); ?>">
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <label for="tipo_contrato" class="form-label fw-semibold">Tipo de Contrato</label>
                                <select class="form-select rounded-3 py-2" id="tipo_contrato" name="tipo_contrato">
                                    <option value="">Seleccione...</option>
                                    <option value="Tiempo completo" <?php echo ($_POST['tipo_contrato'] ?? '') == 'Tiempo completo' ? 'selected' : ''; ?>>Tiempo completo</option>
                                    <option value="Medio tiempo" <?php echo ($_POST['tipo_contrato'] ?? '') == 'Medio tiempo' ? 'selected' : ''; ?>>Medio tiempo</option>
                                    <option value="Por proyecto" <?php echo ($_POST['tipo_contrato'] ?? '') == 'Por proyecto' ? 'selected' : ''; ?>>Por proyecto</option>
                                    <option value="Prácticas" <?php echo ($_POST['tipo_contrato'] ?? '') == 'Prácticas' ? 'selected' : ''; ?>>Prácticas</option>
                                    <option value="Freelance" <?php echo ($_POST['tipo_contrato'] ?? '') == 'Freelance' ? 'selected' : ''; ?>>Freelance</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-4">
                                <label for="fecha_cierre" class="form-label fw-semibold">Fecha de Cierre (opcional)</label>
                                <input type="date" class="form-control rounded-3 py-2" id="fecha_cierre" name="fecha_cierre" 
                                       value="<?php echo htmlspecialchars($_POST['fecha_cierre'] ?? ''); ?>">
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <div class="form-check form-switch">
                                <input type="checkbox" class="form-check-input" id="activa" name="activa" checked>
                                <label class="form-check-label fw-semibold" for="activa">Oferta activa (visible para candidatos)</label>
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-end gap-2 mt-4">
                            <a href="dashboard.php" class="btn btn-light btn-lg rounded-pill px-4">Cancelar</a>
                            <button type="submit" class="btn btn-primary btn-lg rounded-pill px-5" style="background-color: #1a73e8; border: none;">Publicar Oferta</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>