#!/usr/bin/env php
<?php
// ── WORKER DE COLA ────────────────────────────────────────────────────────────
// Se ejecuta via cron cada minuto.
// Procesa los trabajos pendientes en /tmp/yt_queue/

define('YTDLP_BIN',    '/home/braishernandez/bin/yt-dlp');
define('FFMPEG_BIN',   '/home/braishernandez/bin/ffmpeg');
define('QUEUE_DIR',    '/tmp/yt_queue');
define('RESULT_DIR',   '/tmp/yt_results');
define('COOKIES_FILE', '/home/braishernandez/bin/cookies.txt');
define('YTDLP_COMMON', '--no-playlist --no-check-certificates --no-warnings --js-runtimes node:/home/braishernandez/bin/node');

putenv('HOME=/home/braishernandez');
putenv('PATH=/home/braishernandez/bin:/usr/local/bin:/usr/bin:/bin');

foreach ([QUEUE_DIR, RESULT_DIR] as $dir) {
    if (!is_dir($dir)) mkdir($dir, 0755, true);
}

function cookiesFlag(): string {
    return file_exists(COOKIES_FILE) ? '--cookies ' . escapeshellarg(COOKIES_FILE) : '';
}

// Limpiar resultados viejos (más de 1 hora)
foreach (glob(RESULT_DIR . '/*.json') as $f) {
    if (time() - filemtime($f) > 3600) {
        $data = json_decode(file_get_contents($f), true);
        if (!empty($data['file']) && file_exists($data['file'])) unlink($data['file']);
        unlink($f);
    }
}

// Procesar trabajos pendientes
$jobs = glob(QUEUE_DIR . '/*.job');
if (empty($jobs)) exit(0);

foreach ($jobs as $jobFile) {
    $job = json_decode(file_get_contents($jobFile), true);
    if (!$job) { unlink($jobFile); continue; }

    $jobId   = $job['id'];
    $url     = $job['url'];
    $format  = $job['format'] ?? 'video';
    $outDir  = RESULT_DIR . '/' . $jobId;

    // Marcar como en proceso
    unlink($jobFile);
    file_put_contents(RESULT_DIR . '/' . $jobId . '.json', json_encode(['status' => 'processing']));

    if (!is_dir($outDir)) mkdir($outDir, 0755, true);

    $ffmpegDir = escapeshellarg(dirname(FFMPEG_BIN));
    $outTpl    = escapeshellarg($outDir . '/%(title)s.%(ext)s');
    $cookies   = cookiesFlag();

    if ($format === 'audio') {
        $cmd = sprintf(
            '%s -x --audio-format mp3 --audio-quality 0 --ffmpeg-location %s -o %s %s %s %s 2>&1',
            escapeshellcmd(YTDLP_BIN), $ffmpegDir, $outTpl, YTDLP_COMMON, $cookies, escapeshellarg($url)
        );
    } else {
        $cmd = sprintf(
            '%s -f "bestvideo[ext=mp4]+bestaudio/bestvideo+bestaudio/best" --merge-output-format mp4 --ffmpeg-location %s -o %s %s %s %s 2>&1',
            escapeshellcmd(YTDLP_BIN), $ffmpegDir, $outTpl, YTDLP_COMMON, $cookies, escapeshellarg($url)
        );
    }

    exec($cmd, $output, $exitCode);

    if ($exitCode !== 0) {
        $errMsg = implode(' | ', array_slice($output, -3));
        file_put_contents(RESULT_DIR . '/' . $jobId . '.json', json_encode([
            'status' => 'error',
            'error'  => $errMsg,
        ]));
        // Limpiar directorio de salida
        array_map('unlink', glob($outDir . '/*'));
        rmdir($outDir);
        continue;
    }

    $files = glob($outDir . '/*');
    if (empty($files)) {
        file_put_contents(RESULT_DIR . '/' . $jobId . '.json', json_encode([
            'status' => 'error',
            'error'  => 'No se generó ningún archivo',
        ]));
        continue;
    }

    file_put_contents(RESULT_DIR . '/' . $jobId . '.json', json_encode([
        'status'   => 'done',
        'file'     => $files[0],
        'filename' => basename($files[0]),
    ]));
}
