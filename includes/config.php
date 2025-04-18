<?php
session_start();

// Configuración de la base de datos
define('DB_HOST', 'localhost:3307');// Cambia el puerto si es necesario
define('DB_NAME', 'plataforma_empleos');
define('DB_USER', 'root');
define('DB_PASS', '');

// Conexión a la base de datos
try {
    $pdo = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME.";charset=utf8mb4", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}

// Configuración del sitio
define('SITE_NAME', 'Plataforma de Empleos');
define('BASE_URL', 'http://localhost/Proyecto_Final_Web');
define('UPLOAD_DIR', __DIR__ . '/../assets/uploads/');
define('CV_DIR', __DIR__ . '/../assets/cv/');
define('PHOTOS_DIR', __DIR__ . '/../assets/uploads/photos/');

// Función para redireccionar
function redirect($url) {
    header("Location: $url");
    exit();
}

// Función para verificar si el usuario está logueado
function isLoggedIn() {
    return isset($_SESSION['usuario_id']);
}

// Función para obtener el usuario actual
function getCurrentUser() {
    global $pdo;
    if (isLoggedIn()) {
        $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id = ?");
        $stmt->execute([$_SESSION['usuario_id']]);
        return $stmt->fetch();
    }
    return null;
}

// Función para verificar el tipo de usuario
function isCandidate() {
    $user = getCurrentUser();
    return $user && $user['tipo'] == 'candidato';
}

function isCompany() {
    $user = getCurrentUser();
    return $user && $user['tipo'] == 'empresa';
}