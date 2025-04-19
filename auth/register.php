<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth_functions.php';

$pageTitle = "Registro";

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $tipo = $_POST['tipo'] ?? '';
    
    // Validaciones
    if (empty($nombre)) $errors[] = "El nombre es requerido";
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Email inválido";
    if (strlen($password) < 6) $errors[] = "La contraseña debe tener al menos 6 caracteres";
    if ($password !== $confirmPassword) $errors[] = "Las contraseñas no coinciden";
    if (!in_array($tipo, ['candidato', 'empresa'])) $errors[] = "Tipo de usuario inválido";

    $fotoPerfil = null;

    if (empty($errors)) {
        // Registrar usuario sin foto al inicio
        $userId = registerUser($nombre, $email, $password, $tipo, null);

        if ($userId) {
            // Procesar foto de perfil después de tener el ID
            if (isset($_FILES['foto_perfil']) && $_FILES['foto_perfil']['error'] === UPLOAD_ERR_OK) {
    $file = $_FILES['foto_perfil'];
    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);

    // Determinar el prefijo según el tipo de usuario
    $prefix = ($tipo === 'empresa') ? 'logo_' : 'profile_';
    
    // Crear slug del nombre
    $slugNombre = strtolower(preg_replace('/[^a-z0-9]+/', '_', iconv('UTF-8', 'ASCII//TRANSLIT', $nombre)));
    $filename = $prefix . $userId . '_' . $slugNombre . '.' . $ext;
    $target = PHOTOS_DIR . $filename;

    $allowed = ['jpg', 'jpeg', 'png', 'gif'];
    if (in_array(strtolower($ext), $allowed)) {
        if (!is_dir(PHOTOS_DIR)) {
            mkdir(PHOTOS_DIR, 0777, true);
        }

        if (move_uploaded_file($file['tmp_name'], $target)) {
            $fotoPerfil = $filename;

            // Actualizar usuario con foto/logo según el tipo
            if ($tipo === 'candidato') {
                updateUserPhoto($userId, $fotoPerfil);
            } else {
                updateCompanyLogo($userId, $fotoPerfil);
            }
        } else {
            $errors[] = "Error al subir la imagen";
        }
    } else {
        $errors[] = "Formato de archivo no permitido. Use JPG, PNG o GIF";
    }
}

            // Registrar datos específicos según el tipo
            if ($tipo === 'candidato') {
                $telefono = trim($_POST['telefono'] ?? '');
                $direccion = trim($_POST['direccion'] ?? '');
                $ciudad = trim($_POST['ciudad'] ?? '');
                $provincia = trim($_POST['provincia'] ?? '');

                if (registerCandidate($userId, $telefono, $direccion, $ciudad, $provincia, $fotoPerfil)) {
                    $success = true;
                }
            } elseif ($tipo === 'empresa') {
                $nombreEmpresa = trim($_POST['nombre_empresa'] ?? '');
                $descripcion = trim($_POST['descripcion'] ?? '');
                $direccionEmpresa = trim($_POST['direccion_empresa'] ?? '');
                $telefonoEmpresa = trim($_POST['telefono_empresa'] ?? '');
                $sitioWeb = trim($_POST['sitio_web'] ?? '');

                if (registerCompany($userId, $nombreEmpresa, $descripcion, $direccionEmpresa, $telefonoEmpresa, $sitioWeb, $fotoPerfil)) {
                    $success = true;
                }
            }

            if ($success) {
                login($email, $password);
                redirect(BASE_URL . '/' . ($tipo === 'candidato' ? 'candidates/dashboard.php' : 'empresas/dashboard.php'));
            } else {
                $errors[] = "Error al registrar los datos específicos del usuario";
            }
        } else {
            $errors[] = "Error al registrar el usuario (¿Email ya en uso?)";
        }
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<!-- Estilos CSS mejorados -->
<style>
    body {
        background: #f8f9fa;
    }
    
    .registro-container {
        padding: 40px 0;
    }
    
    .registro-card {
        border-radius: 15px;
        overflow: hidden;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        border: none;
    }
    
    .registro-header {
        background: linear-gradient(135deg, #4a89dc, #5e72e4);
        color: white;
        padding: 25px;
        text-align: center;
    }
    
    .profile-container {
        position: relative;
        width: 150px;
        height: 150px;
        border-radius: 50%;
        overflow: hidden;
        border: 4px solid #fff;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.15);
        margin: 0 auto 20px;
        transition: all 0.3s ease;
    }
    
    .profile-container:hover {
        transform: scale(1.05);
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
    }
    
    .profile-container::after {
        content: "Cambiar foto";
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        background-color: rgba(0, 0, 0, 0.6);
        color: white;
        font-size: 12px;
        padding: 5px 0;
        text-align: center;
        opacity: 0;
        transition: opacity 0.3s ease;
    }
    
    .profile-container:hover::after {
        opacity: 1;
    }
    
    .profile-img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    
    .tipo-usuario-card {
        border-radius: 10px;
        padding: 20px;
        margin: 10px;
        flex: 1;
        transition: all 0.3s ease;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
    }
    
    .tipo-usuario-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 15px rgba(0, 0, 0, 0.1);
    }
    
    .tipo-usuario-card.selected {
        border: 2px solid #4a89dc;
        background-color: rgba(74, 137, 220, 0.05);
    }
    
    .tipo-usuario-card i {
        font-size: 40px;
        margin-bottom: 15px;
        display: block;
        color: #4a89dc;
    }
    
    .form-group {
        margin-bottom: 20px;
        position: relative;
    }
    
    .form-control {
        border-radius: 8px;
        padding: 12px 45px 12px 15px;
        border: 1px solid #e0e0e0;
        transition: all 0.3s ease;
    }
    
    .form-control:focus {
        border-color: #4a89dc;
        box-shadow: 0 0 0 0.2rem rgba(74, 137, 220, 0.25);
    }
    
    .input-icon {
        position: absolute;
        top: 38px;
        right: 15px;
        color: #6c757d;
        font-size: 18px;
    }
    
    .btn-primary {
        background: linear-gradient(135deg, #4a89dc, #5e72e4);
        border: none;
        border-radius: 8px;
        padding: 12px;
        font-weight: 600;
        letter-spacing: 0.5px;
        transition: all 0.3s ease;
    }
    
    .btn-primary:hover {
        background: linear-gradient(135deg, #5e72e4, #4a89dc);
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(94, 114, 228, 0.4);
    }
    
    .section-title {
        position: relative;
        margin-bottom: 30px;
        padding-bottom: 10px;
    }
    
    .section-title:after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        width: 50px;
        height: 3px;
        background: linear-gradient(135deg, #4a89dc, #5e72e4);
    }
    
    .fade-in {
        animation: fadeIn 0.5s ease forwards;
    }
    
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    /* Mejoras para responsividad */
    @media (max-width: 768px) {
        .tipo-usuario-cards {
            flex-direction: column;
        }
        
        .tipo-usuario-card {
            width: 100%;
            margin: 10px 0;
        }
    }
</style>

<div class="container registro-container">
    <div class="row justify-content-center">
        <div class="col-md-10 col-lg-8">
            <!-- Tarjeta de registro mejorada -->
            <div class="card registro-card">
                <div class="registro-header">
                    <h2 class="mb-0">Crea tu cuenta</h2>
                    <p class="text-white-50 mb-0">Únete a nuestra plataforma de empleo</p>
                </div>
                
                <div class="card-body p-4 p-md-5">
                    <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <ul class="mb-0">
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo htmlspecialchars($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    <?php endif; ?>

                    <form method="post" class="needs-validation" novalidate enctype="multipart/form-data">
                        <!-- Sección de foto de perfil mejorada -->
                        <div class="text-center mb-4">
                            <div class="profile-container" onclick="document.getElementById('foto_perfil').click()">
                                <img id="previewFoto" src="<?php echo BASE_URL; ?>/assets/img/default-profile.png" 
                                     class="profile-img">
                            </div>
                            <input type="file" id="foto_perfil" name="foto_perfil" accept="image/*" 
                                   class="d-none" onchange="previewImage(this)">
                            <small class="text-muted">Haz clic en la imagen para cambiar tu foto de perfil</small>
                        </div>
                        
                        <!-- Datos básicos -->
                        <div class="form-group">
                            <label for="nombre" class="form-label">Nombre completo</label>
                            <input type="text" class="form-control" id="nombre" name="nombre" required 
                                   value="<?php echo htmlspecialchars($_POST['nombre'] ?? ''); ?>">
                            <i class="bi bi-person input-icon"></i>
                        </div>

                        <div class="form-group">
                            <label for="email" class="form-label">Correo electrónico</label>
                            <input type="email" class="form-control" id="email" name="email" required 
                                   value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                            <i class="bi bi-envelope input-icon"></i>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="password" class="form-label">Contraseña</label>
                                    <input type="password" class="form-control" id="password" name="password" required>
                                    <i class="bi bi-lock input-icon"></i>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="confirm_password" class="form-label">Confirmar contraseña</label>
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                    <i class="bi bi-lock-fill input-icon"></i>
                                </div>
                            </div>
                        </div>

                        <!-- Tarjetas de selección de tipo de usuario mejoradas -->
                        <div class="form-group mt-4">
                            <label class="form-label">¿Cómo quieres usar nuestra plataforma?</label>
                            <div class="d-flex tipo-usuario-cards">
                                <!-- Tarjeta para Candidato -->
                                <div class="tipo-usuario-card text-center selected" id="card_candidato">
                                    <input class="form-check-input d-none" type="radio" name="tipo" id="tipo_candidato" value="candidato" checked>
                                    <i class="bi bi-person-badge"></i>
                                    <h5 class="card-title">Candidato</h5>
                                    <p class="card-text text-muted">Busco nuevas oportunidades laborales</p>
                                </div>

                                <!-- Tarjeta para Empresa -->
                                <div class="tipo-usuario-card text-center" id="card_empresa">
                                    <input class="form-check-input d-none" type="radio" name="tipo" id="tipo_empresa" value="empresa">
                                    <i class="bi bi-building"></i>
                                    <h5 class="card-title">Empresa</h5>
                                    <p class="card-text text-muted">Quiero publicar ofertas de empleo</p>
                                </div>
                            </div>
                        </div>

                        <!-- Campos específicos para candidatos -->
                        <div id="campos_candidato" class="fade-in">
                            <h4 class="section-title mt-4">Información personal</h4>
                            
                            <div class="form-group">
                                <label for="telefono" class="form-label">Teléfono de contacto</label>
                                <input type="text" class="form-control" id="telefono" name="telefono" 
                                       value="<?php echo htmlspecialchars($_POST['telefono'] ?? ''); ?>">
                                <i class="bi bi-telephone input-icon"></i>
                            </div>

                            <div class="form-group">
                                <label for="direccion" class="form-label">Dirección</label>
                                <input type="text" class="form-control" id="direccion" name="direccion" 
                                       value="<?php echo htmlspecialchars($_POST['direccion'] ?? ''); ?>">
                                <i class="bi bi-geo-alt input-icon"></i>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="ciudad" class="form-label">Ciudad</label>
                                        <input type="text" class="form-control" id="ciudad" name="ciudad" 
                                               value="<?php echo htmlspecialchars($_POST['ciudad'] ?? ''); ?>">
                                        <i class="bi bi-building input-icon"></i>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="provincia" class="form-label">Provincia</label>
                                        <input type="text" class="form-control" id="provincia" name="provincia" 
                                               value="<?php echo htmlspecialchars($_POST['provincia'] ?? ''); ?>">
                                        <i class="bi bi-map input-icon"></i>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Campos específicos para empresas -->
                        <div id="campos_empresa" class="fade-in" style="display: none;">
                            <h4 class="section-title mt-4">Información de la empresa</h4>
                            
                            <div class="form-group">
                                <label for="nombre_empresa" class="form-label">Nombre de la empresa</label>
                                <input type="text" class="form-control" id="nombre_empresa" name="nombre_empresa" 
                                       value="<?php echo htmlspecialchars($_POST['nombre_empresa'] ?? ''); ?>">
                                <i class="bi bi-building input-icon"></i>
                            </div>

                            <div class="form-group">
                                <label for="descripcion" class="form-label">Descripción de la empresa</label>
                                <textarea class="form-control" id="descripcion" name="descripcion" rows="3"><?php echo htmlspecialchars($_POST['descripcion'] ?? ''); ?></textarea>
                                <i class="bi bi-card-text input-icon"></i>
                            </div>

                            <div class="form-group">
                                <label for="direccion_empresa" class="form-label">Dirección de la empresa</label>
                                <input type="text" class="form-control" id="direccion_empresa" name="direccion_empresa" 
                                       value="<?php echo htmlspecialchars($_POST['direccion_empresa'] ?? ''); ?>">
                                <i class="bi bi-geo-alt input-icon"></i>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="telefono_empresa" class="form-label">Teléfono de contacto</label>
                                        <input type="text" class="form-control" id="telefono_empresa" name="telefono_empresa" 
                                               value="<?php echo htmlspecialchars($_POST['telefono_empresa'] ?? ''); ?>">
                                        <i class="bi bi-telephone input-icon"></i>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="sitio_web" class="form-label">Sitio web (opcional)</label>
                                        <input type="url" class="form-control" id="sitio_web" name="sitio_web" 
                                               value="<?php echo htmlspecialchars($_POST['sitio_web'] ?? ''); ?>">
                                        <i class="bi bi-globe input-icon"></i>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary mt-4">
                                <i class="bi bi-person-plus me-2"></i>Crear cuenta
                            </button>
                            <p class="text-center mt-3">
                                ¿Ya tienes una cuenta? <a href="<?php echo BASE_URL; ?>/login.php" class="text-primary">Inicia sesión</a>
                            </p>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Función para previsualizar la imagen seleccionada
    function previewImage(input) {
        const preview = document.getElementById('previewFoto');
        const file = input.files[0];
        const reader = new FileReader();
        
        if (file) {
            reader.onloadstart = function() {
                preview.style.opacity = 0.5; // Inicia animación de carga
            }
            
            reader.onload = function(e) {
                setTimeout(() => {
                    preview.src = e.target.result;
                    preview.style.opacity = 1; // Termina animación de carga
                }, 300);
            }
            
            reader.readAsDataURL(file);
        }
    }
    
    // Selección de tipo de usuario con tarjetas interactivas mejoradas
    const cardCandidato = document.getElementById('card_candidato');
    const cardEmpresa = document.getElementById('card_empresa');
    const inputCandidato = document.getElementById('tipo_candidato');
    const inputEmpresa = document.getElementById('tipo_empresa');
    const camposCandidato = document.getElementById('campos_candidato');
    const camposEmpresa = document.getElementById('campos_empresa');

    cardCandidato.addEventListener('click', () => {
        inputCandidato.checked = true;
        
        // Actualizar clases de selección
        cardCandidato.classList.add('selected');
        cardEmpresa.classList.remove('selected');
        
        // Mostrar/ocultar campos con animación
        camposCandidato.style.display = 'block';
        camposEmpresa.style.display = 'none';
        
        // Reiniciar animación
        camposCandidato.classList.remove('fade-in');
        void camposCandidato.offsetWidth; // Forzar reflow
        camposCandidato.classList.add('fade-in');
    });

    cardEmpresa.addEventListener('click', () => {
        inputEmpresa.checked = true;
        
        // Actualizar clases de selección
        cardEmpresa.classList.add('selected');
        cardCandidato.classList.remove('selected');
        
        // Mostrar/ocultar campos con animación
        camposCandidato.style.display = 'none';
        camposEmpresa.style.display = 'block';
        
        // Reiniciar animación
        camposEmpresa.classList.remove('fade-in');
        void camposEmpresa.offsetWidth; // Forzar reflow
        camposEmpresa.classList.add('fade-in');
    });
    
    // Validación del formulario
    (function() {
        'use strict';
        
        const forms = document.querySelectorAll('.needs-validation');
        
        Array.from(forms).forEach(form => {
            form.addEventListener('submit', event => {
                if (!form.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                
                form.classList.add('was-validated');
            }, false);
        });
    })();
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>