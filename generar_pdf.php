<?php
require_once('vendor/tecnickcom/tcpdf/tcpdf.php');
date_default_timezone_set('America/Mexico_City');

if (!isset($_POST['sucursal']) || !isset($_POST['datos'])) {
    die('Error: Datos incompletos para generar el PDF');
}

$sucursal = $_POST['sucursal'];
$datos = json_decode($_POST['datos'], true);

if (!$datos || count($datos) === 0) {
    die('Error: No hay datos de empleados para procesar');
}

$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

$pdf->SetCreator('EDEN');
$pdf->SetAuthor('Sistema de Nómina');
$pdf->SetTitle('Nómina - ' . $sucursal);
$pdf->SetSubject('Reporte de Nómina');

$pdf->SetMargins(15, 15, 15);
$pdf->SetHeaderMargin(5);
$pdf->SetFooterMargin(10);

$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);

$pdf->AddPage();

// Paleta rojo/blanco
$colorRojo     = array(200, 0, 0);
$colorRojoClaro= array(255, 200, 200);
$colorBlanco   = array(255, 255, 255);

// ==================== ENCABEZADO ====================
$pdf->SetFillColor($colorRojo[0], $colorRojo[1], $colorRojo[2]);
$pdf->SetTextColor(255, 255, 255);
$pdf->SetFont('helvetica', 'B', 20);
$pdf->Cell(0, 15, 'EDEN', 0, 1, 'C', 1);

$pdf->Ln(3);

// Título del reporte
$pdf->SetFillColor($colorRojoClaro[0], $colorRojoClaro[1], $colorRojoClaro[2]);
$pdf->SetTextColor($colorRojo[0], $colorRojo[1], $colorRojo[2]);
$pdf->SetFont('helvetica', 'B', 16);
$pdf->Cell(0, 10, 'NÓMINA ', 0, 1, 'C', 1);

$pdf->Ln(2);

// Información Sucursal
$pdf->SetFillColor($colorBlanco[0], $colorBlanco[1], $colorBlanco[2]);
$pdf->SetTextColor(0,0,0);
$pdf->SetFont('helvetica', 'B', 12);
$pdf->Cell(40, 8, 'Sucursal:', 0, 0, 'L', 1);
$pdf->SetFont('helvetica', '', 12);
$pdf->Cell(0, 8, $sucursal, 0, 1, 'L', 1);

$pdf->SetFont('helvetica', 'B', 12);
$pdf->Cell(40, 8, 'Fecha:', 0, 0, 'L', 1);
$pdf->SetFont('helvetica', '', 12);
$pdf->Cell(0, 8, date('d/m/Y'), 0, 1, 'L', 1);

$pdf->Ln(5);

// ==================== TABLA ====================

// Ajuste de anchos para incluir "Faltante"
// Total Width original: 40+18+12+16+16+16+16+18+28 = 180
// Nuevo Width: Ajustado para que encaje Faltante.
$w = array(35, 18, 10, 16, 16, 14, 16, 14, 18, 23); 
$totalWidth = array_sum($w);

$pageWidth = $pdf->getPageWidth();
$margins = $pdf->getMargins();
// Solo centrar si no excede el ancho disponible
$leftMargin = ($pageWidth - $totalWidth) / 2;
if ($leftMargin < $margins['left']) {
    $leftMargin = $margins['left'];
}
$pdf->SetX($leftMargin);

// Encabezados
$pdf->SetFillColor($colorRojo[0], $colorRojo[1], $colorRojo[2]);
$pdf->SetTextColor(255, 255, 255);
$pdf->SetFont('helvetica', 'B', 8);

$headers = array(
'Empleado',
'Sueldo',
'Días',
'Sanciones',
'Préstamo',
'Extra',
'Adelanto',
'Faltante', // <-- Nueva columna añadida
'Pago Tarjeta',
'Total Efectivo'
);

foreach ($headers as $i => $header) {
    // Si el texto es muy largo, puedes ajustar el font-size aquí para el header si es necesario,
    // pero TCPDF suele cortar si se pasa. Por eso ajusté un poco los tamaños de $w.
    $pdf->Cell($w[$i], 8, $header, 1, 0, 'C', 1);
}

$pdf->Ln();

$pdf->SetFont('helvetica', '', 8); // Reduje a tamaño 8 para asegurar que quepa el texto con las nuevas columnas
$pdf->SetTextColor(0, 0, 0);

$totalGeneral = 0;
$fill = false;

foreach ($datos as $empleado) {
    // Asignar los datos del empleado
    $sueldoBase = floatval($empleado['sueldo_base']);
    $dias = intval($empleado['dias']);
    $sanciones = intval($empleado['sanciones']);
    $prestamo = floatval($empleado['prestamo']);
    $extra = floatval($empleado['extra']);
    $adelanto = floatval($empleado['adelanto']);
    $faltante = floatval($empleado['faltante'] ?? 0); // <-- Lectura de la nueva propiedad
    $tarjeta = floatval($empleado['tarjeta'] ?? 0);
    $total = floatval($empleado['total']);

    $totalSanciones = $sanciones * 100;
    $totalGeneral += $total;

    if ($fill) {
        $pdf->SetFillColor($colorRojoClaro[0], $colorRojoClaro[1], $colorRojoClaro[2]);
    } else {
        $pdf->SetFillColor($colorBlanco[0], $colorBlanco[1], $colorBlanco[2]);
    }

    $pdf->SetX($leftMargin); // Asegurarse de empezar en el margen correcto

    $pdf->Cell($w[0], 7, substr($empleado['nombre'], 0, 25), 1, 0, 'L', 1);
    $pdf->Cell($w[1], 7, '$' . number_format($sueldoBase, 2), 1, 0, 'R', 1);
    $pdf->Cell($w[2], 7, $dias, 1, 0, 'C', 1);
    $pdf->Cell($w[3], 7, $sanciones . ' (-$' . number_format($totalSanciones, 0) . ')', 1, 0, 'C', 1);
    $pdf->Cell($w[4], 7, '$' . number_format($prestamo, 2), 1, 0, 'R', 1);
    $pdf->Cell($w[5], 7, '$' . number_format($extra, 2), 1, 0, 'R', 1);
    $pdf->Cell($w[6], 7, '$' . number_format($adelanto, 2), 1, 0, 'R', 1);
    $pdf->Cell($w[7], 7, '$' . number_format($faltante, 2), 1, 0, 'R', 1); // <-- Imprimir Faltante
    $pdf->Cell($w[8], 7, '$' . number_format($tarjeta, 2), 1, 0, 'R', 1);

    // Total
    $pdf->SetFont('helvetica', 'B', 8);
    $pdf->SetFillColor($colorRojoClaro[0], $colorRojoClaro[1], $colorRojoClaro[2]);
    $pdf->Cell($w[9], 7, '$' . number_format($total, 2), 1, 0, 'R', 1);

    $pdf->SetFont('helvetica', '', 8);

    $pdf->Ln();
    $fill = !$fill;
}

// Total General
$pdf->SetX($leftMargin); // Alinear con la tabla
$pdf->SetFont('helvetica', 'B', 9);
$pdf->SetFillColor($colorRojo[0], $colorRojo[1], $colorRojo[2]);
$pdf->SetTextColor(255, 255, 255);

// Sumar los anchos de las celdas previas a la columna "Total Efectivo" (índices del 0 al 8)
$anchoPrevio = array_sum(array_slice($w, 0, 9));

$pdf->Cell($anchoPrevio, 8, 'TOTAL GENERAL A PAGAR:', 1, 0, 'R', 1);
$pdf->Cell($w[9], 8, '$' . number_format($totalGeneral, 2), 1, 0, 'R', 1);
$pdf->Ln();

// ==================== PIE ====================
$pdf->Ln(10);
$pdf->SetTextColor(100, 100, 100);
$pdf->SetFont('helvetica', 'I', 8);
$pdf->Cell(0, 5, 'Carniceria Eden ' . date('d/m/Y H:i:s'), 0, 1, 'C');

$pdf->Ln(5);

$pdf->SetTextColor(0, 0, 0);
$pdf->SetFont('helvetica', '', 9);
$pdf->Cell(90, 20, '', 0, 0, 'C');
$pdf->Cell(90, 20, '', 0, 1, 'C');

$pdf->Cell(90, 1, '', 'T', 0, 'C');
$pdf->Cell(90, 1, '', 'T', 1, 'C');

$pdf->SetFont('helvetica', 'B', 9);
$pdf->Cell(90, 5, 'Elaboró', 0, 0, 'C');
$pdf->Cell(90, 5, 'Autorizó', 0, 1, 'C');

$filename = 'Nomina_' . str_replace(' ', '_', $sucursal) . '_' . date('Y-m-d') . '.pdf';
$pdf->Output($filename, 'D');
exit;
?>