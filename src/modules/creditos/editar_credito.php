<?php
// Incluir la configuración de la base de datos
require_once '../../config/database.php';

$error = "";
$mensaje = "";

// Verificar que se haya enviado el ID del crédito
if (!isset($_GET['id'])) {
    header("Location: index_creditos.php?error=No se especificó el crédito");
    exit();
}

$credito_id = intval($_GET['id']);

// Obtener datos del crédito
$query = "SELECT cliente_id, monto, cuotas, fecha_inicio, fecha_vencimiento, frecuencia, estado 
          FROM creditos 
          WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $credito_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    header("Location: index_creditos.php?error=Crédito no encontrado");
    exit();
}

$credito = $result->fetch_assoc();

// En la sección donde procesas los datos del formulario (método POST)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Recibir y sanitizar los datos del formulario
    $cliente_id = intval($_POST['cliente_id']);
    $monto_base = floatval($_POST['monto']);
    $cuotas = intval($_POST['cuotas']);
    $fecha_inicio = $conn->real_escape_string($_POST['fecha_inicio']);
    $frecuencia = $conn->real_escape_string($_POST['frecuencia']);
    $estado = $conn->real_escape_string($_POST['estado']);
    $fecha_vencimiento = $conn->real_escape_string($_POST['fecha_vencimiento']);

    // Agregar estos campos
    $tasa_interes = floatval($_POST['intereses']) / 100; // Convertir a decimal
    $gastos_administrativos = floatval($_POST['gastos']);

    // Validación de campos obligatorios
    if (empty($cliente_id) || empty($monto_base) || empty($cuotas) || empty($fecha_inicio) || empty($fecha_vencimiento) || empty($frecuencia)) {
        $error = "Todos los campos son obligatorios.";
    }

    // Calcular interés según frecuencia - similar a registrar_credito.php
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

    if (empty($error)) {
        // Actualizar los datos del crédito en la base de datos
        $update_query = "UPDATE creditos 
                        SET cliente_id = ?, monto = ?, cuotas = ?, fecha_inicio = ?, 
                        fecha_vencimiento = ?, frecuencia = ?, estado = ?,
                        monto_total = ?, monto_cuota = ? 
                        WHERE id = ?";
        $stmt_update = $conn->prepare($update_query);
        $stmt_update->bind_param(
            "idissssddi",
            $cliente_id,
            $monto_base,
            $cuotas,
            $fecha_inicio,
            $fecha_vencimiento,
            $frecuencia,
            $estado,
            $monto_total,
            $monto_cuota,
            $credito_id
        );

        if ($stmt_update->execute()) {
            $mensaje = "Crédito actualizado exitosamente.";
        } else {
            $error = "Error al actualizar el crédito: " . $conn->error;
        }
    }
}


// Obtener la lista de clientes
$clientes_query = "SELECT id_cliente, nombre, apellido, dni FROM clientes";
$clientes_result = $conn->query($clientes_query);
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Editar Crédito</title>
    <!-- <link rel="stylesheet" href="../../../style/style.css"> -->
</head>

<body>
    <h1>Editar Crédito</h1>

    <?php if ($error): ?>
        <p style="color:red;"><?php echo $error; ?></p>
    <?php endif; ?>

    <?php if ($mensaje): ?>
        <p style="color:green;"><?php echo $mensaje; ?></p>
    <?php endif; ?>

    <form action="editar_credito.php?id=<?php echo $credito_id; ?>" method="POST">
        <label for="cliente_id">Cliente:</label>
        <select name="cliente_id" id="cliente_id" required>
            <?php while ($cliente = $clientes_result->fetch_assoc()): ?>
                <option value="<?php echo $cliente['id_cliente']; ?>" <?php echo $cliente['id_cliente'] == $credito['cliente_id'] ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($cliente['nombre'] . ' ' . $cliente['apellido'] . ' - ' . $cliente['dni']); ?>
                </option>
            <?php endwhile; ?>
        </select><br><br>

        <label for="monto">Monto a prestar:</label>
        <input type="number" name="monto" id="monto" step="0.01" value="<?php echo $credito['monto']; ?>" required><br><br>

        <label for="cuotas">Cuotas:</label>
        <input type="number" name="cuotas" id="cuotas" value="<?php echo $credito['cuotas']; ?>" required><br><br>

        <label for="fecha_inicio">Fecha de Inicio:</label>
        <input type="date" name="fecha_inicio" id="fecha_inicio" value="<?php echo $credito['fecha_inicio']; ?>" required onchange="calcularVencimiento()"><br><br>

        <label for="fecha_vencimiento">Fecha de Vencimiento:</label>
        <input type="date" name="fecha_vencimiento_visible" id="fecha_vencimiento" value="<?php echo $credito['fecha_vencimiento']; ?>" readonly><br><br>
        <input type="hidden" name="fecha_vencimiento" id="fecha_vencimiento_hidden">

        <label for="frecuencia">Frecuencia:</label>
        <select name="frecuencia" id="frecuencia" required>
            <option value="mensual" <?php echo $credito['frecuencia'] == 'mensual' ? 'selected' : ''; ?>>Mensual</option>
            <option value="semanal" <?php echo $credito['frecuencia'] == 'semanal' ? 'selected' : ''; ?>>Semanal</option>
            <option value="quincenal" <?php echo $credito['frecuencia'] == 'quincenal' ? 'selected' : ''; ?>>Quincenal</option>
        </select><br><br>

        <label for="intereses">Tasa de Interés:</label>
        <input type="number" name="intereses" id="intereses" step="0.01" value=25 required><br><br>

        <label for="gastos">Gastos Administrativos:</label>
        <input type="number" name="gastos" id="gastos" step="0.01" value=11000 required><br><br>
        <div>
            <label>Monto Total Calculado:</label>
            <span id="monto_total_display">0.00</span>
            <input type="hidden" name="monto_total" id="monto_total_hidden">
        </div>

        <div>
            <label>Monto por Cuota Calculado:</label>
            <span id="monto_cuota_display">0.00</span>
            <input type="hidden" name="monto_cuota" id="monto_cuota_hidden">
        </div>

        <label for="estado">Estado:</label>
        <select name="estado" id="estado" required>
            <option value="Activo" <?php echo $credito['estado'] == 'Activo' ? 'selected' : ''; ?>>Activo</option>
            <option value="Vencido" <?php echo $credito['estado'] == 'Vencido' ? 'selected' : ''; ?>>Vencido</option>
            <option value="Pagado" <?php echo $credito['estado'] == 'Pagado' ? 'selected' : ''; ?>>Pagado</option>
        </select><br><br>

        <button type="submit">Actualizar Crédito</button>
    </form>

    <br>
    <a href="index_creditos.php">Volver al listado de créditos</a>

    <script>
        // Mismo script que en registrar_credito.php
        document.addEventListener('DOMContentLoaded', function() {
            calcularVencimiento();
            actualizarCuotas();
        });

        function actualizarCuotas() {
            const frecuencia = document.getElementById('frecuencia').value;
            let maxCuotas = 12;
            switch (frecuencia) {
                case 'semanal':
                    maxCuotas = 52;
                    break;
                case 'quincenal':
                    maxCuotas = 24;
                    break;
                case 'mensual':
                    maxCuotas = 12;
                    break;
            }
            document.getElementById('cuotas').max = maxCuotas;
        }

        function calcularVencimiento() {
            const fechaInicio = document.getElementById('fecha_inicio').value;
            const cuotas = parseInt(document.getElementById('cuotas').value);
            const frecuencia = document.getElementById('frecuencia').value;

            if (!fechaInicio || cuotas < 1) return;

            if (cuotas === 1) {
                document.getElementById('fecha_vencimiento').value = fechaInicio;
                document.getElementById('fecha_vencimiento_hidden').value = fechaInicio;
                return;
            }

            const fecha = new Date(fechaInicio);
            for (let i = 0; i < cuotas - 1; i++) {
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
            document.getElementById('fecha_vencimiento_hidden').value = fecha.toISOString().split('T')[0];
        }
        // Agregar esta función para calcular el monto total y la cuota
        function calcularMontos() {
            const montoBase = parseFloat(document.getElementById('monto').value) || 0;
            const cuotas = parseInt(document.getElementById('cuotas').value) || 1;
            const frecuencia = document.getElementById('frecuencia').value;
            const tasaInteres = parseFloat(document.getElementById('intereses').value) / 100 || 0;
            const gastosAdmin = parseFloat(document.getElementById('gastos').value) || 0;

            let interes = 0;

            switch (frecuencia) {
                case 'mensual':
                    interes = montoBase * tasaInteres * cuotas;
                    break;
                case 'quincenal':
                    interes = montoBase * (tasaInteres / 2) * cuotas;
                    break;
                case 'semanal':
                    interes = montoBase * (tasaInteres / 4) * cuotas;
                    break;
            }

            const montoTotal = montoBase + interes + gastosAdmin;
            const montoCuota = montoTotal / cuotas;

            // Mostrar los resultados (puedes añadir elementos en el HTML para mostrarlos)
            if (document.getElementById('monto_total_display')) {
                document.getElementById('monto_total_display').textContent = montoTotal.toFixed(2);
            }

            if (document.getElementById('monto_cuota_display')) {
                document.getElementById('monto_cuota_display').textContent = montoCuota.toFixed(2);
            }

            // También puedes guardar estos valores en campos ocultos para enviarlos al servidor
            if (document.getElementById('monto_total_hidden')) {
                document.getElementById('monto_total_hidden').value = montoTotal.toFixed(2);
            }

            if (document.getElementById('monto_cuota_hidden')) {
                document.getElementById('monto_cuota_hidden').value = montoCuota.toFixed(2);
            }
        }

        // Añadir los event listeners a los campos que afectan el cálculo
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('monto').addEventListener('input', calcularMontos);
            document.getElementById('cuotas').addEventListener('input', calcularMontos);
            document.getElementById('frecuencia').addEventListener('change', calcularMontos);
            document.getElementById('intereses').addEventListener('input', calcularMontos);
            document.getElementById('gastos').addEventListener('input', calcularMontos);

            // Calcular inicialmente
            calcularMontos();
            calcularVencimiento();
            actualizarCuotas();
        });
    </script>
</body>

</html>