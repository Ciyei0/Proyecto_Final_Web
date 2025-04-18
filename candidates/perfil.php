<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth_functions.php';

if (!isLoggedIn() || !isCandidate()) {
    redirect(BASE_URL . '/auth/login.php');
}

$pageTitle = "Editar Perfil";
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

// Obtener formación académica
$education = [];
if ($cv) {
    $stmt = $pdo->prepare("SELECT * FROM formacion_academica WHERE cv_id = ? ORDER BY fecha_inicio DESC");
    $stmt->execute([$cv['id']]);
    $education = $stmt->fetchAll();
}

// Obtener experiencia laboral
$experience = [];
if ($cv) {
    $stmt = $pdo->prepare("SELECT * FROM experiencia_laboral WHERE cv_id = ? ORDER BY fecha_inicio DESC");
    $stmt->execute([$cv['id']]);
    $experience = $stmt->fetchAll();
}

// Obtener idiomas
$languages = [];
if ($cv) {
    $stmt = $pdo->prepare("SELECT * FROM idiomas WHERE cv_id = ?");
    $stmt->execute([$cv['id']]);
    $languages = $stmt->fetchAll();
}

// Obtener proyectos/logros
$projects = [];
if ($cv) {
    $stmt = $pdo->prepare("SELECT * FROM proyectos_logros WHERE cv_id = ? ORDER BY fecha DESC");
    $stmt->execute([$cv['id']]);
    $projects = $stmt->fetchAll();
}

$errors = [];
$success = false;

// Procesar formulario de perfil básico
if (isset($_POST['save_basic'])) {
    $telefono = trim($_POST['telefono'] ?? '');
    $direccion = trim($_POST['direccion'] ?? '');
    $ciudad = trim($_POST['ciudad'] ?? '');
    $provincia = trim($_POST['provincia'] ?? '');
    
    // Procesar foto de perfil
    $fotoPerfil = $candidate['foto_perfil'] ?? null;
    if (isset($_FILES['foto_perfil']) && $_FILES['foto_perfil']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['foto_perfil'];
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = 'profile_' . $candidateId . '_' . time() . '.' . $ext;
        $target = PHOTOS_DIR . $filename;

        // Validar tipo de archivo
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        if (in_array(strtolower($ext), $allowed)) {
            // Crear la carpeta si no existe
            if (!is_dir(PHOTOS_DIR)) {
                mkdir(PHOTOS_DIR, 0777, true);
            }

            if (move_uploaded_file($file['tmp_name'], $target)) {
                // Eliminar foto anterior si existe
                if ($fotoPerfil && file_exists(PHOTOS_DIR . $fotoPerfil)) {
                    unlink(PHOTOS_DIR . $fotoPerfil);
                }
                $fotoPerfil = $filename;
            } else {
                $errors[] = "Error al subir la foto de perfil";
            }
        } else {
            $errors[] = "Formato de archivo no permitido. Use JPG, PNG o GIF";
        }
    }
    
    if (empty($errors)) {
        if ($candidate) {
            // Actualizar candidato existente
            $stmt = $pdo->prepare("UPDATE candidatos SET telefono = ?, direccion = ?, ciudad = ?, provincia = ?, foto_perfil = ? WHERE usuario_id = ?");
            $stmt->execute([$telefono, $direccion, $ciudad, $provincia, $fotoPerfil, $candidateId]);
        } else {
            // Crear nuevo registro de candidato
            $stmt = $pdo->prepare("INSERT INTO candidatos (usuario_id, telefono, direccion, ciudad, provincia, foto_perfil) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$candidateId, $telefono, $direccion, $ciudad, $provincia, $fotoPerfil]);
        }
        $success = true;
    }
}

// Procesar formulario de CV
if (isset($_POST['save_cv'])) {
    $objetivo = trim($_POST['objetivo_profesional'] ?? '');
    $habilidades = trim($_POST['habilidades_clave'] ?? '');
    $disponibilidad = trim($_POST['disponibilidad'] ?? '');
    $linkedin = trim($_POST['linkedin'] ?? '');
    $github = trim($_POST['github'] ?? '');
    $otrasRedes = trim($_POST['otras_redes'] ?? '');
    $referencias = trim($_POST['referencias'] ?? '');
    
    // Procesar PDF del CV
    $cvPdf = $cv['cv_pdf'] ?? null;
    if (isset($_FILES['cv_pdf']) && $_FILES['cv_pdf']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['cv_pdf'];
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        
        if (strtolower($ext) === 'pdf') {
            $filename = 'cv_' . $candidateId . '_' . time() . '.pdf';
            $target = CV_DIR . $filename;
            
            if (move_uploaded_file($file['tmp_name'], $target)) {
                // Eliminar PDF anterior si existe
                if ($cvPdf && file_exists(CV_DIR . $cvPdf)) {
                    unlink(CV_DIR . $cvPdf);
                }
                $cvPdf = $filename;
            } else {
                $errors[] = "Error al subir el archivo PDF";
            }
        } else {
            $errors[] = "Solo se permiten archivos PDF";
        }
    }
    
    if (empty($errors)) {
        if ($cv) {
            // Actualizar CV existente
            $stmt = $pdo->prepare("UPDATE cvs SET objetivo_profesional = ?, habilidades_clave = ?, disponibilidad = ?, linkedin = ?, github = ?, otras_redes = ?, referencias = ?, cv_pdf = ? WHERE candidato_id = ?");
            $stmt->execute([$objetivo, $habilidades, $disponibilidad, $linkedin, $github, $otrasRedes, $referencias, $cvPdf, $candidateId]);
        } else {
            // Crear nuevo CV
            $stmt = $pdo->prepare("INSERT INTO cvs (candidato_id, objetivo_profesional, habilidades_clave, disponibilidad, linkedin, github, otras_redes, referencias, cv_pdf) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$candidateId, $objetivo, $habilidades, $disponibilidad, $linkedin, $github, $otrasRedes, $referencias, $cvPdf]);
        }
        $success = true;
    }
}

// Procesar formulario de educación
if (isset($_POST['add_education'])) {
    $institucion = trim($_POST['institucion'] ?? '');
    $titulo = trim($_POST['titulo'] ?? '');
    $fechaInicio = trim($_POST['fecha_inicio'] ?? '');
    $fechaFin = trim($_POST['fecha_fin'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    
    if (empty($institucion) || empty($titulo) || empty($fechaInicio)) {
        $errors[] = "Institución, título y fecha de inicio son requeridos";
    } elseif (!$cv) {
        $errors[] = "Primero debes crear tu CV antes de añadir educación";
    } else {
        $stmt = $pdo->prepare("INSERT INTO formacion_academica (cv_id, institucion, titulo, fecha_inicio, fecha_fin, descripcion) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$cv['id'], $institucion, $titulo, $fechaInicio, $fechaFin ?: null, $descripcion]);
        $success = true;
    }
}

// Procesar formulario de experiencia
if (isset($_POST['add_experience'])) {
    $empresa = trim($_POST['empresa'] ?? '');
    $puesto = trim($_POST['puesto'] ?? '');
    $fechaInicio = trim($_POST['fecha_inicio'] ?? '');
    $fechaFin = trim($_POST['fecha_fin'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    
    if (empty($empresa) || empty($puesto) || empty($fechaInicio)) {
        $errors[] = "Empresa, puesto y fecha de inicio son requeridos";
    } elseif (!$cv) {
        $errors[] = "Primero debes crear tu CV antes de añadir experiencia";
    } else {
        $stmt = $pdo->prepare("INSERT INTO experiencia_laboral (cv_id, empresa, puesto, fecha_inicio, fecha_fin, descripcion) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$cv['id'], $empresa, $puesto, $fechaInicio, $fechaFin ?: null, $descripcion]);
        $success = true;
    }
}

// Procesar formulario de idiomas
if (isset($_POST['add_language'])) {
    $idioma = trim($_POST['idioma'] ?? '');
    $nivel = trim($_POST['nivel'] ?? '');
    
    if (empty($idioma) || empty($nivel)) {
        $errors[] = "Idioma y nivel son requeridos";
    } elseif (!$cv) {
        $errors[] = "Primero debes crear tu CV antes de añadir idiomas";
    } else {
        $stmt = $pdo->prepare("INSERT INTO idiomas (cv_id, idioma, nivel) VALUES (?, ?, ?)");
        $stmt->execute([$cv['id'], $idioma, $nivel]);
        $success = true;
    }
}

// Procesar formulario de proyectos
if (isset($_POST['add_project'])) {
    $titulo = trim($_POST['titulo'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $fecha = trim($_POST['fecha'] ?? '');
    $enlace = trim($_POST['enlace'] ?? '');
    
    if (empty($titulo) || empty($descripcion)) {
        $errors[] = "Título y descripción son requeridos";
    } elseif (!$cv) {
        $errors[] = "Primero debes crear tu CV antes de añadir proyectos";
    } else {
        $stmt = $pdo->prepare("INSERT INTO proyectos_logros (cv_id, titulo, descripcion, fecha, enlace) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$cv['id'], $titulo, $descripcion, $fecha ?: null, $enlace ?: null]);
        $success = true;
    }
}

// Eliminar elementos del CV
if (isset($_GET['delete'])) {
    $type = $_GET['type'] ?? '';
    $id = $_GET['id'] ?? 0;
    
    if ($type && $id) {
        $validTypes = ['education', 'experience', 'language', 'project'];
        if (in_array($type, $validTypes)) {
            $tableMap = [
                'education' => 'formacion_academica',
                'experience' => 'experiencia_laboral',
                'language' => 'idiomas',
                'project' => 'proyectos_logros'
            ];
            
            // Verificar que el elemento pertenece al CV del usuario
            $stmt = $pdo->prepare("
                SELECT COUNT(*) FROM {$tableMap[$type]} t
                JOIN cvs c ON t.cv_id = c.id
                WHERE t.id = ? AND c.candidato_id = ?
            ");
            $stmt->execute([$id, $candidateId]);
            
            if ($stmt->fetchColumn()) {
                $stmt = $pdo->prepare("DELETE FROM {$tableMap[$type]} WHERE id = ?");
                $stmt->execute([$id]);
                $success = true;
            }
        }
    }
}
?>

<div class="row">
    <div class="col-md-12">
        <h2 class="mb-4">Mi Perfil y CV</h2>
        
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
                Cambios guardados exitosamente.
            </div>
        <?php endif; ?>
        
        <div class="card mb-4">
            <div class="card-header">
                <h5>Información Básica</h5>
            </div>
            <div class="card-body">
                <form method="post" enctype="multipart/form-data">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="telefono" class="form-label">Teléfono</label>
                                <input type="text" class="form-control" id="telefono" name="telefono" 
                                       value="<?php echo htmlspecialchars($candidate['telefono'] ?? ''); ?>">
                            </div>
                            
                            <div class="mb-3">
                                <label for="direccion" class="form-label">Dirección</label>
                                <input type="text" class="form-control" id="direccion" name="direccion" 
                                       value="<?php echo htmlspecialchars($candidate['direccion'] ?? ''); ?>">
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="ciudad" class="form-label">Ciudad</label>
                                    <input type="text" class="form-control" id="ciudad" name="ciudad" 
                                           value="<?php echo htmlspecialchars($candidate['ciudad'] ?? ''); ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="provincia" class="form-label">Provincia</label>
                                    <input type="text" class="form-control" id="provincia" name="provincia" 
                                           value="<?php echo htmlspecialchars($candidate['provincia'] ?? ''); ?>">
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="foto_perfil" class="form-label">Foto de Perfil</label>
                                <input type="file" class="form-control" id="foto_perfil" name="foto_perfil" accept="image/*">
                                
                                <?php if ($candidate && $candidate['foto_perfil']): ?>
                                    <div class="mt-2">
                                        <img src="<?php echo BASE_URL . '/assets/uploads/photos/' . htmlspecialchars($candidate['foto_perfil']); ?>" 
                                             class="img-thumbnail" style="max-height: 100px;">
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <button type="submit" name="save_basic" class="btn btn-primary">Guardar Información Básica</button>
                </form>
            </div>
        </div>
        
        <div class="card mb-4">
            <div class="card-header">
                <h5>CV Digital</h5>
            </div>
            <div class="card-body">
                <form method="post" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="objetivo_profesional" class="form-label">Objetivo Profesional</label>
                        <textarea class="form-control" id="objetivo_profesional" name="objetivo_profesional" rows="3"><?php 
                            echo htmlspecialchars($cv['objetivo_profesional'] ?? ''); 
                        ?></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="habilidades_clave" class="form-label">Habilidades Clave</label>
                        <textarea class="form-control" id="habilidades_clave" name="habilidades_clave" rows="3"><?php 
                            echo htmlspecialchars($cv['habilidades_clave'] ?? ''); 
                        ?></textarea>
                        <small class="text-muted">Separa cada habilidad con una coma</small>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="disponibilidad" class="form-label">Disponibilidad</label>
                            <select class="form-select" id="disponibilidad" name="disponibilidad">
                                <option value="">Seleccione...</option>
                                <option value="Inmediata" <?php echo ($cv['disponibilidad'] ?? '') == 'Inmediata' ? 'selected' : ''; ?>>Inmediata</option>
                                <option value="15 días" <?php echo ($cv['disponibilidad'] ?? '') == '15 días' ? 'selected' : ''; ?>>15 días</option>
                                <option value="1 mes" <?php echo ($cv['disponibilidad'] ?? '') == '1 mes' ? 'selected' : ''; ?>>1 mes</option>
                                <option value="3 meses" <?php echo ($cv['disponibilidad'] ?? '') == '3 meses' ? 'selected' : ''; ?>>3 meses</option>
                            </select>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="cv_pdf" class="form-label">CV en PDF (opcional)</label>
                            <input type="file" class="form-control" id="cv_pdf" name="cv_pdf" accept=".pdf">
                            
                            <?php if ($cv && $cv['cv_pdf']): ?>
                                <div class="mt-2">
                                    <a href="<?php echo BASE_URL . '/assets/cv/' . htmlspecialchars($cv['cv_pdf']); ?>" 
                                       target="_blank" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-file-pdf"></i> Ver CV actual
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="linkedin" class="form-label">LinkedIn (opcional)</label>
                            <input type="url" class="form-control" id="linkedin" name="linkedin" 
                                   value="<?php echo htmlspecialchars($cv['linkedin'] ?? ''); ?>">
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="github" class="form-label">GitHub (opcional)</label>
                            <input type="url" class="form-control" id="github" name="github" 
                                   value="<?php echo htmlspecialchars($cv['github'] ?? ''); ?>">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="otras_redes" class="form-label">Otras Redes/Portafolio (opcional)</label>
                        <textarea class="form-control" id="otras_redes" name="otras_redes" rows="2"><?php 
                            echo htmlspecialchars($cv['otras_redes'] ?? ''); 
                        ?></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="referencias" class="form-label">Referencias (opcional)</label>
                        <textarea class="form-control" id="referencias" name="referencias" rows="3"><?php 
                            echo htmlspecialchars($cv['referencias'] ?? ''); 
                        ?></textarea>
                    </div>
                    
                    <button type="submit" name="save_cv" class="btn btn-primary">Guardar CV</button>
                </form>
            </div>
        </div>
        
        <!-- Sección de Formación Académica -->
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5>Formación Académica</h5>
                <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addEducationModal">
                    <i class="fas fa-plus"></i> Añadir
                </button>
            </div>
            <div class="card-body">
                <?php if ($education): ?>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Institución</th>
                                    <th>Título</th>
                                    <th>Fechas</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($education as $edu): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($edu['institucion']); ?></td>
                                        <td><?php echo htmlspecialchars($edu['titulo']); ?></td>
                                        <td>
                                            <?php echo date('m/Y', strtotime($edu['fecha_inicio'])); ?> - 
                                            <?php echo $edu['fecha_fin'] ? date('m/Y', strtotime($edu['fecha_fin'])) : 'Actualidad'; ?>
                                        </td>
                                        <td>
                                            <a href="?delete=1&type=education&id=<?php echo $edu['id']; ?>" 
                                               class="btn btn-sm btn-danger" onclick="return confirm('¿Eliminar este registro?')">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info">
                        No has añadido formación académica aún.
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Sección de Experiencia Laboral -->
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5>Experiencia Laboral</h5>
                <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addExperienceModal">
                    <i class="fas fa-plus"></i> Añadir
                </button>
            </div>
            <div class="card-body">
                <?php if ($experience): ?>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Empresa</th>
                                    <th>Puesto</th>
                                    <th>Fechas</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($experience as $exp): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($exp['empresa']); ?></td>
                                        <td><?php echo htmlspecialchars($exp['puesto']); ?></td>
                                        <td>
                                            <?php echo date('m/Y', strtotime($exp['fecha_inicio'])); ?> - 
                                            <?php echo $exp['fecha_fin'] ? date('m/Y', strtotime($exp['fecha_fin'])) : 'Actualidad'; ?>
                                        </td>
                                        <td>
                                            <a href="?delete=1&type=experience&id=<?php echo $exp['id']; ?>" 
                                               class="btn btn-sm btn-danger" onclick="return confirm('¿Eliminar este registro?')">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info">
                        No has añadido experiencia laboral aún.
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Sección de Idiomas -->
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5>Idiomas</h5>
                <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addLanguageModal">
                    <i class="fas fa-plus"></i> Añadir
                </button>
            </div>
            <div class="card-body">
                <?php if ($languages): ?>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Idioma</th>
                                    <th>Nivel</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($languages as $lang): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($lang['idioma']); ?></td>
                                        <td><?php echo htmlspecialchars($lang['nivel']); ?></td>
                                        <td>
                                            <a href="?delete=1&type=language&id=<?php echo $lang['id']; ?>" 
                                               class="btn btn-sm btn-danger" onclick="return confirm('¿Eliminar este registro?')">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info">
                        No has añadido idiomas aún.
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Sección de Proyectos/Logros -->
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5>Proyectos y Logros</h5>
                <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addProjectModal">
                    <i class="fas fa-plus"></i> Añadir
                </button>
            </div>
            <div class="card-body">
                <?php if ($projects): ?>
                    <div class="row">
                        <?php foreach ($projects as $proj): ?>
                            <div class="col-md-6 mb-3">
                                <div class="card h-100">
                                    <div class="card-body">
                                        <h6><?php echo htmlspecialchars($proj['titulo']); ?></h6>
                                        <p><?php echo nl2br(htmlspecialchars($proj['descripcion'])); ?></p>
                                        <?php if ($proj['fecha']): ?>
                                            <small class="text-muted"><?php echo date('m/Y', strtotime($proj['fecha'])); ?></small>
                                        <?php endif; ?>
                                        <?php if ($proj['enlace']): ?>
                                            <div class="mt-2">
                                                <a href="<?php echo htmlspecialchars($proj['enlace']); ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                                    Ver proyecto
                                                </a>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="card-footer bg-transparent">
                                        <a href="?delete=1&type=project&id=<?php echo $proj['id']; ?>" 
                                           class="btn btn-sm btn-danger" onclick="return confirm('¿Eliminar este proyecto?')">
                                            <i class="fas fa-trash"></i> Eliminar
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info">
                        No has añadido proyectos o logros aún.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Modal para añadir formación académica -->
<div class="modal fade" id="addEducationModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post">
                <div class="modal-header">
                    <h5 class="modal-title">Añadir Formación Académica</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="institucion" class="form-label">Institución*</label>
                        <input type="text" class="form-control" id="institucion" name="institucion" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="titulo" class="form-label">Título Obtenido*</label>
                        <input type="text" class="form-control" id="titulo" name="titulo" required>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="fecha_inicio" class="form-label">Fecha de Inicio*</label>
                            <input type="date" class="form-control" id="fecha_inicio" name="fecha_inicio" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="fecha_fin" class="form-label">Fecha de Finalización</label>
                            <input type="date" class="form-control" id="fecha_fin" name="fecha_fin">
                            <small class="text-muted">Dejar vacío si aún estudias aquí</small>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="descripcion" class="form-label">Descripción (opcional)</label>
                        <textarea class="form-control" id="descripcion" name="descripcion" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" name="add_education" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal para añadir experiencia laboral -->
<div class="modal fade" id="addExperienceModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post">
                <div class="modal-header">
                    <h5 class="modal-title">Añadir Experiencia Laboral</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="empresa" class="form-label">Empresa*</label>
                        <input type="text" class="form-control" id="empresa" name="empresa" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="puesto" class="form-label">Puesto*</label>
                        <input type="text" class="form-control" id="puesto" name="puesto" required>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="fecha_inicio" class="form-label">Fecha de Inicio*</label>
                            <input type="date" class="form-control" id="fecha_inicio" name="fecha_inicio" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="fecha_fin" class="form-label">Fecha de Finalización</label>
                            <input type="date" class="form-control" id="fecha_fin" name="fecha_fin">
                            <small class="text-muted">Dejar vacío si aún trabajas aquí</small>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="descripcion" class="form-label">Descripción (opcional)</label>
                        <textarea class="form-control" id="descripcion" name="descripcion" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" name="add_experience" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal para añadir idiomas -->
<div class="modal fade" id="addLanguageModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post">
                <div class="modal-header">
                    <h5 class="modal-title">Añadir Idioma</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="idioma" class="form-label">Idioma*</label>
                        <input type="text" class="form-control" id="idioma" name="idioma" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="nivel" class="form-label">Nivel*</label>
                        <select class="form-select" id="nivel" name="nivel" required>
                            <option value="">Seleccione...</option>
                            <option value="Básico">Básico</option>
                            <option value="Intermedio">Intermedio</option>
                            <option value="Avanzado">Avanzado</option>
                            <option value="Nativo">Nativo</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" name="add_language" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal para añadir proyectos -->
<div class="modal fade" id="addProjectModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post">
                <div class="modal-header">
                    <h5 class="modal-title">Añadir Proyecto o Logro</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="titulo" class="form-label">Título*</label>
                        <input type="text" class="form-control" id="titulo" name="titulo" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="descripcion" class="form-label">Descripción*</label>
                        <textarea class="form-control" id="descripcion" name="descripcion" rows="3" required></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="fecha" class="form-label">Fecha (opcional)</label>
                            <input type="date" class="form-control" id="fecha" name="fecha">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="enlace" class="form-label">Enlace (opcional)</label>
                            <input type="url" class="form-control" id="enlace" name="enlace" placeholder="https://...">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" name="add_project" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Validación de fechas en los formularios
document.addEventListener('DOMContentLoaded', function() {
    // Validar que fecha fin no sea menor que fecha inicio
    const validateDates = (startId, endId) => {
        const startInput = document.getElementById(startId);
        const endInput = document.getElementById(endId);
        
        if (startInput && endInput) {
            startInput.addEventListener('change', function() {
                if (endInput.value && new Date(endInput.value) < new Date(this.value)) {
                    endInput.value = '';
                    alert('La fecha de finalización no puede ser anterior a la fecha de inicio');
                }
            });
            
            endInput.addEventListener('change', function() {
                if (startInput.value && new Date(this.value) < new Date(startInput.value)) {
                    this.value = '';
                    alert('La fecha de finalización no puede ser anterior a la fecha de inicio');
                }
            });
        }
    };
    
    validateDates('fecha_inicio', 'fecha_fin'); // Formación académica
    validateDates('fecha_inicio_exp', 'fecha_fin_exp'); // Experiencia laboral
    
    // Mostrar mensaje si no hay CV creado aún
    <?php if (!$cv): ?>
        const showCvAlert = () => {
            const alertDiv = document.createElement('div');
            alertDiv.className = 'alert alert-warning mt-3';
            alertDiv.innerHTML = 'Debes guardar tu CV primero antes de añadir educación, experiencia, etc.';
            return alertDiv;
        };
        
        const modals = ['addEducationModal', 'addExperienceModal', 'addLanguageModal', 'addProjectModal'];
        modals.forEach(modalId => {
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.addEventListener('show.bs.modal', function() {
                    const form = this.querySelector('form');
                    if (form && !document.querySelector(`#${modalId} .alert`)) {
                        form.insertBefore(showCvAlert(), form.firstChild);
                    }
                });
            }
        });
    <?php endif; ?>
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>