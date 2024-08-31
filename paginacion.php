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

// Configuración de la paginación
$clientesPorPagina = 20;
$paginaActual = isset($_GET['pagina']) ? $_GET['pagina'] : 1;
$offset = ($paginaActual - 1) * $clientesPorPagina;

// Procesar la búsqueda
$busqueda = isset($_GET['busqueda']) ? $conn->real_escape_string($_GET['busqueda']) : '';
$whereBusqueda = '';
if (!empty($busqueda)) {
    $whereBusqueda = "WHERE nombre LIKE '%$busqueda%' 
                      OR apellido LIKE '%$busqueda%' 
                      OR dni LIKE '%$busqueda%' 
                      OR id_cliente LIKE '%$busqueda%'";
}

// Consulta para obtener el total de clientes (para la paginación)
$sqlTotal = "SELECT COUNT(*) as total FROM clientes $whereBusqueda";
$resultadoTotal = $conn->query($sqlTotal);
$fila = $resultadoTotal->fetch_assoc();
$totalClientes = $fila['total'];
$totalPaginas = ceil($totalClientes / $clientesPorPagina);

// Consulta para obtener los clientes de la página actual
$sql = "SELECT id_cliente, nombre, apellido, dni, domicilio, telefono 
        FROM clientes 
        $whereBusqueda
        ORDER BY id_cliente 
        LIMIT $offset, $clientesPorPagina";
$resultado = $conn->query($sql);

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Listado de Clientes</title>
    <style>
        table {
            border-collapse: collapse;
            width: 100%;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>
    <h2>Listado de Clientes</h2>

    <!-- Formulario de búsqueda -->
    <form method="get" action="">
        <input type="text" name="busqueda" placeholder="Buscar por nombre, apellido, DNI o ID" value="<?php echo htmlspecialchars($busqueda); ?>">
        <input type="submit" value="Buscar">
    </form>

    <table>
        <tr>
            <th>N° Cliente</th>
            <th>Nombre</th>
            <th>Apellido</th>
            <th>DNI</th>
            <th>Domicilio</th>
            <th>Teléfono</th>
        </tr>
        <?php
        if ($resultado->num_rows > 0) {
            while($row = $resultado->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($row["id_cliente"]) . "</td>";
                echo "<td>" . htmlspecialchars($row["nombre"]) . "</td>";
                echo "<td>" . htmlspecialchars($row["apellido"]) . "</td>";
                echo "<td>" . htmlspecialchars($row["dni"]) . "</td>";
                echo "<td>" . htmlspecialchars($row["domicilio"]) . "</td>";
                echo "<td>" . htmlspecialchars($row["telefono"]) . "</td>";
                echo "</tr>";
            }
        } else {
            echo "<tr><td colspan='6'>No se encontraron clientes</td></tr>";
        }
        ?>
    </table>

    <!-- Paginación -->
    <div>
        <?php
        for ($i = 1; $i <= $totalPaginas; $i++) {
            echo "<a href='?pagina=$i&busqueda=" . urlencode($busqueda) . "'>$i</a> ";
        }
        ?>
    </div>
</body>
</html>

<?php
$conn->close();
?>