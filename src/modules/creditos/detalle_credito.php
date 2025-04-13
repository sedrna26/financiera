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
$query = "SELECT c.id, c.cliente_id, c.monto, c.cuotas, c.fecha_inicio, c.fecha_vencimiento, c.estado, c.frecuencia, c.monto_total, c.monto_cuota,
                 cl.nombre, cl.apellido, cl.dni, cl.domicilio, cl.telefono 
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


// Obtener los pagos realizados
$pagos_query = "SELECT *, SUM(monto_pagado) AS total_pagado 
                FROM pagos 
                WHERE id_credito = ? 
                GROUP BY nro_cuota";
$stmt_pagos = $conn->prepare($pagos_query);
$stmt_pagos->bind_param("i", $credito_id);
$stmt_pagos->execute();
$pagos_result = $stmt_pagos->get_result();
$pagos = [];
while ($pago = $pagos_result->fetch_assoc()) {
    $pagos[] = $pago;
}

// // Calcular el valor de cada cuota
// $valorCuota = calcularCuotas($credito['monto'], $credito['cuotas'], $credito['frecuencia']);

// Función para calcular las fechas de pago
function calcularFechasPago($fechaInicio, $cuotas, $frecuencia)
{
    $fechas = [];
    $fechaActual = $fechaInicio;

    for ($i = 0; $i < $cuotas; $i++) {
        $fechas[] = $fechaActual;
        switch ($frecuencia) {
            case 'mensual':
                $fechaActual = date('Y-m-d', strtotime($fechaActual . ' +1 month'));
                break;
            case 'quincenal':
                $fechaActual = date('Y-m-d', strtotime($fechaActual . ' +15 days'));
                break;
            case 'semanal':
                $fechaActual = date('Y-m-d', strtotime($fechaActual . ' +7 days'));
                break;
            default:
                // Si no se especifica una frecuencia válida, se asume mensual
                $fechaActual = date('Y-m-d', strtotime($fechaActual . ' +1 month'));
                break;
        }
    }

    return $fechas;
}

// Obtener las fechas de pago
$fechasPago = calcularFechasPago($credito['fecha_inicio'], $credito['cuotas'], $credito['frecuencia']);

// Función para formatear fechas en dd/mm/aaaa
function formatearFecha($fecha)
{
    return date('d/m/Y', strtotime($fecha));
}
// Calcular deuda con intereses para cada cuota
$cuotas_con_deuda = [];
foreach ($fechasPago as $i => $fechaVencimiento) {
    $nro_cuota = $i + 1;
    $pagos_cuota = array_filter($pagos, function($p) use ($nro_cuota) {
        return $p['nro_cuota'] == $nro_cuota;
    });
    
    $total_pagado = array_sum(array_column($pagos_cuota, 'monto_pagado'));
    $monto_cuota = $credito['monto_cuota'];
    
    // Calcular días de retraso
    $fecha_vencimiento = new DateTime($fechaVencimiento);
    $hoy = new DateTime();
    $dias_retraso = $hoy > $fecha_vencimiento ? $hoy->diff($fecha_vencimiento)->days : 0;
    
    // Calcular intereses
    $intereses = $monto_cuota * 0.005 * $dias_retraso;
    $total_adeudado = $monto_cuota + $intereses - $total_pagado;
    
    $cuotas_con_deuda[] = [
        'nro' => $nro_cuota,
        'adeudado' => max($total_adeudado, 0),
        'dias_retraso' => $dias_retraso,
        'pagado' => $total_pagado
    ];
}
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
    <p><strong>Monto Prestado:</strong> $<?php echo number_format($credito['monto'], 2); ?></p>
    <p><strong>Cuotas:</strong> <?php echo $credito['cuotas']; ?></p>
    <p><strong>Monto Total:</strong> $<?php echo number_format($credito['monto_total'], 2); ?></p>
    <p><strong>Valor por cuota:</strong> $<?php echo number_format($credito['monto_cuota'], 2); ?></p>
    <?php if ($credito['cuotas'] > 1): ?>
        <p><strong>Fecha de Inicio:</strong> <?php echo formatearFecha($credito['fecha_inicio']); ?></p>
    <?php endif; ?>
    <p><strong>Fecha de Vencimiento:</strong> <?php echo formatearFecha($credito['fecha_vencimiento']); ?></p>
    <p><strong>Estado:</strong> 
<?php 
// Determinar el estado real del crédito
$estado_real = $credito['estado'];
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
<span class="<?php echo $clase_estado; ?>"><?php echo $estado_real; ?></span>
</p>
    <div style="margin: 20px 0;">
        <!-- Botón Pagaré -->
        <a href="generar_documentos.php?tipo=pagare&cliente=<?= urlencode($credito['nombre'] . ' ' . $credito['apellido']) ?>&dni=<?= $credito['dni'] ?>&domicilio=<?= urlencode($credito['domicilio']) ?>&telefono=<?= $credito['telefono'] ?>&monto=<?= $credito['monto'] ?>&cuotas=<?= $credito['cuotas'] ?>&fecha=<?= $credito['fecha_inicio'] ?>&monto_total=<?= $credito['monto_total'] ?>&monto_cuota=<?= $credito['monto_cuota'] ?>&frecuencia=<?= $credito['frecuencia'] ?>"
            class="btn-pdf"
            target="_blank">
            Generar Pagaré
        </a>

        <!-- Botón Contrato -->
        <a href="generar_documentos.php?tipo=contrato&cliente=<?= urlencode($credito['nombre'] . ' ' . $credito['apellido']) ?>&dni=<?= $credito['dni'] ?>&domicilio=<?= urlencode($credito['domicilio']) ?>&telefono=<?= $credito['telefono'] ?>&monto=<?= $credito['monto'] ?>&cuotas=<?= $credito['cuotas'] ?>&fecha_inicio=<?= $credito['fecha_inicio'] ?>&frecuencia=<?= $credito['frecuencia'] ?>&monto_total=<?= $credito['monto_total'] ?>&monto_cuota=<?= $credito['monto_cuota'] ?>" class="btn-pdf"
            target="_blank">
            Generar Contrato
        </a>
    </div>

    <h2>Información del Cliente</h2>
    <p><strong>Nombre:</strong> <?php echo $credito['nombre'] . ' ' . $credito['apellido']; ?></p>
    <p><strong>DNI:</strong> <?php echo $credito['dni']; ?></p>
<!-- Formulario para registrar pago -->
<h2>Registrar Pago</h2>
<form action="registrar_pago.php" method="POST">
    <input type="hidden" name="credito_id" value="<?= $credito['id'] ?>">
    
    <label>Cuota:
        <select name="nro_cuota" required>
            <?php foreach ($cuotas_con_deuda as $cuota): ?>
                <?php if ($cuota['adeudado'] > 0): ?>
                    <option value="<?= $cuota['nro'] ?>">
                        Cuota <?= $cuota['nro'] ?> - Adeuda: $<?= number_format($cuota['adeudado'], 2) ?>
                    </option>
                <?php endif; ?>
            <?php endforeach; ?>
        </select>
    </label>

    <label>Monto a pagar:
        <input type="number" name="monto" step="0.01" required>
    </label>

    <button type="submit">Registrar Pago</button>
</form>

<!-- Tabla de cuotas actualizada -->
  <h2>Detalle de Cuotas</h2>
<table border="1">
<th>Nro de Cuota</th>
                <th>Fecha de Pago</th>
                <th>Monto a Pagar</th>
                <th>Estado</th>
    <tbody>
        <?php foreach ($cuotas_con_deuda as $cuota): ?>
            <tr>
                <td><?= $cuota['nro'] ?></td>
                <td><?= formatearFecha($fechasPago[$cuota['nro']-1]) ?></td>
                <td>
                    $<?= number_format($credito['monto_cuota'], 2) ?>
                    <?php if ($cuota['dias_retraso'] > 0): ?>
                        <br><small>(+ $<?= number_format($credito['monto_cuota'] * 0.005 * $cuota['dias_retraso'], 2) ?> intereses)</small>
                    <?php endif; ?>
                </td>
                <td>
                    <?php if ($cuota['pagado'] >= $credito['monto_cuota']): ?>
                        Pagado
                    <?php else: ?>
                        Adeuda: $<?= number_format($cuota['adeudado'], 2) ?>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

    <br>
    <a href="editar_credito.php?id=<?php echo $credito['id']; ?>">Editar Crédito</a><br><br>
    <a href="index_creditos.php">Volver al listado de créditos</a>

    <style>
.estado-cancelado {
    background-color: #c8e6c9; /* Verde claro */
    color: #1b5e20;
    padding: 3px 8px;
    border-radius: 4px;
}
.estado-mora {
    background-color: #ffcdd2; /* Rojo claro */
    color: #b71c1c;
    padding: 3px 8px;
    border-radius: 4px;
}
.estado-activo {
    background-color: #e3f2fd; /* Azul claro */
    color: #0d47a1;
    padding: 3px 8px;
    border-radius: 4px;
}
</style>
</body>
<script>
    // Función para mostrar notificaciones
    function mostrarNotificacion(mensaje, tipo) {
        // Crear el elemento de notificación
        const notificacion = document.createElement('div');
        notificacion.className = 'notificacion ' + tipo;
        notificacion.textContent = mensaje;
        
        // Estilos para la notificación
        notificacion.style.position = 'fixed';
        notificacion.style.top = '20px';
        notificacion.style.right = '20px';
        notificacion.style.padding = '15px 20px';
        notificacion.style.borderRadius = '5px';
        notificacion.style.zIndex = '1000';
        notificacion.style.boxShadow = '0 3px 10px rgba(0,0,0,0.2)';
        notificacion.style.opacity = '0';
        notificacion.style.transition = 'opacity 0.3s ease-in-out';
        
        // Estilos según el tipo
        if (tipo === 'exito') {
            notificacion.style.backgroundColor = '#4CAF50';
            notificacion.style.color = 'white';
        } else if (tipo === 'error') {
            notificacion.style.backgroundColor = '#f44336';
            notificacion.style.color = 'white';
        }
        
        // Agregar al DOM
        document.body.appendChild(notificacion);
        
        // Hacer visible la notificación
        setTimeout(() => {
            notificacion.style.opacity = '1';
        }, 10);
        
        // Eliminar después de 5 segundos
        setTimeout(() => {
            notificacion.style.opacity = '0';
            setTimeout(() => {
                document.body.removeChild(notificacion);
            }, 300);
        }, 5000);
    }

    // Verificar si hay un parámetro en la URL
    document.addEventListener('DOMContentLoaded', function() {
        // Obtener parámetros de la URL
        const urlParams = new URLSearchParams(window.location.search);
        
        // Verificar si hay mensaje de éxito
        if (urlParams.has('success')) {
            mostrarNotificacion(urlParams.get('success'), 'exito');
        }
        
        // Verificar si hay mensaje de error
        if (urlParams.has('error')) {
            mostrarNotificacion(urlParams.get('error'), 'error');
        }
    });
</script>
</html>