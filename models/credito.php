<?php
// archivo: models/Credito.php
class Credito {
    private $db;

    public function __construct($conexion) {
        $this->db = $conexion;
    }

    public function registrarCredito($cliente_id, $monto, $cuotas, $frecuencia) {
        $sql = "INSERT INTO creditos (cliente_id, monto, cuotas, frecuencia, estado) VALUES (?, ?, ?, ?, 'Activo')";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$cliente_id, $monto, $cuotas, $frecuencia]);
    }

    public function actualizarEstadoCliente($cliente_id) {
        // Verifica si hay dos cuotas vencidas y actualiza estado del cliente
        $sql = "SELECT COUNT(*) FROM pagos WHERE cliente_id = ? AND estado = 'Vencido'";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$cliente_id]);
        $cuotasVencidas = $stmt->fetchColumn();

        if ($cuotasVencidas >= 2) {
            $sql = "UPDATE clientes SET estado = 'Deudor' WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$cliente_id]);
        }
    }

    public function listarCreditos() {
        $sql = "SELECT c.id, cl.nombre, cl.apellido, c.monto, c.cuotas, c.frecuencia, c.estado FROM creditos c 
                INNER JOIN clientes cl ON c.cliente_id = cl.id";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
