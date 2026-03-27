<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../src/Controllers/AppointmentController.php';

$database = new Database();
$db = $database->getConnection();
$controller = new AppointmentController($db);

$mensaje = '';
$turnoPendiente = null;

// Si ya tiene sesión con appointment_id pagado sin hora, redirigir directo
if (isset($_SESSION['appointment_id'])) {
    $turno = $controller->getAppointmentById($_SESSION['appointment_id']);
    if ($turno && $turno['estado'] === 'pagado' && empty($turno['hora_inicio'])) {
        header("Location: confirmar_turno.php");
        exit;
    }
}

// Buscar por email
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['email'])) {
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    
    $query = "SELECT a.*, p.nombre as vet_nombre, p.apellido as vet_apellido 
              FROM appointments a 
              JOIN professionals p ON a.professional_id = p.id 
              WHERE a.cliente_email = :email 
              AND a.estado = 'pagado' 
              AND a.hora_inicio IS NULL
              ORDER BY a.created_at DESC 
              LIMIT 1";
    
    $stmt = $db->prepare($query);
    $stmt->bindParam(":email", $email);
    $stmt->execute();
    $turnoPendiente = $stmt->fetch();
    
    if ($turnoPendiente) {
        // Guardar en sesión y redirigir
        $_SESSION['appointment_id'] = $turnoPendiente['id'];
        $_SESSION['tipo_turno'] = 'proximo'; // Default
        header("Location: confirmar_turno.php");
        exit;
    } else {
        $mensaje = 'No encontramos reservas pendientes para ese email.';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Retomar Reserva - Vete a un Click</title>
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

    <div class="max-w-md mx-auto px-4 py-16">
        <div class="bg-white rounded-3xl shadow-lg p-8 border border-gray-100 text-center">
            
            <div class="w-20 h-20 bg-gradient-to-br from-blue-400 to-indigo-500 rounded-full flex items-center justify-center mx-auto mb-6">
                <i data-lucide="refresh-cw" class="text-white w-10 h-10"></i>
            </div>

            <h1 class="text-2xl font-bold text-gray-800 mb-2">¿Abandonaste tu reserva?</h1>
            <p class="text-gray-600 mb-8">Ingresá tu email para retomar donde quedaste</p>

            <?php if ($mensaje): ?>
            <div class="mb-6 p-4 rounded-xl bg-orange-50 border border-orange-200 text-orange-700 flex items-center gap-3">
                <i data-lucide="info" class="w-5 h-5"></i>
                <?php echo $mensaje; ?>
            </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2 text-left">
                        <i data-lucide="mail" class="w-4 h-4 inline mr-1"></i>
                        Tu Email
                    </label>
                    <input type="email" name="email" required 
                           class="w-full p-4 border-2 border-gray-200 rounded-xl focus:border-blue-500 focus:ring-2 focus:ring-blue-100"
                           placeholder="tu@email.com">
                </div>
                
                <button type="submit" 
                        class="w-full bg-gradient-to-r from-blue-600 to-indigo-600 text-white p-4 rounded-xl font-bold hover:from-blue-700 hover:to-indigo-700 transition flex items-center justify-center gap-2">
                    <i data-lucide="search" class="w-5 h-5"></i>
                    Buscar mi reserva
                </button>
            </form>

            <div class="mt-6 pt-6 border-t border-gray-100">
                <a href="index.php" class="text-gray-500 hover:text-gray-700 text-sm flex items-center justify-center gap-2">
                    <i data-lucide="arrow-left" class="w-4 h-4"></i>
                    Volver al inicio
                </a>
            </div>
        </div>

        <!-- Info box -->
        <div class="mt-6 text-center text-sm text-gray-500">
            <p>Si ya pagaste pero no elegiste horario, acá podés retomar.</p>
            <p class="mt-1">Tu reserva se mantiene por 24 horas.</p>
        </div>
    </div>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>