<?php
session_start();

// ── AJAX: check URL ──────────────────────────────────────────────────────────
if (isset($_POST['action']) && $_POST['action'] === 'check') {
    header('Content-Type: application/json');

    $url = filter_var(trim($_POST['url'] ?? ''), FILTER_VALIDATE_URL);
    if (!$url) {
        echo json_encode(['success' => false, 'error' => 'URL inválida']);
        exit;
    }

    $context = stream_context_create([
        'http' => [
            'timeout'    => 10,
            'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
            'follow_location' => 1,
        ],
        'ssl' => [
            'verify_peer'      => false,
            'verify_peer_name' => false,
        ]
    ]);

    $content = @file_get_contents($url, false, $context);
    if ($content === false) {
        echo json_encode(['success' => false, 'error' => 'No se pudo acceder a la URL']);
        exit;
    }

    $text = preg_replace('/\s+/', ' ', strip_tags($content));
    $hash = md5($text);

    $changed = false;
    if (
        isset($_SESSION['mon_hash'], $_SESSION['mon_url']) &&
        $_SESSION['mon_url'] === $url &&
        $_SESSION['mon_hash'] !== $hash
    ) {
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

// ── AJAX: reset session ──────────────────────────────────────────────────────
if (isset($_POST['action']) && $_POST['action'] === 'reset') {
    header('Content-Type: application/json');
    session_start();
    unset($_SESSION['mon_hash'], $_SESSION['mon_url']);
    echo json_encode(['success' => true]);
    exit;
}

// ── AJAX: proxy preview ──────────────────────────────────────────────────────
if (isset($_GET['proxy'])) {
    $url = $_SESSION['mon_url'] ?? '';
    if ($url) {
        $context = stream_context_create([
            'http' => ['timeout' => 10, 'user_agent' => 'Mozilla/5.0'],
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

    <!-- Indicador + Timer + Stats -->
    <div class="mon-dashboard">
      <button class="mon-indicator status-gray" id="mon-indicator" onclick="openMonUrl()">
        ESPERANDO
      </button>
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

    <!-- Preview -->
    <div id="mon-preview-wrap" style="display:none; margin-top:1rem">
      <label class="field-label">Vista previa</label>
      <div class="preview-frame-wrap">
        <iframe id="mon-iframe" src="about:blank"></iframe>
      </div>
    </div>

    <div class="status-box" id="mon-status"></div>

    <div class="info-note">
      ℹ️ Haz clic en el indicador de color para abrir la URL en nueva pestaña. La primera comprobación establece la línea base; los cambios se detectan a partir de la segunda.
    </div>
  </div>
</section>
