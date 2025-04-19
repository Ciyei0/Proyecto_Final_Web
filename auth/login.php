<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth_functions.php';

$pageTitle = "Iniciar Sesión";

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (login($email, $password)) {
        $user = getCurrentUser();
        redirect(BASE_URL . '/' . ($user['tipo'] === 'candidato' ? 'candidates/dashboard.php' : 'empresas/dashboard.php'));
    } else {
        $error = "Email o contraseña incorrectos";
    }
}
require_once __DIR__ . '/../includes/header.php';

?>

<div class="row justify-content-center align-items-center vh-100">
    <div class="col-md-6">
        <div class="card shadow-lg border-0">
            <div class="card-header text-white text-center" style="background: linear-gradient(90deg, #6610f2, #007bff);">
                <h2 class="mb-0">Iniciar Sesión</h2>
                <p class="mt-2 small">Por favor, introduce tus credenciales para acceder a tu cuenta.</p>
            </div>
            <div class="card-body p-4" style="background-color: #fff; color: #000;">
                <?php if ($error): ?>
                    <div class="alert alert-danger text-center">
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <form method="post" class="needs-validation" novalidate>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" required value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Contraseña</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">Iniciar Sesión</button>
                </form>

                <div class="mt-4 text-center">
                    <p>¿No tienes una cuenta? <a href="<?php echo BASE_URL; ?>/auth/register.php" class="text-primary fw-bold">Regístrate aquí</a></p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>