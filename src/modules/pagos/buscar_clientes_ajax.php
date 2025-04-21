<?php
// Incluir la configuración de la base de datos
require_once '../../config/database.php';

// Verificar que se envió un término de búsqueda
if (!isset($_GET['buscar']) || empty($_GET['buscar'])) {
    echo json_encode([]);
    exit;
}

$buscar = $conn->real_escape_string($_GET['buscar']);

// Consulta para buscar clientes con créditos activos
$query = "SELECT cl.id_cliente, cl.nombre, cl.apellido, cl.dni, c.id as credito_id, c.monto 
          FROM clientes cl 
          INNER JOIN creditos c ON cl.id_cliente = c.cliente_id 
          WHERE (cl.nombre LIKE '%$buscar%' OR cl.apellido LIKE '%$buscar%' OR cl.dni LIKE '%$buscar%')
          AND c.estado != 'Pagado'";  // Solo créditos no pagados

$result = $conn->query($query);

$clientes = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $clientes[] = $row;
    }
}

// Devolver resultados en formato JSON
header('Content-Type: application/json');
echo json_encode($clientes);
?>