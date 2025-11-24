<?php
require_once __DIR__ . '/includes/header.php';
require_login();
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4">Inspecciones</h1>
    <div>
        <a href="/map.php" class="btn btn-outline-primary me-2">Ver mapa</a>
    </div>
</div>
<div class="row g-3">
    <div class="col-lg-8">
        <div class="card shadow-sm">
            <div class="card-body">
                <form class="row g-2 mb-3" id="filter-form" hx-get="/partials/inspection_table.php" hx-target="#table-container" hx-trigger="change from:select">
                    <div class="col-md-4">
                        <label class="form-label">Estado</label>
                        <select name="status" class="form-select">
                            <option value="">Todos</option>
                            <option value="pendiente">Pendiente</option>
                            <option value="en_proceso">En proceso</option>
                            <option value="finalizado">Finalizado</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Búsqueda</label>
                        <input type="text" name="q" class="form-control" placeholder="Título o descripción" hx-trigger="keyup changed delay:400ms" hx-get="/partials/inspection_table.php" hx-target="#table-container" hx-include="#filter-form">
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <button type="button" class="btn btn-primary w-100" hx-get="/partials/inspection_table.php" hx-target="#table-container" hx-include="#filter-form">Filtrar</button>
                    </div>
                </form>
                <div id="table-container" hx-get="/partials/inspection_table.php" hx-trigger="load">
                    Cargando...
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card shadow-sm">
            <div class="card-body" id="form-panel">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h5 class="mb-0">Crear / Editar</h5>
                    <button class="btn btn-sm btn-outline-primary" hx-get="/partials/inspection_form.php" hx-target="#form-panel">Nuevo</button>
                </div>
                <p class="text-muted small mb-0">Selecciona una inspección para editarla o usa el botón Nuevo.</p>
            </div>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
