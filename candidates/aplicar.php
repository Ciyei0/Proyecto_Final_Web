<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth_functions.php';


if (!isLoggedIn() || !isCandidate()) {
    header("Location: ../auth/login.php");
    exit;
}

$candidatoId = $_SESSION['usuario_id'];
$ofertaId = $_POST['oferta_id'] ?? null;
$mensaje = trim($_POST['mensaje'] ?? '');


if (!$ofertaId) {
    die("ID de oferta no proporcionado.");
}

// Obtener el CV más reciente del candidato
$stmt = $pdo->prepare("SELECT id FROM cvs WHERE candidato_id = ? ORDER BY fecha_actualizacion DESC LIMIT 1");
$stmt->execute([$candidatoId]);
$cv = $stmt->fetch();

if (!$cv) {
    // Redirigir si el usuario no tiene CV cargado
    header("Location: ../candidates/crear_cv.php?mensaje=Debes crear un CV antes de aplicar");
    exit;
}

$cvId = $cv['id'];

// Verificar si ya aplicó
$stmt = $pdo->prepare("SELECT id FROM aplicaciones WHERE oferta_id = ? AND candidato_id = ?");
$stmt->execute([$ofertaId, $candidatoId]);
if ($stmt->fetch()) {
    header("Location: ../candidates/aplicaciones.php?mensaje=Ya has aplicado a esta oferta");
    exit;
}

// Insertar aplicación
$stmt = $pdo->prepare("
    INSERT INTO aplicaciones (oferta_id, candidato_id, cv_id, mensaje)
    VALUES (?, ?, ?, ?)
");
$stmt->execute([$ofertaId, $candidatoId, $cvId, $mensaje]);

header("Location: ../candidates/aplicaciones.php?mensaje=Has aplicado exitosamente a la oferta");
exit;
?>
