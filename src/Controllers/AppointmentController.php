<?php
require_once __DIR__ . '/../Models/Appointment.php';
require_once __DIR__ . '/../Models/Professional.php';

class AppointmentController {
    private $db;
    private $appointment;
    private $professional;

    public function __construct($db) {
        $this->db = $db;
        $this->appointment = new Appointment($db);
        $this->professional = new Professional($db);
    }

    public function getAllProfessionals() {
        $query = "SELECT id, nombre, apellido, email, matricula, estado_verificacion 
                  FROM professionals 
                  WHERE estado_verificacion = 1";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function createAppointment($data) {
        $vet = $this->professional->getById($data['professional_id']);
        if (!$vet) {
            return ['success' => false, 'message' => 'Veterinario no encontrado'];
        }

        $data['estado'] = 'pendiente_pago';
        $appointmentId = $this->appointment->create($data);
        
        if ($appointmentId) {
            return ['success' => true, 'appointment_id' => $appointmentId];
        }
        
        return ['success' => false, 'message' => 'Error al crear la reserva'];
    }

    public function confirmPayment($appointmentId) {
        return $this->appointment->updateStatus($appointmentId, 'pagado');
    }

    public function finalizeAppointment($appointmentId, $hora) {
        $turno = $this->appointment->getById($appointmentId);
        if (!$turno || $turno['estado'] !== 'pagado') {
            return ['success' => false, 'message' => 'El turno no está pagado'];
        }

        if (!$this->appointment->isSlotAvailable($turno['professional_id'], $turno['fecha'], $hora)) {
            return ['success' => false, 'message' => 'Ese horario ya no está disponible'];
        }

        $query = "UPDATE appointments SET hora_inicio = :hora, hora_fin = :hora_fin 
                  WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $horaFin = date('H:i', strtotime($hora . ' +30 minutes'));
        $stmt->bindParam(":hora", $hora);
        $stmt->bindParam(":hora_fin", $horaFin);
        $stmt->bindParam(":id", $appointmentId);
        
        if ($stmt->execute()) {
            $meetingLink = "https://meet.jit.si/vetconnect-" . md5($appointmentId . time());
            $this->appointment->updateMeetingLink($appointmentId, $meetingLink);
            
            return ['success' => true, 'meeting_link' => $meetingLink];
        }
        
        return ['success' => false, 'message' => 'Error al confirmar el turno'];
    }

    public function getAvailableSlots($professionalId, $fecha) {
        return $this->appointment->getAvailableSlots($professionalId, $fecha);
    }

    public function getAppointmentById($id) {
        return $this->appointment->getById($id);
    }

    public function getNextAvailableSlots($professionalId, $limit = 10) {
        return $this->appointment->getNextAvailableSlots($professionalId, $limit);
    }

    public function getProfessionalsWithAvailability($fecha) {
        $diaSemana = date('w', strtotime($fecha));

        $query = "SELECT p.id, p.nombre, p.apellido, p.matricula, 
                     MIN(a.hora_inicio) as primer_horario
              FROM professionals p
              INNER JOIN availability a ON p.id = a.professional_id
              WHERE p.estado_verificacion = 1 
              AND a.dia_semana = :dia_semana 
              AND a.activo = 1
              GROUP BY p.id";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(":dia_semana", $diaSemana);
        $stmt->execute();
        
        $vets = $stmt->fetchAll();
        
        $vetsDisponibles = [];
        foreach ($vets as $vet) {
            $slots = $this->appointment->getAvailableSlots($vet['id'], $fecha);
            if (!empty($slots)) {
                $vet['primer_turno'] = $slots[0];
                $vetsDisponibles[] = $vet;
            }
        }
        
        return $vetsDisponibles;
    }

    public function getProfessionalsWithNextAvailability() {
        $hoy = new DateTime();
        $hoy->setTime(0, 0);
        
        $query = "SELECT p.id, p.nombre, p.apellido, p.matricula 
                  FROM professionals p
                  WHERE p.estado_verificacion = 1";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $vets = $stmt->fetchAll();
        
        $vetsDisponibles = [];
        
        foreach ($vets as $vet) {
            $slots = $this->appointment->getNextAvailableSlots($vet['id'], 1);
            
            if (!empty($slots)) {
                $vet['primer_turno'] = $slots[0]['dia_nombre'] . ' ' . $slots[0]['fecha_formateada'] . ' - ' . $slots[0]['hora'];
                $vet['fecha'] = $slots[0]['fecha'];
                $vetsDisponibles[] = $vet;
            }
        }
        
        usort($vetsDisponibles, function($a, $b) {
            return strtotime($a['fecha']) - strtotime($b['fecha']);
        });
        
        return $vetsDisponibles;
    }

    public function getAppointmentsForToday($professionalId) {
        $hoy = date('Y-m-d');
        $query = "SELECT a.* 
                  FROM appointments a 
                  WHERE a.professional_id = :professional_id 
                  AND a.fecha = :fecha 
                  AND a.estado = 'pagado'
                  ORDER BY a.hora_inicio";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(":professional_id", $professionalId);
        $stmt->bindParam(":fecha", $hoy);
        $stmt->execute();
        
        $turnos = $stmt->fetchAll();
        
        foreach ($turnos as &$turno) {
            $turno['adjuntos'] = $this->getAttachments($turno['id']);
        }
        
        return $turnos;
    }
    public function saveAttachment($appointmentId, $nombreOriginal, $nombreArchivo, $tipo, $tamano, $ruta) {
        // Usar nombres de parámetros sin acentos para evitar problemas de encoding
        $query = "INSERT INTO attachments (appointment_id, nombre_original, nombre_archivo, tipo_archivo, tamano, ruta) 
              VALUES (:app_id, :nombre_orig, :nombre_arch, :tipo_arch, :tamano, :ruta)";

        $stmt = $this->db->prepare($query);

        // Validar que todos los valores existan
        if (empty($appointmentId) || empty($nombreArchivo) || empty($ruta)) {
            error_log("Error saveAttachment: Datos incompletos");
            return false;
        }

        $stmt->bindParam(":app_id", $appointmentId, PDO::PARAM_INT);
        $stmt->bindParam(":nombre_orig", $nombreOriginal, PDO::PARAM_STR);
        $stmt->bindParam(":nombre_arch", $nombreArchivo, PDO::PARAM_STR);
        $stmt->bindParam(":tipo_arch", $tipo, PDO::PARAM_STR);
        $stmt->bindParam(":tamano", $tamano, PDO::PARAM_INT);
        $stmt->bindParam(":ruta", $ruta, PDO::PARAM_STR);

        return $stmt->execute();
    }

    public function getAttachments($appointmentId) {
        $query = "SELECT * FROM attachments WHERE appointment_id = :appointment_id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(":appointment_id", $appointmentId);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getAppointmentsForDate($professionalId, $fecha) {
        $query = "SELECT a.* FROM appointments a 
              WHERE a.professional_id = :professional_id 
              AND a.fecha = :fecha 
              ORDER BY a.hora_inicio";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(":professional_id", $professionalId);
        $stmt->bindParam(":fecha", $fecha);
        $stmt->execute();

        $turnos = $stmt->fetchAll();
        foreach ($turnos as &$turno) {
            $turno['adjuntos'] = $this->getAttachments($turno['id']);
        }
        return $turnos;
    }

    public function getPendingAppointmentsCount($professionalId) {
        $query = "SELECT COUNT(*) FROM appointments 
              WHERE professional_id = :professional_id 
              AND estado = 'pagado' 
              AND fecha >= CURDATE()";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(":professional_id", $professionalId);
        $stmt->execute();
        return $stmt->fetchColumn();
    }

    public function getAttendedCount($professionalId) {
        $query = "SELECT COUNT(*) FROM appointments 
              WHERE professional_id = :professional_id 
              AND estado = 'atendido'";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(":professional_id", $professionalId);
        $stmt->execute();
        return $stmt->fetchColumn();
    }

    public function getWeeklyCount($professionalId) {
        $query = "SELECT COUNT(*) FROM appointments 
              WHERE professional_id = :professional_id 
              AND fecha BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(":professional_id", $professionalId);
        $stmt->execute();
        return $stmt->fetchColumn();
    }

    /*
     * Metodo para registrar el pago de un appointment. Generalmente se llama desde le webhook cuando se registra
     * un pago exitoso.
     */
    public function registerPayment($appointmentId, $mpPaymentId, $monto, $estado = 'approved') {
        try {
            $query = "INSERT INTO payments (appointment_id, mp_payment_id, monto, estado, fecha_pago) 
                  VALUES (:appointment_id, :mp_payment_id, :monto, :estado, NOW())";

            $stmt = $this->db->prepare($query);
            $stmt->bindParam(":appointment_id", $appointmentId, PDO::PARAM_INT);
            $stmt->bindParam(":mp_payment_id", $mpPaymentId, PDO::PARAM_STR);
            $stmt->bindParam(":monto", $monto, PDO::PARAM_STR);
            $stmt->bindParam(":estado", $estado, PDO::PARAM_STR);

            $executed = $stmt->execute();

            // Logging para debug
            error_log("registerPayment: appointment_id=$appointmentId, mp_payment_id=$mpPaymentId, executed=" . ($executed ? 'true' : 'false'));

            return $executed;
        } catch (PDOException $e) {
            error_log("ERROR registerPayment: " . $e->getMessage());
            return false;
        }
    }

}