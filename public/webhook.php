<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/mercadopago.php';
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../src/Controllers/AppointmentController.php';
require_once __DIR__ . '/../src/Controllers/EmailController.php';

// ✅ CORREGIDO: use statements AL PRINCIPIO, no dentro del try
use MercadoPago\MercadoPagoConfig;
use MercadoPago\Client\Payment\PaymentClient;

$mpConfig = require __DIR__ . '/../config/mercadopago.php';

// Configurar access token
$accessToken = $mpConfig['modo_prueba'] 
    ? $mpConfig['sandbox']['access_token'] 
    : $mpConfig['production']['access_token'];

MercadoPagoConfig::setAccessToken($accessToken);

// Logging para debugging
$logFile = __DIR__ . '/webhook_log.txt';

// ✅ Verificar que se puede escribir el log
if (!is_writable(dirname($logFile))) {
    error_log("ERROR: No se puede escribir en " . dirname($logFile));
}

file_put_contents($logFile, 
    date('Y-m-d H:i:s') . ' - IP: ' . ($_SERVER['REMOTE_ADDR'] ?? 'unknown') . PHP_EOL, 
    FILE_APPEND | LOCK_EX
);

$input = file_get_contents('php://input');
file_put_contents($logFile, 
    'Payload: ' . $input . PHP_EOL . str_repeat('-', 50) . PHP_EOL, 
    FILE_APPEND | LOCK_EX
);

$payload = json_decode($input, true);

if (!isset($payload['type']) || $payload['type'] !== 'payment') {
    http_response_code(200);
    exit;
}

$paymentId = $payload['data']['id'];

try {
    // ✅ SDK 3.x: Usar PaymentClient para obtener el pago
    $paymentClient = new PaymentClient();
    $payment = $paymentClient->get($paymentId);
    
    file_put_contents($logFile, 
        'Payment Status: ' . $payment->status . PHP_EOL, 
        FILE_APPEND | LOCK_EX
    );

    if ($payment->status === 'approved') {
        $database = new Database();
        $db = $database->getConnection();
        $controller = new AppointmentController($db);
        
        $appointmentId = $payment->external_reference;
        
        // 1. Confirmar pago (actualiza estado del turno)
        $controller->confirmPayment($appointmentId);
        file_put_contents($logFile, 'confirmPayment() ejecutado' . PHP_EOL, FILE_APPEND | LOCK_EX);
        
        // ✅ 2. Registrar pago usando el método del controller
        $monto = $payment->transaction_amount ?? 0;
        $paymentResult = $controller->registerPayment($appointmentId, $paymentId, $monto);
        
        if ($paymentResult) {
            file_put_contents($logFile, 
                '✅ PAYMENT INSERTED - ID: ' . $paymentId . ', Amount: ' . $monto . PHP_EOL, 
                FILE_APPEND | LOCK_EX
            );
        } else {
            file_put_contents($logFile, 
                '❌ ERROR: registerPayment() falló' . PHP_EOL, 
                FILE_APPEND | LOCK_EX
            );
            error_log("ERROR: registerPayment() returned false for appointment $appointmentId");
        }
        
        // 3. Obtener datos del turno
        $turno = $controller->getAppointmentById($appointmentId);
        
        // 4. Generar meeting link
        // ✅ CORREGIDO: Sin espacios extra en la URL
        $meetingLink = "https://meet.jit.si/vetconnect-" . md5($appointmentId . time());
        $controller->appointment->updateMeetingLink($appointmentId, $meetingLink);
        
        // 5. Obtener adjuntos
        $adjuntos = $controller->getAttachments($appointmentId);
        
        // 6. Enviar emails
        $emailController = new EmailController();
        $emailController->enviarConfirmacionTurno($turno, $meetingLink, $adjuntos);
        
        file_put_contents($logFile, 
            '✅ SUCCESS - Turno ' . $appointmentId . ' confirmado' . PHP_EOL, 
            FILE_APPEND | LOCK_EX
        );
        
        http_response_code(200);
        echo json_encode(['status' => 'success']);
    } else {
        file_put_contents($logFile, 'Payment NO approved - Status: ' . $payment->status . PHP_EOL, FILE_APPEND | LOCK_EX);
        http_response_code(200);
        echo json_encode(['status' => 'ignored']);
    }
    
} catch (Exception $e) {
    file_put_contents($logFile, 
        '❌ ERROR GENERAL: ' . $e->getMessage() . PHP_EOL . 
        'Stack: ' . $e->getTraceAsString() . PHP_EOL, 
        FILE_APPEND | LOCK_EX
    );
    error_log("WEBHOOK ERROR: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['status' => 'error']);
}
?>