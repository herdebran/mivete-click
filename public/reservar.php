<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../src/Controllers/AppointmentController.php';
require_once __DIR__ . '/../src/Models/Professional.php';

$vetId = $_GET['vet_id'] ?? null;
$fecha = $_GET['fecha'] ?? date('Y-m-d');
$tipo = $_GET['tipo'] ?? 'proximo';

$database = new Database();
$db = $database->getConnection();
$professionalModel = new Professional($db);

$vet = null;
if ($vetId) {
    $vet = $professionalModel->getById($vetId);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Completa tus Datos - Vete a un Click</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        .input-field { transition: all 0.3s ease; }
        .input-field:focus { border-color: #7c3aed; ring: 4px; ring-color: #7c3aed20; }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">
    
    <!-- Header -->
    <nav class="bg-white shadow-sm sticky top-0 z-50">
        <div class="max-w-4xl mx-auto px-4 py-4 flex justify-between items-center">
            <a href="index.php" class="flex items-center gap-3">
                <div class="w-10 h-10 bg-gradient-to-br from-purple-500 to-indigo-600 rounded-xl flex items-center justify-center">
                    <i data-lucide="stethoscope" class="text-white w-6 h-6"></i>
                </div>
                <span class="font-bold text-lg bg-gradient-to-r from-purple-600 to-indigo-600 bg-clip-text text-transparent">
                    Vete a un Click
                </span>
            </a>
        </div>
    </nav>

    <!-- Progress Indicator -->
    <div class="max-w-4xl mx-auto px-4 pt-8">
        <div class="flex items-center gap-4 mb-8">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 bg-green-500 text-white rounded-full flex items-center justify-center font-bold text-sm">
                    <i data-lucide="check" class="w-4 h-4"></i>
                </div>
                <span class="text-green-600 font-medium">Cuándo</span>
            </div>
            <div class="w-12 h-0.5 bg-green-500"></div>
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 bg-green-500 text-white rounded-full flex items-center justify-center font-bold text-sm">
                    <i data-lucide="check" class="w-4 h-4"></i>
                </div>
                <span class="text-green-600 font-medium">Quién</span>
            </div>
            <div class="w-12 h-0.5 bg-purple-600"></div>
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 bg-purple-600 text-white rounded-full flex items-center justify-center font-bold text-sm">3</div>
                <span class="text-purple-600 font-medium">Tus Datos</span>
            </div>
        </div>
    </div>

    <div class="max-w-2xl mx-auto px-4 py-8">
        
        <!-- Info del Veterinario -->
        <?php if ($vet): ?>
        <div class="bg-gradient-to-br from-purple-50 to-indigo-50 rounded-3xl p-6 mb-8 border border-purple-100">
            <div class="flex items-center gap-4">
                <div class="w-16 h-16 bg-gradient-to-br from-purple-400 to-indigo-500 rounded-2xl flex items-center justify-center flex-shrink-0">
                    <i data-lucide="user-circle" class="text-white w-8 h-8"></i>
                </div>
                <div>
                    <h2 class="font-bold text-lg text-gray-800">
                        Dr./Dra. <?php echo htmlspecialchars($vet['nombre'] . ' ' . $vet['apellido']); ?>
                    </h2>
                    <p class="text-sm text-gray-500 flex items-center gap-1">
                        <i data-lucide="badge-check" class="w-4 h-4"></i>
                        Matrícula: <?php echo htmlspecialchars($vet['matricula']); ?>
                    </p>
                    <p class="text-sm text-purple-600 font-medium flex items-center gap-1 mt-1">
                        <i data-lucide="video" class="w-4 h-4"></i>
                        Consulta Online • 30 minutos
                    </p>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Formulario -->
        <div class="bg-white rounded-3xl shadow-lg p-8 border border-gray-100 mb-8">
            <h1 class="text-2xl font-bold text-gray-800 mb-2">Completa tus datos</h1>
            <p class="text-gray-600 mb-8">Necesitamos esta información para crear tu turno</p>
            
            <form action="pago_simulado.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="vet_id" value="<?php echo $vetId ?? ''; ?>">
                <input type="hidden" name="fecha" value="<?php echo $fecha; ?>">
                <input type="hidden" name="tipo_busqueda" value="<?php echo $tipo; ?>">
                
                <div class="space-y-5">
                    <!-- Nombre -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2 flex items-center gap-2">
                            <i data-lucide="user" class="w-4 h-4 text-purple-500"></i>
                            Tu Nombre Completo
                        </label>
                        <input type="text" name="cliente_nombre" required 
                               class="input-field w-full p-4 border-2 border-gray-200 rounded-xl focus:border-purple-500 focus:ring-4 focus:ring-purple-100 transition"
                               placeholder="Ej: Juan Pérez">
                    </div>

                    <!-- Email -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2 flex items-center gap-2">
                            <i data-lucide="mail" class="w-4 h-4 text-purple-500"></i>
                            Tu Email
                        </label>
                        <input type="email" name="cliente_email" required 
                               class="input-field w-full p-4 border-2 border-gray-200 rounded-xl focus:border-purple-500 focus:ring-4 focus:ring-purple-100 transition"
                               placeholder="juan@email.com">
                    </div>

                    <!-- Teléfono -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2 flex items-center gap-2">
                            <i data-lucide="phone" class="w-4 h-4 text-purple-500"></i>
                            Tu Teléfono
                        </label>
                        <input type="tel" name="cliente_telefono" required 
                               class="input-field w-full p-4 border-2 border-gray-200 rounded-xl focus:border-purple-500 focus:ring-4 focus:ring-purple-100 transition"
                               placeholder="Ej: 11 1234 5678">
                    </div>

                    <!-- Mascota -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2 flex items-center gap-2">
                            <i data-lucide="heart" class="w-4 h-4 text-purple-500"></i>
                            Nombre de tu Mascota
                        </label>
                        <input type="text" name="cliente_mascota_nombre" required 
                               class="input-field w-full p-4 border-2 border-gray-200 rounded-xl focus:border-purple-500 focus:ring-4 focus:ring-purple-100 transition"
                               placeholder="Ej: Firulais">
                    </div>

                    <!-- Motivo -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2 flex items-center gap-2">
                            <i data-lucide="file-text" class="w-4 h-4 text-purple-500"></i>
                            Motivo de Consulta
                        </label>
                        <textarea name="motivo_consulta" rows="4" required 
                                  class="input-field w-full p-4 border-2 border-gray-200 rounded-xl focus:border-purple-500 focus:ring-4 focus:ring-purple-100 transition resize-none"
                                  placeholder="Contanos brevemente qué le pasa a tu mascota..."></textarea>
                    </div>

                    <!-- Adjuntos -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2 flex items-center gap-2">
                            <i data-lucide="paperclip" class="w-4 h-4 text-purple-500"></i>
                            Adjuntar Estudios (Opcional)
                        </label>
                        <div class="border-2 border-dashed border-gray-200 rounded-xl p-6 text-center hover:border-purple-400 transition">
                            <i data-lucide="upload-cloud" class="w-10 h-10 text-gray-400 mx-auto mb-3"></i>
                            <p class="text-sm text-gray-600 mb-2">Arrastrá archivos aquí o hacé clic para seleccionar</p>
                            <p class="text-xs text-gray-400">PDF, JPG, PNG. Máximo 5MB por archivo</p>
                            <input type="file" name="adjuntos[]" multiple accept=".pdf,.jpg,.jpeg,.png" 
                                   class="mt-3 text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-purple-50 file:text-purple-700 hover:file:bg-purple-100">
                        </div>
                    </div>
                </div>

                <!-- Info Box -->
                <div class="mt-8 bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-100 rounded-2xl p-5">
                    <div class="flex items-start gap-3">
                        <i data-lucide="info" class="w-5 h-5 text-blue-600 flex-shrink-0 mt-0.5"></i>
                        <div>
                            <p class="text-sm font-medium text-blue-800 mb-1">Próximo paso: Pago seguro</p>
                            <p class="text-sm text-blue-600">Después de completar este formulario, serás redirigido al pago. Una vez aprobado, podrás seleccionar tu horario exacto.</p>
                        </div>
                    </div>
                </div>

                <!-- Submit -->
                <button type="submit" 
                        class="w-full mt-6 bg-gradient-to-r from-purple-600 to-indigo-600 text-white p-4 rounded-xl font-bold text-lg hover:from-purple-700 hover:to-indigo-700 transition shadow-lg hover:shadow-xl flex items-center justify-center gap-2">
                    Continuar al Pago
                    <i data-lucide="arrow-right" class="w-5 h-5"></i>
                </button>
            </form>
        </div>

        <div class="text-center">
            <a href="ver_veterinarios.php?tipo=<?php echo $tipo; ?>&fecha=<?php echo $fecha; ?>" 
               class="text-gray-500 hover:text-gray-700 text-sm flex items-center justify-center gap-2">
                <i data-lucide="arrow-left" class="w-4 h-4"></i>
                Volver a veterinarios
            </a>
        </div>
    </div>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>