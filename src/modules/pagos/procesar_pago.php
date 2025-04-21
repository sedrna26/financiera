<?php
// Iniciar sesión para mensajes
session_start();

// Incluir la configuración de la base de datos
require_once '../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $credito_id = intval($_POST['credito_id']);
    $nro_cuota = intval($_POST['nro_cuota']);
    $monto = floatval($_POST['monto']);
    
    if ($credito_id <= 0 || $nro_cuota <= 0 || $monto <= 0) {
        $_SESSION['mensaje'] = "Los datos proporcionados no son válidos";
        $_SESSION['tipo_mensaje'] = "error";
        header("Location: gestionar_pagos.php");
        exit;
    }
    
    // Obtener detalles del crédito
    $query = "SELECT cuotas, fecha_inicio, fecha_vencimiento, frecuencia, monto_cuota 
              FROM creditos WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $credito_id);
    $stmt->execute();
    $credito = $stmt->get_result()->fetch_assoc();
    
    if (!$credito) {
        $_SESSION['mensaje'] = "El crédito especificado no existe";
        $_SESSION['tipo_mensaje'] = "error";
        header("Location: gestionar_pagos.php");
        exit;
    }
    
    // Calcular fecha de vencimiento de la cuota específica
    function calcularFechaVencimiento($fechaInicio, $nroCuota, $frecuencia) {
        $fechaActual = $fechaInicio;
        
        for ($i = 1; $i < $nroCuota; $i++) {
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
        
        return $fechaActual;
    }
    
    $fecha_vencimiento = calcularFechaVencimiento($credito['fecha_inicio'], $nro_cuota, $credito['frecuencia']);
   // Calcular días de retraso
$fecha_venc_obj = new DateTime($fecha_vencimiento);
$hoy = new DateTime();
$dias_retraso = $hoy > $fecha_venc_obj ? $hoy->diff($fecha_venc_obj)->days : 0;

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
    // Calcular adeudo total (cuota base + intereses)
    $intereses = $credito['monto_cuota'] * 0.005 * $dias_retraso;
    $adeudado = $credito['monto_cuota'] + $intereses;
    
    // Verificar si ya existe un pago para esta cuota
    $check_query = "SELECT SUM(monto_pagado) as pagado_antes 
                   FROM pagos 
                   WHERE id_credito = ? AND nro_cuota = ?";
    $stmt_check = $conn->prepare($check_query);
    $stmt_check->bind_param("ii", $credito_id, $nro_cuota);
    $stmt_check->execute();
    $pagado_antes = $stmt_check->get_result()->fetch_assoc()['pagado_antes'] ?? 0;
    
    // Determinar si se completa el pago
    $total_pagado = $pagado_antes + $monto;
    $estado = $total_pagado >= $adeudado ? 'Pagado' : 'Impago';
    
    // Insertar registro de pago
    $insert = "INSERT INTO pagos (
        id_credito, 
        nro_cuota, 
        fecha_pago, 
        monto_pagado, 
        estado, 
        fecha_vencimiento, 
        monto_adeudado, 
        dias_retraso
    ) VALUES (?, ?, NOW(), ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($insert);
    $stmt->bind_param(
        "iidsdsi", 
        $credito_id,
        $nro_cuota,
        $monto,
        $estado,
        $fecha_vencimiento,
        $adeudado,
        $dias_retraso
    );
    
    if ($stmt->execute()) {
        // Verificar si el crédito está completamente pagado
        $query_pagado = "SELECT SUM(monto_pagado) as total_pagado FROM pagos WHERE id_credito = ?";
        $stmt_pagado = $conn->prepare($query_pagado);
        $stmt_pagado->bind_param("i", $credito_id);
        $stmt_pagado->execute();
        $total_pagado_credito = $stmt_pagado->get_result()->fetch_assoc()['total_pagado'];
        
        $query_monto_total = "SELECT monto_total FROM creditos WHERE id = ?";
        $stmt_monto = $conn->prepare($query_monto_total);
        $stmt_monto->bind_param("i", $credito_id);
        $stmt_monto->execute();
        $monto_total = $stmt_monto->get_result()->fetch_assoc()['monto_total'];
        
        if ($total_pagado_credito >= $monto_total) {
            // Actualizar estado del crédito a Pagado
            $update_credito = "UPDATE creditos SET estado = 'Pagado' WHERE id = ?";
            $stmt_update = $conn->prepare($update_credito);
            $stmt_update->bind_param("i", $credito_id);
            $stmt_update->execute();
        }
        
        $_SESSION['mensaje'] = "Pago registrado correctamente";
        $_SESSION['tipo_mensaje'] = "exito";
    } else {
        $_SESSION['mensaje'] = "Error al registrar el pago: " . $conn->error;
        $_SESSION['tipo_mensaje'] = "error";
    }
    
    header("Location: gestionar_pagos.php");
    exit;
} else {
    $_SESSION['mensaje'] = "Método de solicitud no válido";
    $_SESSION['tipo_mensaje'] = "error";
    header("Location: gestionar_pagos.php");
    exit;
}
?>