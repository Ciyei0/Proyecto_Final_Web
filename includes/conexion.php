<?php
session_start();

// Configuración de la base de datos
define('DB_HOST', 'localhost');
define('DB_NAME', 'trabajo_rd');
define('DB_USER', 'root');
define('DB_PASS', '');

// Configuración de rutas
define('BASE_URL', 'http://localhost/trabajo_rd');
define('UPLOAD_DIR', $_SERVER['DOCUMENT_ROOT'].'/trabajo_rd/assets/uploads/');

// Conexión a MySQL
try {
    $pdo = new PDO(
        "mysql:host=".DB_HOST.";dbname=".DB_NAME.";charset=utf8",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
} catch(PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}

// Función de sanitización
function limpiarDatos($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

// Verificar autenticación
function verificarAutenticacion($rolRequerido = null) {
    if(!isset($_SESSION['usuario_id'])) {
        header('Location: /auth/login.php');
        exit;
    }
    
    if($rolRequerido && $_SESSION['rol'] !== $rolRequerido) {
        header('HTTP/1.0 403 Forbidden');
        exit('Acceso no autorizado');
    }
}
?>