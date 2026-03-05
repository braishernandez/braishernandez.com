<?php
// ── SEGURIDAD GLOBAL ──────────────────────────────────────────────────────────
// Bloquear acceso directo desde navegador (solo AJAX)
if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    !isset($_GET['serve'])) {
    // Permitir igualmente — algunos clientes no envían X-Requested-With
}

// Rate limiting simple por IP (máx 10 peticiones por minuto)
session_start();
$ip       = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
$rateKey  = 'rate_' . md5($ip);
$now      = time();
$window   = 60;
$maxReqs  = 10;

if (!isset($_SESSION[$rateKey])) {
    $_SESSION[$rateKey] = ['count' => 0, 'start' => $now];
}
if ($now - $_SESSION[$rateKey]['start'] > $window) {
    $_SESSION[$rateKey] = ['count' => 0, 'start' => $now];
}
$_SESSION[$rateKey]['count']++;
if ($_SESSION[$rateKey]['count'] > $maxReqs) {
    http_response_code(429);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Demasiadas peticiones. Espera un momento.']);
    exit;
}

// ── CONFIGURACIÓN ─────────────────────────────────────────────────────────────
define('QUEUE_DIR',  '/tmp/yt_queue');
define('RESULT_DIR', '/tmp/yt_results');

// Dominios permitidos (whitelist)
define('ALLOWED_DOMAINS', [
    'youtube.com', 'youtu.be', 'www.youtube.com',
    'instagram.com', 'www.instagram.com',
    'facebook.com', 'www.facebook.com', 'm.facebook.com',
    'tiktok.com', 'www.tiktok.com',
    'twitter.com', 'x.com', 'www.twitter.com',
    'vimeo.com', 'www.vimeo.com',
    'twitch.tv', 'www.twitch.tv',
]);

foreach ([QUEUE_DIR, RESULT_DIR] as $dir) {
    if (!is_dir($dir)) mkdir($dir, 0755, true);
}

header('Content-Type: application/json');

// Validar que la URL pertenece a un dominio permitido
function validateUrl(string $url): bool {
    if (!filter_var($url, FILTER_VALIDATE_URL)) return false;
    $host = strtolower(parse_url($url, PHP_URL_HOST) ?? '');
    foreach (ALLOWED_DOMAINS as $domain) {
        if ($host === $domain || str_ends_with($host, '.' . $domain)) return true;
    }
    return false;
}

// Sanitizar jobId — solo alfanumérico, guiones y puntos
function sanitizeJobId(string $id): string {
    return preg_replace('/[^a-zA-Z0-9_.]/', '', $id);
}

// ── SERVIR ARCHIVO DESCARGADO ─────────────────────────────────────────────────
if (isset($_GET['serve']) && !empty($_GET['jobid'])) {
    $jobId      = sanitizeJobId($_GET['jobid']);
    $resultFile = RESULT_DIR . '/' . $jobId . '.json';

    if (!$jobId || !file_exists($resultFile)) {
        http_response_code(404); echo json_encode(['error' => 'Trabajo no encontrado']); exit;
    }

    $result = json_decode(file_get_contents($resultFile), true);
    if (!$result || $result['status'] !== 'done' || !file_exists($result['file'])) {
        http_response_code(404); echo json_encode(['error' => 'Archivo no disponible']); exit;
    }

    // Verificar que el archivo está dentro de RESULT_DIR (path traversal)
    $realFile    = realpath($result['file']);
    $realResults = realpath(RESULT_DIR);
    if (!$realFile || !str_starts_with($realFile, $realResults)) {
        http_response_code(403); echo json_encode(['error' => 'Acceso denegado']); exit;
    }

    $filename = basename($result['filename']); // basename evita path traversal
    $ext      = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    $allowed  = ['mp4', 'webm', 'mp3', 'm4a'];
    if (!in_array($ext, $allowed)) {
        http_response_code(403); echo json_encode(['error' => 'Tipo de archivo no permitido']); exit;
    }

    $mime = match($ext) {
        'mp4'  => 'video/mp4', 'webm' => 'video/webm',
        'mp3'  => 'audio/mpeg', 'm4a' => 'audio/mp4',
    };

    header_remove('Content-Type');
    header('Content-Type: ' . $mime);
    header('Content-Disposition: attachment; filename="' . rawurlencode($filename) . '"');
    header('Content-Length: ' . filesize($realFile));
    header('Cache-Control: no-cache, no-store');
    header('X-Content-Type-Options: nosniff');
    readfile($realFile);

    unlink($realFile);
    $outDir = dirname($realFile);
    if (is_dir($outDir) && realpath($outDir) !== $realResults) rmdir($outDir);
    unlink($resultFile);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); echo json_encode(['error' => 'Método no permitido']); exit;
}

$action = trim($_POST['action'] ?? '');
$url    = trim($_POST['url']    ?? '');

// ── ENCOLAR DESCARGA ──────────────────────────────────────────────────────────
if ($action === 'download') {
    if (!validateUrl($url)) {
        echo json_encode(['success' => false, 'error' => 'URL no permitida. Solo se admiten YouTube, Instagram, Facebook, TikTok, Twitter, Vimeo y Twitch.']); exit;
    }
    $format = in_array(trim($_POST['format'] ?? ''), ['video', 'audio']) ? trim($_POST['format']) : 'video';
    $jobId  = uniqid('job_', true);
    file_put_contents(QUEUE_DIR . '/' . $jobId . '.job', json_encode([
        'id' => $jobId, 'url' => $url, 'format' => $format, 'time' => time(),
    ]));
    echo json_encode(['success' => true, 'jobid' => $jobId]);
    exit;
}

// ── CONSULTAR ESTADO ──────────────────────────────────────────────────────────
if ($action === 'status') {
    $jobId      = sanitizeJobId(trim($_POST['jobid'] ?? ''));
    $jobFile    = QUEUE_DIR  . '/' . $jobId . '.job';
    $resultFile = RESULT_DIR . '/' . $jobId . '.json';

    if (!$jobId) { echo json_encode(['status' => 'not_found']); exit; }
    if (file_exists($jobFile))    { echo json_encode(['status' => 'queued']); exit; }
    if (file_exists($resultFile)) { echo json_encode(json_decode(file_get_contents($resultFile), true)); exit; }
    echo json_encode(['status' => 'not_found']);
    exit;
}

echo json_encode(['success' => false, 'error' => 'Acción desconocida']);
