<?php
require_once __DIR__ . '/../includes/auth.php';
require_login();
$pdo = get_pdo();
$id = (int) ($_GET['id'] ?? 0);
$stmt = $pdo->prepare('SELECT inspections.*, users.username FROM inspections LEFT JOIN users ON users.id = inspections.created_by WHERE inspections.id = ?');
$stmt->execute([$id]);
$data = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$data) {
    http_response_code(404);
    exit('No encontrado');
}
$photosStmt = $pdo->prepare('SELECT filename FROM photos WHERE inspection_id = ?');
$photosStmt->execute([$id]);
$photos = $photosStmt->fetchAll(PDO::FETCH_COLUMN);

$pageWidth = 595.28; // A4 points
$pageHeight = 841.89;
$objects = [];

function add_object(&$objects, $content) {
    $objects[] = $content;
    return count($objects);
}

function escape_text(string $text): string {
    return str_replace(['\\', '(', ')', "\r"], ['\\\\', '\\(', '\\)', ''], $text);
}

$imageEntries = [];
$resourceImages = '';

function load_image_as_jpeg(string $path): ?string {
    $info = getimagesize($path);
    if (!$info) return null;
    $mime = $info['mime'] ?? '';
    if ($mime === 'image/jpeg') {
        return file_get_contents($path);
    }
    if (!function_exists('imagecreatefromstring')) return null;
    $img = imagecreatefromstring(file_get_contents($path));
    if (!$img) return null;
    ob_start();
    imagejpeg($img, null, 85);
    $data = ob_get_clean();
    imagedestroy($img);
    return $data;
}

foreach ($photos as $index => $file) {
    $fullPath = $UPLOAD_DIR . '/' . $file;
    if (!is_file($fullPath)) continue;
    $jpeg = load_image_as_jpeg($fullPath);
    if (!$jpeg) continue;
    $info = getimagesizefromstring($jpeg);
    $imgObj = '<< /Type /XObject /Subtype /Image /Width ' . $info[0] . ' /Height ' . $info[1] . ' /ColorSpace /DeviceRGB /BitsPerComponent 8 /Filter /DCTDecode /Length ' . strlen($jpeg) . ' >>\nstream\n' . $jpeg . "\nendstream";
    $num = add_object($objects, $imgObj);
    $name = '/Im' . ($index + 1);
    $imageEntries[] = ['name' => $name, 'num' => $num, 'w' => $info[0], 'h' => $info[1]];
    $resourceImages .= $name . ' ' . $num . ' 0 R ';
}

$resourceDict = '<< /Font << /F1 4 0 R >>';
if ($resourceImages) {
    $resourceDict .= ' /XObject << ' . $resourceImages . '>>';
}
$resourceDict .= ' >>';

// Content stream
$y = $pageHeight - 60;
$content = "BT /F1 18 Tf 50 $y Td (Informe de inspeccion) Tj ET\n";
$y -= 24;
$content .= "BT /F1 12 Tf 50 $y Td (Titulo: " . escape_text($data['title']) . ") Tj ET\n";
$y -= 16;
$content .= "BT /F1 12 Tf 50 $y Td (Estado: " . escape_text($data['status']) . ") Tj ET\n";
$y -= 16;
$content .= "BT /F1 12 Tf 50 $y Td (Coordenadas: " . escape_text($data['latitude'] . ', ' . $data['longitude']) . ") Tj ET\n";
$y -= 16;
$desc = substr(preg_replace('/\s+/', ' ', $data['description']), 0, 180);
$content .= "BT /F1 12 Tf 50 $y Td (Descripcion: " . escape_text($desc) . ") Tj ET\n";
$y -= 30;
$content .= "BT /F1 12 Tf 50 $y Td (Ubicacion aproximada) Tj ET\n";
$y -= 5;
$content .= "0.5 w 50 $y 200 0 m 250 $y l S\n";
$content .= "50 " . ($y-40) . " m 250 " . ($y-40) . " l S\n";
$content .= "50 $y m 50 " . ($y-40) . " l S\n";
$content .= "250 $y m 250 " . ($y-40) . " l S\n";
$y -= 70;

foreach ($imageEntries as $img) {
    $maxWidth = 200;
    $scale = $maxWidth / $img['w'];
    $displayW = $maxWidth;
    $displayH = $img['h'] * $scale;
    if ($y - $displayH < 50) {
        break; // limit simple layout
    }
    $content .= sprintf("q %.2F 0 0 %.2F 50 %.2F cm %s Do Q\n", $displayW, $displayH, $y - $displayH, $img['name']);
    $y -= $displayH + 10;
}

$contentsStream = '<< /Length ' . strlen($content) . ' >>\nstream\n' . $content . 'endstream';

// Objects
$catalog = '<< /Type /Catalog /Pages 2 0 R >>';
$pages = '<< /Type /Pages /Kids [3 0 R] /Count 1 >>';
$page = '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 ' . $pageWidth . ' ' . $pageHeight . '] /Resources ' . $resourceDict . ' /Contents 5 0 R >>';
$font = '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>';

add_object($objects, $catalog); // 1
add_object($objects, $pages);   // 2
add_object($objects, $page);    // 3
add_object($objects, $font);    // 4
add_object($objects, $contentsStream); //5

// Build PDF
$pdf = "%PDF-1.4\n";
$offsets = [0];
foreach ($objects as $i => $obj) {
    $offsets[$i + 1] = strlen($pdf);
    $pdf .= ($i + 1) . " 0 obj\n" . $obj . "\nendobj\n";
}
$xrefPos = strlen($pdf);
$pdf .= 'xref\n0 ' . (count($objects) + 1) . "\n";
$pdf .= "0000000000 65535 f \n";
for ($i = 1; $i <= count($objects); $i++) {
    $pdf .= sprintf('%010d 00000 n ', $offsets[$i]) . "\n";
}
$pdf .= 'trailer<< /Size ' . (count($objects) + 1) . ' /Root 1 0 R >>\nstartxref\n' . $xrefPos . "\n%%EOF";

header('Content-Type: application/pdf');
header('Content-Disposition: inline; filename="informe-' . $id . '.pdf"');
echo $pdf;
exit;
