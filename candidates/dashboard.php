<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth_functions.php';

if (!isLoggedIn() || !isCandidate()) {
    redirect(BASE_URL . '/auth/login.php');
}
require_once __DIR__ . '/../includes/header.php';
$pageTitle = "Panel del Candidato";

$user = getCurrentUser();
$candidateId = $user['id'];

// Obtener información del candidato
$stmt = $pdo->prepare("SELECT * FROM candidatos WHERE usuario_id = ?");
$stmt->execute([$candidateId]);
$candidate = $stmt->fetch();

// Obtener CV del candidato
$stmt = $pdo->prepare("SELECT * FROM cvs WHERE candidato_id = ?");
$stmt->execute([$candidateId]);
$cv = $stmt->fetch();

// Obtener aplicaciones del candidato
$stmt = $pdo->prepare("
    SELECT a.*, o.titulo, o.empresa_id, e.nombre_empresa 
    FROM aplicaciones a
    JOIN ofertas_empleo o ON a.oferta_id = o.id
    JOIN empresas e ON o.empresa_id = e.usuario_id
    WHERE a.candidato_id = ?
    ORDER BY a.fecha_aplicacion DESC
");
$stmt->execute([$candidateId]);
$applications = $stmt->fetchAll();
?>
    
    <style>
    body {
        background: #f8f9fa;
    }
    
    .dashboard-container {
        padding: 40px 0;
    }
    
    .profile-card, .cv-card, .applications-card {
        border-radius: 15px;
        overflow: hidden;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        border: none;
        transition: all 0.3s ease;
    }
    
    .profile-card:hover, .cv-card:hover, .applications-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15);
    }
    
    .card-header {
        background: linear-gradient(135deg, #4a89dc, #5e72e4);
        color: white;
        padding: 20px;
        border-bottom: none;
    }
    
    .profile-image-container {
        width: 150px;
        height: 150px;
        position: relative;
        margin: 0 auto 20px;
        border-radius: 50%;
        border: 5px solid #fff;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        overflow: hidden;
        transition: all 0.3s ease;
    }
    
    .profile-image-container:hover {
        transform: scale(1.05);
        border-color: #4a89dc;
    }
    
    .profile-image {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    
    .avatar-placeholder {
        width: 150px;
        height: 150px;
        border-radius: 50%;
        background: linear-gradient(135deg, #e0e7ff 0%, #c7d2fe 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 20px;
        border: 5px solid #fff;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        transition: all 0.3s ease;
    }
    
    .avatar-placeholder:hover {
        transform: scale(1.05);
        border-color: #4a89dc;
    }
    
    .user-info {
        margin-bottom: 25px;
    }
    
    .user-info h4 {
        font-weight: 700;
        color: #2d3748;
        margin-bottom: 5px;
    }
    
    .user-info p {
        color: #718096;
        margin-bottom: 15px;
    }
    
    .contact-info {
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 10px;
        color: #4a5568;
    }
    
    .contact-info i {
        color: #4a89dc;
        margin-right: 10px;
        width: 20px;
        text-align: center;
    }
    
    .btn-edit-profile {
        background: linear-gradient(135deg, #4a89dc, #5e72e4);
        border: none;
        border-radius: 8px;
        padding: 10px 15px;
        font-weight: 600;
        transition: all 0.3s ease;
        letter-spacing: 0.5px;
    }
    
    .btn-edit-profile:hover {
        background: linear-gradient(135deg, #5e72e4, #4a89dc);
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(94, 114, 228, 0.4);
    }
    
    .btn-update-cv {
        background-color: #fff;
        color: #4a89dc;
        border: none;
        border-radius: 5px;
        font-weight: 600;
        transition: all 0.3s ease;
    }
    
    .btn-update-cv:hover {
        background-color: #e6eeff;
        transform: translateY(-2px);
    }
    
    .section-title {
        display: flex;
        align-items: center;
        font-weight: 700;
        color: #2d3748;
        margin-bottom: 15px;
    }
    
    .section-title i {
        color: #4a89dc;
        margin-right: 10px;
    }
    
    .skill-badge {
        background: linear-gradient(135deg, #4a89dc, #5e72e4);
        color: white;
        border-radius: 20px;
        padding: 6px 12px;
        margin: 3px;
        font-size: 0.85rem;
        font-weight: 500;
        transition: all 0.3s ease;
    }
    
    .skill-badge:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 10px rgba(94, 114, 228, 0.2);
    }
    
    .btn-view-pdf {
        background-color: transparent;
        color: #4a89dc;
        border: 1px solid #4a89dc;
        border-radius: 8px;
        padding: 8px 16px;
        transition: all 0.3s ease;
        font-weight: 600;
    }
    
    .btn-view-pdf:hover {
        background-color: #4a89dc;
        color: white;
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(94, 114, 228, 0.2);
    }
    
    .table {
        margin-bottom: 0;
    }
    
    .table thead th {
        background-color: #f8f9fa;
        color: #4a5568;
        font-weight: 600;
        border-top: none;
        padding: 12px;
    }
    
    .table td {
        padding: 12px;
        vertical-align: middle;
    }
    
    .status-badge {
        padding: 5px 10px;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .status-pending {
        background-color: #fef3c7;
        color: #92400e;
    }
    
    .status-reviewed {
        background-color: #dbeafe;
        color: #1e40af;
    }
    
    .status-selected {
        background-color: #d1fae5;
        color: #065f46;
    }
    
    .status-rejected {
        background-color: #fee2e2;
        color: #b91c1c;
    }
    
    .btn-view-offer {
        border: 1px solid #4a89dc;
        color: #4a89dc;
        background-color: transparent;
        border-radius: 5px;
        padding: 5px 10px;
        font-size: 0.8rem;
        font-weight: 600;
        transition: all 0.3s ease;
    }
    
    .btn-view-offer:hover {
        background-color: #4a89dc;
        color: white;
    }
    
    .alert {
        border-radius: 10px;
        padding: 15px;
    }
    
    .alert-warning {
        background-color: #fff7ed;
        border-left: 4px solid #f59e0b;
        color: #b45309;
    }
    
    .alert-info {
        background-color: #eff6ff;
        border-left: 4px solid #3b82f6;
        color: #1e40af;
    }
    
    .alert a {
        color: inherit;
        font-weight: 600;
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
    @media (max-width: 992px) {
        .dashboard-container {
            padding: 20px 0;
        }
        
        .profile-card, .cv-card, .applications-card {
            margin-bottom: 20px;
        }
    }
</style>
<div class="container dashboard-container">
    <div class="row">
        <!-- Perfil del candidato -->
        <div class="col-lg-4">
            <div class="card profile-card fade-in mb-4">
                <div class="card-header text-center">
                    <h5 class="mb-0">Mi Perfil</h5>
                </div>
                <div class="card-body text-center">
                    <div class="mb-4">
                        <?php if ($candidate && $candidate['foto_perfil']): ?>
                            <div class="profile-image-container">
                                <img src="<?php echo BASE_URL . '/assets/uploads/photos/' . htmlspecialchars($candidate['foto_perfil']); ?>" 
                                class="profile-image">
                            </div>
                        <?php else: ?>
                            <div class="avatar-placeholder">
                                <i class="fas fa-user fa-3x text-secondary"></i>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="user-info">
                        <h4><?php echo htmlspecialchars($user['nombre']); ?></h4>
                        <p><?php echo htmlspecialchars($user['email']); ?></p>
                    </div>
                    
                    <?php if ($candidate): ?>
                        <div class="contact-info">
                            <i class="fas fa-phone"></i>
                            <span><?php echo htmlspecialchars($candidate['telefono'] ?? 'No especificado'); ?></span>
                        </div>
                        <div class="contact-info">
                            <i class="fas fa-map-marker-alt"></i>
                            <span>
                                <?php 
                                $location = [];
                                if ($candidate['ciudad']) $location[] = $candidate['ciudad'];
                                if ($candidate['provincia']) $location[] = $candidate['provincia'];
                                echo $location ? htmlspecialchars(implode(', ', $location)) : 'Ubicación no especificada';
                                ?>
                            </span>
                        </div>
                    <?php endif; ?>
                    
                    <a href="perfil.php" class="btn btn-edit-profile mt-4 w-100">
                        <i class="fas fa-user-edit me-2"></i> Editar Perfil
                    </a>
                </div>
            </div>
        </div>
        
        <!-- CV y postulaciones -->
        <div class="col-lg-8">
            <!-- CV -->
            <div class="card cv-card fade-in mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Mi CV</h5>
                    <a href="perfil.php" class="btn btn-update-cv">
                        <i class="fas fa-sync-alt me-1"></i> <?php echo $cv ? 'Actualizar CV' : 'Crear CV'; ?>
                    </a>
                </div>
                <div class="card-body">
                    <?php if ($cv): ?>
                        <h6 class="section-title">
                            <i class="fas fa-bullseye"></i> Objetivo Profesional
                        </h6>
                        <p class="text-gray-600"><?php echo $cv['objetivo_profesional'] ? nl2br(htmlspecialchars($cv['objetivo_profesional'])) : 'No especificado'; ?></p>
                        
                        <h6 class="section-title mt-4">
                            <i class="fas fa-tools"></i> Habilidades Clave
                        </h6>
                        <div class="d-flex flex-wrap gap-2">
                            <?php 
                            $skills = explode(',', $cv['habilidades_clave'] ?? '');
                            foreach ($skills as $skill): 
                                if(trim($skill) !== ""):
                            ?>
                                <span class="skill-badge"><?php echo htmlspecialchars(trim($skill)); ?></span>
                            <?php 
                                endif;
                            endforeach; 
                            ?>
                        </div>
                        
                        <?php if ($cv['cv_pdf']): ?>
                            <a href="<?php echo BASE_URL . '/assets/cv/' . htmlspecialchars($cv['cv_pdf']); ?>" 
                               target="_blank" class="btn btn-view-pdf mt-4">
                                <i class="fas fa-file-pdf me-2"></i> Ver CV en PDF
                            </a>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="alert alert-warning">
                            Aún no has creado tu CV digital. Completa tu perfil para aumentar tus oportunidades de empleo.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Postulaciones -->
            <div class="card applications-card fade-in">
                <div class="card-header">
                    <h5 class="mb-0">Mis Postulaciones</h5>
                </div>
                <div class="card-body">
                    <?php if ($applications): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Oferta</th>
                                        <th>Empresa</th>
                                        <th>Fecha</th>
                                        <th>Estado</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($applications as $app): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($app['titulo']); ?></td>
                                            <td><?php echo htmlspecialchars($app['nombre_empresa']); ?></td>
                                            <td><?php echo date('d/m/Y', strtotime($app['fecha_aplicacion'])); ?></td>
                                            <td>
                                                <span class="status-badge 
                                                    <?php echo $app['estado'] == 'pendiente' ? 'status-pending' : 
                                                          ($app['estado'] == 'revisado' ? 'status-reviewed' : 
                                                          ($app['estado'] == 'seleccionado' ? 'status-selected' : 'status-rejected')); ?>">
                                                    <?php echo ucfirst($app['estado']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <a href="<?php echo BASE_URL; ?>/ofertas/ver.php?id=<?php echo $app['oferta_id']; ?>" 
                                                   class="btn btn-view-offer">
                                                    Ver Oferta
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info">
                            Aún no te has postulado a ninguna oferta. <a href="<?php echo BASE_URL; ?>/ofertas/">Explora las ofertas disponibles</a>.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>