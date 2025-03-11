<?php
require_once 'config/database.php';

// Obtener créditos con cuotas vencidas (ejemplo simplificado)
$query = "SELECT c.cliente_id, COUNT(p.id) AS cuotas_impagas 
          FROM creditos c 
          LEFT JOIN pagos p ON c.id = p.credito_id 
          WHERE p.estado = 'Impago' AND p.fecha_vencimiento < CURDATE() 
          GROUP BY c.cliente_id 
          HAVING cuotas_impagas >= 2";
$result = $conn->query($query);

while ($row = $result->fetch_assoc()) {
    // Actualizar estado del cliente a "Deudor"
    $conn->query("UPDATE clientes SET estado = 'Deudor' WHERE id = " . $row['cliente_id']);
}

// Cambiar a "Inactivo" si no tiene créditos activos
$conn->query("UPDATE clientes 
              SET estado = 'Inactivo' 
              WHERE id NOT IN (SELECT cliente_id FROM creditos WHERE estado = 'Activo')");
