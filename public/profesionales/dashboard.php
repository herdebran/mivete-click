<?php
session_start();
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../src/Controllers/AuthController.php';
require_once __DIR__ . '/../../src/Controllers/AppointmentController.php';
require_once __DIR__ . '/../../src/Models/Professional.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$database = new Database();
$db = $database->getConnection();
$professionalModel = new Professional($db);
$appointmentController = new AppointmentController($db);
$profesional = $professionalModel->getById($_SESSION['user_id']);

// Navegación entre días
$fechaSeleccionada = $_GET['fecha'] ?? date('Y-m-d');
$turnosDelDia = $appointmentController->getAppointmentsForDate($_SESSION['user_id'], $fechaSeleccionada);
$turnosPendientes = $appointmentController->getPendingAppointmentsCount($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Vete a un Click Profesionales</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        .card-hover { transition: all 0.3s ease; }
        .card-hover:hover { transform: translateY(-2px); box-shadow: 0 10px 30px rgba(0,0,0,0.1); }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">
    
    <!-- Header -->
    <nav class="bg-white shadow-sm sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 py-4 flex justify-between items-center">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-gradient-to-br from-purple-500 to-indigo-600 rounded-xl flex items-center justify-center">
                    <i data-lucide="stethoscope" class="text-white w-6 h-6"></i>
                </div>
                <div>
                    <h1 class="font-bold text-lg bg-gradient-to-r from-purple-600 to-indigo-600 bg-clip-text text-transparent">
                        Vete a un Click
                    </h1>
                    <p class="text-xs text-gray-500">Panel de Profesionales</p>
                </div>
            </div>
            
            <div class="flex items-center gap-4">
                <div class="text-right hidden md:block">
                    <p class="text-sm font-medium text-gray-800">Dr./Dra. <?php echo htmlspecialchars($profesional['nombre']); ?></p>
                    <p class="text-xs text-gray-500"><?php echo htmlspecialchars($profesional['email']); ?></p>
                </div>
                <div class="flex items-center gap-2">
                    <a href="horarios.php" class="p-2 text-gray-600 hover:text-purple-600 transition" title="Configurar Horarios">
                        <i data-lucide="calendar-settings" class="w-5 h-5"></i>
                    </a>
                    <a href="bloquear_fechas.php" class="p-2 text-gray-600 hover:text-purple-600 transition" title="Bloquear Fechas">
                        <i data-lucide="calendar-x" class="w-5 h-5"></i>
                    </a>
                    <a href="logout.php" class="p-2 text-red-600 hover:text-red-700 transition" title="Cerrar Sesión">
                        <i data-lucide="log-out" class="w-5 h-5"></i>
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto px-4 py-8">
        
        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
            <div class="bg-gradient-to-br from-purple-500 to-indigo-600 rounded-2xl p-6 text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-purple-100 text-sm mb-1">Turnos Hoy</p>
                        <p class="text-3xl font-bold"><?php echo count($turnosDelDia); ?></p>
                    </div>
                    <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center">
                        <i data-lucide="calendar" class="w-6 h-6"></i>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-2xl p-6 shadow-lg border border-gray-100 card-hover">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm mb-1">Pendientes</p>
                        <p class="text-3xl font-bold text-gray-800"><?php echo $turnosPendientes; ?></p>
                    </div>
                    <div class="w-12 h-12 bg-yellow-100 rounded-xl flex items-center justify-center">
                        <i data-lucide="clock" class="w-6 h-6 text-yellow-600"></i>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-2xl p-6 shadow-lg border border-gray-100 card-hover">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm mb-1">Atendidos</p>
                        <p class="text-3xl font-bold text-gray-800"><?php echo $appointmentController->getAttendedCount($_SESSION['user_id']); ?></p>
                    </div>
                    <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center">
                        <i data-lucide="check-circle" class="w-6 h-6 text-green-600"></i>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-2xl p-6 shadow-lg border border-gray-100 card-hover">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm mb-1">Esta Semana</p>
                        <p class="text-3xl font-bold text-gray-800"><?php echo $appointmentController->getWeeklyCount($_SESSION['user_id']); ?></p>
                    </div>
                    <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center">
                        <i data-lucide="bar-chart" class="w-6 h-6 text-blue-600"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Navegación de Días -->
        <div class="bg-white rounded-2xl shadow-lg p-4 mb-8 border border-gray-100">
            <div class="flex items-center justify-between">
                <a href="?fecha=<?php echo date('Y-m-d', strtotime($fechaSeleccionada . ' -1 day')); ?>" 
                   class="p-2 hover:bg-gray-100 rounded-lg transition">
                    <i data-lucide="chevron-left" class="w-6 h-6 text-gray-600"></i>
                </a>
                
                <div class="text-center">
                    <h2 class="text-xl font-bold text-gray-800">
                        <?php 
                        $dias = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
                        echo $dias[date('w', strtotime($fechaSeleccionada))];
                        ?>
                    </h2>
                    <p class="text-gray-500"><?php echo date('d/m/Y', strtotime($fechaSeleccionada)); ?></p>
                </div>
                
                <a href="?fecha=<?php echo date('Y-m-d', strtotime($fechaSeleccionada . ' +1 day')); ?>" 
                   class="p-2 hover:bg-gray-100 rounded-lg transition">
                    <i data-lucide="chevron-right" class="w-6 h-6 text-gray-600"></i>
                </a>
            </div>
            
            <!-- Días rápidos -->
            <div class="flex justify-center gap-2 mt-4 pt-4 border-t border-gray-100">
                <?php for ($i = 0; $i < 7; $i++): 
                    $dia = date('Y-m-d', strtotime($fechaSeleccionada . ' +' . ($i - 3) . ' days'));
                    $esHoy = $dia === date('Y-m-d');
                    $esSeleccionado = $dia === $fechaSeleccionada;
                ?>
                <a href="?fecha=<?php echo $dia; ?>" 
                   class="px-4 py-2 rounded-xl text-sm font-medium transition <?php echo $esSeleccionado ? 'bg-purple-600 text-white' : ($esHoy ? 'bg-purple-100 text-purple-600' : 'hover:bg-gray-100 text-gray-600'); ?>">
                    <?php echo date('d/m', strtotime($dia)); ?>
                </a>
                <?php endfor; ?>
            </div>
        </div>

        <!-- Lista de Turnos -->
        <div class="bg-white rounded-2xl shadow-lg border border-gray-100">
            <div class="p-6 border-b border-gray-100 flex justify-between items-center">
                <h3 class="text-lg font-bold text-gray-800 flex items-center gap-2">
                    <i data-lucide="calendar-clock" class="w-5 h-5 text-purple-600"></i>
                    Turnos del Día
                </h3>
                <span class="bg-purple-100 text-purple-700 px-3 py-1 rounded-full text-sm font-medium">
                    <?php echo count($turnosDelDia); ?> turnos
                </span>
            </div>
            
            <?php if (empty($turnosDelDia)): ?>
            <div class="p-12 text-center">
                <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i data-lucide="calendar-off" class="w-10 h-10 text-gray-400"></i>
                </div>
                <p class="text-gray-600 font-medium">No tenés turnos programados para este día</p>
                <p class="text-gray-400 text-sm mt-2">¡Disfrutá tu tiempo libre!</p>
            </div>
            <?php else: ?>
            <div class="divide-y divide-gray-100">
                <?php foreach ($turnosDelDia as $turno): ?>
                <div class="p-6 hover:bg-gray-50 transition">
                    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                        <div class="flex items-start gap-4 flex-1">
                            <div class="w-12 h-12 bg-gradient-to-br from-purple-400 to-indigo-500 rounded-xl flex items-center justify-center flex-shrink-0">
                                <i data-lucide="user" class="text-white w-6 h-6"></i>
                            </div>
                            <div class="flex-1">
                                <div class="flex items-center gap-3 mb-2">
                                    <span class="bg-gradient-to-r from-purple-500 to-indigo-500 text-white px-3 py-1 rounded-lg text-sm font-bold">
                                        <?php echo $turno['hora_inicio']; ?> hs
                                    </span>
                                    <?php if ($turno['estado'] === 'atendido'): ?>
                                    <span class="bg-green-100 text-green-700 px-3 py-1 rounded-full text-xs font-medium flex items-center gap-1">
                                        <i data-lucide="check-circle" class="w-3 h-3"></i>
                                        Atendido
                                    </span>
                                    <?php elseif ($turno['estado'] === 'pagado'): ?>
                                    <span class="bg-yellow-100 text-yellow-700 px-3 py-1 rounded-full text-xs font-medium flex items-center gap-1">
                                        <i data-lucide="clock" class="w-3 h-3"></i>
                                        Pendiente
                                    </span>
                                    <?php endif; ?>
                                </div>
                                
                                <h4 class="font-bold text-gray-800 mb-1">
                                    <?php echo htmlspecialchars($turno['cliente_nombre']); ?>
                                </h4>
                                <p class="text-sm text-gray-500 flex items-center gap-2 mb-2">
                                    <i data-lucide="heart" class="w-4 h-4"></i>
                                    <?php echo htmlspecialchars($turno['cliente_mascota_nombre']); ?>
                                </p>
                                <p class="text-sm text-gray-600 bg-gray-100 rounded-lg p-3">
                                    <strong>Motivo:</strong> <?php echo htmlspecialchars($turno['motivo_consulta']); ?>
                                </p>
                                
                                <?php if (!empty($turno['adjuntos'])): ?>
                                <div class="mt-3 flex items-center gap-2">
                                    <i data-lucide="paperclip" class="w-4 h-4 text-gray-400"></i>
                                    <span class="text-sm text-gray-500"><?php echo count($turno['adjuntos']); ?> archivo(s) adjunto(s)</span>
                                </div>
                                <?php endif; ?>
                                
                                <?php if ($turno['estado'] === 'atendido' && !empty($turno['diagnostico'])): ?>
                                <div class="mt-3 bg-green-50 border border-green-200 rounded-xl p-4">
                                    <p class="text-sm font-medium text-green-800 mb-2">📋 Diagnóstico:</p>
                                    <p class="text-sm text-green-700"><?php echo htmlspecialchars($turno['diagnostico']); ?></p>
                                    <?php if (!empty($turno['recomendacion'])): ?>
                                    <p class="text-sm text-green-700 mt-2">💡 <strong>Recomendación:</strong> <?php echo htmlspecialchars($turno['recomendacion']); ?></p>
                                    <?php endif; ?>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="flex flex-col gap-2 w-full md:w-auto">
                            <a href="<?php echo $turno['meeting_link']; ?>" target="_blank" 
                               class="inline-flex items-center justify-center gap-2 bg-gradient-to-r from-purple-600 to-indigo-600 text-white px-5 py-2.5 rounded-xl font-medium hover:from-purple-700 hover:to-indigo-700 transition">
                                <i data-lucide="video" class="w-4 h-4"></i>
                                Video
                            </a>
                            
                            <?php if ($turno['estado'] === 'pagado'): ?>
                            <a href="turno_detalle.php?id=<?php echo $turno['id']; ?>" 
                               class="inline-flex items-center justify-center gap-2 bg-gray-100 text-gray-700 px-5 py-2.5 rounded-xl font-medium hover:bg-gray-200 transition">
                                <i data-lucide="file-text" class="w-4 h-4"></i>
                                Completar
                            </a>
                            <?php else: ?>
                            <a href="turno_detalle.php?id=<?php echo $turno['id']; ?>" 
                               class="inline-flex items-center justify-center gap-2 bg-gray-100 text-gray-700 px-5 py-2.5 rounded-xl font-medium hover:bg-gray-200 transition">
                                <i data-lucide="eye" class="w-4 h-4"></i>
                                Ver Historia
                            </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>

        <!-- Accesos Rápidos -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-8">
            <a href="horarios.php" class="bg-gradient-to-br from-blue-500 to-cyan-600 rounded-2xl p-6 text-white card-hover">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center">
                        <i data-lucide="calendar-settings" class="w-6 h-6"></i>
                    </div>
                    <div>
                        <h4 class="font-bold text-lg">Configurar Horarios</h4>
                        <p class="text-blue-100 text-sm">Definí tu disponibilidad semanal</p>
                    </div>
                </div>
            </a>
            
            <a href="bloquear_fechas.php" class="bg-gradient-to-br from-orange-500 to-red-600 rounded-2xl p-6 text-white card-hover">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center">
                        <i data-lucide="calendar-x" class="w-6 h-6"></i>
                    </div>
                    <div>
                        <h4 class="font-bold text-lg">Bloquear Fechas</h4>
                        <p class="text-orange-100 text-sm">Marcá días no disponibles</p>
                    </div>
                </div>
            </a>
        </div>

    </div>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>