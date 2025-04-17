<?php include '../includes/config.php'; ?>

<!DOCTYPE html>
<html lang="es">
<head>
    <!-- Header común -->
</head>
<body class="bg-gray-100">
    <div class="container mx-auto p-6 max-w-4xl">
        <h1 class="text-3xl font-bold mb-6">Registro de Candidato</h1>
        
        <form action="procesar_registro.php" method="POST" enctype="multipart/form-data" class="bg-white p-6 rounded-lg shadow-md">
            <!-- Sección de información básica -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-gray-700 mb-2">Nombres</label>
                    <input type="text" name="nombres" required class="w-full p-2 border rounded">
                </div>
                
                <div>
                    <label class="block text-gray-700 mb-2">Apellidos</label>
                    <input type="text" name="apellidos" required class="w-full p-2 border rounded">
                </div>
                
                <!-- Repetir para todos los campos requeridos -->
                
                <div class="col-span-2">
                    <label class="block text-gray-700 mb-2">Objetivo Profesional</label>
                    <textarea name="objetivo" rows="3" class="w-full p-2 border rounded"></textarea>
                </div>
            </div>
            
            <!-- Sección de archivos -->
            <div class="mt-6 border-t pt-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-gray-700 mb-2">Foto de perfil (opcional)</label>
                        <input type="file" name="foto" accept="image/*" class="w-full">
                    </div>
                    
                    <div>
                        <label class="block text-gray-700 mb-2">CV en PDF (opcional)</label>
                        <input type="file" name="cv_pdf" accept=".pdf" class="w-full">
                    </div>
                </div>
            </div>
            
            <button type="submit" class="mt-6 bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700 transition">
                Completar Registro
            </button>
        </form>
    </div>
</body>
</html>