<?php
require 'config.php'; // Archivo de conexión a la base de datos

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

// Conectar a la base de datos
$db = new PDO("mysql:host=localhost;dbname=financiera", "root", "");
$cliente = new Cliente($db);

class Credito {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function registrarCredito($cliente_id, $monto, $cuotas, $frecuencia) {
        $query = "INSERT INTO creditos (cliente_id, monto, cuotas, frecuencia, estado) VALUES (?, ?, ?, ?, 'Activo')";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$cliente_id, $monto, $cuotas, $frecuencia]);
    }

    public function actualizarEstadoCliente($cliente_id) {
        $query = "SELECT COUNT(*) FROM pagos WHERE cliente_id = ? AND estado = 'Vencido'";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$cliente_id]);
        $cuotasVencidas = $stmt->fetchColumn();

        if ($cuotasVencidas >= 2) {
            $query = "UPDATE clientes SET estado = 'Deudor' WHERE id = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$cliente_id]);
        }
    }

    public function listarCreditos() {
        $query = "SELECT c.id, cl.nombre, cl.apellido, c.monto, c.cuotas, c.frecuencia, c.estado FROM creditos c 
                  INNER JOIN clientes cl ON c.cliente_id = cl.id";
        $stmt = $this->conn->query($query);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

$credito = new Credito($db);
?>
