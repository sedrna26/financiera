<?php
// Incluir la configuración de la base de datos
require_once '../../config/database.php';

$error = "";

// Procesar el formulario al enviarse
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obtener y validar los datos enviados
    $cliente_id = isset($_POST['cliente_id']) ? intval($_POST['cliente_id']) : 0;
    $monto = isset($_POST['monto']) ? floatval($_POST['monto']) : 0;
    $fecha_pago = date('Y-m-d'); // Se utiliza la fecha actual

    if ($cliente_id > 0 && $monto > 0) {
        // Preparar la inserción del pago en la base de datos
        $query = "INSERT INTO pagos (cliente_id, monto, fecha_pago) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ids", $cliente_id, $monto, $fecha_pago);

        if ($stmt->execute()) {
            // Redirigir a la generación del recibo en PDF, pasando el ID del pago insertado
            header("Location: generar_recibo.php?payment_id=" . $conn->insert_id);
            exit();
        } else {
            $error = "Error al registrar el pago: " . $conn->error;
        }
    } else {
        $error = "Datos de pago incorrectos.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Registrar Pago</title>
    <link rel="stylesheet" href="../../public/css/style.css">
</head>

<body>
    <h1>Registrar Pago</h1>

    <?php if ($error): ?>
        <p style="color:red;"><?php echo $error; ?></p>
    <?php endif; ?>

    <form action="registrar_pago.php" method="POST">
        <label for="cliente_id">ID del Cliente:</label>
        <input type="number" name="cliente_id" id="cliente_id" required><br><br>

        <label for="monto">Monto a Pagar:</label>
        <input type="number" step="0.01" name="monto" id="monto" required><br><br>

        <button type="submit">Registrar Pago</button>
    </form>
    <br>
    <a href="index.php">Volver al listado de pagos</a>
</body>

</html>