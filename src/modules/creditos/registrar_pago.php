<?php
require_once '../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $credito_id = intval($_POST['credito_id']);
    $nro_cuota = intval($_POST['nro_cuota']);
    $monto = floatval($_POST['monto']);
    
    // Obtener detalles de la cuota
    $query = "SELECT fecha_vencimiento, monto_cuota FROM creditos 
              WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $credito_id);
    $stmt->execute();
    $credito = $stmt->get_result()->fetch_assoc();
    
    // Calcular días de retraso
    $fecha_vencimiento = new DateTime($credito['fecha_vencimiento']);
    $hoy = new DateTime();
    $dias_retraso = $hoy > $fecha_vencimiento ? $hoy->diff($fecha_vencimiento)->days : 0;
    
    // Calcular adeudo total
    $adeudado = $credito['monto_cuota'] + ($credito['monto_cuota'] * 0.005 * $dias_retraso);
    
    // Consulta SQL
    $insert = "INSERT INTO pagos (
        id_credito, 
        nro_cuota, 
        fecha_pago, 
        monto_pagado, 
        estado, 
        fecha_vencimiento, 
        monto_adeudado, 
        dias_retraso
    ) VALUES (?, ?, NOW(), ?, 'Impago', ?, ?, ?)";

    // Ajustar la cadena de tipos y parámetros
    $stmt = $conn->prepare($insert);
    $stmt->bind_param(
        "iiddsi", // 7 parámetros (id_credito, nro_cuota, monto_pagado, monto_adeudado, fecha_vencimiento, dias_retraso)
        $credito_id,
        $nro_cuota,
        $monto,        // Corresponde a monto_pagado (3er ?)
        $adeudado,     // Corresponde a monto_adeudado (7mo ?)
        $credito['fecha_vencimiento'], 
        $dias_retraso
    );
    
    if ($stmt->execute()) {
        // Verificar si está completamente pagado
        $total_pagado = $monto + obtener_pagos_previos($conn, $credito_id, $nro_cuota);
        
        if ($total_pagado >= $adeudado) {
            $conn->query("UPDATE pagos SET estado = 'Pagado' 
                          WHERE id_credito = $credito_id AND nro_cuota = $nro_cuota");
        }
        
        // Actualizar estado del crédito
        actualizarEstadoCredito($conn, $credito_id);
        
        header("Location: detalle_credito.php?id=$credito_id&success=Pago registrado");
    } else {
        header("Location: detalle_credito.php?id=$credito_id&error=Error al registrar pago");
    }
}

function obtener_pagos_previos($conn, $credito_id, $cuota) {
    $result = $conn->query("SELECT SUM(monto_pagado) AS total 
                            FROM pagos 
                            WHERE id_credito = $credito_id AND nro_cuota = $cuota");
    return $result->fetch_assoc()['total'] ?? 0;
}

function actualizarEstadoCredito($conn, $credito_id) {
    // Obtener el número total de cuotas
    $query = "SELECT cuotas FROM creditos WHERE id = $credito_id";
    $result = $conn->query($query);
    $credito = $result->fetch_assoc();
    $total_cuotas = $credito['cuotas'];
    
    // Contar cuotas pagadas
    $query_pagadas = "SELECT COUNT(DISTINCT nro_cuota) as cuotas_pagadas 
                     FROM pagos 
                     WHERE id_credito = $credito_id AND estado = 'Pagado'";
    $result_pagadas = $conn->query($query_pagadas);
    $cuotas_pagadas = $result_pagadas->fetch_assoc()['cuotas_pagadas'];
    
    // Determinar el nuevo estado
    $nuevo_estado = "";
    
    // Si todas las cuotas están pagadas
    if ($cuotas_pagadas >= $total_cuotas) {
        $nuevo_estado = "Cancelado";
    } else {
        // Verificar si hay cuotas en mora
        $hoy = date('Y-m-d');
        $query_mora = "SELECT * FROM pagos 
                      WHERE id_credito = $credito_id 
                      AND fecha_vencimiento < '$hoy' 
                      AND estado = 'Impago'
                      LIMIT 1";
        $result_mora = $conn->query($query_mora);
        
        if ($result_mora->num_rows > 0) {
            $nuevo_estado = "Mora";
        } else {
            $nuevo_estado = "Activo";
        }
    }
    
    // Actualizar el estado del crédito
    $conn->query("UPDATE creditos SET estado = '$nuevo_estado' WHERE id = $credito_id");
}
?>