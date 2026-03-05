# braishernandez.com

Web personal de Brais Hernández Facal — Ingeniero Técnico en Informática de Sistemas.

## Estructura

```
/
├── index.php               # Punto de entrada, enrutador por ?page=
├── includes/
│   ├── nav.php             # Barra de navegación
│   └── footer.php          # Pie de página
├── tools/
│   ├── inicio.php          # CV / Presentación
│   ├── monitor.php         # Monitor de URLs (backend AJAX + proxy + frontend)
│   ├── videos.php          # Descargador de vídeos y MP3
│   ├── pdf.php             # Herramientas PDF (unir, convertir)
│   └── curiosidades.php    # Blog (en construcción)
└── assets/
    ├── css/
    │   └── main.css        # Estilos globales
    └── js/
        ├── main.js         # Utilidades compartidas
        ├── monitor.js      # Lógica del monitor de URLs
        ├── videos.js       # Lógica del descargador
        └── pdf.js          # Lógica PDF (merge + JPG)
```

## Requisitos del servidor

- PHP 7.4+ con `allow_url_fopen = On` (para el monitor de URLs)
- `session_start()` disponible (para el monitor)
- Cualquier hosting compartido estándar lo soporta

## Despliegue

1. Clona el repositorio en tu servidor web
2. Asegúrate de que el DocumentRoot apunta a la carpeta raíz
3. No requiere base de datos ni configuración adicional

## Páginas

| URL | Contenido |
|-----|-----------|
| `/?page=inicio` | CV y presentación |
| `/?page=monitor` | Monitor de cambios en URLs |
| `/?page=videos` | Descargador de vídeos/MP3 |
| `/?page=pdf` | Unir PDFs y convertir a JPG |
| `/?page=curiosidades` | Blog (próximamente) |
