<?php
// ✅ Ruta corregida: dos niveles hacia arriba para llegar al root del proyecto
require_once __DIR__ . '/../../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class EmailController {
    private $mail;

    public function __construct() {
        $this->mail = new PHPMailer(true);
        
        // Configuración SMTP (ajustar con tus credenciales reales)
        $this->mail->isSMTP();
        $this->mail->CharSet = 'UTF-8';
        $this->mail->Encoding = 'base64';
        $this->mail->Host = 'smtp.gmail.com';
        $this->mail->SMTPAuth = true;
        $this->mail->Username = 'tuagenda.online.ok@gmail.com'; // ️ ⚠️ CAMBIAR POR MAIL DE VETE CLICK
        $this->mail->Password = 'dqkd tmcr tolb puji';      // ⚠️ CAMBIAR POR TU APP PASSWORD
        $this->mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $this->mail->Port = 587;
        
        $this->mail->setFrom('noreply@veteaunclick.com', 'VeteAUnClick');
        //$this->mail->setName('Vete a Un Click');
		
    }

    public function enviarConfirmacionTurno($turno, $meetingLink, $adjuntos = []) {
        try {
            // Email al CLIENTE
            $this->mail->clearAddresses();
            $this->mail->addAddress($turno['cliente_email'], $turno['cliente_nombre']);
            
            $this->mail->isHTML(true);
            $this->mail->Subject = '✅ Tu turno veterinario está confirmado - VetConnect';
            
            $adjuntosHTML = '';
            if (!empty($adjuntos)) {
                $adjuntosHTML = '<p><strong>Archivos adjuntos:</strong></p><ul>';
                foreach ($adjuntos as $adj) {
                    $adjuntosHTML .= '<li>' . htmlspecialchars($adj['nombre_original']) . '</li>';
                }
                $adjuntosHTML .= '</ul>';
            }
            
            $this->mail->Body = "
                <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
                    <h2 style='color: #2563eb;'>🐾 ¡Tu turno está confirmado!</h2>
                    
                    <div style='background: #f0f9ff; padding: 20px; border-radius: 8px; margin: 20px 0;'>
                        <p><strong>📅 Fecha:</strong> " . date('d/m/Y', strtotime($turno['fecha'])) . "</p>
                        <p><strong>⏰ Hora:</strong> " . $turno['hora_inicio'] . " hs</p>
                        <p><strong>🩺 Veterinario:</strong> Dr./Dra. " . htmlspecialchars($turno['vet_nombre'] . ' ' . $turno['vet_apellido']) . "</p>
                        <p><strong>🐕 Mascota:</strong> " . htmlspecialchars($turno['cliente_mascota_nombre']) . "</p>
                        <p><strong>📝 Motivo:</strong> " . htmlspecialchars($turno['motivo_consulta']) . "</p>
                    </div>
                    
                    <div style='background: #dcfce7; padding: 20px; border-radius: 8px; margin: 20px 0;'>
                        <p style='margin: 0;'><strong>🔗 Link de la videollamada:</strong></p>
                        <a href='{$meetingLink}' style='display: inline-block; background: #16a34a; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px; margin-top: 10px;'>
                            Ingresar a la consulta
                        </a>
                    </div>
                    
                    {$adjuntosHTML}
                    
                    <p style='color: #6b7280; font-size: 14px; margin-top: 30px;'>
                        Por favor, ingresá 5 minutos antes del horario acordado.
                    </p>
                </div>
            ";
            
            $this->mail->AltBody = "Tu turno está confirmado. Fecha: " . date('d/m/Y', strtotime($turno['fecha'])) . " Hora: " . $turno['hora_inicio'] . " hs. Link: " . $meetingLink;
            
            $this->mail->send();
            
            // Email al VETERINARIO
            $this->mail->clearAddresses();
            $this->mail->addAddress($turno['vet_email']);
            
            $this->mail->Subject = '🔔 Nuevo turno confirmado - VetConnect';
            
            $this->mail->Body = "
                <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
                    <h2 style='color: #2563eb;'>🔔 Nuevo Turno Confirmado</h2>
                    
                    <div style='background: #f0f9ff; padding: 20px; border-radius: 8px; margin: 20px 0;'>
                        <p><strong>📅 Fecha:</strong> " . date('d/m/Y', strtotime($turno['fecha'])) . "</p>
                        <p><strong>⏰ Hora:</strong> " . $turno['hora_inicio'] . " hs</p>
                        <p><strong>👤 Cliente:</strong> " . htmlspecialchars($turno['cliente_nombre']) . "</p>
                        <p><strong>🐕 Mascota:</strong> " . htmlspecialchars($turno['cliente_mascota_nombre']) . "</p>
                        <p><strong>📝 Motivo:</strong> " . htmlspecialchars($turno['motivo_consulta']) . "</p>
                    </div>
                    
                    <div style='background: #dcfce7; padding: 20px; border-radius: 8px; margin: 20px 0;'>
                        <p style='margin: 0;'><strong>🔗 Link de la videollamada:</strong></p>
                        <a href='{$meetingLink}' style='display: inline-block; background: #16a34a; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px; margin-top: 10px;'>
                            Ingresar a la consulta
                        </a>
                    </div>
                    
                    {$adjuntosHTML}
                </div>
            ";
            
            $this->mail->send();
            
            return ['success' => true];
            
        } catch (Exception $e) {
            error_log("Error enviando email: {$this->mail->ErrorInfo}");
            return ['success' => false, 'message' => 'Error al enviar emails'];
        }
    }
}