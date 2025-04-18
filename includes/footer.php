</div> <!-- Cierre del container -->
    <footer class="bg-dark text-white mt-5 py-4">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5><?php echo SITE_NAME; ?></h5>
                    <p>Plataforma de conexión entre candidatos y empresas.</p>
                </div>
                <div class="col-md-3">
                    <h5>Enlaces</h5>
                    <ul class="list-unstyled">
                        <li><a href="<?php echo BASE_URL; ?>/ofertas/" class="text-white">Ofertas de trabajo</a></li>
                        <li><a href="#" class="text-white">Términos y condiciones</a></li>
                    </ul>
                </div>
                <div class="col-md-3">
                    <h5>Contacto</h5>
                    <ul class="list-unstyled">
                        <li>Email: info@plataformaempleos.com</li>
                        <li>Teléfono: +123 456 789</li>
                    </ul>
                </div>
            </div>
            <hr>
            <div class="text-center">
                <p>&copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?>. Todos los derechos reservados.</p>
            </div>
        </div>
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?php echo BASE_URL; ?>/assets/js/main.js"></script>
</body>
</html>