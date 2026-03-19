<!-- public/pago_exitoso.php -->
<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../src/Controllers/AppointmentController.php';

$appointmentId = $_SESSION['appointment_id'] ?? null;

if (!$appointmentId) {
    header("Location: index.php");
    exit;
}

$database = new Database();
$db = $database->getConnection();
$controller = new AppointmentController($db);
$turno = $controller->getAppointmentById($appointmentId);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pago Exitoso - Vete a un Click</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="bg-gray-50 min-h-screen">
    <div class="max-w-2xl mx-auto px-4 py-16">
        <div class="bg-white rounded-3xl shadow-lg p-8 border border-gray-100 text-center">
            <div class="w-24 h-24 bg-gradient-to-br from-green-400 to-emerald-500 rounded-full flex items-center justify-center mx-auto mb-6">
                <i data-lucide="check-circle" class="text-white w-12 h-12"></i>
            </div>
            <h1 class="text-3xl font-bold text-gray-800 mb-4">¡Pago Exitoso!</h1>
            <p class="text-gray-600 mb-8">Tu turno ha sido confirmado. Ahora seleccioná tu horario preferido.</p>
            <a href="confirmar_turno.php" class="inline-flex items-center gap-2 bg-gradient-to-r from-purple-600 to-indigo-600 text-white px-8 py-4 rounded-xl font-bold hover:from-purple-700 hover:to-indigo-700 transition">
                Seleccionar Horario
                <i data-lucide="arrow-right" class="w-5 h-5"></i>
            </a>
        </div>
    </div>
    <script>lucide.createIcons();</script>
</body>
</html>