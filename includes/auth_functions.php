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

// Función para registrar un candidato (actualizada)
function registerCandidate($usuarioId, $telefono, $direccion, $ciudad, $provincia, $fotoPerfil = null) {
    global $pdo;
    
    $stmt = $pdo->prepare("INSERT INTO candidatos (usuario_id, telefono, direccion, ciudad, provincia, foto_perfil) 
                          VALUES (?, ?, ?, ?, ?, ?)");
    return $stmt->execute([$usuarioId, $telefono, $direccion, $ciudad, $provincia, $fotoPerfil]);
}

// Función para actualizar la foto de perfil de un usuario
function updateUserPhoto($userId, $fotoPerfil) {
    global $pdo;
    
    // Actualizar en candidatos si es candidato
    $stmt = $pdo->prepare("UPDATE candidatos SET foto_perfil = ? WHERE usuario_id = ?");
    return $stmt->execute([$fotoPerfil, $userId]);
}

// Función para registrar una empresa
function registerCompany($usuarioId, $nombreEmpresa, $descripcion, $direccion, $telefono, $sitioWeb, $logo)  {
    global $pdo;
    
    $stmt = $pdo->prepare("INSERT INTO empresas (usuario_id, nombre_empresa, descripcion, direccion, telefono, sitio_web, logo) 
                          VALUES (?, ?, ?, ?, ?, ?, ?)");
    return $stmt->execute([$usuarioId, $nombreEmpresa, $descripcion, $direccion, $telefono, $sitioWeb, $logo]);
}

function updateCompanyLogo($userId, $logo) {
    global $pdo;
    
    $stmt = $pdo->prepare("UPDATE empresas SET logo = ? WHERE usuario_id = ?");
    return $stmt->execute([$logo, $userId]);
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