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
$pagos_query = "SELECT * FROM pagos WHERE id_credito = ?";
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
    <p><strong>Estado:</strong> <?php echo $credito['estado']; ?></p>
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

    <h2>Detalle de Cuotas</h2>
    <table border="1">
        <thead>
            <tr>
                <th>Nro de Cuota</th>
                <th>Fecha de Pago</th>
                <th>Monto a Pagar</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
            <?php for ($i = 1; $i <= $credito['cuotas']; $i++): ?>
                <tr>
                    <td><?php echo $i; ?></td>
                    <td><?php echo formatearFecha($fechasPago[$i - 1]); ?></td>
                    <td>$<?php echo number_format($credito['monto_cuota']); ?></td>
                    <td>
                        <?php
                        $pago = array_filter($pagos, function ($pago) use ($i) {
                            return $pago['nro_cuota'] == $i;
                        });
                        if (!empty($pago)) {
                            echo 'Pagado';
                        } else {
                            echo 'Pendiente';
                        }
                        ?>
                    </td>
                </tr>
            <?php endfor; ?>
        </tbody>
    </table>

    <br>
    <a href="editar_credito.php?id=<?php echo $credito['id']; ?>">Editar Crédito</a><br><br>
    <a href="index_creditos.php">Volver al listado de créditos</a>
</body>

</html>