<?php
require_once __DIR__ . '/../../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class EmailController {
    private $mail;

    public function __construct() {
        $this->mail = new PHPMailer(true);
        
        $this->mail->isSMTP();
        $this->mail->CharSet = 'UTF-8';
        $this->mail->Encoding = 'base64';
        $this->mail->Host = 'smtp.gmail.com';
        $this->mail->SMTPAuth = true;
        $this->mail->Username = 'tuagenda.online.ok@gmail.com';  // ⚠️ CAMBIAR
        $this->mail->Password = 'dqkd tmcr tolb puji';      // ⚠️ CAMBIAR
        $this->mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $this->mail->Port = 587;
        
        $this->mail->setFrom('tu-email@gmail.com', 'Vete a un Click');
    }

    public function enviarConfirmacionTurno($turno, $meetingLink, $adjuntos = []) {
        try {
            $adjuntosHTML = '';
            if (!empty($adjuntos)) {
                $adjuntosHTML = '<div style="background:#f0f9ff;padding:15px;border-radius:12px;margin:20px 0;"><p style="margin:0 0 10px 0;font-weight:600;color:#1e40af;">📎 Archivos adjuntos:</p><ul style="margin:0;padding-left:20px;">';
                foreach ($adjuntos as $adj) {
                    $adjuntosHTML .= '<li style="color:#3b82f6;">' . htmlspecialchars($adj['nombre_original']) . '</li>';
                }
                $adjuntosHTML .= '</ul></div>';
            }

            // Email al CLIENTE
            $this->mail->clearAddresses();
            $this->mail->addAddress($turno['cliente_email'], $turno['cliente_nombre']);
            
            $this->mail->isHTML(true);
            $this->mail->Subject = '🐾 ¡Tu turno está confirmado! - Vete a un Click';
            
            $this->mail->Body = "
                <!DOCTYPE html>
                <html>
                <head>
                    <meta charset='UTF-8'>
                    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
                </head>
                <body style='font-family:Arial,sans-serif;background:#f9fafb;margin:0;padding:0;'>
                    <div style='max-width:600px;margin:0 auto;background:#ffffff;'>
                        <!-- Header -->
                        <div style='background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);padding:40px 20px;text-align:center;'>
                            <h1 style='color:#ffffff;margin:0;font-size:28px;'>🐾 Vete a un Click</h1>
                            <p style='color:#ffffff;margin:10px 0 0 0;opacity:0.9;'>Tu turno está confirmado</p>
                        </div>
                        
                        <!-- Contenido -->
                        <div style='padding:40px 30px;'>
                            <h2 style='color:#1f2937;margin:0 0 20px 0;font-size:24px;'>¡Hola, {$turno['cliente_nombre']}!</h2>
                            <p style='color:#6b7280;line-height:1.6;margin:0 0 30px 0;'>Tu consulta veterinaria online ha sido confirmada. Acá tenés todos los detalles:</p>
                            
                            <!-- Detalles -->
                            <div style='background:linear-gradient(135deg,#f0f9ff 0%,#e0e7ff 100%);padding:25px;border-radius:16px;margin:20px 0;'>
                                <table style='width:100%;border-collapse:collapse;'>
                                    <tr>
                                        <td style='padding:10px 0;color:#6b7280;font-size:14px;'>📅 Fecha</td>
                                        <td style='padding:10px 0;color:#1f2937;font-weight:600;text-align:right;'>" . date('d/m/Y', strtotime($turno['fecha'])) . "</td>
                                    </tr>
                                    <tr>
                                        <td style='padding:10px 0;color:#6b7280;font-size:14px;'>⏰ Hora</td>
                                        <td style='padding:10px 0;color:#1f2937;font-weight:600;text-align:right;'>{$turno['hora_inicio']} hs</td>
                                    </tr>
                                    <tr>
                                        <td style='padding:10px 0;color:#6b7280;font-size:14px;'>🩺 Veterinario</td>
                                        <td style='padding:10px 0;color:#1f2937;font-weight:600;text-align:right;'>Dr./Dra. " . htmlspecialchars($turno['vet_nombre'] . ' ' . $turno['vet_apellido']) . "</td>
                                    </tr>
                                    <tr>
                                        <td style='padding:10px 0;color:#6b7280;font-size:14px;'>🐕 Mascota</td>
                                        <td style='padding:10px 0;color:#1f2937;font-weight:600;text-align:right;'>" . htmlspecialchars($turno['cliente_mascota_nombre']) . "</td>
                                    </tr>
                                </table>
                            </div>
                            
                            {$adjuntosHTML}
                            
                            <!-- Botón -->
                            <div style='text-align:center;margin:30px 0;'>
                                <a href='{$meetingLink}' style='display:inline-block;background:linear-gradient(135deg,#10b981 0%,#059669 100%);color:#ffffff;padding:16px 40px;text-decoration:none;border-radius:12px;font-weight:600;font-size:16px;'>
                                    📹 Ingresar a la Consulta
                                </a>
                            </div>
                            
                            <!-- Info -->
                            <div style='background:#fef3c7;border-left:4px solid #f59e0b;padding:15px;border-radius:8px;margin:20px 0;'>
                                <p style='margin:0;color:#92400e;font-size:14px;'>💡 <strong>Consejo:</strong> Ingresá 5 minutos antes del horario acordado. Asegurate de tener buena conexión a internet.</p>
                            </div>
                            
                            <p style='color:#6b7280;line-height:1.6;margin:30px 0 0 0;'>Cualquier consulta, respondé este email. ¡Estamos para ayudarte!</p>
                        </div>
                        
                        <!-- Footer -->
                        <div style='background:#1f2937;padding:30px;text-align:center;'>
                            <p style='color:#9ca3af;margin:0;font-size:14px;'>© 2026 Vete a un Click. Todos los derechos reservados.</p>
                        </div>
                    </div>
                </body>
                </html>
            ";
            
            $this->mail->AltBody = "Tu turno está confirmado. Fecha: " . date('d/m/Y', strtotime($turno['fecha'])) . " Hora: " . $turno['hora_inicio'] . " hs. Link: " . $meetingLink;
            
            $this->mail->send();
            
            // Email al VETERINARIO
            $this->mail->clearAddresses();
            $this->mail->addAddress($turno['vet_email']);
            
            $this->mail->Subject = '🔔 Nuevo turno confirmado - Vete a un Click';
            
            $this->mail->Body = "
                <!DOCTYPE html>
                <html>
                <body style='font-family:Arial,sans-serif;background:#f9fafb;margin:0;padding:0;'>
                    <div style='max-width:600px;margin:0 auto;background:#ffffff;'>
                        <div style='background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);padding:40px 20px;text-align:center;'>
                            <h1 style='color:#ffffff;margin:0;font-size:28px;'>🩺 Vete a un Click</h1>
                            <p style='color:#ffffff;margin:10px 0 0 0;opacity:0.9;'>Nuevo turno confirmado</p>
                        </div>
                        <div style='padding:40px 30px;'>
                            <h2 style='color:#1f2937;margin:0 0 20px 0;font-size:24px;'>Tenés un nuevo turno</h2>
                            <div style='background:linear-gradient(135deg,#f0f9ff 0%,#e0e7ff 100%);padding:25px;border-radius:16px;margin:20px 0;'>
                                <table style='width:100%;'>
                                    <tr><td style='padding:8px 0;color:#6b7280;'>Fecha:</td><td style='font-weight:600;text-align:right;'>" . date('d/m/Y', strtotime($turno['fecha'])) . "</td></tr>
                                    <tr><td style='padding:8px 0;color:#6b7280;'>Hora:</td><td style='font-weight:600;text-align:right;'>{$turno['hora_inicio']} hs</td></tr>
                                    <tr><td style='padding:8px 0;color:#6b7280;'>Cliente:</td><td style='font-weight:600;text-align:right;'>" . htmlspecialchars($turno['cliente_nombre']) . "</td></tr>
                                    <tr><td style='padding:8px 0;color:#6b7280;'>Mascota:</td><td style='font-weight:600;text-align:right;'>" . htmlspecialchars($turno['cliente_mascota_nombre']) . "</td></tr>
                                    <tr><td style='padding:8px 0;color:#6b7280;'>Motivo:</td><td style='font-weight:600;text-align:right;'>" . htmlspecialchars($turno['motivo_consulta']) . "</td></tr>
                                </table>
                            </div>
                            <div style='text-align:center;margin:30px 0;'>
                                <a href='{$meetingLink}' style='display:inline-block;background:linear-gradient(135deg,#10b981 0%,#059669 100%);color:#ffffff;padding:16px 40px;text-decoration:none;border-radius:12px;font-weight:600;'>📹 Iniciar Videollamada</a>
                            </div>
                            {$adjuntosHTML}
                        </div>
                    </div>
                </body>
                </html>
            ";
            
            $this->mail->send();
            return ['success' => true];
            
        } catch (Exception $e) {
            error_log("Error enviando email: {$this->mail->ErrorInfo}");
            return ['success' => false, 'message' => 'Error al enviar emails'];
        }
    }

    public function enviarResumenConsulta($turno, $diagnostico, $recomendacion) {
        try {
            $this->mail->clearAddresses();
            $this->mail->addAddress($turno['cliente_email'], $turno['cliente_nombre']);

            $this->mail->isHTML(true);
            $this->mail->Subject = '📋 Resumen de tu consulta - Vete a un Click';

            $this->mail->Body = "
            <!DOCTYPE html>
            <html>
            <body style='font-family:Arial,sans-serif;background:#f9fafb;margin:0;padding:0;'>
                <div style='max-width:600px;margin:0 auto;background:#ffffff;'>
                    <div style='background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);padding:40px 20px;text-align:center;'>
                        <h1 style='color:#ffffff;margin:0;font-size:28px;'>🐾 Vete a un Click</h1>
                        <p style='color:#ffffff;margin:10px 0 0 0;opacity:0.9;'>Resumen de Consulta</p>
                    </div>
                    <div style='padding:40px 30px;'>
                        <h2 style='color:#1f2937;margin:0 0 20px 0;font-size:24px;'>Hola, {$turno['cliente_nombre']}</h2>
                        <p style='color:#6b7280;line-height:1.6;margin:0 0 30px 0;'>Acá tenés el resumen de la consulta de <strong>{$turno['cliente_mascota_nombre']}</strong>:</p>
                        
                        <div style='background:linear-gradient(135deg,#f0fdf4 0%,#dcfce7 100%);padding:25px;border-radius:16px;margin:20px 0;border-left:4px solid #10b981;'>
                            <p style='margin:0 0 10px 0;font-weight:600;color:#047857;font-size:16px;'>📋 Diagnóstico:</p>
                            <p style='margin:0;color:#065f46;line-height:1.6;'>".nl2br(htmlspecialchars($diagnostico))."</p>
                        </div>
                        
                        ".($recomendacion ? "
                        <div style='background:linear-gradient(135deg,#fef3c7 0%,#fde68a 100%);padding:25px;border-radius:16px;margin:20px 0;border-left:4px solid #f59e0b;'>
                            <p style='margin:0 0 10px 0;font-weight:600;color:#92400e;font-size:16px;'>💡 Recomendación:</p>
                            <p style='margin:0;color:#78350f;line-height:1.6;'>".nl2br(htmlspecialchars($recomendacion))."</p>
                        </div>
                        " : "")."
                        
                        <p style='color:#6b7280;line-height:1.6;margin:30px 0 0 0;'>Cualquier duda, no dudes en contactarnos. ¡Que se mejore {$turno['cliente_mascota_nombre']}!</p>
                    </div>
                    <div style='background:#1f2937;padding:30px;text-align:center;'>
                        <p style='color:#9ca3af;margin:0;font-size:14px;'>© 2026 Vete a un Click</p>
                    </div>
                </div>
            </body>
            </html>
        ";

            $this->mail->send();
            return ['success' => true];

        } catch (Exception $e) {
            error_log("Error enviando resumen: {$this->mail->ErrorInfo}");
            return ['success' => false];
        }
    }
}