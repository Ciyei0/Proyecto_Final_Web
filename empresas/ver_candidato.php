<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth_functions.php';

if (!isLoggedIn() || !isCompany()) {
    redirect(BASE_URL . '/auth/login.php');
}

$pageTitle = "Perfil del Candidato";
require_once __DIR__ . '/../includes/header.php';

$candidatoId = $_GET['candidato_id'] ?? null;
$ofertaId    = $_GET['oferta_id']    ?? null;

if (!$candidatoId || !$ofertaId) {
    echo "<div class='alert alert-danger'>Faltan parámetros requeridos.</div>";
    require_once __DIR__ . '/../includes/footer.php';
    exit;
}

// 1) Validar que el candidato aplicó a esta oferta
$stmt = $pdo->prepare("
    SELECT a.id
    FROM aplicaciones a
    WHERE a.candidato_id = ? AND a.oferta_id = ?
");
$stmt->execute([$candidatoId, $ofertaId]);
if (!$stmt->fetch()) {
    echo "<div class='alert alert-danger'>Candidato no encontrado o no ha aplicado a esta oferta.</div>";
    require_once __DIR__ . '/../includes/footer.php';
    exit;
}

// 2) Traer datos básicos de usuario, candidato y CV
$stmt = $pdo->prepare("
    SELECT 
      u.nombre,
      u.email,
      cand.telefono,
      cand.direccion,
      cand.ciudad,
      cand.provincia,
      cand.foto_perfil,
      cv.objetivo_profesional,
      cv.habilidades_clave,
      cv.disponibilidad,
      cv.linkedin,
      cv.github,
      cv.otras_redes,
      cv.referencias,
      cv.cv_pdf
    FROM usuarios u
    LEFT JOIN candidatos cand ON u.id = cand.usuario_id
    LEFT JOIN cvs cv         ON cand.usuario_id = cv.candidato_id
    WHERE u.id = ?
    ORDER BY cv.fecha_actualizacion DESC
    LIMIT 1
");
$stmt->execute([$candidatoId]);
$c = $stmt->fetch();

if (!$c) {
    echo "<div class='alert alert-warning'>Este candidato aún no tiene datos de CV.</div>";
    require_once __DIR__ . '/../includes/footer.php';
    exit;
}
?>

<div class="card mx-auto mb-4" style="max-width: 800px;">
  <div class="card-header d-flex align-items-center gap-3">
    <?php if ($c['foto_perfil']): ?>
      <img src="<?php echo BASE_URL . '/assets/photos/' . htmlspecialchars($c['foto_perfil']); ?>"
           class="rounded-circle" width="80" height="80" style="object-fit: cover;">
    <?php else: ?>
      <div class="rounded-circle bg-light d-flex align-items-center justify-content-center"
           style="width: 80px; height: 80px;">
        <i class="fas fa-user fa-2x text-secondary"></i>
      </div>
    <?php endif; ?>
    <div>
      <h4 class="mb-0"><?php echo htmlspecialchars($c['nombre']); ?></h4>
      <p class="mb-0 text-muted"><?php echo htmlspecialchars($c['email']); ?></p>
      <?php if ($c['telefono']): ?>
        <p class="mb-0"><strong>Teléfono:</strong> <?php echo htmlspecialchars($c['telefono']); ?></p>
      <?php endif; ?>
      <?php if ($c['ciudad'] || $c['provincia']): ?>
        <p class="mb-0">
          <strong>Ubicación:</strong>
          <?php echo htmlspecialchars(trim("{$c['ciudad']} {$c['provincia']}")); ?>
        </p>
      <?php endif; ?>
    </div>
  </div>
  <div class="card-body">
    <?php if ($c['objetivo_profesional']): ?>
      <h5>Objetivo Profesional</h5>
      <p><?php echo nl2br(htmlspecialchars($c['objetivo_profesional'])); ?></p>
    <?php endif; ?>

    <?php if ($c['habilidades_clave']): ?>
      <h5>Habilidades Clave</h5>
      <p><?php echo nl2br(htmlspecialchars($c['habilidades_clave'])); ?></p>
    <?php endif; ?>

    <?php if ($c['disponibilidad']): ?>
      <h5>Disponibilidad</h5>
      <p><?php echo htmlspecialchars($c['disponibilidad']); ?></p>
    <?php endif; ?>

    <?php if ($c['linkedin'] || $c['github'] || $c['otras_redes']): ?>
      <h5>Redes / Enlaces</h5>
      <ul>
        <?php if ($c['linkedin']): ?>
          <li><a href="<?php echo htmlspecialchars($c['linkedin']); ?>" target="_blank">LinkedIn</a></li>
        <?php endif; ?>
        <?php if ($c['github']): ?>
          <li><a href="<?php echo htmlspecialchars($c['github']); ?>" target="_blank">GitHub</a></li>
        <?php endif; ?>
        <?php if ($c['otras_redes']): ?>
          <li><?php echo nl2br(htmlspecialchars($c['otras_redes'])); ?></li>
        <?php endif; ?>
      </ul>
    <?php endif; ?>

    <?php if ($c['referencias']): ?>
      <h5>Referencias</h5>
      <p><?php echo nl2br(htmlspecialchars($c['referencias'])); ?></p>
    <?php endif; ?>

    <?php if ($c['cv_pdf']): ?>
      <p>
        <a class="btn btn-outline-primary" 
           href="<?php echo BASE_URL . '/assets/cv/' . htmlspecialchars($c['cv_pdf']); ?>" 
           target="_blank">
          Ver CV en PDF
        </a>
      </p>
    <?php endif; ?>
  </div>
</div>

<?php
// 3) Traer formación académica
$stmt = $pdo->prepare("SELECT * FROM formacion_academica WHERE cv_id = ?");
$stmt->execute([ $c['cv_pdf'] ? $candidatoId : 0 ]); // si cv existe
$formaciones = $stmt->fetchAll();

if ($formaciones): ?>
  <div class="card mb-4 mx-auto" style="max-width: 800px;">
    <div class="card-header"><h5>Formación Académica</h5></div>
    <div class="card-body">
      <?php foreach ($formaciones as $f): ?>
        <div class="mb-3">
          <h6><?php echo htmlspecialchars($f['titulo']); ?> – <?php echo htmlspecialchars($f['institucion']); ?></h6>
          <small class="text-muted">
            <?php echo date('Y', strtotime($f['fecha_inicio'])); ?> –
            <?php echo $f['fecha_fin'] ? date('Y', strtotime($f['fecha_fin'])) : 'Actualidad'; ?>
          </small>
          <?php if ($f['descripcion']): ?>
            <p><?php echo nl2br(htmlspecialchars($f['descripcion'])); ?></p>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
<?php endif;

// 4) Traer experiencia laboral
$stmt = $pdo->prepare("SELECT * FROM experiencia_laboral WHERE cv_id = ?");
$stmt->execute([ $c['cv_pdf'] ? $candidatoId : 0 ]);
$experiencias = $stmt->fetchAll();

if ($experiencias): ?>
  <div class="card mb-4 mx-auto" style="max-width: 800px;">
    <div class="card-header"><h5>Experiencia Laboral</h5></div>
    <div class="card-body">
      <?php foreach ($experiencias as $e): ?>
        <div class="mb-3">
          <h6><?php echo htmlspecialchars($e['puesto']); ?> – <?php echo htmlspecialchars($e['empresa']); ?></h6>
          <small class="text-muted">
            <?php echo date('Y-m', strtotime($e['fecha_inicio'])); ?> –
            <?php echo $e['fecha_fin'] ? date('Y-m', strtotime($e['fecha_fin'])) : 'Actualidad'; ?>
          </small>
          <?php if ($e['descripcion']): ?>
            <p><?php echo nl2br(htmlspecialchars($e['descripcion'])); ?></p>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
<?php endif;

// 5) Traer idiomas
$stmt = $pdo->prepare("SELECT * FROM idiomas WHERE cv_id = ?");
$stmt->execute([ $c['cv_pdf'] ? $candidatoId : 0 ]);
$idiomas = $stmt->fetchAll();

if ($idiomas): ?>
  <div class="card mb-4 mx-auto" style="max-width: 800px;">
    <div class="card-header"><h5>Idiomas</h5></div>
    <div class="card-body">
      <ul>
        <?php foreach ($idiomas as $i): ?>
          <li><?php echo htmlspecialchars($i['idioma']); ?> – <?php echo htmlspecialchars($i['nivel']); ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
  </div>
<?php endif;

// 6) Traer proyectos / logros
$stmt = $pdo->prepare("SELECT * FROM proyectos_logros WHERE cv_id = ?");
$stmt->execute([ $c['cv_pdf'] ? $candidatoId : 0 ]);
$proyectos = $stmt->fetchAll();

if ($proyectos): ?>
  <div class="card mb-4 mx-auto" style="max-width: 800px;">
    <div class="card-header"><h5>Proyectos y Logros</h5></div>
    <div class="card-body">
      <?php foreach ($proyectos as $p): ?>
        <div class="mb-3">
          <h6><?php echo htmlspecialchars($p['titulo']); ?></h6>
          <?php if ($p['fecha']): ?>
            <small class="text-muted"><?php echo date('d/m/Y', strtotime($p['fecha'])); ?></small>
          <?php endif; ?>
          <?php if ($p['descripcion']): ?>
            <p><?php echo nl2br(htmlspecialchars($p['descripcion'])); ?></p>
          <?php endif; ?>
          <?php if ($p['enlace']): ?>
            <p><a href="<?php echo htmlspecialchars($p['enlace']); ?>" target="_blank">Ver más</a></p>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
<?php endif;

require_once __DIR__ . '/../includes/footer.php';
