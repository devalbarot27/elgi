<?php
ob_start();
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
include('session_expiry_page1.php');
include('include/pdo_dpconn.php');
include('include/pdo_obconn.php');

$invref = trim($_GET['invref'] ?? '');
$invno = trim($_GET['invno'] ?? '');
$cuno = trim($_GET['cuno'] ?? '');

if ($invref === '' || $invno === '' || $cuno === '' || !($link instanceof PDO)) {
    while (ob_get_level() > 0) {
        ob_end_clean();
    }
    header('Content-Type: text/plain; charset=UTF-8');
    echo 'Despatch invoice could not be created.';
    exit;
}

$rs = $link->prepare("SELECT invref, invno, invdate, ordno, posno, item_desc, uom, qty, price, cmp, edamt, taxamt, gstamt, cuname FROM despatch WHERE invref = :invref AND invno = :invno AND cuno = :cuno ORDER BY ordno, posno");
$rs->bindParam(':invref', $invref, PDO::PARAM_STR);
$rs->bindParam(':invno', $invno, PDO::PARAM_STR);
$rs->bindParam(':cuno', $cuno, PDO::PARAM_STR);
$rs->execute();
$items = $rs->fetchAll(PDO::FETCH_ASSOC);

if (!$items) {
    while (ob_get_level() > 0) {
        ob_end_clean();
    }
    header('Content-Type: text/plain; charset=UTF-8');
    echo 'No despatch details found for this invoice.';
    exit;
}

$first = $items[0];
$orderNumbers = array();
foreach ($items as $item) {
    $orderNumbers[$item['ordno']] = $item['ordno'];
}
$orderList = implode(', ', $orderNumbers);

$dateRS = $link->prepare("SELECT date_cmp('2017-06-30', :invdate) AS diff_inv");
$dateRS->bindParam(':invdate', $first['invdate'], PDO::PARAM_STR);
$dateRS->execute();
$dateRow = $dateRS->fetch(PDO::FETCH_ASSOC);
$diff = $dateRow['diff_inv'] ?? -1;

$lr = $link->prepare("SELECT tname, lrno, lrdate, cases, bundles, boxes, carton_box, spl_cases, weight, w_unit, dly_code FROM lr_details WHERE trim(invref) = :invref AND invno = :invno");
$lr->bindParam(':invref', $invref, PDO::PARAM_STR);
$lr->bindParam(':invno', $invno, PDO::PARAM_STR);
$lr->execute();
$lrRow = $lr->fetch(PDO::FETCH_ASSOC);
if (!$lrRow) {
    $lrRow = array(
        'tname' => '',
        'lrno' => '',
        'lrdate' => '',
        'cases' => '',
        'bundles' => '',
        'boxes' => '',
        'carton_box' => '',
        'spl_cases' => '',
        'weight' => '',
        'w_unit' => '',
        'dly_code' => '',
    );
}

$custaddr = '';
if ($con instanceof PDO && $lrRow['dly_code'] !== '') {
    try {
        $addr = $con->prepare("SELECT custaddr FROM customer_address WHERE adr_code = :dly_code AND cuno = :cuno");
        $addr->bindParam(':dly_code', $lrRow['dly_code'], PDO::PARAM_STR);
        $addr->bindParam(':cuno', $cuno, PDO::PARAM_STR);
        $addr->execute();
        $addrRow = $addr->fetch(PDO::FETCH_ASSOC);
        $custaddr = $addrRow['custaddr'] ?? '';
    } catch (PDOException $e) {
        $custaddr = '';
    }
}

$fab = $link->prepare("SELECT tpl, tpl_desc, fabno FROM ln_desp_details WHERE trans_type = :invref AND inv_no = :invno AND company IN ('401','440','450') ORDER BY fabno");
$fab->bindParam(':invref', $invref, PDO::PARAM_STR);
$fab->bindParam(':invno', $invno, PDO::PARAM_STR);
$fab->execute();
$fabRows = $fab->fetchAll(PDO::FETCH_ASSOC);

$basic = 0;
foreach ($items as $item) {
    $basic += (float) $item['price'];
}

$invoiceNo = $first['cmp'] . '-' . $first['invref'] . '-' . trim($first['invno']);
$weightUnit = $lrRow['w_unit'] !== '' && $lrRow['w_unit'] !== null ? $lrRow['w_unit'] : $first['uom'];
$customerName = trim((string) ($first['cuname'] ?? ''));
$customerLine = $customerName !== '' ? $customerName . '  (' . $cuno . ')' : $cuno;

$pdf = new DespatchPdf();
$pdf->documentHeader('ELGI EQUIPMENTS LTD', 'DESPATCH DETAIL', $invoiceNo, invoice_date($first['invdate']));
$pdf->fieldGrid(array(
    array('Customer', $customerLine),
    array('Order Number(s)', $orderList),
    array('Transporter', invoice_text($lrRow['tname'])),
    array('LR Number', invoice_text($lrRow['lrno'])),
    array('LR Date', invoice_date($lrRow['lrdate'])),
    array('Weight', trim(invoice_text($lrRow['weight']) . ' ' . $weightUnit)),
    array('Delivery Code', invoice_text($lrRow['dly_code'])),
    array('Delivery Address', invoice_text($custaddr)),
));
$pdf->statRow(array(
    array('Cases', invoice_text($lrRow['cases'])),
    array('Boxes', invoice_text($lrRow['boxes'])),
    array('Bundles', invoice_text($lrRow['bundles'])),
    array('Cartoons', invoice_text($lrRow['carton_box'])),
    array('Special Cases', invoice_text($lrRow['spl_cases'])),
));

$itemRows = array();
foreach ($items as $item) {
    $itemRows[] = array(
        $item['ordno'] . '/' . $item['posno'],
        $item['item_desc'],
        $item['qty'],
        number_format((float) $item['price'], 2),
    );
}
$pdf->sectionTitle('Order Items');
$pdf->dataTable(
    array('AO No / Pos No', 'Item Description', 'Qty', 'Basic Value'),
    $itemRows,
    array(110, 261, 60, 100),
    array('L', 'L', 'R', 'R')
);

$totalRows = array(array('Total Basic Value', number_format($basic, 2)));
if ($diff >= 0) {
    $edamt = (float) $first['edamt'];
    $taxamt = (float) $first['taxamt'];
    $invoiceValue = $basic + $edamt + $taxamt;
    $totalRows[] = array('Total Excise Duty', number_format($edamt, 2));
    $totalRows[] = array('Total Sales Tax', number_format($taxamt, 2));
} else {
    $gstamt = (float) $first['gstamt'];
    $invoiceValue = $basic + $gstamt;
    $totalRows[] = array('Total GST', number_format($gstamt, 2));
}
$totalRows[] = array('Total Invoice Value', number_format($invoiceValue, 2));
$pdf->totals($totalRows);

if ($fabRows) {
    $fabTable = array();
    foreach ($fabRows as $fabRow) {
        $fabTable[] = array($fabRow['tpl'], $fabRow['tpl_desc'], $fabRow['fabno']);
    }
    $pdf->sectionTitle('FAB Number Details');
    $pdf->dataTable(
        array('TPL Code', 'TPL Description', 'FAB No'),
        $fabTable,
        array(130, 271, 130),
        array('L', 'L', 'L')
    );
}

$filename = 'Despatch-Invoice-' . preg_replace('/[^A-Za-z0-9_-]/', '', $invref) . '-' . preg_replace('/[^A-Za-z0-9_-]/', '', $invno) . '.pdf';
$binary = $pdf->render();
while (ob_get_level() > 0) {
    ob_end_clean();
}
if (PHP_SAPI !== 'cli') {
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Content-Length: ' . strlen($binary));
    header('Cache-Control: private, max-age=0, must-revalidate');
    header('Pragma: public');
}
echo $binary;
exit;

function invoice_text($value)
{
    $value = trim((string) $value);
    return $value === '' ? '-' : $value;
}

function invoice_date($value)
{
    $value = trim((string) $value);
    if ($value === '') {
        return '-';
    }
    $timestamp = strtotime($value);
    return $timestamp ? date('d M Y', $timestamp) : $value;
}

class DespatchPdf
{
    private $pageW = 595;
    private $pageH = 842;
    private $margin = 32;
    private $contentW = 531;
    private $top = 28;
    private $streams = array();
    private $stream = '';
    private $pageNo = 0;
    private $ink = array(0.09, 0.17, 0.23);
    private $teal = array(0.09, 0.42, 0.53);
    private $line = array(0.82, 0.88, 0.90);
    private $muted = array(0.35, 0.45, 0.52);
    private $paper = array(0.96, 0.98, 0.98);

    public function __construct()
    {
        $this->newPage();
    }

    public function documentHeader($company, $title, $invoiceNo, $invoiceDate)
    {
        $h = 86;
        $this->ensure($h);
        $y = $this->pageH - $this->top - $h;
        $this->fill($this->margin, $y, $this->contentW, $h, $this->ink);
        $this->fill($this->margin, $y, 6, $h, $this->teal);
        $this->drawText($this->margin + 20, $y + 54, $company, 16, true, array(1, 1, 1));
        $this->drawText($this->margin + 20, $y + 34, $title, 10, false, array(0.75, 0.86, 0.90));
        $this->drawText($this->margin + 20, $y + 16, 'Invoice date  ' . $invoiceDate, 9, false, array(0.75, 0.86, 0.90));
        $badge = 'INVOICE  ' . $invoiceNo;
        $badgeW = $this->textWidth($badge, 9, true) + 18;
        $badgeX = $this->margin + $this->contentW - $badgeW - 16;
        $this->fill($badgeX, $y + 32, $badgeW, 22, $this->teal);
        $this->drawText($badgeX + 9, $y + 39, $badge, 9, true, array(1, 1, 1));
        $this->top += $h + 14;
    }

    public function fieldGrid($fields)
    {
        $colW = ($this->contentW - 10) / 2;
        for ($i = 0; $i < count($fields); $i += 2) {
            $left = $fields[$i];
            $right = isset($fields[$i + 1]) ? $fields[$i + 1] : null;
            $leftLines = $this->wrap($left[1], $colW - 20, 9);
            $rightLines = $right ? $this->wrap($right[1], $colW - 20, 9) : array('');
            $lines = max(count($leftLines), count($rightLines), 1);
            $h = 22 + ($lines * 12);
            $this->ensure($h + 8);
            $y = $this->pageH - $this->top - $h;
            $this->fieldCard($this->margin, $y, $colW, $h, $left[0], $leftLines);
            if ($right) {
                $this->fieldCard($this->margin + $colW + 10, $y, $colW, $h, $right[0], $rightLines);
            }
            $this->top += $h + 8;
        }
    }

    public function statRow($stats)
    {
        $this->ensure(62);
        $gap = 8;
        $count = count($stats);
        $w = ($this->contentW - (($count - 1) * $gap)) / $count;
        $h = 48;
        $y = $this->pageH - $this->top - $h;
        foreach ($stats as $i => $stat) {
            $x = $this->margin + ($i * ($w + $gap));
            $this->fill($x, $y, $w, $h, $this->paper);
            $this->stroke($x, $y, $w, $h, $this->line);
            $this->fill($x, $y + $h - 3, $w, 3, $this->teal);
            $this->drawText($x + 8, $y + 28, strtoupper($stat[0]), 7, true, $this->muted);
            $this->drawText($x + 8, $y + 12, $stat[1], 11, true, $this->ink);
        }
        $this->top += $h + 6;
    }

    public function sectionTitle($text)
    {
        $this->top += 12;
        $this->ensure(22);
        $y = $this->pageH - $this->top - 4;
        $this->drawText($this->margin, $y, strtoupper($text), 10, true, $this->ink);
        $this->strokeLine($this->margin, $y - 6, $this->margin + $this->contentW, $y - 6, $this->teal, 1.4);
        $this->top += 16;
    }

    public function dataTable($headers, $rows, $widths, $aligns)
    {
        $this->tableRow($headers, $widths, $aligns, true, false);
        foreach ($rows as $index => $row) {
            $this->tableRow($row, $widths, $aligns, false, $index % 2 === 1);
        }
    }

    public function totals($rows)
    {
        $this->top += 10;
        $w = 250;
        $x = $this->margin + $this->contentW - $w;
        $last = count($rows) - 1;
        foreach ($rows as $index => $row) {
            $h = $index === $last ? 28 : 22;
            $this->ensure($h);
            $y = $this->pageH - $this->top - $h;
            $fill = $index === $last ? $this->ink : $this->paper;
            $color = $index === $last ? array(1, 1, 1) : $this->ink;
            $this->fill($x, $y, $w, $h, $fill);
            if ($index !== $last) {
                $this->stroke($x, $y, $w, $h, $this->line);
            }
            $this->drawText($x + 12, $y + ($h / 2) - 3, $row[0], 9, true, $color);
            $amountW = $this->textWidth($row[1], 9, true);
            $this->drawText($x + $w - 12 - $amountW, $y + ($h / 2) - 3, $row[1], 9, true, $color);
            $this->top += $h;
        }
    }

    public function render()
    {
        $this->streams[] = $this->stream;
        $total = count($this->streams);
        foreach ($this->streams as $index => $stream) {
            $this->stream = $stream;
            $this->drawText($this->margin, 22, 'ELGI EQUIPMENTS LTD', 8, false, $this->muted);
            $pageLabel = 'Page ' . ($index + 1) . ' of ' . $total;
            $pageW = $this->textWidth($pageLabel, 8, false);
            $this->drawText($this->margin + $this->contentW - $pageW, 22, $pageLabel, 8, false, $this->muted);
            $this->strokeLine($this->margin, 34, $this->margin + $this->contentW, 34, $this->line, 0.6);
            $this->streams[$index] = $this->stream;
        }

        $objects = array();
        $objects[3] = "<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>";
        $objects[4] = "<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold >>";
        $next = 5;
        $kids = array();
        foreach ($this->streams as $stream) {
            $contentId = $next++;
            $pageId = $next++;
            $objects[$contentId] = "<< /Length " . strlen($stream) . " >>\nstream\n" . $stream . "endstream";
            $objects[$pageId] = "<< /Type /Page /Parent 2 0 R /MediaBox [0 0 " . $this->pageW . " " . $this->pageH . "] /Contents " . $contentId . " 0 R /Resources << /Font << /F1 3 0 R /F2 4 0 R >> >> >>";
            $kids[] = $pageId . " 0 R";
        }
        $objects[2] = "<< /Type /Pages /Kids [" . implode(' ', $kids) . "] /Count " . count($kids) . " >>";
        $objects[1] = "<< /Type /Catalog /Pages 2 0 R >>";
        ksort($objects);

        $pdf = "%PDF-1.4\n";
        $offsets = array();
        foreach ($objects as $id => $body) {
            $offsets[$id] = strlen($pdf);
            $pdf .= $id . " 0 obj\n" . $body . "\nendobj\n";
        }
        $xref = strlen($pdf);
        $size = count($objects) + 1;
        $pdf .= "xref\n0 $size\n";
        $pdf .= "0000000000 65535 f \n";
        for ($i = 1; $i < $size; $i++) {
            $pdf .= sprintf("%010d 00000 n \n", $offsets[$i]);
        }
        $pdf .= "trailer\n<< /Size $size /Root 1 0 R >>\nstartxref\n$xref\n%%EOF";
        return $pdf;
    }

    private function fieldCard($x, $y, $w, $h, $label, $lines)
    {
        $this->fill($x, $y, $w, $h, array(1, 1, 1));
        $this->stroke($x, $y, $w, $h, $this->line);
        $this->fill($x, $y, 3, $h, $this->teal);
        $this->drawText($x + 12, $y + $h - 14, strtoupper($label), 7, true, $this->muted);
        $lineY = $y + $h - 28;
        foreach ($lines as $line) {
            $this->drawText($x + 12, $lineY, $line, 9, false, $this->ink);
            $lineY -= 12;
        }
    }

    private function tableRow($cells, $widths, $aligns, $header, $stripe)
    {
        $wrapped = array();
        $lineCount = 1;
        foreach ($cells as $i => $cell) {
            $lines = $this->wrap((string) $cell, $widths[$i] - 16, $header ? 8 : 9);
            if (!$lines) {
                $lines = array('');
            }
            $wrapped[] = $lines;
            $lineCount = max($lineCount, count($lines));
        }
        $h = $header ? 24 : (12 + ($lineCount * 12));
        $this->ensure($h);
        $y = $this->pageH - $this->top - $h;
        $x = $this->margin;
        if ($header) {
            $this->fill($x, $y, $this->contentW, $h, $this->ink);
        } elseif ($stripe) {
            $this->fill($x, $y, $this->contentW, $h, $this->paper);
        }
        $this->stroke($x, $y, $this->contentW, $h, $header ? $this->ink : $this->line);
        foreach ($widths as $i => $width) {
            $color = $header ? array(1, 1, 1) : $this->ink;
            $size = $header ? 8 : 9;
            $bold = $header || $aligns[$i] === 'R';
            $lineY = $y + $h - 15;
            foreach ($wrapped[$i] as $line) {
                $textX = $x + 8;
                if ($aligns[$i] === 'R') {
                    $textX = $x + $width - 8 - $this->textWidth($line, $size, $bold);
                }
                $this->drawText($textX, $lineY, $line, $size, $bold, $color);
                $lineY -= 12;
            }
            $x += $width;
        }
        $this->top += $h;
    }

    private function newPage()
    {
        if ($this->stream !== '') {
            $this->streams[] = $this->stream;
        }
        $this->stream = '';
        $this->pageNo++;
        $this->top = $this->pageNo === 1 ? 28 : 48;
        if ($this->pageNo > 1) {
            $this->drawText($this->margin, $this->pageH - 34, 'DESPATCH DETAIL', 9, true, $this->ink);
            $this->strokeLine($this->margin, $this->pageH - 42, $this->margin + $this->contentW, $this->pageH - 42, $this->teal, 1.2);
        }
    }

    private function ensure($h)
    {
        if ($this->top + $h > $this->pageH - 48) {
            $this->newPage();
        }
    }

    private function fill($x, $y, $w, $h, $color)
    {
        $this->stream .= sprintf("%.3f %.3f %.3f rg\n%.2f %.2f %.2f %.2f re f\n", $color[0], $color[1], $color[2], $x, $y, $w, $h);
    }

    private function stroke($x, $y, $w, $h, $color)
    {
        $this->stream .= sprintf("%.3f %.3f %.3f RG\n0.7 w\n%.2f %.2f %.2f %.2f re S\n", $color[0], $color[1], $color[2], $x, $y, $w, $h);
    }

    private function strokeLine($x1, $y1, $x2, $y2, $color, $width)
    {
        $this->stream .= sprintf("%.3f %.3f %.3f RG\n%.2f w\n%.2f %.2f m %.2f %.2f l S\n", $color[0], $color[1], $color[2], $width, $x1, $y1, $x2, $y2);
    }

    private function drawText($x, $y, $text, $size, $bold, $color)
    {
        $font = $bold ? 'F2' : 'F1';
        $this->stream .= sprintf(
            "BT %.3f %.3f %.3f rg /%s %.2f Tf %.2f %.2f Td (%s) Tj ET\n",
            $color[0],
            $color[1],
            $color[2],
            $font,
            $size,
            $x,
            $y,
            $this->escape($text)
        );
    }

    private function textWidth($text, $size, $bold)
    {
        return strlen((string) $text) * $size * ($bold ? 0.52 : 0.46);
    }

    private function wrap($text, $width, $size)
    {
        $text = str_replace(array("\r\n", "\r"), "\n", (string) $text);
        $maxChars = max(8, (int) floor($width / ($size * 0.48)));
        $lines = array();
        foreach (explode("\n", $text) as $paragraph) {
            $wrapped = $paragraph === '' ? array('') : explode("\n", wordwrap($paragraph, $maxChars, "\n", true));
            foreach ($wrapped as $line) {
                $lines[] = $line;
            }
        }
        return $lines;
    }

    private function escape($text)
    {
        $text = str_replace(array('\\', '(', ')'), array('\\\\', '\\(', '\\)'), (string) $text);
        return preg_replace('/[^\x20-\x7E]/', '?', $text);
    }
}
