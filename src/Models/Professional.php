<?php
class Professional {
    private $conn;
    private $table = "professionals";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create($data) {
        $query = "INSERT INTO {$this->table} 
                  (nombre, apellido, email, telefono, dni, matricula, password_hash) 
                  VALUES (:nombre, :apellido, :email, :telefono, :dni, :matricula, :password_hash)";
        
        $stmt = $this->conn->prepare($query);
        
        $password_hash = password_hash($data['password'], PASSWORD_DEFAULT);
        
        $stmt->bindParam(":nombre", $data['nombre']);
        $stmt->bindParam(":apellido", $data['apellido']);
        $stmt->bindParam(":email", $data['email']);
        $stmt->bindParam(":telefono", $data['telefono']);
        $stmt->bindParam(":dni", $data['dni']);
        $stmt->bindParam(":matricula", $data['matricula']);
        $stmt->bindParam(":password_hash", $password_hash);
        
        return $stmt->execute();
    }

    public function findByEmail($email) {
        $query = "SELECT * FROM {$this->table} WHERE email = :email LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":email", $email);
        $stmt->execute();
        return $stmt->fetch();
    }

    public function getById($id) {
        $query = "SELECT * FROM {$this->table} WHERE id = :id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        return $stmt->fetch();
    }
}
?>