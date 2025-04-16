<?php
// Iniciar sesión para recuperar mensajes
session_start();

// Incluir la configuración de la base de datos
require_once '../../config/database.php';

// Consulta para obtener todos los créditos junto con datos del cliente
$query = "SELECT c.id, cl.nombre, cl.apellido, c.monto, c.cuotas, c.fecha_inicio, c.fecha_vencimiento, c.estado 
          FROM creditos c 
          INNER JOIN clientes cl ON c.cliente_id = cl.id_cliente";

// Si se realiza una búsqueda, se filtra por nombre, apellido o número de crédito
if (isset($_GET['buscar']) && !empty($_GET['buscar'])) {
    $buscar = $conn->real_escape_string($_GET['buscar']);
    $query .= " WHERE cl.nombre LIKE '%$buscar%' OR cl.apellido LIKE '%$buscar%' OR c.id LIKE '%$buscar%'";
}

$result = $conn->query($query);

// Función para determinar el estado real del crédito
function obtenerEstadoCredito($conn, $credito_id, $credito_cuotas)
{
    // Obtener todas las cuotas pagadas
    $query_pagadas = "SELECT COUNT(*) as cuotas_pagadas FROM pagos 
                     WHERE id_credito = $credito_id AND estado = 'Pagado' 
                     GROUP BY nro_cuota";
    $result_pagadas = $conn->query($query_pagadas);
    $cuotas_pagadas = $result_pagadas ? $result_pagadas->num_rows : 0;

    // Si todas las cuotas están pagadas
    if ($cuotas_pagadas >= $credito_cuotas) {
        return "Cancelado";
    }

    // Verificar si hay cuotas en mora
    $hoy = date('Y-m-d');
    $query_mora = "SELECT * FROM pagos 
                  WHERE id_credito = $credito_id 
                  AND fecha_vencimiento < '$hoy' 
                  AND estado = 'Impago'
                  LIMIT 1";
    $result_mora = $conn->query($query_mora);

    if ($result_mora && $result_mora->num_rows > 0) {
        return "Mora";
    }

    // Si no está cancelado ni en mora, está activo
    return "Activo";
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Listado de Créditos</title>
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

        .estado-cancelado {
            background-color: #c8e6c9;
            /* Verde claro */
            color: #1b5e20;
        }

        .estado-mora {
            background-color: #ffcdd2;
            /* Rojo claro */
            color: #b71c1c;
        }

        .estado-activo {
            background-color: #e3f2fd;
            /* Azul claro */
            color: #0d47a1;
        }
    </style>
</head>

<body>
    <h1>Listado de Créditos</h1>

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

    <!-- Enlace para registrar un nuevo crédito -->
    <a href="registrar_credito.php">Registrar Nuevo Crédito</a>

    <!-- Formulario de búsqueda -->
    <form action="index_creditos.php" method="GET">
        <input type="text" name="buscar" placeholder="Buscar crédito..." value="<?php echo isset($_GET['buscar']) ? $_GET['buscar'] : ''; ?>">
        <button type="submit">Buscar</button>
    </form>

    <table border="1" cellpadding="10" cellspacing="0">
        <thead>
            <tr>
                <th>Nro de Crédito</th>
                <th>Cliente</th>
                <th>Monto</th>
                <th>Cuotas</th>
                <th>Fecha de Inicio</th>
                <th>Fecha de Vencimiento</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result && $result->num_rows > 0): ?>
                <?php while ($credito = $result->fetch_assoc()):
                    // Calculamos el estado real del crédito
                    $estado_real = obtenerEstadoCredito($conn, $credito['id'], $credito['cuotas']);

                    // Definimos clases CSS para los diferentes estados
                    $clase_estado = '';
                    switch ($estado_real) {
                        case 'Cancelado':
                            $clase_estado = 'estado-cancelado';
                            break;
                        case 'Mora':
                            $clase_estado = 'estado-mora';
                            break;
                        case 'Activo':
                            $clase_estado = 'estado-activo';
                            break;
                    }
                ?>
                    <tr>
                        <td><?php echo $credito['id']; ?></td>
                        <td><?php echo $credito['nombre'] . ' ' . $credito['apellido']; ?></td>
                        <td>$<?php echo number_format($credito['monto'], 2); ?></td>
                        <td><?php echo $credito['cuotas']; ?></td>
                        <td><?php echo $credito['fecha_inicio']; ?></td>
                        <td><?php echo $credito['fecha_vencimiento']; ?></td>
                        <td class="<?php echo $clase_estado; ?>"><?php echo $estado_real; ?></td>
                        <td>
                            <a href="editar_credito.php?id=<?php echo $credito['id']; ?>">Editar</a>
                            <a href="detalle_credito.php?id=<?php echo $credito['id']; ?>">Ver</a>
                            <a href="eliminar_credito.php?id=<?= $credito['id'] ?>" onclick="return confirm('¿Eliminar crédito?')">Eliminar</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="8">No se encontraron créditos.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</body>

</html>