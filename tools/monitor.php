<?php
session_start();

// ── RATE LIMITING ─────────────────────────────────────────────────────────────
$ip      = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
$rateKey = 'mon_rate_' . md5($ip);
$now     = time();
if (!isset($_SESSION[$rateKey])) $_SESSION[$rateKey] = ['count' => 0, 'start' => $now];
if ($now - $_SESSION[$rateKey]['start'] > 60) $_SESSION[$rateKey] = ['count' => 0, 'start' => $now];
$_SESSION[$rateKey]['count']++;
if ($_SESSION[$rateKey]['count'] > 20 && isset($_POST['action'])) {
    header('Content-Type: application/json');
    http_response_code(429);
    echo json_encode(['success' => false, 'error' => 'Demasiadas peticiones.']);
    exit;
}

// Dominios no permitidos en el monitor (evitar SSRF a red interna)
function isBlockedUrl(string $url): bool {
    $host = strtolower(parse_url($url, PHP_URL_HOST) ?? '');
    $ip   = gethostbyname($host);
    // Bloquear IPs privadas / locales (SSRF)
    return filter_var($ip, FILTER_VALIDATE_IP, 
        FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false;
}

// ── AJAX: check URL ───────────────────────────────────────────────────────────
if (isset($_POST['action']) && $_POST['action'] === 'check') {
    header('Content-Type: application/json');

    $url = filter_var(trim($_POST['url'] ?? ''), FILTER_VALIDATE_URL);
    if (!$url) {
        echo json_encode(['success' => false, 'error' => 'URL inválida']); exit;
    }

    // Bloquear SSRF — no permitir acceso a red interna
    if (isBlockedUrl($url)) {
        echo json_encode(['success' => false, 'error' => 'URL no permitida']); exit;
    }

    // Solo http/https
    $scheme = strtolower(parse_url($url, PHP_URL_SCHEME) ?? '');
    if (!in_array($scheme, ['http', 'https'])) {
        echo json_encode(['success' => false, 'error' => 'Solo se permiten URLs http/https']); exit;
    }

    $context = stream_context_create([
        'http' => [
            'timeout'         => 10,
            'user_agent'      => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
            'follow_location' => 1,
            'max_redirects'   => 3,
        ],
        'ssl' => ['verify_peer' => false, 'verify_peer_name' => false]
    ]);

    $content = @file_get_contents($url, false, $context);
    if ($content === false) {
        echo json_encode(['success' => false, 'error' => 'No se pudo acceder a la URL']); exit;
    }

    // Limitar tamaño para evitar DoS
    $content = substr($content, 0, 2 * 1024 * 1024); // máx 2MB
    $text    = preg_replace('/\s+/', ' ', strip_tags($content));
    $hash    = md5($text);

    $changed = false;
    if (isset($_SESSION['mon_hash'], $_SESSION['mon_url']) &&
        $_SESSION['mon_url'] === $url && $_SESSION['mon_hash'] !== $hash) {
        $changed = true;
    }

    $_SESSION['mon_hash'] = $hash;
    $_SESSION['mon_url']  = $url;

    echo json_encode([
        'success'   => true,
        'changed'   => $changed,
        'hash'      => substr($hash, 0, 8),
        'timestamp' => time(),
    ]);
    exit;
}

// ── AJAX: reset session ───────────────────────────────────────────────────────
if (isset($_POST['action']) && $_POST['action'] === 'reset') {
    header('Content-Type: application/json');
    unset($_SESSION['mon_hash'], $_SESSION['mon_url']);
    echo json_encode(['success' => true]);
    exit;
}

// ── PROXY PREVIEW ─────────────────────────────────────────────────────────────
if (isset($_GET['proxy'])) {
    $url = $_SESSION['mon_url'] ?? '';
    // Solo servir si la URL ya fue validada y está en sesión
    if ($url && filter_var($url, FILTER_VALIDATE_URL) && !isBlockedUrl($url)) {
        $context = stream_context_create([
            'http' => ['timeout' => 10, 'user_agent' => 'Mozilla/5.0', 'max_redirects' => 3],
            'ssl'  => ['verify_peer' => false, 'verify_peer_name' => false],
        ]);
        $content = @file_get_contents($url, false, $context);
        if ($content !== false) {
            $base    = parse_url($url);
            $baseUrl = $base['scheme'] . '://' . $base['host'];
            $content = preg_replace('/(src|href)=["\']\/([^"\']*)["\']/', '$1="' . $baseUrl . '/$2"', $content);
            echo $content;
        }
    }
    exit;
}
?>

<section class="section-page">
  <div class="section-header">
    <p class="section-label">Herramienta</p>
    <h2>🔍 Monitor de URLs</h2>
    <p>Detecta cambios en cualquier página web automáticamente cada 60 segundos.</p>
  </div>

  <div class="tool-box">
    <h3>URL a monitorizar</h3>
    <p class="desc">El sistema compara el contenido de texto de la página en cada comprobación y te alerta si detecta cambios.</p>

    <div class="input-row">
      <input type="url" id="mon-url" placeholder="https://www.ejemplo.com" />
      <button class="btn btn-accent" id="mon-btn" onclick="toggleMonitor()">▶ Monitorizar</button>
    </div>

    <div class="mon-dashboard">
      <button class="mon-indicator status-gray" id="mon-indicator" onclick="openMonUrl()">ESPERANDO</button>
      <div>
        <div class="mon-timer" id="mon-timer">00:00</div>
        <div class="mon-timer-label">Tiempo activo</div>
      </div>
      <div class="mon-stats" id="mon-stats" style="display:none">
        <div class="mon-stat">
          <div class="mon-stat-label">Comprobaciones</div>
          <div class="mon-stat-value" id="mon-checks">0</div>
        </div>
        <div class="mon-stat">
          <div class="mon-stat-label">Cambios</div>
          <div class="mon-stat-value danger" id="mon-changes">0</div>
        </div>
        <div class="mon-stat">
          <div class="mon-stat-label">Hash</div>
          <div class="mon-stat-value mono" id="mon-hash">—</div>
        </div>
      </div>
    </div>

    <div id="mon-preview-wrap" style="display:none; margin-top:1rem">
      <label class="field-label">Vista previa</label>
      <div class="preview-frame-wrap">
        <iframe id="mon-iframe" src="about:blank"></iframe>
      </div>
    </div>

    <div class="status-box" id="mon-status"></div>

    <div class="info-note">
      ℹ️ Haz clic en el indicador de color para abrir la URL en nueva pestaña. Solo se permiten URLs públicas (http/https).
    </div>
  </div>
</section>
