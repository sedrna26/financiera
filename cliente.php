<?php
// Configuración de la conexión a la base de datos
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "financiera";

// Crear conexión
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Procesar el formulario cuando se envía
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = $_POST['nombre'];
    $apellido = $_POST['apellido'];
    $dni = $_POST['dni'];
    $domicilio = $_POST['domicilio'];
    $telefono = $_POST['telefono'];

    // Preparar la consulta SQL
    $sql = "INSERT INTO clientes (nombre, apellido, dni, domicilio, telefono) 
            VALUES (?, ?, ?, ?, ?)";
    
    // Preparar y vincular
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssiss", $nombre, $apellido, $dni, $domicilio, $telefono);

    // Ejecutar la consulta
    if ($stmt->execute()) {
        echo "Nuevo cliente registrado con éxito";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }

    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Nuevo Cliente</title>
</head>
<body>
    <h2>Registro de Nuevo Cliente</h2>
    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
        <label for="nombre">Nombre:</label><br>
        <input type="text" id="nombre" name="nombre" required><br>

        <label for="apellido">Apellido:</label><br>
        <input type="text" id="apellido" name="apellido" required><br>

        <label for="dni">DNI:</label><br>
        <input type="number" id="dni" name="dni" required><br>

        <label for="domicilio">Domicilio:</label><br>
        <input type="text" id="domicilio" name="domicilio" required><br>

        <label for="telefono">Teléfono:</label><br>
        <input type="tel" id="telefono" name="telefono"><br><br>

        <input type="submit" value="Registrar Cliente">
    </form>
</body>
</html>