<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $monto = floatval($_POST['monto']);
    $cuotas = intval($_POST['cuotas']);
    $frecuencia = $_POST['frecuencia'];
    $tasa_interes = 0.05; // 5% de interés por período

    // Cálculo de la cuota usando el sistema de amortización francés
    $tasa = $tasa_interes;
    $cuota = ($monto * $tasa) / (1 - pow(1 + $tasa, -$cuotas));
    
    $cronograma = [];
    $saldo = $monto;
    $fecha_actual = date('Y-m-d');

    for ($i = 1; $i <= $cuotas; $i++) {
        // Calcular intereses y amortización
        $interes = $saldo * $tasa;
        $capital = $cuota - $interes;
        $saldo -= $capital;

        // Calcular fecha de vencimiento según la frecuencia
        switch ($frecuencia) {
            case 'semanal':
                $fecha_actual = date('Y-m-d', strtotime($fecha_actual . ' +7 days'));
                break;
            case 'quincenal':
                $fecha_actual = date('Y-m-d', strtotime($fecha_actual . ' +15 days'));
                break;
            case 'mensual':
                $fecha_actual = date('Y-m-d', strtotime($fecha_actual . ' +1 month'));
                break;
        }

        $cronograma[] = [
            'nro_cuota' => $i,
            'fecha_vencimiento' => $fecha_actual,
            'cuota' => round($cuota, 2),
            'interes' => round($interes, 2),
            'capital' => round($capital, 2),
            'saldo' => round($saldo, 2)
        ];
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Simulador de Cuotas</title>
</head>
<body>
    <h2>Simulador de Cuotas</h2>
    <form method="post">
        <label>Monto a Prestar:</label>
        <input type="number" name="monto" required><br>

        <label>Cantidad de Cuotas:</label>
        <input type="number" name="cuotas" required><br>

        <label>Frecuencia:</label>
        <select name="frecuencia">
            <option value="semanal">Semanal</option>
            <option value="quincenal">Quincenal</option>
            <option value="mensual">Mensual</option>
        </select><br>

        <button type="submit">Calcular</button>
    </form>
    
    <?php if (!empty($cronograma)): ?>
        <h3>Cronograma de Pagos</h3>
        <table border="1">
            <tr>
                <th>Nro Cuota</th>
                <th>Fecha de Vencimiento</th>
                <th>Cuota</th>
                <th>Interés</th>
                <th>Capital</th>
                <th>Saldo</th>
            </tr>
            <?php foreach ($cronograma as $pago): ?>
                <tr>
                    <td><?php echo $pago['nro_cuota']; ?></td>
                    <td><?php echo $pago['fecha_vencimiento']; ?></td>
                    <td><?php echo $pago['cuota']; ?></td>
                    <td><?php echo $pago['interes']; ?></td>
                    <td><?php echo $pago['capital']; ?></td>
                    <td><?php echo $pago['saldo']; ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>
</body>
</html>
