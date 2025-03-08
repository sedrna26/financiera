<?php
require 'connection.php'; // Incluye el archivo de conexión a la base de datos

class Cliente {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Obtener todos los clientes
    public function getClientes() {
        $query = "SELECT * FROM clientes";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Obtener un cliente por ID
    public function getClienteById($id) {
        $query = "SELECT * FROM clientes WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Agregar un nuevo cliente
    public function addCliente($nombre, $apellido, $dni, $domicilio, $telefono, $estado) {
        $query = "INSERT INTO clientes (nombre, apellido, dni, domicilio, telefono, estado) VALUES (:nombre, :apellido, :dni, :domicilio, :telefono, :estado)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':nombre', $nombre);
        $stmt->bindParam(':apellido', $apellido);
        $stmt->bindParam(':dni', $dni);
        $stmt->bindParam(':domicilio', $domicilio);
        $stmt->bindParam(':telefono', $telefono);
        $stmt->bindParam(':estado', $estado);
        return $stmt->execute();
    }

    // Actualizar cliente
    public function updateCliente($id, $nombre, $apellido, $dni, $domicilio, $telefono, $estado) {
        $query = "UPDATE clientes SET nombre = :nombre, apellido = :apellido, dni = :dni, domicilio = :domicilio, telefono = :telefono, estado = :estado WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->bindParam(':nombre', $nombre);
        $stmt->bindParam(':apellido', $apellido);
        $stmt->bindParam(':dni', $dni);
        $stmt->bindParam(':domicilio', $domicilio);
        $stmt->bindParam(':telefono', $telefono);
        $stmt->bindParam(':estado', $estado);
        return $stmt->execute();
    }

    // Eliminar cliente
    public function deleteCliente($id) {
        $query = "DELETE FROM clientes WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }
}

// Crear una instancia de la clase Cliente
$cliente = new Cliente($db);
?>