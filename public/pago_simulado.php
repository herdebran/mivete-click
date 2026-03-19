<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../src/Controllers/AppointmentController.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: index.php");
    exit;
}

try {
    $database = new Database();
    $db = $database->getConnection();
    $controller = new AppointmentController($db);

    $tipoBusqueda = $_POST['tipo_busqueda'] ?? 'proximo';
    $fecha = $_POST['fecha'] ?? date('Y-m-d');
    $vetId = $_POST['vet_id'] ?? null;

    $data = [
        'professional_id' => $vetId,
        'cliente_nombre' => htmlspecialchars($_POST['cliente_nombre']),
        'cliente_email' => filter_var($_POST['cliente_email'], FILTER_SANITIZE_EMAIL),
        'cliente_telefono' => htmlspecialchars($_POST['cliente_telefono']),
        'cliente_mascota_nombre' => htmlspecialchars($_POST['cliente_mascota_nombre']),
        'motivo_consulta' => htmlspecialchars($_POST['motivo_consulta']),
        'fecha' => $fecha
    ];

    $resultado = $controller->createAppointment($data);

    if (!$resultado['success']) {
        throw new Exception("Error al crear la reserva: " . $resultado['message']);
    }

    $appointmentId = $resultado['appointment_id'];

    // Procesar archivos adjuntos
    if (!empty($_FILES['adjuntos']['name'][0])) {
        $uploadDir = __DIR__ . '/uploads/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $totalFiles = count($_FILES['adjuntos']['name']);
        
        for ($i = 0; $i < $totalFiles; $i++) {
            if ($_FILES['adjuntos']['error'][$i] === UPLOAD_ERR_OK) {
                $fileTmpPath = $_FILES['adjuntos']['tmp_name'][$i];
                $fileName = $_FILES['adjuntos']['name'][$i];
                $fileSize = $_FILES['adjuntos']['size'][$i];
                $fileType = $_FILES['adjuntos']['type'][$i];
                
                $allowedTypes = ['application/pdf', 'image/jpeg', 'image/jpg', 'image/png'];
                if (!in_array($fileType, $allowedTypes)) continue;
                if ($fileSize > 5 * 1024 * 1024) continue;
                
                $newFileName = uniqid('adj_' . $appointmentId . '_') . '_' . basename($fileName);
                $destPath = $uploadDir . $newFileName;
                
                if (move_uploaded_file($fileTmpPath, $destPath)) {
                    $controller->saveAttachment($appointmentId, $fileName, $newFileName, $fileType, (int)$fileSize, '/uploads/' . $newFileName);
                }
            }
        }
    }

    $_SESSION['appointment_id'] = $appointmentId;
    $_SESSION['tipo_turno'] = $tipoBusqueda;

} catch (Exception $e) {
    die("<div style='text-align:center;padding:50px;font-family:Arial;'><h1>Error</h1><p>" . $e->getMessage() . "</p><a href='index.php'>Volver</a></div>");
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pago - Vete a un Click</title>
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
            <!-- Icono -->
            <div class="w-20 h-20 bg-gradient-to-br from-green-400 to-emerald-500 rounded-full flex items-center justify-center mx-auto mb-6">
                <i data-lucide="credit-card" class="text-white w-10 h-10"></i>
            </div>

            <h1 class="text-2xl font-bold text-gray-800 mb-2">Pago Seguro</h1>
            <p class="text-gray-600 mb-8">Completá tu reserva con un pago seguro</p>

            <!-- Resumen -->
            <div class="bg-gradient-to-br from-purple-50 to-indigo-50 rounded-2xl p-6 mb-8 border border-purple-100">
                <div class="flex justify-between items-center mb-4">
                    <span class="text-gray-600">Reserva #</span>
                    <span class="font-bold text-gray-800"><?php echo $appointmentId; ?></span>
                </div>
                <div class="flex justify-between items-center mb-4">
                    <span class="text-gray-600">Servicio</span>
                    <span class="font-bold text-gray-800">Consulta Veterinaria</span>
                </div>
                <div class="border-t border-purple-200 pt-4">
                    <div class="flex justify-between items-center">
                        <span class="text-gray-800 font-medium">Total</span>
                        <span class="text-3xl font-bold bg-gradient-to-r from-purple-600 to-indigo-600 bg-clip-text text-transparent">$5.000 ARS</span>
                    </div>
                </div>
            </div>

            <!-- Info Mercado Pago -->
            <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-4 mb-8 text-left">
                <div class="flex items-start gap-3">
                    <i data-lucide="info" class="w-5 h-5 text-yellow-600 flex-shrink-0 mt-0.5"></i>
                    <div>
                        <p class="text-sm font-medium text-yellow-800 mb-1">Modo Desarrollo</p>
                        <p class="text-sm text-yellow-700">En producción, aquí se integraría Mercado Pago. Por ahora, simulamos el pago exitoso.</p>
                    </div>
                </div>
            </div>

            <!-- Botón de Pago -->
            <form action="confirmar_turno.php" method="POST">
                <button type="submit" 
                        class="w-full bg-gradient-to-r from-green-500 to-emerald-600 text-white p-4 rounded-xl font-bold text-lg hover:from-green-600 hover:to-emerald-700 transition shadow-lg hover:shadow-xl flex items-center justify-center gap-2 mb-4">
                    <i data-lucide="shield-check" class="w-5 h-5"></i>
                    Pagar Ahora (Simulado)
                </button>
            </form>

            <p class="text-xs text-gray-400 flex items-center justify-center gap-2">
                <i data-lucide="lock" class="w-3 h-3"></i>
                Pago encriptado y seguro
            </p>
        </div>

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