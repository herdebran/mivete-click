<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/mercadopago.php';
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../src/Controllers/AppointmentController.php';
require_once __DIR__ . '/../src/Controllers/EmailController.php';

$mpConfig = require __DIR__ . '/../config/mercadopago.php';

// Configurar access token
$accessToken = $mpConfig['modo_prueba'] 
    ? $mpConfig['sandbox']['access_token'] 
    : $mpConfig['production']['access_token'];

MercadoPagoConfig::setAccessToken($accessToken);

// Logging para debugging
$logFile = __DIR__ . '/webhook_log.txt';
file_put_contents($logFile, 
    date('Y-m-d H:i:s') . ' - IP: ' . ($_SERVER['REMOTE_ADDR'] ?? 'unknown') . PHP_EOL, 
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
    // ✅ SDK 3.x: Usar PaymentClient para obtener el pago
    use MercadoPago\Client\Payment\PaymentClient;
    $paymentClient = new PaymentClient();
    $payment = $paymentClient->get($paymentId);
    
    file_put_contents($logFile, 
        'Payment Status: ' . $payment->status . PHP_EOL, 
        FILE_APPEND
    );

    if ($payment->status === 'approved') {
        $database = new Database();
        $db = $database->getConnection();
        $controller = new AppointmentController($db);
        
        $appointmentId = $payment->external_reference;
        
        // 1. Confirmar pago (actualiza estado del turno)
        $controller->confirmPayment($appointmentId);
        
        // ✅ 2. INSERTAR EN TABLA PAYMENTS
        $controller->registerPayment($appointmentId, $paymentId, $payment->transaction_amount ?? 0);
        
        file_put_contents($logFile, 
            'PAYMENT INSERTED - ID: ' . $paymentId . ', Amount: ' . ($payment->transaction_amount ?? 0) . PHP_EOL, 
            FILE_APPEND
        );
        
        // 3. Obtener datos del turno
        $turno = $controller->getAppointmentById($appointmentId);
        
        // 4. Generar meeting link
        $meetingLink = "https://meet.jit.si/vetconnect-" . md5($appointmentId . time());
        $controller->appointment->updateMeetingLink($appointmentId, $meetingLink);
        
        // 5. Obtener adjuntos
        $adjuntos = $controller->getAttachments($appointmentId);
        
        // 6. Enviar emails
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