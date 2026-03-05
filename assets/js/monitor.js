// ── MONITOR DE URLs ───────────────────────────────────────────────────────────
let monRunning   = false;
let monTimer     = null;
let monCheckInt  = null;
let monSeconds   = 0;
let monUrl       = '';
let monChecks    = 0;
let monChanges   = 0;
let monFirst     = true;

function openMonUrl() {
  if (monUrl) window.open(monUrl, '_blank');
}

function setMonIndicator(color, text) {
  const el = document.getElementById('mon-indicator');
  el.className = 'mon-indicator status-' + color;
  el.textContent = text;
}

function updateMonStats(hash) {
  document.getElementById('mon-stats').style.display  = 'flex';
  document.getElementById('mon-checks').textContent   = monChecks;
  document.getElementById('mon-changes').textContent  = monChanges;
  document.getElementById('mon-hash').textContent     = hash || '—';
}

async function doMonCheck() {
  setMonIndicator('gray', 'VERIFICANDO...');
  try {
    const fd = new FormData();
    fd.append('action', 'check');
    fd.append('url', monUrl);

    const res  = await fetch('index.php?page=monitor', { method: 'POST', body: fd });
    const data = await res.json();

    if (!data.success) {
      setMonIndicator('gray', 'ERROR');
      showStatus('mon-status', 'error', '❌ ' + (data.error || 'Error al verificar la URL'));
      return;
    }

    monChecks++;

    if (monFirst) {
      monFirst = false;
      setMonIndicator('green', 'SIN CAMBIOS');
      showStatus('mon-status', 'success', '✅ Monitorización iniciada. Primera captura registrada.');
      // Show preview iframe
      document.getElementById('mon-preview-wrap').style.display = 'block';
      document.getElementById('mon-iframe').src = 'index.php?page=monitor&proxy=1&t=' + Date.now();
    } else if (data.changed) {
      monChanges++;
      setMonIndicator('red', '¡CAMBIO!');
      showStatus('mon-status', 'error', '🔴 ¡Se detectaron cambios en la página! — ' + new Date().toLocaleTimeString());
      // Refresh preview
      document.getElementById('mon-iframe').src = 'index.php?page=monitor&proxy=1&t=' + Date.now();
    } else {
      setMonIndicator('green', 'SIN CAMBIOS');
      showStatus('mon-status', 'info', '✅ Sin cambios — ' + new Date().toLocaleTimeString());
    }

    updateMonStats(data.hash);

  } catch (err) {
    setMonIndicator('gray', 'ERROR');
    showStatus('mon-status', 'error', '❌ Error de conexión: ' + err.message);
  }
}

async function resetMonSession() {
  const fd = new FormData();
  fd.append('action', 'reset');
  await fetch('index.php?page=monitor', { method: 'POST', body: fd });
}

function toggleMonitor() {
  if (monRunning) stopMonitor();
  else startMonitor();
}

async function startMonitor() {
  const url = document.getElementById('mon-url').value.trim();
  if (!url || (!url.startsWith('http://') && !url.startsWith('https://'))) {
    showStatus('mon-status', 'error', '⚠️ Introduce una URL válida comenzando por http:// o https://');
    return;
  }

  monUrl     = url;
  monRunning = true;
  monSeconds = 0;
  monFirst   = true;
  monChecks  = 0;
  monChanges = 0;

  await resetMonSession();

  document.getElementById('mon-url').disabled  = true;
  document.getElementById('mon-btn').textContent = '⏹ Detener';
  document.getElementById('mon-btn').className   = 'btn btn-danger';

  monTimer = setInterval(() => {
    monSeconds++;
    const m = String(Math.floor(monSeconds / 60)).padStart(2, '0');
    const s = String(monSeconds % 60).padStart(2, '0');
    document.getElementById('mon-timer').textContent = `${m}:${s}`;
  }, 1000);

  await doMonCheck();
  monCheckInt = setInterval(doMonCheck, 60000);
}

function stopMonitor() {
  monRunning = false;
  clearInterval(monTimer);
  clearInterval(monCheckInt);

  document.getElementById('mon-url').disabled  = false;
  document.getElementById('mon-btn').textContent = '▶ Monitorizar';
  document.getElementById('mon-btn').className   = 'btn btn-accent';

  setMonIndicator('gray', 'DETENIDO');
  showStatus('mon-status', 'info', '⏸️ Monitorización detenida.');
}

// Enter key on URL input
document.addEventListener('DOMContentLoaded', () => {
  const inp = document.getElementById('mon-url');
  if (inp) inp.addEventListener('keypress', e => {
    if (e.key === 'Enter' && !monRunning) startMonitor();
  });
});
