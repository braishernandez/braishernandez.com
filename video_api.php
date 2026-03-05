<?php
// ── API DE VÍDEOS — Sistema de cola ──────────────────────────────────────────
define('QUEUE_DIR',  '/tmp/yt_queue');
define('RESULT_DIR', '/tmp/yt_results');

foreach ([QUEUE_DIR, RESULT_DIR] as $dir) {
    if (!is_dir($dir)) mkdir($dir, 0755, true);
}

header('Content-Type: application/json');

// ── SERVIR ARCHIVO DESCARGADO ─────────────────────────────────────────────────
if (isset($_GET['serve']) && !empty($_GET['jobid'])) {
    $jobId      = preg_replace('/[^a-zA-Z0-9_.]/', '', $_GET['jobid']);
    $resultFile = RESULT_DIR . '/' . $jobId . '.json';

    if (!file_exists($resultFile)) {
        http_response_code(404); echo json_encode(['error' => 'Trabajo no encontrado']); exit;
    }

    $result = json_decode(file_get_contents($resultFile), true);
    if ($result['status'] !== 'done' || !file_exists($result['file'])) {
        http_response_code(404); echo json_encode(['error' => 'Archivo no disponible']); exit;
    }

    $filename = $result['filename'];
    $ext      = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    $mime     = match($ext) {
        'mp4'  => 'video/mp4', 'webm' => 'video/webm',
        'mp3'  => 'audio/mpeg', 'm4a' => 'audio/mp4',
        default => 'application/octet-stream',
    };

    header_remove('Content-Type');
    header('Content-Type: ' . $mime);
    header('Content-Disposition: attachment; filename="' . rawurlencode($filename) . '"');
    header('Content-Length: ' . filesize($result['file']));
    header('Cache-Control: no-cache');
    readfile($result['file']);

    unlink($result['file']);
    $outDir = dirname($result['file']);
    if (is_dir($outDir) && $outDir !== RESULT_DIR) rmdir($outDir);
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
    if (!filter_var($url, FILTER_VALIDATE_URL)) {
        echo json_encode(['success' => false, 'error' => 'URL inválida']); exit;
    }
    $jobId  = uniqid('job_', true);
    $format = trim($_POST['format'] ?? 'video');
    file_put_contents(QUEUE_DIR . '/' . $jobId . '.job', json_encode([
        'id' => $jobId, 'url' => $url, 'format' => $format, 'time' => time(),
    ]));
    echo json_encode(['success' => true, 'jobid' => $jobId]);
    exit;
}

// ── CONSULTAR ESTADO ──────────────────────────────────────────────────────────
if ($action === 'status') {
    $jobId      = preg_replace('/[^a-zA-Z0-9_.]/', '', trim($_POST['jobid'] ?? ''));
    $jobFile    = QUEUE_DIR  . '/' . $jobId . '.job';
    $resultFile = RESULT_DIR . '/' . $jobId . '.json';

    if (file_exists($jobFile))    { echo json_encode(['status' => 'queued']);    exit; }
    if (file_exists($resultFile)) { echo json_encode(json_decode(file_get_contents($resultFile), true)); exit; }
    echo json_encode(['status' => 'not_found']);
    exit;
}

echo json_encode(['success' => false, 'error' => 'Acción desconocida']);
