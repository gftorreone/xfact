<?php
require_once __DIR__ . '/auth.php';
initialize_database();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Inspecciones</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://unpkg.com/htmx.org@1.9.12"></script>
    <link rel="stylesheet" href="/assets/css/styles.css">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
    <div class="container-fluid">
        <a class="navbar-brand" href="/dashboard.php">Inspecciones</a>
        <div class="d-flex">
            <?php if (current_user()): ?>
                <span class="navbar-text text-white me-3">Hola, <?php echo htmlspecialchars(current_user()['username']); ?> (<?php echo current_user()['role']; ?>)</span>
                <a class="btn btn-outline-light btn-sm" href="/logout.php">Salir</a>
            <?php endif; ?>
        </div>
    </div>
</nav>
<div class="container">
