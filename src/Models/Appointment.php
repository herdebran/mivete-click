<?php
class Appointment {
    private $conn;
    private $table = "appointments";

    public function __construct($db) {
        $this->conn = $db;
    }


	public function create($data) {
		$query = "INSERT INTO {$this->table} 
				  (professional_id, cliente_nombre, cliente_email, cliente_telefono, 
				   cliente_mascota_nombre, motivo_consulta, fecha, estado, created_at) 
				  VALUES (:professional_id, :cliente_nombre, :cliente_email, :cliente_telefono, 
						  :cliente_mascota_nombre, :motivo_consulta, :fecha, :estado, NOW())";
		
		$stmt = $this->conn->prepare($query);
		
		$stmt->bindParam(":professional_id", $data['professional_id']);
		$stmt->bindParam(":cliente_nombre", $data['cliente_nombre']);
		$stmt->bindParam(":cliente_email", $data['cliente_email']);
		$stmt->bindParam(":cliente_telefono", $data['cliente_telefono']);
		$stmt->bindParam(":cliente_mascota_nombre", $data['cliente_mascota_nombre']);
		$stmt->bindParam(":motivo_consulta", $data['motivo_consulta']);
		$stmt->bindParam(":fecha", $data['fecha']);
		$stmt->bindParam(":estado", $data['estado']);
		
		if ($stmt->execute()) {
			return $this->conn->lastInsertId();
		}
		return false;
	}

    public function updateStatus($id, $estado) {
        $query = "UPDATE {$this->table} SET estado = :estado WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":estado", $estado);
        $stmt->bindParam(":id", $id);
        return $stmt->execute();
    }

    public function updateMeetingLink($id, $link) {
        $query = "UPDATE {$this->table} SET meeting_link = :link WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":link", $link);
        $stmt->bindParam(":id", $id);
        return $stmt->execute();
    }

    public function getById($id) {
        $query = "SELECT a.*, p.nombre as vet_nombre, p.apellido as vet_apellido, p.email as vet_email 
                  FROM {$this->table} a 
                  JOIN professionals p ON a.professional_id = p.id 
                  WHERE a.id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        return $stmt->fetch();
    }

    public function getAvailableSlots($professionalId, $fecha) {
        $diaSemana = date('w', strtotime($fecha));

        // Obtener disponibilidad base
        $query = "SELECT hora_inicio, hora_fin FROM availability 
              WHERE professional_id = :profesional_id 
              AND dia_semana = :dia_semana 
              AND activo = 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":profesional_id", $professionalId);
        $stmt->bindParam(":dia_semana", $diaSemana);
        $stmt->execute();
        $disponibilidad = $stmt->fetch();

        if (!$disponibilidad) {
            return [];
        }

        // ✅ Obtener bloqueos para esa fecha
        $queryBloqueos = "SELECT hora_inicio, hora_fin FROM blocked_dates 
                      WHERE professional_id = :profesional_id 
                      AND fecha = :fecha";
        $stmtBloqueos = $this->conn->prepare($queryBloqueos);
        $stmtBloqueos->bindParam(":profesional_id", $professionalId);
        $stmtBloqueos->bindParam(":fecha", $fecha);
        $stmtBloqueos->execute();
        $bloqueos = $stmtBloqueos->fetchAll();

        // Generar slots de 30 minutos
        $slots = [];
        $inicio = strtotime($disponibilidad['hora_inicio']);
        $fin = strtotime($disponibilidad['hora_fin']);
        $duracion = 30 * 60; // 30 minutos en segundos

        // Obtener turnos ya reservados
        $queryReservados = "SELECT hora_inicio FROM appointments 
                       WHERE professional_id = :profesional_id 
                       AND fecha = :fecha 
                       AND estado IN ('pagado', 'pendiente_pago')";
        $stmtReservados = $this->conn->prepare($queryReservados);
        $stmtReservados->bindParam(":profesional_id", $professionalId);
        $stmtReservados->bindParam(":fecha", $fecha);
        $stmtReservados->execute();
        $reservados = $stmtReservados->fetchAll(PDO::FETCH_COLUMN);

        while ($inicio + $duracion <= $fin) {
            $horaSlot = date('H:i', $inicio);
            $horaSlotFin = date('H:i', strtotime($horaSlot . ' +30 minutes'));

            // ✅ Verificar si está reservado
            if (in_array($horaSlot, $reservados)) {
                $inicio += $duracion;
                continue;
            }

            // ✅ Verificar si está bloqueado (COMPARACIÓN CORREGIDA)
            $bloqueado = false;
            $slotInicioMinutos = $this->timeToMinutes($horaSlot);
            $slotFinMinutos = $this->timeToMinutes($horaSlotFin);

            foreach ($bloqueos as $bloqueo) {
                if (!empty($bloqueo['hora_inicio']) && !empty($bloqueo['hora_fin'])) {
                    // ✅ Convertir a minutos para comparar correctamente
                    $bloqueoInicioMinutos = $this->timeToMinutes($bloqueo['hora_inicio']);
                    $bloqueoFinMinutos = $this->timeToMinutes($bloqueo['hora_fin']);

                    // ✅ Verificar superposición: el slot se superpone si comienza antes del fin del bloqueo Y termina después del inicio del bloqueo
                    if ($slotInicioMinutos < $bloqueoFinMinutos && $slotFinMinutos > $bloqueoInicioMinutos) {
                        $bloqueado = true;
                        break;
                    }
                }
            }

            if (!$bloqueado) {
                $slots[] = $horaSlot;
            }

            $inicio += $duracion;
        }

        return $slots;
    }

    // ✅ Método auxiliar para convertir tiempo a minutos desde la medianoche
    private function timeToMinutes($time) {
        list($hours, $minutes) = explode(':', $time);
        return ((int)$hours * 60) + (int)$minutes;
    }
    public function isSlotAvailable($professionalId, $fecha, $hora) {
        $query = "SELECT COUNT(*) FROM {$this->table} 
                  WHERE professional_id = :profesional_id 
                  AND fecha = :fecha 
                  AND hora_inicio = :hora 
                  AND estado IN ('pagado', 'pendiente_pago')";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":profesional_id", $professionalId);
        $stmt->bindParam(":fecha", $fecha);
        $stmt->bindParam(":hora", $hora);
        $stmt->execute();
        return $stmt->fetchColumn() == 0;
    }

    public function getNextAvailableSlots($professionalId, $limit = 10) {
        $hoy = new DateTime();
        $hoy->setTime(0, 0);

        $query = "SELECT dia_semana, hora_inicio, hora_fin FROM availability 
              WHERE professional_id = :profesional_id AND activo = 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":profesional_id", $professionalId);
        $stmt->execute();
        $disponibilidad = $stmt->fetchAll();

        if (empty($disponibilidad)) {
            return [];
        }

        $horariosPorDia = [];
        foreach ($disponibilidad as $d) {
            $horariosPorDia[$d['dia_semana']] = $d;
        }

        $slots = [];

        for ($i = 0; $i < 15; $i++) {
            if (count($slots) >= $limit) break;

            $fecha = clone $hoy;
            $fecha->modify("+{$i} days");
            $diaSemana = (int)$fecha->format('w');

            if (!isset($horariosPorDia[$diaSemana])) {
                continue;
            }

            $inicio = strtotime($horariosPorDia[$diaSemana]['hora_inicio']);
            $fin = strtotime($horariosPorDia[$diaSemana]['hora_fin']);
            $duracion = 30 * 60;

            $fechaStr = $fecha->format('Y-m-d');

            // ✅ Obtener bloqueos para ese día
            $queryBloqueos = "SELECT hora_inicio, hora_fin FROM blocked_dates 
                          WHERE professional_id = :profesional_id 
                          AND fecha = :fecha";
            $stmtBloqueos = $this->conn->prepare($queryBloqueos);
            $stmtBloqueos->bindParam(":profesional_id", $professionalId);
            $stmtBloqueos->bindParam(":fecha", $fechaStr);
            $stmtBloqueos->execute();
            $bloqueosDia = $stmtBloqueos->fetchAll();

            // Obtener turnos reservados
            $queryReservados = "SELECT hora_inicio FROM appointments 
                           WHERE professional_id = :profesional_id 
                           AND fecha = :fecha 
                           AND estado IN ('pagado', 'pendiente_pago')";
            $stmtReservados = $this->conn->prepare($queryReservados);
            $stmtReservados->bindParam(":profesional_id", $professionalId);
            $stmtReservados->bindParam(":fecha", $fechaStr);
            $stmtReservados->execute();
            $reservados = $stmtReservados->fetchAll(PDO::FETCH_COLUMN);

            while ($inicio + $duracion <= $fin) {
                if (count($slots) >= $limit) break 2;

                $horaSlot = date('H:i', $inicio);
                $horaSlotFin = date('H:i', strtotime($horaSlot . ' +30 minutes'));

                // Verificar reservados
                if (in_array($horaSlot, $reservados)) {
                    $inicio += $duracion;
                    continue;
                }

                // ✅ Verificar bloqueos con comparación corregida
                $bloqueado = false;
                $slotInicioMinutos = $this->timeToMinutes($horaSlot);
                $slotFinMinutos = $this->timeToMinutes($horaSlotFin);

                foreach ($bloqueosDia as $bloqueo) {
                    if (!empty($bloqueo['hora_inicio']) && !empty($bloqueo['hora_fin'])) {
                        $bloqueoInicioMinutos = $this->timeToMinutes($bloqueo['hora_inicio']);
                        $bloqueoFinMinutos = $this->timeToMinutes($bloqueo['hora_fin']);

                        if ($slotInicioMinutos < $bloqueoFinMinutos && $slotFinMinutos > $bloqueoInicioMinutos) {
                            $bloqueado = true;
                            break;
                        }
                    }
                }

                if (!$bloqueado) {
                    $diasEspanol = [
                        'Sunday' => 'Domingo', 'Monday' => 'Lunes', 'Tuesday' => 'Martes',
                        'Wednesday' => 'Miércoles', 'Thursday' => 'Jueves', 'Friday' => 'Viernes',
                        'Saturday' => 'Sábado'
                    ];
                    $diaNombreIngles = $fecha->format('l');

                    $slots[] = [
                        'fecha' => $fechaStr,
                        'fecha_formateada' => $fecha->format('d/m/Y'),
                        'dia_nombre' => $diasEspanol[$diaNombreIngles] ?? $diaNombreIngles,
                        'hora' => $horaSlot
                    ];
                }
                $inicio += $duracion;
            }
        }

        return $slots;
    }
}
?>