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

<style>
    body {
        background: #f8f9fa;
    }
    
    .login-container {
        padding: 40px 0;
    }
    
    .login-card {
        border-radius: 15px;
        overflow: hidden;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        border: none;
    }
    
    .login-header {
        background: linear-gradient(135deg, #4a89dc, #5e72e4);
        color: white;
        padding: 25px;
        text-align: center;
    }
    

    
    .login-icon:hover {
        transform: scale(1.05);
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
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
    
    .alert-danger {
        border-radius: 8px;
        background-color: #fff5f7;
        border-left: 4px solid #dc3545;
        color: #dc3545;
        padding: 12px 15px;
    }
    
    .register-link {
        margin-top: 25px;
        text-align: center;
    }
    
    .register-link a {
        color: #4a89dc;
        font-weight: 600;
        text-decoration: none;
        transition: all 0.2s;
    }
    
    .register-link a:hover {
        color: #5e72e4;
        text-decoration: underline;
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
        .col-md-6 {
            width: 90%;
            margin: 0 auto;
        }
    }
</style>
<div class="row justify-content-center align-items-center vh-100 login-container">
    <div class="col-md-6">
        <div class="card shadow-lg border-0 login-card fade-in">
            <div class="login-header">
                <div class="login-icon">
                    <i class="fas fa-user"></i>
                </div>
                <h2 class="mb-0">Iniciar Sesión</h2>
                <p class="mt-2">Por favor, introduce tus credenciales para acceder a tu cuenta.</p>
            </div>
            <div class="card-body p-4" style="background-color: #fff; color: #000;">
                <?php if ($error): ?>
                    <div class="alert alert-danger text-center">
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <form method="post" class="needs-validation" novalidate>
                    <div class="form-group mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" required value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                        <span class="input-icon">
                            <i class="fas fa-envelope"></i>
                        </span>
                    </div>

                    <div class="form-group mb-3">
                        <label for="password" class="form-label">Contraseña</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                        <span class="input-icon">
                            <i class="fas fa-lock"></i>
                        </span>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">Iniciar Sesión</button>
                </form>

                <div class="register-link">
                    <p>¿No tienes una cuenta? <a href="<?php echo BASE_URL; ?>/auth/register.php">Regístrate aquí</a></p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>