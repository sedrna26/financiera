<?php
session_start();
require_once '../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $credito_id = intval($_POST['credito_id']);
    $nro_cuota = intval($_POST['nro_cuota']);
    
    if ($credito_id <= 0 || $nro_cuota <= 0) {
        echo json_encode(['success' => false, 'message' => 'Datos inválidos']);
        exit;
    }
    
    try {
        // Comenzar transacción para asegurar la integridad de los datos
        $conn->begin_transaction();
        
        // Obtener monto base de la cuota y verificar pagos anteriores
        $query = "SELECT c.monto_cuota, 
                        (SELECT SUM(monto_pagado) FROM pagos WHERE id_credito = ? AND nro_cuota = ?) as pagado_antes 
                 FROM creditos c WHERE c.id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("iii", $credito_id, $nro_cuota, $credito_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            echo json_encode(['success' => false, 'message' => 'Crédito no encontrado']);
            exit;
        }
        
        $credito = $result->fetch_assoc();
        $monto_base = $credito['monto_cuota'];
        $pagado_antes = $credito['pagado_antes'] ?? 0;
        
        // Calcular el monto pendiente (solo la parte principal, sin intereses)
        $monto_pendiente = $monto_base - $pagado_antes;
        
        if ($monto_pendiente <= 0) {
            // Si ya se pagó el monto base, solo marcar intereses como anulados
            $update_query = "UPDATE pagos 
                            SET intereses_anulados = 1
                            WHERE id_credito = ? AND nro_cuota = ?";
            $stmt_update = $conn->prepare($update_query);
            $stmt_update->bind_param("ii", $credito_id, $nro_cuota);
            $stmt_update->execute();
        } else {
            // Marcar intereses como anulados en pagos existentes
            $update_query = "UPDATE pagos 
                            SET intereses_anulados = 1
                            WHERE id_credito = ? AND nro_cuota = ?";
            $stmt_update = $conn->prepare($update_query);
            $stmt_update->bind_param("ii", $credito_id, $nro_cuota);
            $stmt_update->execute();
            
            // Calcular fecha de vencimiento de la cuota
            $query_fecha = "SELECT fecha_inicio, frecuencia FROM creditos WHERE id = ?";
            $stmt_fecha = $conn->prepare($query_fecha);
            $stmt_fecha->bind_param("i", $credito_id);
            $stmt_fecha->execute();
            $credito_fecha = $stmt_fecha->get_result()->fetch_assoc();
            
            $fecha_inicio = $credito_fecha['fecha_inicio'];
            $frecuencia = $credito_fecha['frecuencia'];
            
            // Calcular fecha de vencimiento
            $fecha_vencimiento = calcularFechaVencimiento($fecha_inicio, $nro_cuota, $frecuencia);
            
            // Insertar un pago automático por el monto pendiente
            $insert_pago = "INSERT INTO pagos (
                id_credito, 
                nro_cuota, 
                fecha_pago, 
                monto_pagado, 
                estado, 
                fecha_vencimiento, 
                monto_adeudado,
                dias_retraso,
                intereses_anulados
            ) VALUES (?, ?, NOW(), ?, 'Pagado', ?, ?, 0, 1)";
            
            $stmt_pago = $conn->prepare($insert_pago);
            $stmt_pago->bind_param(
                "iidsd", 
                $credito_id,
                $nro_cuota,
                $monto_pendiente,
                $fecha_vencimiento,
                $monto_base
            );
            $stmt_pago->execute();
        }
        
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
        
        // Si se ha pagado todo el crédito, actualizar su estado
        if ($total_pagado_credito >= $monto_total) {
            $update_credito = "UPDATE creditos SET estado = 'Pagado' WHERE id = ?";
            $stmt_update = $conn->prepare($update_credito);
            $stmt_update->bind_param("i", $credito_id);
            $stmt_update->execute();
        }
        
        // Confirmar transacción
        $conn->commit();
        
        echo json_encode([
            'success' => true,
            'monto_base' => $monto_base,
            'pagado' => true
        ]);
        
    } catch (Exception $e) {
        // Revertir cambios en caso de error
        $conn->rollback();
        
        echo json_encode([
            'success' => false,
            'message' => 'Error: ' . $e->getMessage()
        ]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
}

// Función para calcular la fecha de vencimiento (tomada de otros archivos)
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
?>