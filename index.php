<?php
define('BASE_PATH', __DIR__);

// Límites para descargas largas (PHP-FPM compatible)
@ini_set('max_execution_time', 300);
@ini_set('memory_limit', '256M');

$page = isset($_GET['page']) ? $_GET['page'] : 'inicio';
$allowed = ['inicio', 'monitor', 'videos', 'pdf', 'curiosidades'];
if (!in_array($page, $allowed)) $page = 'inicio';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Brais Hernández Facal — Ingeniero de Sistemas</title>
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600&family=DM+Serif+Display:ital@0;1&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/main.css">
</head>
<body>

<?php include BASE_PATH . '/includes/nav.php'; ?>

<main>
  <?php include BASE_PATH . '/tools/' . $page . '.php'; ?>
</main>

<?php include BASE_PATH . '/includes/footer.php'; ?>

<script src="assets/js/main.js"></script>
<?php if ($page === 'monitor'): ?>
<script src="assets/js/monitor.js"></script>
<?php endif; ?>
<?php if ($page === 'pdf'): ?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf-lib/1.17.1/pdf-lib.min.js"></script>
<script src="assets/js/pdf.js"></script>
<?php endif; ?>
<?php if ($page === 'videos'): ?>
<script src="assets/js/videos.js"></script>
<?php endif; ?>

</body>
</html>
