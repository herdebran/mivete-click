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

// Procesar guardado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $db->prepare("DELETE FROM availability WHERE professional_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    
    $insertados = 0;
    for ($i = 0; $i < 7; $i++) {
        $hora_inicio = $_POST["hora_inicio_{$i}"] ?? null;
        $hora_fin = $_POST["hora_fin_{$i}"] ?? null;
        $activo = $_POST["activo_{$i}"] ?? 0;
        
        if ($hora_inicio && $hora_fin && $activo) {
            $stmt = $db->prepare("INSERT INTO availability (professional_id, dia_semana, hora_inicio, hora_fin, activo) VALUES (?, ?, ?, ?, 1)");
            $stmt->execute([$_SESSION['user_id'], $i, $hora_inicio, $hora_fin]);
            $insertados++;
        }
    }
    
    $mensaje = "✅ Se guardaron {$insertados} horarios correctamente.";
}

// Obtener horarios actuales
$stmt = $db->prepare("SELECT * FROM availability WHERE professional_id = ? ORDER BY dia_semana");
$stmt->execute([$_SESSION['user_id']]);
$horarios_existentes = $stmt->fetchAll();

$horarios_por_dia = [];
foreach ($horarios_existentes as $h) {
    $horarios_por_dia[$h['dia_semana']] = $h;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configurar Horarios - Vete a un Click</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
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
        <div class="text-center mb-8">
            <div class="w-16 h-16 bg-gradient-to-br from-blue-400 to-cyan-500 rounded-2xl flex items-center justify-center mx-auto mb-4">
                <i data-lucide="calendar-settings" class="text-white w-8 h-8"></i>
            </div>
            <h1 class="text-3xl font-bold text-gray-800 mb-2">Configurar Horarios</h1>
            <p class="text-gray-600">Definí tu disponibilidad semanal regular</p>
        </div>

        <?php if ($mensaje): ?>
        <div class="mb-6 p-4 rounded-xl bg-green-50 border border-green-200 text-green-700 flex items-center gap-3">
            <i data-lucide="check-circle" class="w-5 h-5"></i>
            <?php echo $mensaje; ?>
        </div>
        <?php endif; ?>

        <div class="bg-white rounded-2xl shadow-lg p-8 border border-gray-100" x-data="{ dias: [
            { nombre: 'Domingo', index: 0 },
            { nombre: 'Lunes', index: 1 },
            { nombre: 'Martes', index: 2 },
            { nombre: 'Miércoles', index: 3 },
            { nombre: 'Jueves', index: 4 },
            { nombre: 'Viernes', index: 5 },
            { nombre: 'Sábado', index: 6 }
        ]}">
            <form method="POST" action="">
                <div class="space-y-4">
                    <template x-for="dia in dias">
                        <div class="border rounded-xl p-4 flex flex-col md:flex-row md:items-center gap-4"
                             :class="horariosPorDia[dia.index] ? 'bg-blue-50 border-blue-200' : 'bg-gray-50'">
                            
                            <div class="flex items-center gap-3 min-w-[150px]">
                                <input type="checkbox" :name="'activo_' + dia.index" :id="'activo_' + dia.index" value="1"
                                       class="w-5 h-5 text-blue-600 rounded focus:ring-blue-500"
                                       :checked="horariosPorDia[dia.index] != null">
                                <label :for="'activo_' + dia.index" class="font-medium text-gray-700" x-text="dia.nombre"></label>
                            </div>

                            <div class="flex items-center gap-3 flex-1">
                                <div class="flex-1">
                                    <label class="block text-xs text-gray-500 mb-1">Desde</label>
                                    <input type="time" :name="'hora_inicio_' + dia.index"
                                           class="w-full p-2.5 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-100"
                                           :value="horariosPorDia[dia.index]?.hora_inicio || '09:00'">
                                </div>
                                <div class="flex-1">
                                    <label class="block text-xs text-gray-500 mb-1">Hasta</label>
                                    <input type="time" :name="'hora_fin_' + dia.index"
                                           class="w-full p-2.5 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-100"
                                           :value="horariosPorDia[dia.index]?.hora_fin || '17:00'">
                                </div>
                            </div>

                            <div class="text-sm text-gray-500" x-show="horariosPorDia[dia.index]">
                                <i data-lucide="check" class="w-4 h-4 text-green-500"></i>
                            </div>
                        </div>
                    </template>
                </div>

                <div class="mt-8 flex gap-4">
                    <button type="submit" 
                            class="flex-1 bg-gradient-to-r from-blue-600 to-cyan-600 text-white px-6 py-3 rounded-xl font-bold hover:from-blue-700 hover:to-cyan-700 transition shadow-lg">
                        Guardar Horarios
                    </button>
                    <a href="dashboard.php" 
                       class="px-6 py-3 rounded-xl font-bold bg-gray-100 text-gray-700 hover:bg-gray-200 transition">
                        Cancelar
                    </a>
                </div>
            </form>

            <div class="mt-6 p-4 bg-blue-50 border border-blue-200 rounded-xl">
                <p class="text-sm text-blue-700 flex items-start gap-2">
                    <i data-lucide="info" class="w-4 h-4 flex-shrink-0 mt-0.5"></i>
                    <span>Para bloquear días específicos (vacaciones, feriados), usá la opción <strong>"Bloquear Fechas"</strong> en el dashboard.</span>
                </p>
            </div>
        </div>

        <script>
            lucide.createIcons();
            const horariosPorDia = <?php echo json_encode($horarios_por_dia); ?>;
        </script>
    </div>
</body>
</html>