<?php
require_once __DIR__ . '/includes/header.php';
require_login();
$pdo = get_pdo();
$inspections = $pdo->query('SELECT id, title, status, latitude, longitude FROM inspections ORDER BY created_at DESC')->fetchAll(PDO::FETCH_ASSOC);
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4">Mapa de inspecciones</h1>
    <div>
        <a class="btn btn-secondary" href="/dashboard.php">Volver</a>
    </div>
</div>
<div class="row g-3">
    <div class="col-lg-8">
        <div id="map" class="map-container"></div>
    </div>
    <div class="col-lg-4">
        <div class="card shadow-sm">
            <div class="card-body" id="form-panel">
                <p class="text-muted">Haz clic en el mapa para crear una inspección nueva o selecciona un marcador para verla.</p>
                <button class="btn btn-primary" id="create-btn" type="button">Crear inspección</button>
            </div>
        </div>
    </div>
</div>
<script>
    const map = L.map('map').setView([40.4168, -3.7038], 6);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; OpenStreetMap'
    }).addTo(map);

    const markers = <?php echo json_encode($inspections); ?>;
    markers.forEach((item) => {
        const marker = L.marker([item.latitude, item.longitude]).addTo(map);
        marker.bindPopup(`<strong>${item.title}</strong><br>${item.status}<br><a href="/inspection_view.php?id=${item.id}">Ver detalle</a>`);
    });

    map.on('click', (e) => {
        htmx.ajax('GET', `/partials/inspection_form.php?lat=${e.latlng.lat}&lng=${e.latlng.lng}`, '#form-panel');
    });

    document.getElementById('create-btn').addEventListener('click', () => {
        map.fire('click', { latlng: map.getCenter() });
    });
</script>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
