<?php
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
function obtenerEstadoCredito($conn, $credito_id, $credito_cuotas) {
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
    // Corrigiendo esta consulta para usar cualquier campo válido que exista en la tabla pagos
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
</head>

<body>
    <h1>Listado de Créditos</h1>

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
    
    <style>
    .estado-cancelado {
        background-color: #c8e6c9; /* Verde claro */
        color: #1b5e20;
    }
    .estado-mora {
        background-color: #ffcdd2; /* Rojo claro */
        color: #b71c1c;
    }
    .estado-activo {
        background-color: #e3f2fd; /* Azul claro */
        color: #0d47a1;
    }
    </style>
</body>

</html>