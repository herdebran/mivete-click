<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/mercadopago.php';
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../src/Controllers/AppointmentController.php';

$mpConfig = require __DIR__ . '/../config/mercadopago.php';

// ✅ Configurar SDK según modo
if ($mpConfig['modo_prueba']) {
    MercadoPago\SDK::setAccessToken($mpConfig['sandbox']['access_token']);
} else {
    MercadoPago\SDK::setAccessToken($mpConfig['production']['access_token']);
}

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

    // ✅ MODO SIMULADO (solo para testing rápido sin MP)
    if (isset($_POST['modo_simulado']) && $_POST['modo_simulado'] === '1' && $mpConfig['modo_prueba']) {
        $_SESSION['appointment_id'] = $appointmentId;
        $_SESSION['tipo_turno'] = $tipoBusqueda;
        header("Location: pago_simulado_paso_final.php");
        exit;
    }

    // ✅ CREAR PREFERENCIA DE MERCADO PAGO
    $preference = new MercadoPago\Preference();
    
    $item = new MercadoPago\Item();
    $item->title = "Consulta Veterinaria Online";
    $item->quantity = 1;
    $item->unit_price = $mpConfig['precio_consulta'];
    $item->currency_id = $mpConfig['moneda'];
    
    $preference->items = [$item];
    
    $preference->payer = [
        "name" => $_POST['cliente_nombre'],
        "email" => $_POST['cliente_email']
    ];
    
    $preference->back_urls = [
        "success" => $mpConfig['urls']['success'],
        "failure" => $mpConfig['urls']['failure'],
        "pending" => $mpConfig['urls']['pending']
    ];
    
    $preference->external_reference = $appointmentId;
    $preference->notification_url = $mpConfig['urls']['webhook'];
    $preference->auto_return = "approved";
    
    $preference->save();
    
    $_SESSION['appointment_id'] = $appointmentId;
    $_SESSION['tipo_turno'] = $tipoBusqueda;
    $_SESSION['preference_id'] = $preference->id;

    // Redirigir a Mercado Pago
    header("Location: " . $preference->init_point);
    exit;

} catch (Exception $e) {
    error_log("Error en pago: " . $e->getMessage());
    die("<div style='text-align:center;padding:50px;font-family:Arial;'><h1>Error</h1><p>Hubo un problema con tu reserva.</p><a href='index.php'>Volver</a></div>");
}
?>