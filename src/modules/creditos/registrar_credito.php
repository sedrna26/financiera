<?php
require_once '../../config/database.php';

$error = "";
$mensaje = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitizar datos del formulario
    $cliente_id = intval($_POST['cliente_id']);
    $monto_base = floatval($_POST['monto']);
    $gastos_administrativos = 11000;
    $tasa_interes = 0.25;
    $cuotas = intval($_POST['cuotas']);
    $fecha_inicio = $conn->real_escape_string($_POST['fecha_inicio']);
    $frecuencia = $conn->real_escape_string($_POST['frecuencia']);
    $estado = 'Activo';

    // Validar campos obligatorios (corregido: $monto_base en lugar de $monto)
    if (empty($cliente_id) || empty($monto_base) || $cuotas < 1 || empty($fecha_inicio) || empty($frecuencia)) {
        $error = "Todos los campos son obligatorios.";
    }

    // Calcular interés según frecuencia
    switch ($frecuencia) {
        case 'mensual':
            $interes = $monto_base * $tasa_interes * $cuotas;
            break;
        case 'quincenal':
            $interes = $monto_base * ($tasa_interes / 2) * $cuotas;
            break;
        case 'semanal':
            $interes = $monto_base * ($tasa_interes / 4) * $cuotas;
            break;
        default:
            $interes = 0;
    }

    $monto_total = $monto_base + $interes + $gastos_administrativos;
    $monto_cuota = $monto_total / $cuotas;
    $fecha_vencimiento = calcularFechaVencimiento($fecha_inicio, $cuotas, $frecuencia);

    // Validar máximo 2 créditos activos (nueva validación)
    if (empty($error)) {
        $check_creditos = $conn->prepare("SELECT COUNT(*) FROM creditos WHERE cliente_id = ? AND estado = 'Activo'");
        $check_creditos->bind_param("i", $cliente_id);
        $check_creditos->execute();
        $check_creditos->bind_result($total_creditos);
        $check_creditos->fetch();
        $check_creditos->close();
        
        if ($total_creditos >= 2) {
            $error = "El cliente ya tiene 2 créditos activos.";
        }
    }

    if (empty($error)) {
        // Insertar crédito
        $insert_query = "INSERT INTO creditos (cliente_id, monto, monto_total, monto_cuota, cuotas, fecha_inicio, fecha_vencimiento, frecuencia, estado) 
                         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt_insert = $conn->prepare($insert_query);
        $stmt_insert->bind_param(
            "idddissss", 
            $cliente_id, 
            $monto_base, 
            $monto_total,
            $monto_cuota,
            $cuotas,
            $fecha_inicio,
            $fecha_vencimiento,
            $frecuencia,
            $estado
        );

        if ($stmt_insert->execute()) {
            // Actualizar estado solo si es necesario (corregido)
            $update_cliente = "UPDATE clientes SET estado = 'Activo' WHERE id_cliente = ? AND estado != 'Activo'";
            $stmt_update = $conn->prepare($update_cliente);
            $stmt_update->bind_param("i", $cliente_id);
            $stmt_update->execute();

            $mensaje = "Crédito registrado exitosamente.";
        } else {
            $error = "Error al registrar el crédito: " . $conn->error;
        }
    }
}

// Función para calcular la fecha de vencimiento
function calcularFechaVencimiento($fecha_inicio, $cuotas, $frecuencia) {
    $fecha_actual = $fecha_inicio;
    for ($i = 0; $i < $cuotas; $i++) { // Eliminar condicional de 1 cuota
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
if (isset($clientes_result)) {
    $clientes_result->free(); // Liberar resultados anteriores
}

$clientes_query = "SELECT id_cliente, nombre, apellido, dni FROM clientes";
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

        


        <label for="frecuencia">Frecuencia:</label>
        <select name="frecuencia" id="frecuencia" required onchange="actualizarCuotas()">
            <option value="mensual">Mensual</option>
            <option value="semanal">Semanal</option>
            <option value="quincenal">Quincenal</option>
        </select><br><br>

        <label for="cuotas">Cuotas:</label>
        <input type="number" name="cuotas" id="cuotas" min="1" required onchange="calcularVencimiento()"><br><br>

        <label for="fecha_inicio">Fecha de Inicio:</label>
        <input type="date" name="fecha_inicio" id="fecha_inicio" required onchange="calcularVencimiento()"><br><br>

        <label for="fecha_vencimiento">Fecha de Vencimiento:</label>
        <input type="date" name="fecha_vencimiento" id="fecha_vencimiento" readonly><br><br>

        <input type="hidden" name="estado" value="Activo">

        <button type="submit">Registrar Crédito</button>
    </form>

    <br>
    <a href="index_creditos.php">Volver al listado de créditos</a>
</body>
<script>

document.addEventListener('DOMContentLoaded', function() {
    calcularVencimiento(); // Ejecutar al cargar
    actualizarCuotas(); // Establecer máximo de cuotas
});

function actualizarCuotas() {
    const frecuencia = document.getElementById('frecuencia').value;
    let maxCuotas = 12; // Valor por defecto
    switch (frecuencia) {
        case 'semanal':
            maxCuotas = 52; // 1 año de semanas
            break;
        case 'quincenal':
            maxCuotas = 24; // 1 año de quincenas
            break;
        case 'mensual':
            maxCuotas = 12; // 1 año de meses
            break;
    }
    document.getElementById('cuotas').max = maxCuotas;
}

function calcularVencimiento() {
    const fechaInicio = document.getElementById('fecha_inicio').value;
    const cuotas = parseInt(document.getElementById('cuotas').value);
    const frecuencia = document.getElementById('frecuencia').value;

    if (!fechaInicio || cuotas < 1) return;

    // Si es 1 cuota, fecha de vencimiento = fecha de inicio
    if (cuotas === 1) {
        document.getElementById('fecha_vencimiento').value = fechaInicio;
        return;
    }

    const fecha = new Date(fechaInicio);
    for (let i = 0; i < cuotas - 1; i++) { // Restar 1 iteración
        switch (frecuencia) {
            case 'semanal':
                fecha.setDate(fecha.getDate() + 7);
                break;
            case 'quincenal':
                fecha.setDate(fecha.getDate() + 15);
                break;
            case 'mensual':
                fecha.setMonth(fecha.getMonth() + 1);
                break;
        }
    }
    document.getElementById('fecha_vencimiento').value = fecha.toISOString().split('T')[0];
}
</script>
</html>