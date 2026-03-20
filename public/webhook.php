<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/mercadopago.php';
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../src/Controllers/AppointmentController.php';
require_once __DIR__ . '/../src/Controllers/EmailController.php';

$mpConfig = require __DIR__ . '/../config/mercadopago.php';

// Configurar SDK
if ($mpConfig['modo_prueba']) {
    MercadoPago\SDK::setAccessToken($mpConfig['sandbox']['access_token']);
} else {
    MercadoPago\SDK::setAccessToken($mpConfig['production']['access_token']);
}

// Logging para debugging (eliminar en producción)
$logFile = __DIR__ . '/webhook_log.txt';
file_put_contents($logFile, 
    date('Y-m-d H:i:s') . ' - IP: ' . $_SERVER['REMOTE_ADDR'] . PHP_EOL, 
    FILE_APPEND
);

$input = file_get_contents('php://input');
file_put_contents($logFile, 
    'Payload: ' . $input . PHP_EOL . str_repeat('-', 50) . PHP_EOL, 
    FILE_APPEND
);

$payload = json_decode($input, true);

if (!isset($payload['type']) || $payload['type'] !== 'payment') {
    http_response_code(200);
    exit;
}

$paymentId = $payload['data']['id'];

try {
    $payment = MercadoPago\Payment::find_by_id($paymentId);
    
    file_put_contents($logFile, 
        'Payment Status: ' . $payment->status . PHP_EOL, 
        FILE_APPEND
    );

    if ($payment->status === 'approved') {
        $database = new Database();
        $db = $database->getConnection();
        $controller = new AppointmentController($db);
        
        $appointmentId = $payment->external_reference;
        
        // Confirmar pago
        $controller->confirmPayment($appointmentId);
        
        // Obtener datos del turno
        $turno = $controller->getAppointmentById($appointmentId);
        
        // Generar meeting link
        $meetingLink = "https://meet.jit.si/vetconnect-" . md5($appointmentId . time());
        $controller->appointment->updateMeetingLink($appointmentId, $meetingLink);
        
        // Obtener adjuntos
        $adjuntos = $controller->getAttachments($appointmentId);
        
        // Enviar emails
        $emailController = new EmailController();
        $emailController->enviarConfirmacionTurno($turno, $meetingLink, $adjuntos);
        
        file_put_contents($logFile, 
            'SUCCESS - Turno ' . $appointmentId . ' confirmado' . PHP_EOL, 
            FILE_APPEND
        );
        
        http_response_code(200);
        echo json_encode(['status' => 'success']);
    } else {
        http_response_code(200);
        echo json_encode(['status' => 'ignored']);
    }
    
} catch (Exception $e) {
    file_put_contents($logFile, 
        'ERROR: ' . $e->getMessage() . PHP_EOL, 
        FILE_APPEND
    );
    http_response_code(500);
    echo json_encode(['status' => 'error']);
}
?>