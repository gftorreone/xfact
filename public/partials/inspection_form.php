<?php
require_once __DIR__ . '/../includes/auth.php';
require_login();
$pdo = get_pdo();
$allowedStatus = ['pendiente', 'en_proceso', 'finalizado'];
$message = '';
$errors = [];
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int) ($_POST['id'] ?? 0);
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $status = $_POST['status'] ?? 'pendiente';
    $lat = (float) ($_POST['latitude'] ?? 0);
    $lng = (float) ($_POST['longitude'] ?? 0);

    if (!$title) { $errors[] = 'El título es obligatorio'; }
    if (!in_array($status, $allowedStatus, true)) { $errors[] = 'Estado inválido'; }

    if (!$errors) {
        if ($id) {
            $stmt = $pdo->prepare('UPDATE inspections SET title = ?, description = ?, status = ?, latitude = ?, longitude = ? WHERE id = ?');
            $stmt->execute([$title, $description, $status, $lat, $lng, $id]);
        } else {
            $stmt = $pdo->prepare('INSERT INTO inspections (title, description, status, latitude, longitude, created_by) VALUES (?, ?, ?, ?, ?, ?)');
            $stmt->execute([$title, $description, $status, $lat, $lng, current_user()['id']]);
            $id = (int) $pdo->lastInsertId();
        }

        if (!empty($_FILES['photos']['name'][0])) {
            $files = $_FILES['photos'];
            $count = min(count($files['name']), 5);
            for ($i = 0; $i < $count; $i++) {
                if ($files['error'][$i] === UPLOAD_ERR_OK) {
                    $ext = pathinfo($files['name'][$i], PATHINFO_EXTENSION);
                    $name = uniqid('photo_') . '.' . $ext;
                    $target = $UPLOAD_DIR . '/' . $name;
                    if (move_uploaded_file($files['tmp_name'][$i], $target)) {
                        $pdo->prepare('INSERT INTO photos (inspection_id, filename) VALUES (?, ?)')->execute([$id, $name]);
                    }
                }
            }
        }

        $message = 'Inspección guardada correctamente';
    }
}

$data = [
    'id' => $id,
    'title' => '',
    'description' => '',
    'status' => 'pendiente',
    'latitude' => $_GET['lat'] ?? '',
    'longitude' => $_GET['lng'] ?? '',
];

if ($id && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    $stmt = $pdo->prepare('SELECT * FROM inspections WHERE id = ?');
    $stmt->execute([$id]);
    $data = $stmt->fetch(PDO::FETCH_ASSOC);
}
if (!$data) {
    echo '<div class="alert alert-danger">No se encontró la inspección</div>';
    exit;
}
?>
<?php if ($message): ?>
    <div class="alert alert-success mb-2"><?php echo $message; ?></div>
    <div hx-get="/partials/inspection_table.php" hx-target="#table-container" hx-trigger="load"></div>
<?php endif; ?>
<?php if ($errors): ?>
    <div class="alert alert-danger">
        <ul class="mb-0">
            <?php foreach ($errors as $err): ?><li><?php echo htmlspecialchars($err); ?></li><?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>
<form hx-post="/partials/inspection_form.php" hx-target="#form-panel" enctype="multipart/form-data">
    <input type="hidden" name="id" value="<?php echo htmlspecialchars($data['id']); ?>">
    <div class="mb-2">
        <label class="form-label">Título</label>
        <input type="text" name="title" class="form-control" value="<?php echo htmlspecialchars($data['title']); ?>" required>
    </div>
    <div class="mb-2">
        <label class="form-label">Descripción</label>
        <textarea name="description" class="form-control" rows="3"><?php echo htmlspecialchars($data['description']); ?></textarea>
    </div>
    <div class="mb-2">
        <label class="form-label">Estado</label>
        <select name="status" class="form-select">
            <?php foreach ($allowedStatus as $st): ?>
                <option value="<?php echo $st; ?>" <?php echo $data['status'] === $st ? 'selected' : ''; ?>><?php echo $st; ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="row">
        <div class="col">
            <label class="form-label">Latitud</label>
            <input type="text" name="latitude" class="form-control" value="<?php echo htmlspecialchars($data['latitude']); ?>" required>
        </div>
        <div class="col">
            <label class="form-label">Longitud</label>
            <input type="text" name="longitude" class="form-control" value="<?php echo htmlspecialchars($data['longitude']); ?>" required>
        </div>
    </div>
    <div class="mb-2 mt-2">
        <label class="form-label">Fotos (hasta 5)</label>
        <input type="file" name="photos[]" class="form-control" accept="image/*" multiple>
    </div>
    <div class="d-flex justify-content-between align-items-center mt-3">
        <button type="submit" class="btn btn-primary">Guardar</button>
        <?php if ($data['id']): ?>
            <a class="btn btn-outline-secondary" href="/inspection_view.php?id=<?php echo $data['id']; ?>">Ver detalle</a>
        <?php endif; ?>
    </div>
</form>
