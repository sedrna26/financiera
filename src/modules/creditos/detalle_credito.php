<?php
// Incluir la configuración de la base de datos
require_once '../../config/database.php';

// Verificar que se haya enviado el ID del crédito
if (!isset($_GET['id'])) {
    header("Location: index.php?error=No se especificó el crédito");
    exit();
}

$credito_id = intval($_GET['id']);

// Consulta para obtener los datos del crédito y del cliente asociado

$query = "SELECT c.id, c.cliente_id, c.monto, c.cuotas, c.fecha_inicio, c.fecha_vencimiento, c.estado, 
                 cl.nombre, cl.apellido, cl.dni 
          FROM creditos c 
          INNER JOIN clientes cl ON c.cliente_id = cl.id_cliente 
          WHERE c.id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $credito_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    header("Location: index.php?error=Crédito no encontrado");
    exit();
}

$credito = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Detalle de Crédito</title>
    <link rel="stylesheet" href="../../../style/style.css">
</head>

<body>
    <h1>Detalle de Crédito</h1>

    <h2>Información del Crédito</h2>
    <p><strong>Nro de Crédito:</strong> <?php echo $credito['id']; ?></p>
    <p><strong>Monto:</strong> <?php echo number_format($credito['monto'], 2); ?></p>
    <p><strong>Cuotas:</strong> <?php echo $credito['cuotas']; ?></p>
    <p><strong>Fecha de Inicio:</strong> <?php echo $credito['fecha_inicio']; ?></p>
    <p><strong>Fecha de Vencimiento:</strong> <?php echo $credito['fecha_vencimiento']; ?></p>
    <p><strong>Estado:</strong> <?php echo $credito['estado']; ?></p>

    <h2>Información del Cliente</h2>
    <p><strong>Nombre:</strong> <?php echo $credito['nombre'] . ' ' . $credito['apellido']; ?></p>
    <p><strong>DNI:</strong> <?php echo $credito['dni']; ?></p>

    <br>
    <a href="editar_credito.php?id=<?php echo $credito['id']; ?>">Editar Crédito</a><br><br>
    <a href="index_creditos.php">Volver al listado de créditos</a>
</body>

</html>