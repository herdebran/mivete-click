<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../src/Controllers/AppointmentController.php';
require_once __DIR__ . '/../src/Controllers/EmailController.php';

MercadoPago\SDK::setAccessToken('TU_ACCESS_TOKEN_REAL');

// Leer el payload
$input = file_get_contents('php://input');
$payload = json_decode($input, true);

// Validar que sea un pago
if (!isset($payload['type']) || $payload['type'] !== 'payment') {
    http_response_code(200);
    exit;
}

$paymentId = $payload['data']['id'];

// Obtener información del pago
$payment = MercadoPago\Payment::find_by_id($paymentId);

// Validar firma (opcional pero recomendado para producción)
// https://www.mercadopago.com.ar/developers/es/guides/online-payments/checkout-pro/security

if ($payment->status === 'approved') {
    $database = new Database();
    $db = $database->getConnection();
    $controller = new AppointmentController($db);
    
    $appointmentId = $payment->external_reference;
    
    // Confirmar pago
    $controller->confirmPayment($appointmentId);
    
    // Obtener datos del turno para enviar emails
    $turno = $controller->getAppointmentById($appointmentId);
    
    // Generar meeting link
    $meetingLink = "https://meet.jit.si/vetconnect-" . md5($appointmentId . time());
    $controller->appointment->updateMeetingLink($appointmentId, $meetingLink);
    
    // Obtener adjuntos
    $adjuntos = $controller->getAttachments($appointmentId);
    
    // Enviar emails
    $emailController = new EmailController();
    $emailController->enviarConfirmacionTurno($turno, $meetingLink, $adjuntos);
    
    http_response_code(200);
    echo json_encode(['status' => 'success']);
} else {
    http_response_code(200);
    echo json_encode(['status' => 'ignored']);
}
?>