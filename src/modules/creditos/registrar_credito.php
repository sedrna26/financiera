<?php
// Incluir la configuración de la base de datos
require_once '../../config/database.php';

// Inicializar variables
$cliente_id = "";
$monto = "";
$cuotas = "";
$fecha_inicio = "";
$fecha_vencimiento = "";
$error = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Recibir y sanitizar los datos enviados
    $cliente_id = intval($_POST['cliente_id']);
    $monto = floatval($_POST['monto']);
    $cuotas = intval($_POST['cuotas']);
    $fecha_inicio = $conn->real_escape_string(trim($_POST['fecha_inicio']));
    $fecha_vencimiento = $conn->real_escape_string(trim($_POST['fecha_vencimiento']));

    // Validación de campos obligatorios
    if ($cliente_id <= 0 || empty($monto) || empty($cuotas) || empty($fecha_inicio) || empty($fecha_vencimiento)) {
        $error = "Todos los campos son obligatorios.";
    }

    if (empty($error)) {
        // Estado por defecto para un nuevo crédito
        $estado = 'Activo';

        // Insertar el crédito en la base de datos
        $query = "INSERT INTO creditos (cliente_id, monto, cuotas, fecha_inicio, fecha_vencimiento, estado) VALUES (?, ?, ?, ?, ?, ?)";
        // Después de insertar el crédito:
        $conn->query("UPDATE clientes SET estado = 'Activo' WHERE id = $cliente_id");
        $stmt = $conn->prepare($query);
        $stmt->bind_param("idisss", $cliente_id, $monto, $cuotas, $fecha_inicio, $fecha_vencimiento, $estado);

        if ($stmt->execute()) {
            header("Location: index.php?mensaje=Crédito registrado exitosamente");
            exit();
        } else {
            $error = "Error al registrar el crédito: " . $conn->error;
        }
    }
}

// Obtener la lista de clientes para el desplegable
$clientes = [];
$query_clientes = "SELECT id, nombre, apellido FROM clientes";
$result_clientes = $conn->query($query_clientes);
if ($result_clientes && $result_clientes->num_rows > 0) {
    while ($row = $result_clientes->fetch_assoc()) {
        $clientes[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Registrar Nuevo Crédito</title>
    <link rel="stylesheet" href="../../public/css/style.css">
</head>

<body>
    <h1>Registrar Nuevo Crédito</h1>

    <?php if ($error): ?>
        <p style="color:red;"><?php echo $error; ?></p>
    <?php endif; ?>

    <form action="registrar_credito.php" method="POST">
        <label for="cliente_id">Cliente:</label>
        <select name="cliente_id" id="cliente_id" required>
            <option value="">Seleccione un cliente</option>
            <?php foreach ($clientes as $cliente): ?>
                <option value="<?php echo $cliente['id']; ?>" <?php echo ($cliente_id == $cliente['id']) ? 'selected' : ''; ?>>
                    <?php echo $cliente['nombre'] . ' ' . $cliente['apellido']; ?>
                </option>
            <?php endforeach; ?>
        </select>
        <br><br>

        <label for="monto">Monto:</label>
        <input type="number" step="0.01" name="monto" id="monto" value="<?php echo htmlspecialchars($monto); ?>" required><br><br>

        <label for="cuotas">Cantidad de Cuotas:</label>
        <input type="number" name="cuotas" id="cuotas" value="<?php echo htmlspecialchars($cuotas); ?>" required><br><br>

        <label for="fecha_inicio">Fecha de Inicio:</label>
        <input type="date" name="fecha_inicio" id="fecha_inicio" value="<?php echo htmlspecialchars($fecha_inicio); ?>" required><br><br>

        <label for="fecha_vencimiento">Fecha de Vencimiento:</label>
        <input type="date" name="fecha_vencimiento" id="fecha_vencimiento" value="<?php echo htmlspecialchars($fecha_vencimiento); ?>" required><br><br>

        <button type="submit">Registrar Crédito</button>
    </form>
    <br>
    <a href="index.php">Volver al listado de créditos</a>
</body>

</html>