<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth_functions.php';

if (!isLoggedIn() || !isCompany()) {
    redirect(BASE_URL . '/auth/login.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $candidato_id = $_POST['candidato_id'] ?? null;
    $oferta_id    = $_POST['oferta_id'] ?? null;
    $nuevo_estado = $_POST['estado'] ?? null;

    // Validar campos obligatorios
    if (!$candidato_id || !$oferta_id || !$nuevo_estado) {
        $_SESSION['error'] = "Faltan datos obligatorios.";
        redirect(BASE_URL . "/empresas/ofertas.php");
    }

    // Validar estado permitido
    $estados_validos = ['pendiente', 'revisado', 'seleccionado', 'rechazado'];
    if (!in_array($nuevo_estado, $estados_validos)) {
        $_SESSION['error'] = "Estado no válido.";
        redirect(BASE_URL . "/empresas/ofertas.php");
    }

    // Verifica que la aplicación existe
    $stmt = $pdo->prepare("SELECT id FROM aplicaciones WHERE candidato_id = ? AND oferta_id = ?");
    $stmt->execute([$candidato_id, $oferta_id]);
    $aplicacion = $stmt->fetch();

    if (!$aplicacion) {
        $_SESSION['error'] = "Este candidato no aplicó a esta oferta.";
        redirect(BASE_URL . "/empresas/ofertas.php");
    }

    // Actualiza el estado
    $stmt = $pdo->prepare("UPDATE aplicaciones SET estado = ? WHERE candidato_id = ? AND oferta_id = ?");
    $stmt->execute([$nuevo_estado, $candidato_id, $oferta_id]);

    $_SESSION['success'] = "El estado del candidato ha sido actualizado a \"$nuevo_estado\".";
    redirect(BASE_URL . "/empresas/ver_candidato.php?candidato_id=$candidato_id&oferta_id=$oferta_id");
} else {
    $_SESSION['error'] = "Acceso no permitido.";
    redirect(BASE_URL . "/empresas/ofertas.php");
}
