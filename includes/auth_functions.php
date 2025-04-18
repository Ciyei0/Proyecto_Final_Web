<?php
require_once __DIR__ . '/config.php';

// Función para registrar un usuario
function registerUser($nombre, $email, $password, $tipo) {
    global $pdo;
    
    // Verificar si el email ya existe
    $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        return false; // Email ya registrado
    }
    
    // Hash de la contraseña
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
    // Insertar usuario
    $stmt = $pdo->prepare("INSERT INTO usuarios (nombre, email, password, tipo) VALUES (?, ?, ?, ?)");
    $stmt->execute([$nombre, $email, $hashedPassword, $tipo]);
    
    return $pdo->lastInsertId();
}

// Función para registrar un candidato
function registerCandidate($usuarioId, $telefono, $direccion, $ciudad, $provincia) {
    global $pdo;
    
    $stmt = $pdo->prepare("INSERT INTO candidatos (usuario_id, telefono, direccion, ciudad, provincia) 
                          VALUES (?, ?, ?, ?, ?)");
    return $stmt->execute([$usuarioId, $telefono, $direccion, $ciudad, $provincia]);
}

// Función para registrar una empresa
function registerCompany($usuarioId, $nombreEmpresa, $descripcion, $direccion, $telefono, $sitioWeb) {
    global $pdo;
    
    $stmt = $pdo->prepare("INSERT INTO empresas (usuario_id, nombre_empresa, descripcion, direccion, telefono, sitio_web) 
                          VALUES (?, ?, ?, ?, ?, ?)");
    return $stmt->execute([$usuarioId, $nombreEmpresa, $descripcion, $direccion, $telefono, $sitioWeb]);
}

// Función para login
function login($email, $password) {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT id, nombre, email, password, tipo FROM usuarios WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['usuario_id'] = $user['id'];
        $_SESSION['usuario_nombre'] = $user['nombre'];
        $_SESSION['usuario_tipo'] = $user['tipo'];
        return true;
    }
    
    return false;
}

// Función para logout
function logout() {
    session_unset();
    session_destroy();
}