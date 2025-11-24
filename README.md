# Sistema de Inspecciones Geolocalizadas (MVP)

MVP ligero en PHP + HTMX + SQLite para gestionar inspecciones geolocalizadas, fotos y reportes PDF sencillos.

## Requisitos
- PHP >= 8 con extensiones `pdo_sqlite` y `gd` (para convertir imágenes al PDF).
- Navegador con conexión local.
- Sin dependencias de Node o frameworks.

## Estructura
```
public/
  index.php           # Login
  dashboard.php       # Tablero con conteos
  map.php             # Mapa Leaflet + creación rápida
  inspections.php     # Listado filtrable HTMX + panel de edición
  inspection_view.php # Vista detalle + botón PDF
  api/
    delete_inspection.php
    generate_pdf.php
  partials/
    inspection_form.php
    inspection_table.php
  includes/
    config.php, db.php, auth.php, header.php, footer.php
  assets/
    css/styles.css
    js/app.js
  uploads/            # Carpeta de fotos
```

## Pasos de instalación
1. Clona o copia este repo en tu servidor local.
2. Asegúrate de que `public/uploads/` y `data/` sean escribibles por PHP.
3. Arranca el servidor embebido de PHP desde la raíz del repo:
   ```bash
   php -S localhost:8000 -t public
   ```
4. Abre `http://localhost:8000` en el navegador.

La base SQLite se crea automáticamente en `data/database.sqlite` al cargar cualquier página.

## Credenciales de ejemplo
- **admin / admin123**
- **tecnico / tecnico123**

## Flujo principal
1. Inicia sesión.
2. En el *Dashboard* revisa los conteos y navega al mapa o listado.
3. En el *Mapa* haz clic para abrir el formulario HTMX con las coordenadas precargadas.
4. Guarda la inspección con 1–5 fotos. Las fotos se almacenan en `public/uploads/`.
5. En el *Listado* filtra por estado o texto, edita con el panel lateral o elimina registros.
6. En la *Vista detalle* consulta fotos, localización en el mapa y genera el PDF básico.

## Notas sobre el PDF
- El generador (`public/api/generate_pdf.php`) compone un PDF mínimo sin dependencias externas.
- Convierte imágenes PNG/WEBP a JPEG en memoria para incrustarlas.
- Dibuja un recuadro simple como mapa estático e inserta hasta que quepan en una página A4.

## Buenas prácticas y mantenimiento
- No subas fotos mayores a ~5 MB para mantener el tamaño del PDF razonable.
- Si cambias la ruta base, actualiza `$BASE_URL` en `public/includes/config.php`.
- Para limpiar datos, borra `data/database.sqlite` y recarga la página (se regeneran tablas y usuarios demo).
