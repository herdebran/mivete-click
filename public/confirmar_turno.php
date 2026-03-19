<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../src/Controllers/AppointmentController.php';

if (!isset($_SESSION['appointment_id'])) {
    header("Location: index.php");
    exit;
}

$appointmentId = $_SESSION['appointment_id'];
$tipoTurno = $_SESSION['tipo_turno'] ?? 'proximo';
$database = new Database();
$db = $database->getConnection();
$controller = new AppointmentController($db);

$controller->confirmPayment($appointmentId);
$turno = $controller->getAppointmentById($appointmentId);

$mensaje = '';
$meetingLink = '';
$horarios = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['hora'])) {
    $resultado = $controller->finalizeAppointment($appointmentId, $_POST['hora']);
    
    if ($resultado['success']) {
        $meetingLink = $resultado['meeting_link'];

        // ✅ RECARGAR los datos del turno después de actualizar
        $turno = $controller->getAppointmentById($appointmentId);
        
        // Enviar emails
        require_once __DIR__ . '/../src/Controllers/EmailController.php';
        $adjuntos = $controller->getAttachments($appointmentId);
        $emailController = new EmailController();
        $emailController->enviarConfirmacionTurno($turno, $meetingLink, $adjuntos);
        
        unset($_SESSION['appointment_id']);
        unset($_SESSION['tipo_turno']);
    } else {
        $mensaje = $resultado['message'];
    }
}

if ($tipoTurno === 'proximo') {
    $slots = $controller->getNextAvailableSlots($turno['professional_id'], 10);
    foreach ($slots as $slot) {
        $horarios[] = $slot;
    }
} else {
    $slots = $controller->getAvailableSlots($turno['professional_id'], $turno['fecha']);
    foreach ($slots as $hora) {
        $horarios[] = [
            'fecha' => $turno['fecha'],
            'fecha_formateada' => date('d/m/Y', strtotime($turno['fecha'])),
            'dia_nombre' => date('l', strtotime($turno['fecha'])),
            'hora' => $hora
        ];
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seleccionar Horario - Vete a un Click</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="bg-gray-50 min-h-screen">
    
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

    <div class="max-w-2xl mx-auto px-4 py-16">
        
        <?php if ($meetingLink): ?>
        <!-- Turno Confirmado -->
        <div class="bg-white rounded-3xl shadow-lg p-8 border border-gray-100 text-center">
            <div class="w-24 h-24 bg-gradient-to-br from-green-400 to-emerald-500 rounded-full flex items-center justify-center mx-auto mb-6">
                <i data-lucide="circle-check" class="text-white w-12 h-12"></i>
            </div>
            
            <h1 class="text-3xl font-bold text-gray-800 mb-2">¡Turno Confirmado!</h1>
            <p class="text-gray-600 mb-8">Tu mascota va a estar en buenas manos</p>

            <!-- Detalles -->
            <div class="bg-gradient-to-br from-purple-50 to-indigo-50 rounded-2xl p-6 mb-8 border border-purple-100 text-left">
                <div class="space-y-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-white rounded-xl flex items-center justify-center flex-shrink-0">
                            <i data-lucide="calendar" class="text-purple-600 w-5 h-5"></i>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">Fecha</p>
                            <p class="font-bold text-gray-800"><?php echo date('d/m/Y', strtotime($turno['fecha'])); ?></p>
                        </div>
                    </div>
                    
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-white rounded-xl flex items-center justify-center flex-shrink-0">
                            <i data-lucide="clock" class="text-purple-600 w-5 h-5"></i>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">Hora</p>
                            <p class="font-bold text-gray-800"><?php echo $turno['hora_inicio']; ?> hs</p>
                        </div>
                    </div>
                    
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-white rounded-xl flex items-center justify-center flex-shrink-0">
                            <i data-lucide="user-circle" class="text-purple-600 w-5 h-5"></i>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">Veterinario</p>
                            <p class="font-bold text-gray-800">Dr./Dra. <?php echo htmlspecialchars($turno['vet_nombre'] . ' ' . $turno['vet_apellido']); ?></p>
                        </div>
                    </div>
                    
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-white rounded-xl flex items-center justify-center flex-shrink-0">
                            <i data-lucide="heart" class="text-purple-600 w-5 h-5"></i>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">Mascota</p>
                            <p class="font-bold text-gray-800"><?php echo htmlspecialchars($turno['cliente_mascota_nombre']); ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Link de Video -->
            <div class="bg-gradient-to-r from-green-50 to-emerald-50 border border-green-200 rounded-2xl p-6 mb-8">
                <p class="text-sm font-medium text-green-800 mb-3 flex items-center justify-center gap-2">
                    <i data-lucide="video" class="w-4 h-4"></i>
                    Link de la videollamada
                </p>
                <a href="<?php echo $meetingLink; ?>" target="_blank" 
                   class="inline-flex items-center gap-2 bg-gradient-to-r from-green-500 to-emerald-600 text-white px-6 py-3 rounded-xl font-bold hover:from-green-600 hover:to-emerald-700 transition">
                    <i data-lucide="external-link" class="w-5 h-5"></i>
                    Ingresar a la Consulta
                </a>
                <p class="text-xs text-green-600 mt-3">Ingresá 5 minutos antes del horario acordado</p>
            </div>

            <!-- Email Info -->
            <div class="bg-blue-50 border border-blue-100 rounded-xl p-4 mb-8">
                <p class="text-sm text-blue-700 flex items-center justify-center gap-2">
                    <i data-lucide="mail" class="w-4 h-4"></i>
                    Te enviamos un email a <strong><?php echo htmlspecialchars($turno['cliente_email']); ?></strong>
                </p>
            </div>

            <a href="index.php" 
               class="inline-flex items-center gap-2 bg-gray-100 text-gray-700 px-6 py-3 rounded-xl font-bold hover:bg-gray-200 transition">
                <i data-lucide="home" class="w-5 h-5"></i>
                Volver al inicio
            </a>
        </div>

        <?php else: ?>
        <!-- Selección de Horario -->
        <div class="bg-white rounded-3xl shadow-lg p-8 border border-gray-100">
            <div class="text-center mb-8">
                <div class="w-20 h-20 bg-gradient-to-br from-green-400 to-emerald-500 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i data-lucide="check" class="text-white w-10 h-10"></i>
                </div>
                <h1 class="text-2xl font-bold text-gray-800 mb-2">¡Pago Aprobado!</h1>
                <p class="text-gray-600">
                    <?php echo $tipoTurno === 'proximo' ? 'Seleccioná el horario que prefieras:' : 'Elegí tu horario:'; ?>
                </p>
            </div>

            <?php if ($mensaje): ?>
            <div class="mb-6 p-4 rounded-xl bg-red-50 border border-red-200 text-red-700 flex items-center gap-3">
                <i data-lucide="alert-circle" class="w-5 h-5 flex-shrink-0"></i>
                <?php echo $mensaje; ?>
            </div>
            <?php endif; ?>

            <?php if (empty($horarios)): ?>
            <div class="bg-orange-50 border border-orange-200 rounded-2xl p-8 text-center">
                <i data-lucide="calendar-x" class="w-12 h-12 text-orange-500 mx-auto mb-4"></i>
                <p class="text-orange-800 font-medium mb-4">No hay horarios disponibles</p>
                <a href="index.php" class="inline-flex items-center gap-2 bg-orange-500 text-white px-6 py-3 rounded-xl font-bold hover:bg-orange-600 transition">
                    <i data-lucide="refresh-cw" class="w-5 h-5"></i>
                    Volver a buscar
                </a>
            </div>
            <?php else: ?>
                <form method="POST" action="" id="formSeleccionHorario">
                    <div class="space-y-3 mb-8">
                        <?php foreach ($horarios as $slot): ?>
                            <button type="submit" name="hora" value="<?php echo $slot['hora']; ?>"
                                    class="w-full p-4 border-2 border-gray-200 rounded-xl hover:border-purple-500 hover:bg-purple-50 transition flex justify-between items-center group btn-horario">
                                <div class="text-left">
                                    <p class="font-bold text-gray-800 group-hover:text-purple-700">
                                        <?php
                                        // Traducir día si viene en inglés
                                        $diasEspanol = [
                                            'Sunday' => 'Domingo', 'Monday' => 'Lunes', 'Tuesday' => 'Martes',
                                            'Wednesday' => 'Miércoles', 'Thursday' => 'Jueves', 'Friday' => 'Viernes',
                                            'Saturday' => 'Sábado'
                                        ];
                                        $dia = $slot['dia_nombre'];
                                        echo ucfirst($diasEspanol[$dia] ?? $dia);
                                        ?>
                                    </p>
                                    <p class="text-sm text-gray-500"><?php echo $slot['fecha_formateada']; ?></p>
                                </div>
                                <div class="bg-gradient-to-r from-purple-500 to-indigo-500 text-white px-5 py-2 rounded-lg font-bold">
                                    <?php echo $slot['hora']; ?> hs
                                </div>
                            </button>
                        <?php endforeach; ?>
                    </div>
                </form>

                <!-- Spinner Overlay (oculto por defecto) -->
                <div id="loadingOverlay" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 hidden">
                    <div class="bg-white rounded-3xl p-8 text-center">
                        <div class="w-16 h-16 border-4 border-purple-200 border-t-purple-600 rounded-full animate-spin mx-auto mb-4"></div>
                        <p class="text-gray-800 font-bold text-lg">Confirmando tu turno...</p>
                        <p class="text-gray-500 text-sm mt-2">Por favor esperá un momento</p>
                    </div>
                </div>

                <script>
                    // Mostrar spinner al seleccionar horario
                    document.querySelectorAll('.btn-horario').forEach(btn => {
                        btn.addEventListener('click', function() {
                            document.getElementById('loadingOverlay').classList.remove('hidden');
                        });
                    });
                </script>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <div class="mt-6 text-center">
            <a href="index.php" class="text-gray-500 hover:text-gray-700 text-sm flex items-center justify-center gap-2">
                <i data-lucide="home" class="w-4 h-4"></i>
                Volver al inicio
            </a>
        </div>
    </div>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>