<?php
require_once __DIR__ . '/includes/header.php';
require_login();
$pdo = get_pdo();
$id = (int) ($_GET['id'] ?? 0);
$stmt = $pdo->prepare('SELECT inspections.*, users.username FROM inspections LEFT JOIN users ON users.id = inspections.created_by WHERE inspections.id = ?');
$stmt->execute([$id]);
$inspection = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$inspection) {
    echo '<div class="alert alert-danger">Inspección no encontrada</div>';
    require_once __DIR__ . '/includes/footer.php';
    exit;
}
$photos = $pdo->prepare('SELECT * FROM photos WHERE inspection_id = ?');
$photos->execute([$id]);
$photos = $photos->fetchAll(PDO::FETCH_ASSOC);
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h1 class="h4 mb-0"><?php echo htmlspecialchars($inspection['title']); ?></h1>
        <small class="text-muted">Creado por <?php echo htmlspecialchars($inspection['username'] ?? ''); ?> - <?php echo $inspection['created_at']; ?></small>
    </div>
    <div class="d-flex gap-2">
        <a class="btn btn-secondary" href="/inspections.php">Volver</a>
        <a class="btn btn-primary" href="/api/generate_pdf.php?id=<?php echo $inspection['id']; ?>" target="_blank">Generar PDF</a>
    </div>
</div>
<div class="row g-3">
    <div class="col-md-8">
        <div class="card shadow-sm mb-3">
            <div class="card-body">
                <p><strong>Estado:</strong> <span class="badge bg-secondary"><?php echo $inspection['status']; ?></span></p>
                <p><strong>Descripción:</strong><br><?php echo nl2br(htmlspecialchars($inspection['description'])); ?></p>
                <p><strong>Coordenadas:</strong> <?php echo $inspection['latitude']; ?>, <?php echo $inspection['longitude']; ?></p>
            </div>
        </div>
        <div class="card shadow-sm">
            <div class="card-body">
                <h5 class="card-title">Fotos</h5>
                <?php if (!$photos): ?>
                    <p class="text-muted">Sin imágenes.</p>
                <?php else: ?>
                    <div class="row g-2">
                        <?php foreach ($photos as $photo): ?>
                            <div class="col-md-4">
                                <img src="/uploads/<?php echo $photo['filename']; ?>" class="img-fluid rounded">
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card shadow-sm">
            <div class="card-body">
                <div id="view-map" style="height: 300px;"></div>
            </div>
        </div>
    </div>
</div>
<script>
    const viewMap = L.map('view-map').setView([<?php echo $inspection['latitude']; ?>, <?php echo $inspection['longitude']; ?>], 13);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19 }).addTo(viewMap);
    L.marker([<?php echo $inspection['latitude']; ?>, <?php echo $inspection['longitude']; ?>]).addTo(viewMap).bindPopup('<?php echo addslashes($inspection['title']); ?>');
</script>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
