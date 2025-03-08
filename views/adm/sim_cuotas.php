<?php
require 'config.php'; // Archivo de conexión a la base de datos

class Pago {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Registrar un nuevo pago
    public function addPago($id_credito, $monto, $fecha_pago) {
        $query = "INSERT INTO pagos (id_credito, monto, fecha_pago) VALUES (:id_credito, :monto, :fecha_pago)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_credito', $id_credito, PDO::PARAM_INT);
        $stmt->bindParam(':monto', $monto);
        $stmt->bindParam(':fecha_pago', $fecha_pago);
        return $stmt->execute();
    }

    // Obtener todos los pagos de un crédito
    public function getPagosByCredito($id_credito) {
        $query = "SELECT * FROM pagos WHERE id_credito = :id_credito ORDER BY fecha_pago ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_credito', $id_credito, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Obtener pagos pendientes
    public function getPagosPendientes() {
        $query = "SELECT c.id, c.id_cliente, c.monto, c.cuotas, c.tipo_cuota, c.estado, p.fecha_pago 
                  FROM creditos c 
                  LEFT JOIN pagos p ON c.id = p.id_credito 
                  WHERE c.estado = 'Activo' AND (p.fecha_pago IS NULL OR p.fecha_pago < NOW())";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Actualizar estado de pago
    public function updateEstadoCredito($id_credito) {
        $query = "UPDATE creditos SET estado = 'Deudor' WHERE id = :id_credito 
                  AND (SELECT COUNT(*) FROM pagos WHERE id_credito = :id_credito AND fecha_pago < NOW()) >= 2";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_credito', $id_credito, PDO::PARAM_INT);
        return $stmt->execute();
    }
}


