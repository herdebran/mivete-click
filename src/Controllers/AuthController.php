<?php
require_once __DIR__ . '/../Models/Professional.php';

class AuthController {
    private $db;
    private $professional;

    public function __construct($db) {
        $this->db = $db;
        $this->professional = new Professional($db);
    }

    public function register($data) {
        // Validaciones básicas
        if (empty($data['email']) || empty($data['password'])) {
            return ['success' => false, 'message' => 'Email y contraseña son requeridos'];
        }

        // Verificar si el email ya existe
        $existing = $this->professional->findByEmail($data['email']);
        if ($existing) {
            return ['success' => false, 'message' => 'El email ya está registrado'];
        }

        // Crear profesional
        $created = $this->professional->create($data);
        
        if ($created) {
            return ['success' => true, 'message' => 'Registro exitoso. Por favor inicia sesión.'];
        }
        
        return ['success' => false, 'message' => 'Error al registrar. Intentá de nuevo.'];
    }

    public function login($email, $password) {
        $professional = $this->professional->findByEmail($email);
        
        if (!$professional) {
            return ['success' => false, 'message' => 'Email o contraseña incorrectos'];
        }

        if (!password_verify($password, $professional['password_hash'])) {
            return ['success' => false, 'message' => 'Email o contraseña incorrectos'];
        }

        if ($professional['estado_verificacion'] == 0) {
            return ['success' => false, 'message' => 'Tu cuenta está pendiente de verificación. Contactá al administrador.'];
        }

        // Crear sesión
        session_start();
        $_SESSION['user_id'] = $professional['id'];
        $_SESSION['user_email'] = $professional['email'];
        $_SESSION['user_nombre'] = $professional['nombre'];
        
        return ['success' => true, 'message' => 'Login exitoso'];
    }

    public function logout() {
        session_start();
        session_destroy();
        return ['success' => true];
    }

    public function isLoggedIn() {
        session_start();
        return isset($_SESSION['user_id']);
    }

    public function getCurrentUser() {
        session_start();
        if (isset($_SESSION['user_id'])) {
            return [
                'id' => $_SESSION['user_id'],
                'email' => $_SESSION['user_email'],
                'nombre' => $_SESSION['user_nombre']
            ];
        }
        return null;
    }
}
?>