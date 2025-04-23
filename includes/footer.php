</div> <!-- Cierre del container -->
    <footer class="mt-5">
        <div class="footer-top py-5" style="background: linear-gradient(135deg, #182848 0%, #4b6cb7 100%);">
            <div class="container">
                <div class="row g-4">
                    <div class="col-lg-5 col-md-6">
                        <h4 class="fw-bold text-white mb-3"><?php echo SITE_NAME; ?> <i class="bi bi-briefcase-fill text-warning"></i></h4>
                        <p class="text-white-50 mb-4">Conectamos el talento con las mejores oportunidades laborales. Nuestra plataforma facilita el encuentro entre candidatos calificados y empresas innovadoras.</p>
                        <div class="d-flex gap-3 mb-3">
                            <a href="#" class="btn btn-sm btn-outline-light rounded-circle" title="Facebook">
                                <i class="bi bi-facebook"></i>
                            </a>
                            <a href="#" class="btn btn-sm btn-outline-light rounded-circle" title="LinkedIn">
                                <i class="bi bi-linkedin"></i>
                            </a>
 
                            <a href="#" class="btn btn-sm btn-outline-light rounded-circle" title="Instagram">
                                <i class="bi bi-instagram"></i>
                            </a>
                        </div>
                    </div>
                    <div class="col-lg-2 col-md-6">
   
                    </div>
                    <div class="col-lg-2 col-md-6">

                    </div>
                    <div class="col-lg-3 col-md-6">
                        <h5 class="text-white mb-3">Contacto</h5>
                        <ul class="list-unstyled footer-contact">
                            <li class="d-flex mb-2">
                                <i class="bi bi-geo-alt-fill text-warning me-2 mt-1"></i>
                                <span class="text-white-50">Av. Principal 123, Ciudad Empresarial</span>
                            </li>
                            <li class="d-flex mb-2">
                                <i class="bi bi-envelope-fill text-warning me-2 mt-1"></i>
                                <a href="mailto:info@plataformaempleos.com" class="text-white-50 text-decoration-none">info@plataformaempleos.com</a>
                            </li>
                            <li class="d-flex mb-2">
                                <i class="bi bi-telephone-fill text-warning me-2 mt-1"></i>
                                <a href="tel:+123456789" class="text-white-50 text-decoration-none">+123 456 789</a>
                            </li>
    
                            
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <div class="footer-bottom py-3" style="background-color: #14213d;">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-md-6 text-center text-md-start">
                        <p class="text-white-50 mb-0">&copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?>. Todos los derechos reservados.</p>
                    </div>

        </div>
    </footer>

    <style>
        /* Estilos adicionales para el footer */
        footer {
            box-shadow: 0 -4px 10px rgba(0, 0, 0, 0.1);
        }
        
        .footer-links a, .footer-contact a {
            transition: all 0.3s ease;
        }
        
        .footer-links a:hover {
            color: #ffffff !important;
            transform: translateX(5px);
        }
        
        .footer-contact a:hover {
            color: #ffffff !important;
        }
        
        .btn-outline-light.rounded-circle {
            width: 38px;
            height: 38px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
        }
        
        .btn-outline-light.rounded-circle:hover {
            background-color: #ffd700;
            border-color: #ffd700;
            color: #182848;
            transform: translateY(-3px);
        }
        
        .footer-top h4, .footer-top h5 {
            position: relative;
            padding-bottom: 10px;
        }
        
        .footer-top h4::after, .footer-top h5::after {
            content: '';
            position: absolute;
            left: 0;
            bottom: 0;
            width: 50px;
            height: 2px;
            background-color: #ffd700;
        }
    </style>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?php echo BASE_URL; ?>/assets/js/main.js"></script>
</body>
</html>