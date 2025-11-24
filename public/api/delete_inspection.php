<?php
require_once __DIR__ . '/../includes/auth.php';
require_login();
$pdo = get_pdo();
$id = (int) ($_GET['id'] ?? 0);
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $id) {
    // eliminar archivos
    $photos = $pdo->prepare('SELECT filename FROM photos WHERE inspection_id = ?');
    $photos->execute([$id]);
    $files = $photos->fetchAll(PDO::FETCH_COLUMN);
    global $UPLOAD_DIR;
    foreach ($files as $file) {
        $path = $UPLOAD_DIR . '/' . $file;
        if (is_file($path)) {
            @unlink($path);
        }
    }
    $pdo->prepare('DELETE FROM inspections WHERE id = ?')->execute([$id]);
}
// recargar tabla con filtros actuales
$_GET = array_merge($_GET, $_POST);
include __DIR__ . '/../partials/inspection_table.php';
