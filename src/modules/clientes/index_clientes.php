<?php
// Incluir la configuración de la base de datos
require_once '../../config/database.php';

// Iniciar sesión si no está iniciada ya
session_start();

// Obtener parámetros de búsqueda y filtro
$buscar = isset($_GET['buscar']) ? trim($_GET['buscar']) : '';
$estado = isset($_GET['estado']) ? $_GET['estado'] : '';

// Si hay un mensaje en GET, convertirlo a sesión (para compatibilidad)
if (isset($_GET['mensaje'])) {
    $_SESSION['mensaje'] = $_GET['mensaje'];
    $_SESSION['tipo_mensaje'] = 'exito'; // Por defecto asumimos que es un mensaje de éxito
}

// Consulta base
$query = "SELECT id_cliente, nombre, apellido, dni, domicilio, telefono, estado 
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
    <link rel="stylesheet" href="../../../style/style.css">
    <style>
        .mensaje-notificacion {
            padding: 15px;
            margin: 15px 0;
            border-radius: 5px;
            animation: fadeOut 5s forwards;
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .mensaje-exito {
            background-color: #c8e6c9;
            color: #1b5e20;
            border: 1px solid #a5d6a7;
        }

        .mensaje-error {
            background-color: #ffcdd2;
            color: #b71c1c;
            border: 1px solid #ef9a9a;
        }

        @keyframes fadeOut {
            0% {
                opacity: 1;
            }

            70% {
                opacity: 1;
            }

            100% {
                opacity: 0;
                display: none;
            }
        }
    </style>
</head>

<body>
    <h1>Listado de Clientes</h1>

    <?php
    // Mostrar mensaje si existe (de sesión)
    if (isset($_SESSION['mensaje'])) {
        $tipo_clase = (isset($_SESSION['tipo_mensaje']) && $_SESSION['tipo_mensaje'] == 'exito') ? 'mensaje-exito' : 'mensaje-error';
        echo '<div class="mensaje-notificacion ' . $tipo_clase . '">' . $_SESSION['mensaje'] . '</div>';

        // Script para ocultar el mensaje después de 5 segundos
        echo '<script>
            setTimeout(function() {
                var mensaje = document.querySelector(".mensaje-notificacion");
                if (mensaje) {
                    mensaje.style.display = "none";
                }
            }, 5000);
        </script>';

        // Limpiar las variables de sesión
        unset($_SESSION['mensaje']);
        unset($_SESSION['tipo_mensaje']);
    }
    ?>

    <!-- Filtros por estado -->
    <div class="filtros-estado">
        <a href="index_clientes.php" class="<?php echo empty($estado) ? 'activo' : ''; ?>">Todos</a>
        <a href="index_clientes.php?estado=Activo" class="<?php echo $estado == 'Activo' ? 'activo' : ''; ?>">Activos (<?php echo contar_clientes('Activo'); ?>)</a>
        <a href="index_clientes.php?estado=Deudor" class="<?php echo $estado == 'Deudor' ? 'activo' : ''; ?>">Deudores (<?php echo contar_clientes('Deudor'); ?>)</a>
        <a href="index_clientes.php?estado=Inactivo" class="<?php echo $estado == 'Inactivo' ? 'activo' : ''; ?>">Inactivos (<?php echo contar_clientes('Inactivo'); ?>)</a>
    </div>

    <!-- Formulario de búsqueda -->
    <form action="index_clientes.php" method="GET">
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
                        <td><?php echo $cliente['id_cliente']; ?></td>
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
                            <a href="detalle_cliente.php?id_cliente=<?php echo $cliente['id_cliente']; ?>" class="btn-ver">Ver</a>
                            <a href="editar_cliente.php?id_cliente=<?php echo $cliente['id_cliente']; ?>" class="btn-editar">Editar</a>
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