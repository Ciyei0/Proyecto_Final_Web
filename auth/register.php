<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth_functions.php';

$pageTitle = "Registro";
require_once __DIR__ . '/../includes/header.php';

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
    
    if (empty($errors)) {
        $userId = registerUser($nombre, $email, $password, $tipo);
        
        if ($userId) {
            // Registrar datos específicos según el tipo
            if ($tipo === 'candidato') {
                $telefono = trim($_POST['telefono'] ?? '');
                $direccion = trim($_POST['direccion'] ?? '');
                $ciudad = trim($_POST['ciudad'] ?? '');
                $provincia = trim($_POST['provincia'] ?? '');
                
                if (registerCandidate($userId, $telefono, $direccion, $ciudad, $provincia)) {
                    $success = true;
                }
            } elseif ($tipo === 'empresa') {
                $nombreEmpresa = trim($_POST['nombre_empresa'] ?? '');
                $descripcion = trim($_POST['descripcion'] ?? '');
                $direccionEmpresa = trim($_POST['direccion_empresa'] ?? '');
                $telefonoEmpresa = trim($_POST['telefono_empresa'] ?? '');
                $sitioWeb = trim($_POST['sitio_web'] ?? '');
                
                if (registerCompany($userId, $nombreEmpresa, $descripcion, $direccionEmpresa, $telefonoEmpresa, $sitioWeb)) {
                    $success = true;
                }
            }
            
            if ($success) {
                // Auto-login después del registro
                login($email, $password);
                redirect(BASE_URL . '/' . ($tipo === 'candidato' ? 'candidates/dashboard.php' : 'empresas/dashboard.php'));
            } else {
                $errors[] = "Error al registrar los datos específicos del usuario";
            }
        } else {
            $errors[] = "Error al registrar el usuario";
        }
    }
}
?>

<div class="row justify-content-center mt-5">
    <div class="col-md-8">
        <div class="card shadow-lg border-0">
            <div class="card-header text-center" style="background: linear-gradient(90deg, #f8f9fa, #e9ecef); color: #000;">
                <h2 class="mb-0">Registro</h2>
            </div>
            <div class="card-body p-4">
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <ul>
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo htmlspecialchars($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form method="post" class="needs-validation" novalidate>
                    <div class="mb-3">
                        <label for="nombre" class="form-label">Nombre completo</label>
                        <input type="text" class="form-control" id="nombre" name="nombre" required value="<?php echo htmlspecialchars($_POST['nombre'] ?? ''); ?>">
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" required value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Contraseña</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>

                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">Confirmar contraseña</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Selecciona tu tipo de usuario</label>
                        <div class="d-flex justify-content-around">
                            <!-- Tarjeta para Candidato -->
                            <div class="card tipo-usuario-card text-center p-3" id="card_candidato" style="cursor: pointer; border: 2px solid transparent;">
                                <input class="form-check-input d-none" type="radio" name="tipo" id="tipo_candidato" value="candidato" checked>
                                <div class="card-body">
                                    <i class="bi bi-person-circle fs-1 text-primary"></i>
                                    <h5 class="card-title mt-2">Candidato</h5>
                                    <p class="card-text small">Estoy buscando empleo.</p>
                                </div>
                            </div>

                            <!-- Tarjeta para Empresa -->
                            <div class="card tipo-usuario-card text-center p-3" id="card_empresa" style="cursor: pointer; border: 2px solid transparent;">
                                <input class="form-check-input d-none" type="radio" name="tipo" id="tipo_empresa" value="empresa">
                                <div class="card-body">
                                    <i class="bi bi-building fs-1 text-primary"></i>
                                    <h5 class="card-title mt-2">Empresa</h5>
                                    <p class="card-text small">Quiero publicar ofertas de empleo.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Campos específicos para candidatos -->
                    <div id="campos_candidato">
                        <h4 class="mt-4 mb-3">Información adicional (Candidato)</h4>
                        <div class="mb-3">
                            <label for="telefono" class="form-label">Teléfono</label>
                            <input type="text" class="form-control" id="telefono" name="telefono" value="<?php echo htmlspecialchars($_POST['telefono'] ?? ''); ?>">
                        </div>

                        <div class="mb-3">
                            <label for="direccion" class="form-label">Dirección</label>
                            <input type="text" class="form-control" id="direccion" name="direccion" value="<?php echo htmlspecialchars($_POST['direccion'] ?? ''); ?>">
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="ciudad" class="form-label">Ciudad</label>
                                <input type="text" class="form-control" id="ciudad" name="ciudad" value="<?php echo htmlspecialchars($_POST['ciudad'] ?? ''); ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="provincia" class="form-label">Provincia</label>
                                <input type="text" class="form-control" id="provincia" name="provincia" value="<?php echo htmlspecialchars($_POST['provincia'] ?? ''); ?>">
                            </div>
                        </div>
                    </div>

                    <!-- Campos específicos para empresas -->
                    <div id="campos_empresa" style="display: none;">
                        <h4 class="mt-4 mb-3">Información adicional (Empresa)</h4>
                        <div class="mb-3">
                            <label for="nombre_empresa" class="form-label">Nombre de la empresa</label>
                            <input type="text" class="form-control" id="nombre_empresa" name="nombre_empresa" value="<?php echo htmlspecialchars($_POST['nombre_empresa'] ?? ''); ?>">
                        </div>

                        <div class="mb-3">
                            <label for="descripcion" class="form-label">Descripción de la empresa</label>
                            <textarea class="form-control" id="descripcion" name="descripcion"><?php echo htmlspecialchars($_POST['descripcion'] ?? ''); ?></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="direccion_empresa" class="form-label">Dirección de la empresa</label>
                            <input type="text" class="form-control" id="direccion_empresa" name="direccion_empresa" value="<?php echo htmlspecialchars($_POST['direccion_empresa'] ?? ''); ?>">
                        </div>

                        <div class="mb-3">
                            <label for="telefono_empresa" class="form-label">Teléfono de contacto</label>
                            <input type="text" class="form-control" id="telefono_empresa" name="telefono_empresa" value="<?php echo htmlspecialchars($_POST['telefono_empresa'] ?? ''); ?>">
                        </div>

                        <div class="mb-3">
                            <label for="sitio_web" class="form-label">Sitio web (opcional)</label>
                            <input type="url" class="form-control" id="sitio_web" name="sitio_web" value="<?php echo htmlspecialchars($_POST['sitio_web'] ?? ''); ?>">
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 mt-4">Registrarse</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    // Selección de tipo de usuario con tarjetas interactivas
    const cardCandidato = document.getElementById('card_candidato');
    const cardEmpresa = document.getElementById('card_empresa');
    const inputCandidato = document.getElementById('tipo_candidato');
    const inputEmpresa = document.getElementById('tipo_empresa');

    cardCandidato.addEventListener('click', () => {
        inputCandidato.checked = true;
        cardCandidato.style.border = '2px solid #007bff';
        cardEmpresa.style.border = '2px solid transparent';
        document.getElementById('campos_candidato').style.display = 'block';
        document.getElementById('campos_empresa').style.display = 'none';
    });

    cardEmpresa.addEventListener('click', () => {
        inputEmpresa.checked = true;
        cardEmpresa.style.border = '2px solid #007bff';
        cardCandidato.style.border = '2px solid transparent';
        document.getElementById('campos_candidato').style.display = 'none';
        document.getElementById('campos_empresa').style.display = 'block';
    });
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>