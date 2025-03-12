<?php
// Incluir la configuración de la base de datos
require_once '../../config/database.php';

// Verificar que se haya enviado el ID del cliente
if (!isset($_GET['id_cliente'])) {
    header("Location: index_clientes.php?error=No se especificó el cliente");
    exit();
}

$cliente_id = intval($_GET['id_cliente']);

// Obtener datos del cliente
$query = "SELECT id_cliente, nombre, apellido, dni, domicilio, telefono, estado 
          FROM clientes 
          WHERE id_cliente = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $cliente_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    header("Location: index_clientes.php?error=Cliente no encontrado");
    exit();
}

$cliente = $result->fetch_assoc();
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

    <p><strong>ID:</strong> <?php echo $cliente['id_cliente']; ?></p>
    <p><strong>Nombre:</strong> <?php echo htmlspecialchars($cliente['nombre']); ?></p>
    <p><strong>Apellido:</strong> <?php echo htmlspecialchars($cliente['apellido']); ?></p>
    <p><strong>DNI:</strong> <?php echo $cliente['dni']; ?></p>
    <p><strong>Domicilio:</strong> <?php echo htmlspecialchars($cliente['domicilio']); ?></p>
    <p><strong>Teléfono:</strong> <?php echo $cliente['telefono']; ?></p>
    <p><strong>Estado:</strong> <?php echo $cliente['estado']; ?></p>

    <br>
    <a href="index_clientes.php">Volver al listado de clientes</a>
</body>

</html>