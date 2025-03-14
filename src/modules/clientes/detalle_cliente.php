<?php
// Incluir la configuración de la base de datos
require_once '../../config/database.php';

// Verificar que se haya enviado el ID del cliente
if (!isset($_GET['id_cliente']) || !ctype_digit($_GET['id_cliente'])) {
    header("Location: index_clientes.php?error=ID de cliente inválido");
    exit();
}

$cliente_id = intval($_GET['id_cliente']);

// Obtener datos del cliente
$query_cliente = "SELECT id_cliente, nombre, apellido, dni, domicilio, telefono, estado 
                  FROM clientes 
                  WHERE id_cliente = ?";
$stmt_cliente = $conn->prepare($query_cliente);

if (!$stmt_cliente) {
    error_log("Error al preparar consulta del cliente: " . $conn->error);
    die("Error interno. Por favor, intente más tarde.");
}

$stmt_cliente->bind_param("i", $cliente_id);

if (!$stmt_cliente->execute()) {
    error_log("Error al ejecutar consulta del cliente: " . $stmt_cliente->error);
    die("Error al cargar los datos del cliente.");
}

$result_cliente = $stmt_cliente->get_result();

if ($result_cliente->num_rows === 0) {
    header("Location: index_clientes.php?error=Cliente no encontrado");
    exit();
}

$cliente = $result_cliente->fetch_assoc();

// Liberar recursos de la consulta del cliente
$result_cliente->free();
$stmt_cliente->close();

// Obtener créditos del cliente
$creditos_result = null; // Inicializar variable
$query_creditos = "SELECT id, monto, cuotas, fecha_inicio, fecha_vencimiento, estado 
                   FROM creditos 
                   WHERE cliente_id = ?";
$stmt_creditos = $conn->prepare($query_creditos);

if (!$stmt_creditos) {
    error_log("Error al preparar consulta de créditos: " . $conn->error);
} else {
    $stmt_creditos->bind_param("i", $cliente_id);
    
    if (!$stmt_creditos->execute()) {
        error_log("Error al ejecutar consulta de créditos: " . $stmt_creditos->error);
    } else {
        $creditos_result = $stmt_creditos->get_result();
    }
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Detalle del Cliente</title>
    <link rel="stylesheet" href="../../../style/style.css">
</head>
<body>
    <h1>Detalle del Cliente</h1>

    <!-- Datos del cliente -->
    <div class="detalle-cliente">
        <p><strong>ID:</strong> <?= htmlspecialchars($cliente['id_cliente']) ?></p>
        <p><strong>Nombre:</strong> <?= htmlspecialchars($cliente['nombre']) ?></p>
        <p><strong>Apellido:</strong> <?= htmlspecialchars($cliente['apellido']) ?></p>
        <p><strong>DNI:</strong> <?= htmlspecialchars($cliente['dni']) ?></p>
        <p><strong>Domicilio:</strong> <?= htmlspecialchars($cliente['domicilio']) ?></p>
        <p><strong>Teléfono:</strong> <?= htmlspecialchars($cliente['telefono']) ?></p>
        <p><strong>Estado:</strong> <?= htmlspecialchars($cliente['estado']) ?></p>
    </div>

    <!-- Listado de créditos -->
    <h2>Créditos del Cliente</h2>
    <?php if ($creditos_result && $creditos_result->num_rows > 0): ?>
        <table class="tabla-creditos">
            <thead>
                <tr>
                    <th>Nro Crédito</th>
                    <th>Monto</th>
                    <th>Cuotas</th>
                    <th>Fecha Inicio</th>
                    <th>Fecha Vencimiento</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($credito = $creditos_result->fetch_assoc()): ?>
                    <tr>
                        <td><?= $credito['id'] ?></td>
                        <td><?= number_format($credito['monto'], 2) ?></td>
                        <td><?= $credito['cuotas'] ?></td>
                        <td><?= $credito['fecha_inicio'] ?></td>
                        <td><?= $credito['fecha_vencimiento'] ?></td>
                        <td class="estado-<?= strtolower($credito['estado']) ?>">
                            <?= $credito['estado'] ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p class="sin-creditos">El cliente no tiene créditos registrados.</p>
    <?php endif; ?>

    <a href="index_clientes.php" class="btn-volver">Volver al listado</a>

</body>
</html>

<?php
// Liberar recursos de la consulta de créditos
if ($creditos_result) {
    $creditos_result->free();
}

if ($stmt_creditos) {
    $stmt_creditos->close();
}

// Cerrar conexión a la base de datos
$conn->close();
?>