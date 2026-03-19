<?php
session_start();
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../src/Controllers/AuthController.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$database = new Database();
$db = $database->getConnection();
$mensaje = '';

// Bloquear fecha/horario
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion'])) {
    if ($_POST['accion'] === 'bloquear') {
        $stmt = $db->prepare("INSERT INTO blocked_dates (professional_id, fecha, hora_inicio, hora_fin, motivo) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([
            $_SESSION['user_id'], 
            $_POST['fecha'], 
            $_POST['hora_inicio'] ?? null, 
            $_POST['hora_fin'] ?? null, 
            $_POST['motivo'] ?? ''
        ]);
        $mensaje = '✅ Franja horaria bloqueada correctamente.';
    } elseif ($_POST['accion'] === 'desbloquear') {
        $stmt = $db->prepare("DELETE FROM blocked_dates WHERE id = ? AND professional_id = ?");
        $stmt->execute([$_POST['id'], $_SESSION['user_id']]);
        $mensaje = '✅ Bloqueo eliminado correctamente.';
    }
}

// Obtener bloqueos ordenados por fecha
$stmt = $db->prepare("SELECT * FROM blocked_dates WHERE professional_id = ? ORDER BY fecha DESC, hora_inicio DESC");
$stmt->execute([$_SESSION['user_id']]);
$bloqueos = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bloquear Horarios - Vete a un Click</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="bg-gray-50 min-h-screen">
    
    <!-- Header -->
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
        
        <!-- Título -->
        <div class="text-center mb-8">
            <div class="w-16 h-16 bg-gradient-to-br from-orange-400 to-red-500 rounded-2xl flex items-center justify-center mx-auto mb-4">
                <i data-lucide="calendar-clock" class="text-white w-8 h-8"></i>
            </div>
            <h1 class="text-3xl font-bold text-gray-800 mb-2">Bloquear Horarios</h1>
            <p class="text-gray-600">Marcá franjas horarias específicas como no disponibles (turnos personales, vacaciones, etc.)</p>
        </div>

        <?php if ($mensaje): ?>
        <div class="mb-6 p-4 rounded-xl bg-green-50 border border-green-200 text-green-700 flex items-center gap-3">
            <i data-lucide="check-circle" class="w-5 h-5"></i>
            <?php echo $mensaje; ?>
        </div>
        <?php endif; ?>

        <!-- Formulario para bloquear -->
        <div class="bg-white rounded-2xl shadow-lg p-8 border border-gray-100 mb-8">
            <h2 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
                <i data-lucide="plus-circle" class="w-5 h-5 text-orange-600"></i>
                Nueva Franja Bloqueada
            </h2>
            
            <form method="POST" action="">
                <input type="hidden" name="accion" value="bloquear">
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">📅 Fecha</label>
                        <input type="date" name="fecha" required min="<?php echo date('Y-m-d'); ?>"
                               class="w-full p-3 border-2 border-gray-200 rounded-xl focus:border-orange-500 focus:ring-2 focus:ring-orange-100">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">⏰ Desde</label>
                        <input type="time" name="hora_inicio" 
                               class="w-full p-3 border-2 border-gray-200 rounded-xl focus:border-orange-500 focus:ring-2 focus:ring-orange-100">
                        <p class="text-xs text-gray-400 mt-1">Dejá vacío para bloquear todo el día</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">⏰ Hasta</label>
                        <input type="time" name="hora_fin" 
                               class="w-full p-3 border-2 border-gray-200 rounded-xl focus:border-orange-500 focus:ring-2 focus:ring-orange-100">
                    </div>
                </div>
                
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">📝 Motivo (opcional)</label>
                    <input type="text" name="motivo" placeholder="Ej: Turno personal, Vacaciones, Congreso..."
                           class="w-full p-3 border-2 border-gray-200 rounded-xl focus:border-orange-500 focus:ring-2 focus:ring-orange-100">
                </div>
                
                <button type="submit" 
                        class="w-full bg-gradient-to-r from-orange-500 to-red-600 text-white px-6 py-3 rounded-xl font-bold hover:from-orange-600 hover:to-red-700 transition shadow-lg flex items-center justify-center gap-2">
                    <i data-lucide="lock" class="w-5 h-5"></i>
                    Bloquear Franja Horaria
                </button>
            </form>

            <div class="mt-6 p-4 bg-orange-50 border border-orange-200 rounded-xl">
                <p class="text-sm text-orange-700 flex items-start gap-2">
                    <i data-lucide="info" class="w-4 h-4 flex-shrink-0 mt-0.5"></i>
                    <span><strong>Consejo:</strong> Si completás solo la fecha (sin horarios), se bloqueará el día completo. Si completás los horarios, se bloqueará solo esa franja.</span>
                </p>
            </div>
        </div>

        <!-- Bloqueos existentes -->
        <div class="bg-white rounded-2xl shadow-lg border border-gray-100">
            <div class="p-6 border-b border-gray-100">
                <h2 class="text-lg font-bold text-gray-800 flex items-center gap-2">
                    <i data-lucide="calendar-off" class="w-5 h-5 text-orange-600"></i>
                    Bloqueos Activos
                </h2>
            </div>
            
            <?php if (empty($bloqueos)): ?>
            <div class="p-12 text-center">
                <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i data-lucide="calendar-check" class="w-8 h-8 text-gray-400"></i>
                </div>
                <p class="text-gray-600">No tenés bloqueos activos</p>
                <p class="text-gray-400 text-sm mt-2">Tu agenda está completamente disponible</p>
            </div>
            <?php else: ?>
            <div class="divide-y divide-gray-100">
                <?php foreach ($bloqueos as $bloqueo): ?>
                <div class="p-4 flex justify-between items-center hover:bg-gray-50 transition">
                    <div class="flex items-center gap-4">
                        <div class="w-10 h-10 bg-orange-100 rounded-xl flex items-center justify-center flex-shrink-0">
                            <i data-lucide="calendar-x" class="w-5 h-5 text-orange-600"></i>
                        </div>
                        <div>
                            <p class="font-bold text-gray-800">
                                <?php echo date('d/m/Y', strtotime($bloqueo['fecha'])); ?>
                            </p>
                            <?php if ($bloqueo['hora_inicio'] && $bloqueo['hora_fin']): ?>
                            <p class="text-sm text-orange-600 font-medium flex items-center gap-1">
                                <i data-lucide="clock" class="w-3 h-3"></i>
                                <?php echo $bloqueo['hora_inicio']; ?> - <?php echo $bloqueo['hora_fin']; ?> hs
                            </p>
                            <?php else: ?>
                            <p class="text-sm text-gray-500">Día completo</p>
                            <?php endif; ?>
                            <?php if ($bloqueo['motivo']): ?>
                            <p class="text-xs text-gray-400 mt-1"><?php echo htmlspecialchars($bloqueo['motivo']); ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <form method="POST" action="">
                        <input type="hidden" name="accion" value="desbloquear">
                        <input type="hidden" name="id" value="<?php echo $bloqueo['id']; ?>">
                        <button type="submit" 
                                class="text-red-600 hover:text-red-700 text-sm font-medium flex items-center gap-1 px-3 py-2 rounded-lg hover:bg-red-50 transition">
                            <i data-lucide="unlock" class="w-4 h-4"></i>
                            Eliminar
                        </button>
                    </form>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>

    </div>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>