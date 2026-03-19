<?php
session_start();
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../src/Controllers/AuthController.php';
require_once __DIR__ . '/../../src/Controllers/EmailController.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$database = new Database();
$db = $database->getConnection();
$turnoId = $_GET['id'] ?? null;
$mensaje = '';

// Obtener turno
$stmt = $db->prepare("SELECT a.*, p.nombre as vet_nombre, p.email as vet_email FROM appointments a JOIN professionals p ON a.professional_id = p.id WHERE a.id = ?");
$stmt->execute([$turnoId]);
$turno = $stmt->fetch();

if (!$turno || $turno['professional_id'] != $_SESSION['user_id']) {
    header("Location: dashboard.php");
    exit;
}

// Procesar completado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $diagnostico = $_POST['diagnostico'] ?? '';
    $recomendacion = $_POST['recomendacion'] ?? '';
    $enviarEmail = isset($_POST['enviar_email']);
    
    $stmt = $db->prepare("UPDATE appointments SET diagnostico = ?, recomendacion = ?, fecha_atencion = NOW(), estado = 'atendido' WHERE id = ?");
    $stmt->execute([$diagnostico, $recomendacion, $turnoId]);
    
    // Enviar email opcional al paciente
    if ($enviarEmail && !empty($turno['cliente_email'])) {
        require_once __DIR__ . '/../../src/Controllers/EmailController.php';
        $emailController = new EmailController();
        $emailController->enviarResumenConsulta($turno, $diagnostico, $recomendacion);
    }
    
    $mensaje = '✅ Turno marcado como atendido.';
    header("Location: dashboard.php?msg=atendido");
    exit;
}

// Obtener adjuntos
$stmt = $db->prepare("SELECT * FROM attachments WHERE appointment_id = ?");
$stmt->execute([$turnoId]);
$adjuntos = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalle de Turno - Vete a un Click</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="bg-gray-50 min-h-screen">
    
    <nav class="bg-white shadow-sm sticky top-0 z-50">
        <div class="max-w-4xl mx-auto px-4 py-4 flex justify-between items-center">
            <a href="dashboard.php" class="flex items-center gap-3">
                <div class="w-10 h-10 bg-gradient-to-br from-purple-500 to-indigo-600 rounded-xl flex items-center justify-center">
                    <i data-lucide="stethoscope" class="text-white w-6 h-6"></i>
                </div>
                <span class="font-bold text-lg bg-gradient-to-r from-purple-600 to-indigo-600 bg-clip-text text-transparent">
                    Vete a un Click
                </span>
            </a>
            <a href="dashboard.php" class="text-gray-600 hover:text-purple-600 transition flex items-center gap-2">
                <i data-lucide="arrow-left" class="w-4 h-4"></i>
                Volver
            </a>
        </div>
    </nav>

    <div class="max-w-3xl mx-auto px-4 py-8">
        
        <?php if ($turno['estado'] === 'atendido'): ?>
        <!-- Turno ya atendido -->
        <div class="bg-white rounded-2xl shadow-lg p-8 border border-gray-100">
            <div class="text-center mb-6">
                <div class="w-16 h-16 bg-gradient-to-br from-green-400 to-emerald-500 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i data-lucide="check-circle" class="text-white w-8 h-8"></i>
                </div>
                <h1 class="text-2xl font-bold text-gray-800 mb-2">Consulta Atendida</h1>
                <p class="text-gray-600">Esta consulta ya fue completada</p>
            </div>
            
            <div class="bg-green-50 border border-green-200 rounded-xl p-6 mb-6">
                <h3 class="font-bold text-green-800 mb-3 flex items-center gap-2">
                    <i data-lucide="file-text" class="w-5 h-5"></i>
                    Diagnóstico
                </h3>
                <p class="text-green-700 mb-4"><?php echo htmlspecialchars($turno['diagnostico']); ?></p>
                
                <?php if ($turno['recomendacion']): ?>
                <h3 class="font-bold text-green-800 mb-2 flex items-center gap-2">
                    <i data-lucide="lightbulb" class="w-5 h-5"></i>
                    Recomendación
                </h3>
                <p class="text-green-700"><?php echo htmlspecialchars($turno['recomendacion']); ?></p>
                <?php endif; ?>
            </div>
            
            <a href="dashboard.php" class="w-full bg-gray-100 text-gray-700 px-6 py-3 rounded-xl font-bold hover:bg-gray-200 transition text-center block">
                Volver al Dashboard
            </a>
        </div>
        
        <?php else: ?>
        <!-- Formulario para completar -->
        <div class="bg-white rounded-2xl shadow-lg p-8 border border-gray-100">
            <div class="flex items-center gap-4 mb-6">
                <div class="w-14 h-14 bg-gradient-to-br from-purple-400 to-indigo-500 rounded-2xl flex items-center justify-center">
                    <i data-lucide="user" class="text-white w-7 h-7"></i>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-gray-800"><?php echo htmlspecialchars($turno['cliente_nombre']); ?></h1>
                    <p class="text-gray-500 flex items-center gap-2">
                        <i data-lucide="heart" class="w-4 h-4"></i>
                        <?php echo htmlspecialchars($turno['cliente_mascota_nombre']); ?>
                    </p>
                </div>
            </div>

            <!-- Info del turno -->
            <div class="bg-gradient-to-br from-purple-50 to-indigo-50 rounded-xl p-5 mb-6 border border-purple-100">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-xs text-gray-500 mb-1">Fecha</p>
                        <p class="font-bold text-gray-800"><?php echo date('d/m/Y', strtotime($turno['fecha'])); ?></p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 mb-1">Hora</p>
                        <p class="font-bold text-gray-800"><?php echo $turno['hora_inicio']; ?> hs</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 mb-1">Email</p>
                        <p class="font-bold text-gray-800 text-sm"><?php echo htmlspecialchars($turno['cliente_email']); ?></p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 mb-1">Teléfono</p>
                        <p class="font-bold text-gray-800 text-sm"><?php echo htmlspecialchars($turno['cliente_telefono']); ?></p>
                    </div>
                </div>
            </div>

            <!-- Motivo -->
            <div class="mb-6">
                <h3 class="font-bold text-gray-800 mb-2 flex items-center gap-2">
                    <i data-lucide="file-text" class="w-5 h-5 text-purple-600"></i>
                    Motivo de Consulta
                </h3>
                <p class="text-gray-600 bg-gray-50 rounded-xl p-4"><?php echo htmlspecialchars($turno['motivo_consulta']); ?></p>
            </div>

            <!-- Adjuntos -->
            <?php if (!empty($adjuntos)): ?>
            <div class="mb-6">
                <h3 class="font-bold text-gray-800 mb-2 flex items-center gap-2">
                    <i data-lucide="paperclip" class="w-5 h-5 text-purple-600"></i>
                    Archivos Adjuntos
                </h3>
                <div class="space-y-2">
                    <?php foreach ($adjuntos as $adj): ?>
                    <a href="<?php echo $adj['ruta']; ?>" target="_blank" 
                       class="flex items-center gap-3 p-3 bg-gray-50 rounded-xl hover:bg-gray-100 transition">
                        <i data-lucide="file" class="w-5 h-5 text-purple-600"></i>
                        <span class="text-gray-700"><?php echo htmlspecialchars($adj['nombre_original']); ?></span>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Video -->
            <div class="mb-6">
                <a href="<?php echo $turno['meeting_link']; ?>" target="_blank" 
                   class="w-full bg-gradient-to-r from-purple-600 to-indigo-600 text-white px-6 py-4 rounded-xl font-bold hover:from-purple-700 hover:to-indigo-700 transition flex items-center justify-center gap-2">
                    <i data-lucide="video" class="w-5 h-5"></i>
                    Iniciar Videollamada
                </a>
            </div>

            <!-- Formulario de Completado -->
            <form method="POST" action="">
                <div class="border-t border-gray-100 pt-6">
                    <h3 class="font-bold text-gray-800 mb-4 flex items-center gap-2">
                        <i data-lucide="clipboard-check" class="w-5 h-5 text-green-600"></i>
                        Completar Consulta
                    </h3>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                📋 Diagnóstico
                            </label>
                            <textarea name="diagnostico" rows="4" required 
                                      class="w-full p-4 border-2 border-gray-200 rounded-xl focus:border-green-500 focus:ring-2 focus:ring-green-100 resize-none"
                                      placeholder="Describí el diagnóstico de la consulta..."></textarea>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                💡 Recomendación al Paciente
                            </label>
                            <textarea name="recomendacion" rows="3" 
                                      class="w-full p-4 border-2 border-gray-200 rounded-xl focus:border-green-500 focus:ring-2 focus:ring-green-100 resize-none"
                                      placeholder="Indicaciones, tratamiento, cuidados..."></textarea>
                        </div>

                        <div class="bg-blue-50 border border-blue-200 rounded-xl p-4">
                            <label class="flex items-start gap-3 cursor-pointer">
                                <input type="checkbox" name="enviar_email" class="w-5 h-5 text-blue-600 rounded mt-0.5">
                                <div>
                                    <p class="font-medium text-blue-800">Enviar resumen por email al paciente</p>
                                    <p class="text-sm text-blue-600">El paciente recibirá el diagnóstico y recomendación por email</p>
                                </div>
                            </label>
                        </div>
                    </div>

                    <button type="submit" 
                            class="w-full mt-6 bg-gradient-to-r from-green-500 to-emerald-600 text-white px-6 py-4 rounded-xl font-bold hover:from-green-600 hover:to-emerald-700 transition shadow-lg flex items-center justify-center gap-2">
                        <i data-lucide="check-circle" class="w-5 h-5"></i>
                        Marcar como Atendido
                    </button>
                </div>
            </form>
        </div>
        <?php endif; ?>

    </div>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>