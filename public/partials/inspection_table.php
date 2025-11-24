<?php
require_once __DIR__ . '/../includes/auth.php';
require_login();
$pdo = get_pdo();
$status = $_GET['status'] ?? '';
$q = trim($_GET['q'] ?? '');

$sql = 'SELECT inspections.*, users.username FROM inspections LEFT JOIN users ON users.id = inspections.created_by WHERE 1=1';
$params = [];
if ($status) {
    $sql .= ' AND status = ?';
    $params[] = $status;
}
if ($q) {
    $sql .= ' AND (title LIKE ? OR description LIKE ?)';
    $params[] = "%$q%";
    $params[] = "%$q%";
}
$sql .= ' ORDER BY created_at DESC';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<table class="table table-hover align-middle">
    <thead>
        <tr>
            <th>Título</th>
            <th>Estado</th>
            <th>Coordenadas</th>
            <th>Creado por</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
        <?php if (!$rows): ?>
            <tr>
                <td colspan="5" class="text-center text-muted">Sin resultados</td>
            </tr>
        <?php endif; ?>
        <?php foreach ($rows as $row): ?>
            <tr>
                <td><?php echo htmlspecialchars($row['title']); ?></td>
                <td><span class="badge bg-secondary"><?php echo $row['status']; ?></span></td>
                <td><small><?php echo number_format($row['latitude'], 5) . ', ' . number_format($row['longitude'], 5); ?></small></td>
                <td><?php echo htmlspecialchars($row['username'] ?? ''); ?></td>
                <td class="text-end">
                    <a class="btn btn-sm btn-outline-secondary" href="/inspection_view.php?id=<?php echo $row['id']; ?>">Ver</a>
                    <button class="btn btn-sm btn-outline-primary" hx-get="/partials/inspection_form.php?id=<?php echo $row['id']; ?>" hx-target="#form-panel">Editar</button>
                    <button class="btn btn-sm btn-outline-danger" hx-post="/api/delete_inspection.php?id=<?php echo $row['id']; ?>" hx-target="#table-container" hx-confirm="¿Eliminar esta inspección?" hx-swap="outerHTML" hx-include="#filter-form">Eliminar</button>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
