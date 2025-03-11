<?php
// Incluir la configuración de la base de datos
require_once '../../config/database.php';

// Obtener parámetros de búsqueda y filtro
$buscar = isset($_GET['buscar']) ? trim($_GET['buscar']) : '';
$estado = isset($_GET['estado']) ? $_GET['estado'] : '';

// Consulta base
$query = "SELECT id, nombre, apellido, dni, domicilio, telefono, estado 
          FROM clientes 
          WHERE 1";

// Aplicar filtro de estado
if (!empty($estado) && in_array($estado, ['Activo', 'Deudor', 'Inactivo'])) {
    $query .= " AND estado = '" . $conn->real_escape_string($estado) . "'";
}

// Aplicar búsqueda por nombre, apellido o DNI
if (!empty($buscar)) {
    $buscar_limpio = $conn->real_escape_string($buscar);
    $query .= " AND (nombre LIKE '%$buscar_limpio%' 
                OR apellido LIKE '%$buscar_limpio%' 
                OR dni LIKE '%$buscar_limpio%')";
}

$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Listado de Clientes</title>
    <link rel="stylesheet" href="../../public/css/style.css">
</head>

<body>
    <h1>Listado de Clientes</h1>

    <!-- Filtros por estado -->
    <div class="filtros-estado">
        <a href="index.php" class="<?php echo empty($estado) ? 'activo' : ''; ?>">Todos</a>
        <a href="index.php?estado=Activo" class="<?php echo $estado == 'Activo' ? 'activo' : ''; ?>">Activos (<?php echo contar_clientes('Activo'); ?>)</a>
        <a href="index.php?estado=Deudor" class="<?php echo $estado == 'Deudor' ? 'activo' : ''; ?>">Deudores (<?php echo contar_clientes('Deudor'); ?>)</a>
        <a href="index.php?estado=Inactivo" class="<?php echo $estado == 'Inactivo' ? 'activo' : ''; ?>">Inactivos (<?php echo contar_clientes('Inactivo'); ?>)</a>
    </div>

    <!-- Formulario de búsqueda -->
    <form action="index.php" method="GET">
        <input type="text" name="buscar" placeholder="Buscar cliente..." value="<?php echo htmlspecialchars($buscar); ?>">
        <input type="hidden" name="estado" value="<?php echo htmlspecialchars($estado); ?>">
        <button type="submit">Buscar</button>
    </form>

    <!-- Botón de nuevo cliente -->
    <a href="registrar_cliente.php" class="nuevo-cliente">➕ Nuevo Cliente</a>

    <!-- Tabla de clientes -->
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Apellido</th>
                <th>DNI</th>
                <th>Domicilio</th>
                <th>Teléfono</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result->num_rows > 0): ?>
                <?php while ($cliente = $result->fetch_assoc()): ?>
                    <tr class="estado-<?php echo strtolower($cliente['estado']); ?>">
                        <td><?php echo $cliente['id']; ?></td>
                        <td><?php echo htmlspecialchars($cliente['nombre']); ?></td>
                        <td><?php echo htmlspecialchars($cliente['apellido']); ?></td>
                        <td><?php echo $cliente['dni']; ?></td>
                        <td><?php echo htmlspecialchars($cliente['domicilio']); ?></td>
                        <td><?php echo $cliente['telefono']; ?></td>
                        <td>
                            <span class="badge-estado <?php echo strtolower($cliente['estado']); ?>">
                                <?php echo $cliente['estado']; ?>
                            </span>
                        </td>
                        <td>
                            <a href="editar_cliente.php?id=<?php echo $cliente['id']; ?>" class="btn-editar">Editar</a>
                            <a href="detalle_cliente.php?id=<?php echo $cliente['id']; ?>" class="btn-ver">Ver</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="8" class="sin-resultados">No se encontraron clientes</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

</body>

</html>

<?php
// Función para contar clientes por estado
function contar_clientes($estado)
{
    global $conn;
    $query = "SELECT COUNT(*) AS total FROM clientes WHERE estado = '" . $conn->real_escape_string($estado) . "'";
    $result = $conn->query($query);
    return $result->fetch_assoc()['total'];
}
?>