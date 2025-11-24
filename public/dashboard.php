<?php
require_once __DIR__ . '/includes/header.php';
require_login();
$pdo = get_pdo();
$statuses = ['pendiente' => 'Pendientes', 'en_proceso' => 'En proceso', 'finalizado' => 'Finalizadas'];
$counts = [];
foreach ($statuses as $key => $label) {
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM inspections WHERE status = ?');
    $stmt->execute([$key]);
    $counts[$key] = (int) $stmt->fetchColumn();
}
$total = array_sum($counts);
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3">Dashboard</h1>
    <div>
        <a href="/map.php" class="btn btn-outline-primary me-2">Ir al mapa</a>
        <a href="/inspections.php" class="btn btn-primary">Listado</a>
    </div>
</div>
<div class="row g-3">
    <div class="col-md-3">
        <div class="card text-bg-primary">
            <div class="card-body">
                <h5 class="card-title">Pendientes</h5>
                <p class="display-6"><?php echo $counts['pendiente']; ?></p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-bg-warning">
            <div class="card-body">
                <h5 class="card-title">En proceso</h5>
                <p class="display-6"><?php echo $counts['en_proceso']; ?></p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-bg-success">
            <div class="card-body">
                <h5 class="card-title">Finalizadas</h5>
                <p class="display-6"><?php echo $counts['finalizado']; ?></p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-bg-dark">
            <div class="card-body">
                <h5 class="card-title">Total</h5>
                <p class="display-6"><?php echo $total; ?></p>
            </div>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
