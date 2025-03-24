<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/financiera/lib/fpdf.php';
require_once __DIR__ . '/numero_a_letras.php';

class DocumentoPDF extends FPDF
{
    public function convertirTexto($texto)
    {
        return iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $texto);
    }

    function Header()
    {
        // Sin encabezado específico
    }



    function mesEnEspanol($numeroMes)
    {
        $meses = [
            1 => 'Enero',
            2 => 'Febrero',
            3 => 'Marzo',
            4 => 'Abril',
            5 => 'Mayo',
            6 => 'Junio',
            7 => 'Julio',
            8 => 'Agosto',
            9 => 'Septiembre',
            10 => 'Octubre',
            11 => 'Noviembre',
            12 => 'Diciembre'
        ];
        return $meses[$numeroMes];
    }

    function formatoFecha($fecha)
    {
        $dia = date('d', strtotime($fecha));
        $mes = $this->mesEnEspanol(date('n', strtotime($fecha)));
        $anio = date('Y', strtotime($fecha));
        return "$dia días de $mes del " . number_format($anio / 1000, 3, '.', '');
    }

    function numeroAPalabras($numero)
    {

        return NumeroALetras::convertir($numero, 'PESOS', 'CENTAVOS');
    }
}


// Parámetros GET
$tipo = $_GET['tipo'] ?? 'pagare';
$cliente = $_GET['cliente'] ?? 'Cliente no especificado';
$dni = $_GET['dni'] ?? '00000000';
$domicilio = $_GET['domicilio'] ?? 'Domicilio no especificado';
$telefono = $_GET['telefono'] ?? '0000000000';
$monto = $_GET['monto'] ?? '10000';
$cuotas = $_GET['cuotas'] ?? 'Cuotas no especificadas';
$fecha = $_GET['fecha'] ?? date('Y-m-d');
$monto_total = $monto + ($monto * 0.25 * $cuotas) + 11000;
$monto_cuota = $monto_total / $cuotas;


$pdf = new DocumentoPDF();
$pdf->AddPage();
$pdf->SetFont('Times', '', 11);
$pdf->SetMargins(30, 30, 20);
$lineHeight = 11 * 0.6;

if ($tipo == 'pagare') {
    // Encabezado con formato original
    $pdf->SetXY(-50, 30);
    $pdf->Cell(0, $lineHeight, $pdf->convertirTexto('Por $' . number_format($monto_total, 2, ',', '.') . '-'), 0, 1, 'L');

    // Fecha formateada
    $pdf->SetXY(40, 40);
    $fechaFormateada = $pdf->convertirTexto("       En la Ciudad de San Juan, Provincia del mismo nombre, a los " . $pdf->formatoFecha($fecha) . ".-");
    $pdf->MultiCell(0, $lineHeight, $fechaFormateada, 0, 'J');

    // Cuerpo del pagaré
    $pdf->SetXY(40, 55);
    $texto = "      A la vista, pagaré/mos sin protesto (art. 50 D. ley 5965/63) al Sr.WASHINGTON HORACIO RODRIGUEZ" . " o a su orden, la cantidad de " . $pdf->convertirTexto($pdf->numeroAPalabras($monto_total)) . " por igual valor recibido en servicios, a mi entera satisfacción, pagadero en calle Aberastain 510 (S), Planta Baja, Ciudad, San Juan. -\n" . "        Dejo/amos expresamente aclarados en mi carácter de librador/es que, de conformidad a lo dispuesto por el art. 36 del Decreto Ley 5.965/63, este pagaré puede presentarse para el pago dentro del plazo de cinco años desde la fecha de libramiento.\n" . "       A partir de las 0 hs. del primer día del vencimiento, en caso de que el monto consignado en el presente pagaré no se abone en el plazo estipulado, el deudor abonará en concepto de interés compensatorio conforme al interés tasa activa del Banco de la Nación Argentina, y además un interés punitorio del 0,15% diario sobre el capital, por cada día de atraso y hasta el pago efectivo, siendo su capitalización mensual , incurriendo en mora de pleno derecho sin necesidad de interpelación judicial o extrajudicial alguna, con sus oscilaciones a través del tiempo y hasta tanto al portador, se le pague íntegramente lo adeudado en concepto de capital, interés, gastos judiciales y honorarios legales que pudieran corresponder.- Todo período comenzado, pagará íntegro los intereses y no fraccionados.- Se deja expresa constancia que, la prórroga o plazos que el acreedor conceda, como los pagos que perciba a cuenta en cualquier forma o condición, no importarán novaciones, entendiéndose que la deuda subsistirá hasta la completa cancelación de la deuda.- La obligación de pago asumida en el presente pagaré, deberá mantener el equilibrio de las prestaciones de acuerdo con la normativa vigente o por regir en el futuro, es decir, el/los librador/es deberá/n abonar la cantidad de pesos necesarios para mantener la paridad vigente a la época del presente.-
";
    $pdf->MultiCell(0, $lineHeight, $pdf->convertirTexto($texto), 0, 'J');



    // Resto del contenido del pagaré



    // Firmas

    $pdf->SetY(205);

    // Configuración para la primera celda (etiqueta)
    $pdf->SetFont('Times', 'IU'); // I = Italic, U = Underlined
    $pdf->Cell(18, 8, $pdf->convertirTexto("SEÑOR/A:"), 0, 0);
    // Configuración para la segunda celda (contenido)
    $pdf->SetFont('Times', ''); // Sin formato
    $pdf->Cell(0, 8, $pdf->convertirTexto(" $cliente" . ".-"), 0, 1);

    // Repetir para cada línea
    $pdf->SetFont('Times', 'IU');
    $pdf->Cell(12, 8, $pdf->convertirTexto("D.N.I.:"), 0, 0);
    $pdf->SetFont('Times', '');
    $pdf->Cell(0, 8, $pdf->convertirTexto(" $dni" . ".-"), 0, 1);

    $pdf->SetFont('Times', 'IU');
    $pdf->Cell(22, 8, $pdf->convertirTexto("DOMICILIO:"), 0, 0);
    $pdf->SetFont('Times', '');
    $pdf->Cell(0, 8, $pdf->convertirTexto(" $domicilio" . ".-"), 0, 1);

    $pdf->SetFont('Times', 'IU');
    $pdf->Cell(21, 8, $pdf->convertirTexto("TELÉFONO:"), 0, 0);
    $pdf->SetFont('Times', '');
    $pdf->Cell(0, 8, $pdf->convertirTexto(" $telefono" . ".-"), 0, 1);
} elseif ($tipo == 'contrato') {
    // Encabezado del contrato
    $pdf->SetFont('Times', 'UI', 12);
    $pdf->Cell(0, 10, $pdf->convertirTexto('CONTRATO DE MUTUO'), 0, 1, 'C');
    $pdf->Ln(10);

    // Cuerpo del contrato
    $pdf->SetFont('Times', '', 12);
    $texto = "      Entre el Señor WASHINGTON HORACIO RODRIGUEZ D.N.I. N° 20.130.181, con domicilio en calle Aberastain 510 (S), Planta Baja, Capital, Provincia de San Juan, en adelante denominado \"EL MUTUANTE\", y por la otra parte lo hace la/el señor/a " . $cliente . " D.N.I. N° " . $dni . ", con domicilio en: " . $domicilio . ".- en adelante denominado \"EL MUTUARIO\", se celebra el presente contrato de mutuo o préstamo de dinero de acuerdo a las siguientes cláusulas:\n";
    $pdf->MultiCell(0, 8, $pdf->convertirTexto($texto), 0, 'J');



    // Cláusulas
    $clausulas = [
        "PRIMERA. MONTO" => "      El Mutuante da en préstamo al Mutuario la suma de $" . number_format($monto, 2, ',', '.') . " (" . $pdf->numeroAPalabras($monto) . "), dinero que es entregado en sus propias manos, sirviendo el presente de suficiente recibo y carta de pago. – Dicho monto, será devuelto en $cuotas cuota/s con vencimiento el (fechas de pago) por la suma de $" . number_format($monto_cuota, 2, ',', '.'). ". El monto consignado up-supra, será abonado en el domicilio del mutuante, sito en calle Aberastain 510 (S), Planta Baja, Capital, Provincia de San Juan y/o en el lugar que a posteriori se denunciare. -\n     Los montos estipulados reconocen el pacto de interés de financiación y cumplido que fuera el pago, ambas partes no tienen nada más que reclamar por ningún concepto. -",

        "SEGUNDA. IMPUTACIÓN" => "      El Mutuante imputará los pagos del Mutuario, primero a cancelar los gastos originados en la mora, luego a los intereses punitorios y compensatorios del monto en mora, y por último a la cancelación de la cuota atrasada.",
        "TERCERA. GARANTÍA" => "        En garantía de la restitución del préstamo, el Mutuario libra a favor del Mutuante un pagaré por PESOS (total a devolver) el cual será restituido al cancelarse todas las cuotas adeudadas. En caso de ser necesario accionar judicialmente, el Mutuante se obliga a accionar por el pagaré o por el presente contrato, pero no por ambos. -",
        "CUARTA. JURISDICCIÓN" => "     Los contratantes se someten para cualquier divergencia que pudiera surgir de la interpretación y aplicación del presente contrato, a la Jurisdicción de los Tribunales Ordinarios de la Provincia de San Juan, renunciando a cualquier otro fuero, o jurisdicción que pudiera corresponderles; en razón de la materia. -",
        "QUINTA. DOMICILIO ESPECIAL" => "     Para todos los efectos legales los firmantes constituyen los siguientes domicilios especiales en los designados up-supra, lugar donde se tendrán por válidas todas las notificaciones y diligencias que se practiquen, aunque los interesados no vivan o no se encuentren en ellos. -",
        "SEXTA. SELLOS" => "         El impuesto de sello correspondiente al presente contrato es abonado por el mutuante. -",

    ];

    foreach ($clausulas as $titulo => $contenido) {
        $pdf->SetFont('Times', 'UI');
        $pdf->Cell(0, 8, $pdf->convertirTexto($titulo . ":"), 0, 1);
        $pdf->SetFont('Times', '');
        $pdf->MultiCell(0, 8, $pdf->convertirTexto($contenido), 0, 'J');
        $pdf->Ln(4);
    }


    $fechaContrato = $pdf->convertirTexto("       Se firman dos ejemplares de un mismo tenor y a un sólo efecto, en la Ciudad de San Juan, a los ")
        . date('d', strtotime($fecha))
        . $pdf->convertirTexto(" días del mes de ")
        . $pdf->mesEnEspanol(date('n', strtotime($fecha)))
        . $pdf->convertirTexto(" del ")
        . NumeroALetras::convertir(date('Y', strtotime($fecha))) . ".-";

    $pdf->MultiCell(0, 8, $fechaContrato, 0, 'J');
}

$pdf->Output();
