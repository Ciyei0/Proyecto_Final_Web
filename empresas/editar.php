<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth_functions.php';

if (!isLoggedIn() || !isCompany()) {
    redirect(BASE_URL . '/auth/login.php');
}

$pageTitle = "Editar Información de Empresa";
require_once __DIR__ . '/../includes/header.php';

$user = getCurrentUser();
$companyId = $user['id'];

// Obtener información actual de la empresa
$stmt = $pdo->prepare("SELECT * FROM empresas WHERE usuario_id = ?");
$stmt->execute([$companyId]);
$company = $stmt->fetch();

$errors = [];
$success = false;

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombreEmpresa = trim($_POST['nombre_empresa'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');
    $direccion = trim($_POST['direccion'] ?? '');
    $sitioWeb = trim($_POST['sitio_web'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');

    // Validaciones básicas
    if (empty($nombreEmpresa)) {
        $errors[] = "El nombre de la empresa es requerido";
    }

    // Procesar logo
    $logo = $company['logo'] ?? null;
    if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['logo'];
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = 'company_' . $companyId . '_' . time() . '.' . $ext;
        $target = PHOTOS_DIR . $filename;

        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        if (in_array(strtolower($ext), $allowed)) {
            if (!is_dir(PHOTOS_DIR)) {
                mkdir(PHOTOS_DIR, 0777, true);
            }

            if (move_uploaded_file($file['tmp_name'], $target)) {
                if ($logo && file_exists(PHOTOS_DIR . $logo)) {
                    unlink(PHOTOS_DIR . $logo);
                }
                $logo = $filename;
            } else {
                $errors[] = "Error al subir el logo";
            }
        } else {
            $errors[] = "Formato de archivo no permitido. Use JPG, PNG o GIF";
        }
    }

    if (empty($errors)) {
        if ($company) {
            // Actualizar empresa existente
            $stmt = $pdo->prepare("
                UPDATE empresas SET 
                    nombre_empresa = ?, 
                    telefono = ?, 
                    direccion = ?, 
                    sitio_web = ?, 
                    descripcion = ?, 
                    logo = ?
                WHERE usuario_id = ?
            ");
            $stmt->execute([
                $nombreEmpresa, 
                $telefono, 
                $direccion, 
                $sitioWeb, 
                $descripcion, 
                $logo, 
                $companyId
            ]);
        } else {
            // Crear nuevo registro de empresa
            $stmt = $pdo->prepare("
                INSERT INTO empresas (
                    usuario_id, 
                    nombre_empresa, 
                    telefono, 
                    direccion, 
                    sitio_web, 
                    descripcion, 
                    logo
                ) VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $companyId, 
                $nombreEmpresa, 
                $telefono, 
                $direccion, 
                $sitioWeb, 
                $descripcion, 
                $logo
            ]);
        }

        $success = true;

        $stmt = $pdo->prepare("SELECT * FROM empresas WHERE usuario_id = ?");
        $stmt->execute([$companyId]);
        $company = $stmt->fetch();
    }
}
?>

<div class="container py-4">
    <div class="row">
        <div class="col-md-12">
            <h2 class="mb-4">Editar Información de la Empresa</h2>

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
                    Los cambios se han guardado correctamente.
                </div>
            <?php endif; ?>

            <div class="card">
                <div class="card-body">
                    <form method="post" enctype="multipart/form-data">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="nombre_empresa" class="form-label">Nombre de la Empresa*</label>
                                    <input type="text" class="form-control" id="nombre_empresa" name="nombre_empresa"
                                           value="<?php echo htmlspecialchars($company['nombre_empresa'] ?? ''); ?>" required>
                                </div>

                                <div class="mb-3">
                                    <label for="telefono" class="form-label">Teléfono</label>
                                    <input type="text" class="form-control" id="telefono" name="telefono"
                                           value="<?php echo htmlspecialchars($company['telefono'] ?? ''); ?>">
                                </div>

                                <div class="mb-3">
                                    <label for="direccion" class="form-label">Dirección</label>
                                    <input type="text" class="form-control" id="direccion" name="direccion"
                                           value="<?php echo htmlspecialchars($company['direccion'] ?? ''); ?>">
                                </div>

                                <div class="mb-3">
                                    <label for="sitio_web" class="form-label">Sitio Web</label>
                                    <input type="url" class="form-control" id="sitio_web" name="sitio_web"
                                           value="<?php echo htmlspecialchars($company['sitio_web'] ?? ''); ?>">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="logo" class="form-label">Logo</label>
                                    <input type="file" class="form-control" id="logo" name="logo" accept="image/*">

                                    <?php if ($company && $company['logo']): ?>
                                        <div class="mt-2">
                                            <img src="<?php echo BASE_URL . '/assets/uploads/photos/' . htmlspecialchars($company['logo']); ?>"
                                                 class="img-thumbnail" style="max-height: 100px;">
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <div class="mb-3">
                                    <label for="descripcion" class="form-label">Descripción de la Empresa</label>
                                    <textarea class="form-control" id="descripcion" name="descripcion" rows="5"><?php 
                                        echo htmlspecialchars($company['descripcion'] ?? ''); 
                                    ?></textarea>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary px-4 py-2">
                                <i class="bi bi-save me-2"></i>Guardar Cambios
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
