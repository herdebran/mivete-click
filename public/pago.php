<?php
session_start();

// Configurar error reporting (borrar después de testing)
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/mercadopago.php';
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../src/Controllers/AppointmentController.php';

// ✅ IMPORTS CORRECTOS PARA SDK 3.8.0
use MercadoPago\MercadoPagoConfig;
use MercadoPago\Client\Preference\PreferenceClient;
use MercadoPago\Client\MerchantOrder\MerchantOrderClient;

$mpConfig = require __DIR__ . '/../config/mercadopago.php';

// ✅ Configurar access token (NUEVA FORMA SDK 3.x)
$accessToken = $mpConfig['modo_prueba'] 
    ? $mpConfig['sandbox']['access_token'] 
    : $mpConfig['production']['access_token'];

MercadoPagoConfig::setAccessToken($accessToken);

// Opcional: Configurar timeout
MercadoPagoConfig::setTimeout(30000); // 30 segundos

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

    // ✅ CREAR PREFERENCIA CON SDK 3.8.0 (NUEVA API)
    $client = new PreferenceClient();
    
    $preference_data = [
        "items" => [
            [
                "title" => "Consulta Veterinaria Online",
                "quantity" => 1,
                "unit_price" => (float)$mpConfig['precio_consulta'],
                "currency_id" => $mpConfig['moneda']
            ]
        ],
        "payer" => [
            "name" => $_POST['cliente_nombre'],
            "email" => $_POST['cliente_email']
        ],
        "back_urls" => [
            "success" => $mpConfig['urls']['success'],
            "failure" => $mpConfig['urls']['failure'],
            "pending" => $mpConfig['urls']['pending']
        ],
        "external_reference" => (string)$appointmentId,
        "notification_url" => $mpConfig['urls']['webhook'],
        "auto_return" => "approved",
        "statement_descriptor" => "VETE A UN CLICK"
    ];
    
    // Crear la preferencia
    $result = $client->create($preference_data);
    
    // Verificar que se creó correctamente
    if (empty($result->init_point)) {
        throw new Exception("No se pudo generar el link de pago de Mercado Pago");
    }
    
    $_SESSION['appointment_id'] = $appointmentId;
    $_SESSION['tipo_turno'] = $tipoBusqueda;
    $_SESSION['preference_id'] = $result->id;

    // Redirigir al checkout de Mercado Pago
    header("Location: " . $result->init_point);
    exit;

} catch (Exception $e) {
    error_log("Error en pago SDK 3.x: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    die("<div style='text-align:center;padding:50px;font-family:Arial;'><h1>Error</h1><p>" . htmlspecialchars($e->getMessage()) . "</p><a href='index.php'>Volver</a></div>");
}