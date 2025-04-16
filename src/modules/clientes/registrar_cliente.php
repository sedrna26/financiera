<?php
// Incluir la configuración de la base de datos
require_once '../../config/database.php';

$nombre = "";
$apellido = "";
$dni = "";
$domicilio = "";
$telefono = "";
$error = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Recibir y sanitizar los datos enviados
    $nombre = $conn->real_escape_string(trim($_POST['nombre']));
    $apellido = $conn->real_escape_string(trim($_POST['apellido']));
    $dni = $conn->real_escape_string(trim($_POST['dni']));
    $domicilio = $conn->real_escape_string(trim($_POST['domicilio']));
    $telefono = $conn->real_escape_string(trim($_POST['telefono']));

    // Estado por defecto: Inactivo
    $estado = 'Inactivo';

    // Validación de campos obligatorios
    if (empty($nombre) || empty($apellido) || empty($dni)) {
        $error = "Los campos Nombre, Apellido y DNI son requeridos.";
    }

    if (empty($error)) {
        // Insertar el nuevo cliente en la base de datos
        $query = "INSERT INTO clientes (nombre, apellido, dni, domicilio, telefono, estado) 
                  VALUES ('$nombre', '$apellido', '$dni', '$domicilio', '$telefono', '$estado')";
        if ($conn->query($query)) {
            session_start(); // Si no está ya al inicio del archivo
            $_SESSION['mensaje'] = 'Cliente registrado exitosamente';
            $_SESSION['tipo_mensaje'] = 'exito';
            header('Location: index_clientes.php');
            exit();
        } else {
            $error = "Error al registrar el cliente: " . $conn->error;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Registrar Nuevo Cliente</title>
    <!-- <link rel="stylesheet" href="../../../style/style.css"> -->
</head>

<body>
    <h1>Registrar Nuevo Cliente</h1>

    <?php if ($error): ?>
        <p style="color: red;"><?php echo $error; ?></p>
    <?php endif; ?>

    <form action="registrar_cliente.php" method="POST">
        <label for="nombre">Nombre:</label>
        <input type="text" name="nombre" id="nombre" value="<?php echo htmlspecialchars($nombre); ?>" required><br><br>

        <label for="apellido">Apellido:</label>
        <input type="text" name="apellido" id="apellido" value="<?php echo htmlspecialchars($apellido); ?>" required><br><br>

        <label for="dni">DNI:</label>
        <input type="text" name="dni" id="dni" value="<?php echo htmlspecialchars($dni); ?>" required><br><br>

        <label for="domicilio">Domicilio:</label>
        <input type="text" name="domicilio" id="domicilio" value="<?php echo htmlspecialchars($domicilio); ?>"><br><br>

        <label for="telefono">Teléfono:</label>
        <input type="text" name="telefono" id="telefono" value="<?php echo htmlspecialchars($telefono); ?>"><br><br>

        <button type="submit">Registrar Cliente</button>
    </form>
    <br>
    <a href="index_clientes.php">Volver al listado de clientes</a>
</body>

</html>