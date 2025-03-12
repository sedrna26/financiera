<?php
// Incluir la configuración de la base de datos
require_once '../../config/database.php';

$error = "";
$mensaje = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Recibir y sanitizar los datos del formulario
    $cliente_id = intval($_POST['cliente_id']);
    $monto = floatval($_POST['monto']);
    $cuotas = intval($_POST['cuotas']);
    $fecha_inicio = $conn->real_escape_string($_POST['fecha_inicio']);
    $frecuencia = $conn->real_escape_string($_POST['frecuencia']);
    $estado = 'Activo'; // Estado predeterminado

    // Calcular la fecha de vencimiento según la frecuencia y la cantidad de cuotas
    $fecha_vencimiento = calcularFechaVencimiento($fecha_inicio, $cuotas, $frecuencia);

    // Validación de campos obligatorios
    if (empty($cliente_id) || empty($monto) || empty($cuotas) || empty($fecha_inicio) || empty($fecha_vencimiento) || empty($frecuencia)) {
        $error = "Todos los campos son obligatorios.";
    }

    if (empty($error)) {
        // Insertar los datos del crédito en la base de datos
        $insert_query = "INSERT INTO creditos (cliente_id, monto, cuotas, fecha_inicio, fecha_vencimiento, frecuencia, estado) 
                         VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt_insert = $conn->prepare($insert_query);
        $stmt_insert->bind_param("idissss", $cliente_id, $monto, $cuotas, $fecha_inicio, $fecha_vencimiento, $frecuencia, $estado);

        if ($stmt_insert->execute()) {
            $mensaje = "Crédito registrado exitosamente.";
        } else {
            $error = "Error al registrar el crédito: " . $conn->error;
        }
    }
}

// Función para calcular la fecha de vencimiento
function calcularFechaVencimiento($fecha_inicio, $cuotas, $frecuencia)
{
    $fecha_actual = $fecha_inicio;
    for ($i = 0; $i < $cuotas; $i++) {
        switch ($frecuencia) {
            case 'semanal':
                $fecha_actual = date('Y-m-d', strtotime($fecha_actual . ' +7 days'));
                break;
            case 'quincenal':
                $fecha_actual = date('Y-m-d', strtotime($fecha_actual . ' +15 days'));
                break;
            case 'mensual':
                $fecha_actual = date('Y-m-d', strtotime($fecha_actual . ' +1 month'));
                break;
        }
    }
    return $fecha_actual;
}

// Obtener la lista de clientes
$buscar_cliente = isset($_GET['buscar_cliente']) ? $conn->real_escape_string($_GET['buscar_cliente']) : '';
$clientes_query = "SELECT id_cliente, nombre, apellido, dni FROM clientes";
if (!empty($buscar_cliente)) {
    $clientes_query .= " WHERE nombre LIKE '%$buscar_cliente%' OR apellido LIKE '%$buscar_cliente%' OR dni LIKE '%$buscar_cliente%'";
}
$clientes_result = $conn->query($clientes_query);
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Registrar Crédito</title>
    <link rel="stylesheet" href="../../../style/style.css">
</head>

<body>
    <h1>Registrar Crédito</h1>

    <?php if ($error): ?>
        <p style="color:red;"><?php echo $error; ?></p>
    <?php endif; ?>

    <?php if ($mensaje): ?>
        <p style="color:green;"><?php echo $mensaje; ?></p>
    <?php endif; ?>

    <!-- Formulario de búsqueda de clientes -->
    <form action="registrar_credito.php" method="GET">
        <input type="text" name="buscar_cliente" placeholder="Buscar cliente..." value="<?php echo htmlspecialchars($buscar_cliente); ?>">
        <button type="submit">Buscar</button>
    </form>

    <form action="registrar_credito.php" method="POST">
        <label for="cliente_id">Cliente:</label>
        <select name="cliente_id" id="cliente_id" required>
            <?php while ($cliente = $clientes_result->fetch_assoc()): ?>
                <option value="<?php echo $cliente['id_cliente']; ?>">
                    <?php echo htmlspecialchars($cliente['nombre'] . ' ' . $cliente['apellido'] . ' - ' . $cliente['dni']); ?>
                </option>
            <?php endwhile; ?>
        </select><br><br>

        <label for="monto">Monto:</label>
        <input type="number" name="monto" id="monto" step="0.01" required><br><br>

        <label for="cuotas">Cuotas:</label>
        <input type="number" name="cuotas" id="cuotas" required><br><br>

        <label for="fecha_inicio">Fecha de Inicio:</label>
        <input type="date" name="fecha_inicio" id="fecha_inicio" required><br><br>

        <label for="frecuencia">Frecuencia:</label>
        <select name="frecuencia" id="frecuencia" required>
            <option value="mensual">Mensual</option>
            <option value="semanal">Semanal</option>
            <option value="quincenal">Quincenal</option>
        </select><br><br>

        <input type="hidden" name="estado" value="Activo">

        <button type="submit">Registrar Crédito</button>
    </form>

    <br>
    <a href="index_creditos.php">Volver al listado de créditos</a>
</body>

</html>