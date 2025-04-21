<?php
// Incluir la configuración de la base de datos
require_once '../../config/database.php';

// Verificar que se envió un ID de crédito
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo json_encode(['error' => 'ID de crédito no especificado']);
    exit;
}

$credito_id = intval($_GET['id']);

// Consulta para obtener los datos del crédito y del cliente asociado
$query = "SELECT c.id, c.cliente_id, c.monto, c.cuotas, c.fecha_inicio, c.fecha_vencimiento, 
                 c.estado, c.frecuencia, c.monto_total, c.monto_cuota,
                 cl.nombre, cl.apellido, cl.dni, cl.domicilio, cl.telefono 
          FROM creditos c 
          INNER JOIN clientes cl ON c.cliente_id = cl.id_cliente 
          WHERE c.id = ?";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $credito_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    echo json_encode(['error' => 'Crédito no encontrado']);
    exit;
}

$credito = $result->fetch_assoc();

// Calcular fechas de vencimiento para cada cuota
function calcularFechasPago($fechaInicio, $cuotas, $frecuencia) {
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
                $fechaActual = date('Y-m-d', strtotime($fechaActual . ' +1 month'));
                break;
        }
    }

    return $fechas;
}

// Obtener las fechas de pago
$fechasPago = calcularFechasPago($credito['fecha_inicio'], $credito['cuotas'], $credito['frecuencia']);

// Obtener los pagos realizados
$pagos_query = "SELECT nro_cuota, SUM(monto_pagado) AS total_pagado 
                FROM pagos 
                WHERE id_credito = ? 
                GROUP BY nro_cuota";
$stmt_pagos = $conn->prepare($pagos_query);
$stmt_pagos->bind_param("i", $credito_id);
$stmt_pagos->execute();
$pagos_result = $stmt_pagos->get_result();

$pagos = [];
while ($pago = $pagos_result->fetch_assoc()) {
    $pagos[$pago['nro_cuota']] = $pago['total_pagado'];
}

// Calcular deuda con intereses para cada cuota
$cuotas_con_deuda = [];
foreach ($fechasPago as $i => $fechaVencimiento) {
    $nro_cuota = $i + 1;
    $total_pagado = isset($pagos[$nro_cuota]) ? $pagos[$nro_cuota] : 0;
    
    // Calcular días de retraso
    $fecha_vencimiento = new DateTime($fechaVencimiento);
    $hoy = new DateTime();
    $dias_retraso = $hoy > $fecha_vencimiento ? $hoy->diff($fecha_vencimiento)->days : 0;
    
    // Calcular intereses
    $monto_cuota = $credito['monto_cuota'];
    // Verificar si los intereses están anulados para esta cuota
    $query_anulados = "SELECT intereses_anulados FROM pagos 
    WHERE id_credito = ? AND nro_cuota = ? 
    ORDER BY id_pago DESC LIMIT 1";
$stmt_anulados = $conn->prepare($query_anulados);
$stmt_anulados->bind_param("ii", $credito_id, $nro_cuota);
$stmt_anulados->execute();
$result_anulados = $stmt_anulados->get_result();
$anulado = $result_anulados->num_rows > 0 ? $result_anulados->fetch_assoc()['intereses_anulados'] : 0;

// Si están anulados, intereses = 0
$intereses = ($anulado) ? 0 : $credito['monto_cuota'] * 0.005 * $dias_retraso;

    // Calcular intereses (0 si están anulados)
    $intereses = $anulado ? 0 : $monto_cuota * 0.005 * $dias_retraso;
    $total_adeudado = $monto_cuota + $intereses - $total_pagado;
    $intereses = $anulado ? 0 : $monto_cuota * 0.005 * $dias_retraso;
    $total_adeudado = $monto_cuota + $intereses - $total_pagado;
    
    $cuotas_con_deuda[] = [
        'nro' => $nro_cuota,
        'fecha_vencimiento' => date('d/m/Y', strtotime($fechaVencimiento)),
        'adeudado' => max($total_adeudado, 0),
        'dias_retraso' => $dias_retraso,
        'pagado' => $total_pagado
    ];
}

// Función para determinar el estado real del crédito
function obtenerEstadoCredito($cuotas_con_deuda, $total_cuotas) {
    $cuotas_pagadas = 0;
    $hay_mora = false;
    
    foreach ($cuotas_con_deuda as $cuota) {
        if ($cuota['adeudado'] <= 0) {
            $cuotas_pagadas++;
        } elseif ($cuota['dias_retraso'] > 0) {
            $hay_mora = true;
        }
    }
    
    if ($cuotas_pagadas >= $total_cuotas) {
        return "Cancelado";
    } elseif ($hay_mora) {
        return "Mora";
    } else {
        return "Activo";
    }
}

// Preparar respuesta
$response = [
    'id' => $credito['id'],
    'cliente' => $credito['nombre'] . ' ' . $credito['apellido'],
    'dni' => $credito['dni'],
    'monto' => $credito['monto'],
    'monto_total' => $credito['monto_total'],
    'monto_cuota' => $credito['monto_cuota'],
    'cuotas' => $credito['cuotas'],
    'fecha_inicio' => date('d/m/Y', strtotime($credito['fecha_inicio'])),
    'fecha_vencimiento' => date('d/m/Y', strtotime($credito['fecha_vencimiento'])),
    'estado' => obtenerEstadoCredito($cuotas_con_deuda, $credito['cuotas']),
    'cuotas_detalle' => $cuotas_con_deuda
];

// Devolver resultados en formato JSON
header('Content-Type: application/json');
echo json_encode($response);
?>