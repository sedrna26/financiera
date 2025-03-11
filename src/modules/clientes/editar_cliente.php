<?php
// Incluir la configuración de la base de datos
require_once '../../config/database.php';

// Verificar que se haya enviado el ID del cliente
if (!isset($_GET['id'])) {
    header("Location: index.php?error=No se especificó el cliente");
    exit();
}

$cliente_id = intval($_GET['id']);

// Obtener datos del cliente
$query = "SELECT id, nombre, apellido, dni, domicilio, telefono, estado FROM clientes WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $cliente_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    header("Location: index.php?error=Cliente no encontrado");
    exit();
}

$cliente = $result->fetch_assoc();
$error = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Recibir y sanitizar los datos del formulario
    $nombre = $conn->real_escape_string(trim($_POST['nombre']));
    $apellido = $conn->real_escape_string(trim($_POST['apellido']));
    $dni = $conn->real_escape_string(trim($_POST['dni']));
    $domicilio = $conn->real_escape_string(trim($_POST['domicilio']));
    $telefono = $conn->real_escape_string(trim($_POST['telefono']));

    // Validación de campos obligatorios
    if (empty($nombre) || empty($apellido) || empty($dni)) {
        $error = "Nombre, Apellido y DNI son obligatorios.";
    }

    if (empty($error)) {
        // Actualizar los datos del cliente en la base de datos
        $update_query = "UPDATE clientes SET nombre = ?, apellido = ?, dni = ?, domicilio = ?, telefono = ? WHERE id = ?";
        $stmt_update = $conn->prepare($update_query);
        $stmt_update->bind_param("sssssi", $nombre, $apellido, $dni, $domicilio, $telefono, $cliente_id);

        if ($stmt_update->execute()) {
            header("Location: index.php?mensaje=Cliente actualizado exitosamente");
            exit();
        } else {
            $error = "Error al actualizar el cliente: " . $conn->error;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Editar Cliente</title>
    <link rel="stylesheet" href="../../public/css/style.css">
</head>

<body>
    <h1>Editar Cliente</h1>

    <?php if ($error): ?>
        <p style="color:red;"><?php echo $error; ?></p>
    <?php endif; ?>

    <form action="editar_cliente.php?id=<?php echo $cliente_id; ?>" method="POST">
        <label for="nombre">Nombre:</label>
        <input type="text