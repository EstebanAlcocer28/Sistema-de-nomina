<?php
require_once 'vendor/tecnickcom/tcpdf/tcpdf.php';
date_default_timezone_set('America/Mexico_City');

// ── Validación inicial de datos ──────────────────────────────
if (!isset($_POST['sucursal'], $_POST['datos'])) {
    die('Error: Datos incompletos para generar el PDF');
}

$sucursal = trim($_POST['sucursal']);
$json     = $_POST['datos'];
$datos    = json_decode($json, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    die('Error: Formato de datos no válido');
}

if (!is_array($datos) || count($datos) === 0) {
    die('Error: No hay datos de empleados para procesar');
}

// ── Función para sanitizar textos impresos ────────────────────
function sanitizar($str, $maxLen = 50) {
    $str = trim($str);
    $str = strip_tags($str);            // eliminar HTML
    $str = htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
    return mb_substr($str, 0, $maxLen);
}

// Sanitizamos el nombre de la sucursal (para mostrar y para el archivo)
$sucursal = sanitizar($sucursal, 30);

// ── Configuración del PDF ─────────────────────────────────────
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

// Paleta de colores
$colorRojo      = [200, 0, 0];
$colorRojoClaro = [255, 200, 200];
$colorBlanco    = [255, 255, 255];

// ── ENCABEZADO ────────────────────────────────────────────────
$pdf->SetFillColor($colorRojo[0], $colorRojo[1], $colorRojo[2]);
$pdf->SetTextColor(255, 255, 255);
$pdf->SetFont('helvetica', 'B', 20);
$pdf->Cell(0, 15, 'EDEN', 0, 1, 'C', true);

$pdf->Ln(3);
$pdf->SetFillColor($colorRojoClaro[0], $colorRojoClaro[1], $colorRojoClaro[2]);
$pdf->SetTextColor($colorRojo[0], $colorRojo[1], $colorRojo[2]);
$pdf->SetFont('helvetica', 'B', 16);
$pdf->Cell(0, 10, 'NÓMINA', 0, 1, 'C', true);

$pdf->Ln(2);
$pdf->SetFillColor($colorBlanco[0], $colorBlanco[1], $colorBlanco[2]);
$pdf->SetTextColor(0, 0, 0);
$pdf->SetFont('helvetica', 'B', 12);
$pdf->Cell(40, 8, 'Sucursal:', 0, 0, 'L', true);
$pdf->SetFont('helvetica', '', 12);
$pdf->Cell(0, 8, $sucursal, 0, 1, 'L', true);

$pdf->SetFont('helvetica', 'B', 12);
$pdf->Cell(40, 8, 'Fecha:', 0, 0, 'L', true);
$pdf->SetFont('helvetica', '', 12);
$pdf->Cell(0, 8, date('d/m/Y'), 0, 1, 'L', true);
$pdf->Ln(5);

// ── TABLA (con nueva columna de índice) ───────────────────────
// Nuevo arreglo de anchos: #, Empleado, Sueldo, Días, Sanciones, Préstamo, Extra, Adelanto, Faltante, Pago Tarjeta, Total Efectivo
$anchos = [8, 30, 16, 8, 16, 16, 13, 16, 13, 18, 22];
$totalWidth = array_sum($anchos);
$pageWidth  = $pdf->getPageWidth();
$margins    = $pdf->getMargins();
$leftMargin = max(($pageWidth - $totalWidth) / 2, $margins['left']);
$pdf->SetX($leftMargin);

// Cabecera de tabla (ahora con "#")
$pdf->SetFillColor($colorRojo[0], $colorRojo[1], $colorRojo[2]);
$pdf->SetTextColor(255, 255, 255);
$pdf->SetFont('helvetica', 'B', 8);
$headers = ['#', 'Empleado', 'Sueldo', 'Días', 'Sanciones', 'Préstamo', 'Extra', 'Adelanto', 'Faltante', 'Pago Tarjeta', 'Total Efectivo'];
foreach ($headers as $i => $h) {
    $pdf->Cell($anchos[$i], 8, $h, 1, 0, 'C', true);
}
$pdf->Ln();

$pdf->SetFont('helvetica', '', 8);
$pdf->SetTextColor(0, 0, 0);

$totalGeneral = 0;
$fill = false;
$contador = 0;  // Contador de empleados procesados

// ── Procesamiento de cada empleado ────────────────────────────
foreach ($datos as $empleado) {
    // Validar existencia de claves mínimas
    if (!isset($empleado['nombre'], $empleado['sueldo_base'])) {
        continue; // omitir registros incompletos
    }

    $contador++; // Aumentamos el índice

    // 1. Sanitización del nombre
    $nombre = sanitizar($empleado['nombre'], 25);

    // 2. Validación y conversión de valores numéricos
    $sueldoBase = max(0, floatval($empleado['sueldo_base']));
    $dias = max(0, intval($empleado['dias'] ?? 7));// rango 0-7
    $sanciones  = max(0, intval($empleado['sanciones'] ?? 0));
    $prestamo   = max(0, floatval($empleado['prestamo'] ?? 0));
    $extra      = max(0, floatval($empleado['extra'] ?? 0));
    $adelanto   = max(0, floatval($empleado['adelanto'] ?? 0));
    $faltante   = max(0, floatval($empleado['faltante'] ?? 0));
    $tarjeta    = max(0, floatval($empleado['tarjeta'] ?? 0));

    // 3. RECÁLCULO DEL TOTAL en el servidor
    $pagoDiario     = $sueldoBase / 7;
    $pagoAsistencia = $pagoDiario * $dias;
    $totalSanciones = $sanciones * 100;
    $total = ($pagoAsistencia - $totalSanciones - $prestamo - $adelanto - $tarjeta - $faltante) + $extra;
    $total = round($total, 2);

    $totalGeneral += $total;

    // Fila con alternancia de color
    if ($fill) {
        $pdf->SetFillColor($colorRojoClaro[0], $colorRojoClaro[1], $colorRojoClaro[2]);
    } else {
        $pdf->SetFillColor($colorBlanco[0], $colorBlanco[1], $colorBlanco[2]);
    }
    $pdf->SetX($leftMargin);

    // Índice
    $pdf->Cell($anchos[0], 7, $contador, 1, 0, 'C', true);
    // Resto de columnas
    $pdf->Cell($anchos[1], 7, $nombre, 1, 0, 'L', true);
    $pdf->Cell($anchos[2], 7, '$' . number_format($sueldoBase, 2), 1, 0, 'R', true);
    $pdf->Cell($anchos[3], 7, $dias, 1, 0, 'C', true);
    $pdf->Cell($anchos[4], 7, $sanciones . ' (-$' . number_format($totalSanciones, 0) . ')', 1, 0, 'C', true);
    $pdf->Cell($anchos[5], 7, '$' . number_format($prestamo, 2), 1, 0, 'R', true);
    $pdf->Cell($anchos[6], 7, '$' . number_format($extra, 2), 1, 0, 'R', true);
    $pdf->Cell($anchos[7], 7, '$' . number_format($adelanto, 2), 1, 0, 'R', true);
    $pdf->Cell($anchos[8], 7, '$' . number_format($faltante, 2), 1, 0, 'R', true);
    $pdf->Cell($anchos[9], 7, '$' . number_format($tarjeta, 2), 1, 0, 'R', true);

    $pdf->SetFont('helvetica', 'B', 8);
    $pdf->SetFillColor($colorRojoClaro[0], $colorRojoClaro[1], $colorRojoClaro[2]);
    $pdf->Cell($anchos[10], 7, '$' . number_format($total, 2), 1, 0, 'R', true);
    $pdf->SetFont('helvetica', '', 8);

    $pdf->Ln();
    $fill = !$fill;
}

// ── TOTAL GENERAL ─────────────────────────────────────────────
$pdf->SetX($leftMargin);
$pdf->SetFont('helvetica', 'B', 9);
$pdf->SetFillColor($colorRojo[0], $colorRojo[1], $colorRojo[2]);
$pdf->SetTextColor(255, 255, 255);
// Ancho previo ahora son las primeras 10 columnas (0 a 9)
$anchoPrevio = array_sum(array_slice($anchos, 0, 10));
$pdf->Cell($anchoPrevio, 8, 'TOTAL GENERAL A PAGAR:', 1, 0, 'R', true);
$pdf->Cell($anchos[10], 8, '$' . number_format($totalGeneral, 2), 1, 0, 'R', true);
$pdf->Ln();

// ── CANTIDAD DE EMPLEADOS ─────────────────────────────────────
$pdf->Ln(2);
$pdf->SetTextColor(0, 0, 0);
$pdf->SetFont('helvetica', 'B', 10);
$pdf->SetX($leftMargin);
$pdf->Cell($totalWidth, 7, 'Total de empleados procesados: ' . $contador, 0, 1, 'R');

// ── PIE DE PÁGINA ────────────────────────────────────────────
$pdf->Ln(8);
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

// ── Nombre de archivo seguro ──────────────────────────────────
$sucursalSafe = preg_replace('/[^a-zA-Z0-9áéíóúÁÉÍÓÚñÑ\s\-_]/u', '', $sucursal);
$filename = 'Nomina_' . str_replace(' ', '_', $sucursalSafe) . '_' . date('Y-m-d') . '.pdf';

$pdf->Output($filename, 'D');
exit;