<?php
require_once '../../lib/fpdf/fpdf.php';

class DocumentoPDF extends FPDF
{
    function Header()
    {
        $this->SetFont('Arial', 'B', 14);
        $this->Cell(0, 10, 'Documento Financiero', 0, 1, 'C');
        $this->Ln(5);
    }

    function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, 'Pagina ' . $this->PageNo(), 0, 0, 'C');
    }
}

// Recibir parámetros vía GET
$tipo = isset($_GET['tipo']) ? $_GET['tipo'] : 'pagare';  // Valores: 'pagare' o 'contrato'
$cliente = isset($_GET['cliente']) ? $_GET['cliente'] : 'Juan Perez';
$dni = isset($_GET['dni']) ? $_GET['dni'] : '00000000';
$domicilio = isset($_GET['domicilio']) ? $_GET['domicilio'] : 'Domicilio no especificado';
$telefono = isset($_GET['telefono']) ? $_GET['telefono'] : '0000000000';
$monto = isset($_GET['monto']) ? $_GET['monto'] : '10000';
$cuotas = isset($_GET['cuotas']) ? $_GET['cuotas'] : '12';

$pdf = new DocumentoPDF();
$pdf->AddPage();
$pdf->SetFont('Arial', '', 12);

if ($tipo == 'pagare') {
    $texto = "PAGARE SIN PROTESTO\n\n";
    $texto .= "En la Ciudad de San Juan, Provincia del mismo nombre, a la fecha de emisión.\n\n";
    $texto .= "A la vista, pagaré sin protesto (art. 50 D. ley 5965/63) al acreedor o a su orden, la cantidad de PESOS " . $monto . " por igual valor recibido en servicios, a mi entera satisfacción, pagadero en el domicilio pactado.\n\n";
    $texto .= "Dejo expresamente aclarado que, conforme al art. 36 del Decreto Ley 5.965/63, este pagaré puede presentarse para el pago dentro del plazo de cinco años desde la fecha de libramiento.\n\n";
    $texto .= "En caso de impago, se aplicará un interés compensatorio conforme a la tasa activa del Banco de la Nación Argentina y un interés punitorio del 0,15% diario sobre el capital adeudado.\n\n";
    $texto .= "Nombre: " . $cliente . "\nDNI: " . $dni . "\nDomicilio: " . $domicilio . "\nTeléfono: " . $telefono;
    $pdf->MultiCell(0, 10, $texto, 0, 'L');
} elseif ($tipo == 'contrato') {
    $texto = "CONTRATO DE MUTUO\n\n";
    $texto .= "Entre el acreedor y " . $cliente . ", se celebra el presente contrato de mutuo o préstamo de dinero.\n\n";
    $texto .= "PRIMERA. MONTO: El Mutuante da en préstamo al Mutuario la suma de PESOS " . $monto . ", dinero que es entregado en sus propias manos.\n\n";
    $texto .= "SEGUNDA. DEVOLUCIÓN: El monto será devuelto en " . $cuotas . " cuotas, con vencimientos acordados.\n\n";
    $texto .= "TERCERA. IMPUTACIÓN: El acreedor imputará los pagos primero a gastos de mora, luego intereses y finalmente a la cuota atrasada.\n\n";
    $texto .= "QUINTA. GARANTÍA: En garantía de la restitución del préstamo, el Mutuario libra un pagaré equivalente al monto adeudado.\n\n";
    $texto .= "SEXTA. JURISDICCIÓN: Se someten a la Jurisdicción de los Tribunales Ordinarios de San Juan.\n\n";
    $pdf->MultiCell(0, 10, $texto, 0, 'L');
} else {
    $pdf->MultiCell(0, 10, "Tipo de documento no especificado.", 0, 'L');
}

$pdf->Ln(20);
$pdf->Cell(0, 10, "Firma del Cliente: __________________", 0, 1, 'L');
$pdf->Output();
