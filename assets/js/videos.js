const VIDEO_API = 'video_api.php';

function formatDuration(secs) {
  if (!secs) return '';
  const h = Math.floor(secs / 3600);
  const m = Math.floor((secs % 3600) / 60);
  const s = secs % 60;
  if (h > 0) return `${h}:${String(m).padStart(2,'0')}:${String(s).padStart(2,'0')}`;
  return `${m}:${String(s).padStart(2,'0')}`;
}

function resetVideoInfo() {
  document.getElementById('video-info-card').style.display = 'none';
  hideStatus('video-status');
}

async function fetchVideoInfo() {
  const url = document.getElementById('video-url').value.trim();
  if (!url) { showStatus('video-status', 'error', '⚠️ Introduce una URL primero.'); return; }
  showStatus('video-status', 'loading', '🔍 Obteniendo información...');
  try {
    const fd = new FormData();
    fd.append('action', 'info');
    fd.append('url', url);
    const res  = await fetch(VIDEO_API, { method: 'POST', body: fd });
    const data = await res.json();
    if (data.success) {
      document.getElementById('video-thumb').src             = data.thumbnail || '';
      document.getElementById('video-title').textContent     = data.title    || 'Sin título';
      document.getElementById('video-uploader').textContent  = data.uploader || '';
      document.getElementById('video-duration').textContent  = data.duration ? '⏱ ' + formatDuration(data.duration) : '';
      document.getElementById('video-info-card').style.display = 'flex';
      hideStatus('video-status');
    } else {
      showStatus('video-status', 'error', '❌ No se pudo obtener información. Descarga directamente.');
    }
  } catch (e) {
    showStatus('video-status', 'error', '❌ Error: ' + e.message);
  }
}

async function startDownload(format) {
  const url = document.getElementById('video-url').value.trim();
  if (!url) { showStatus('video-status', 'error', '⚠️ Introduce una URL primero.'); return; }

  document.getElementById('btn-video').disabled = true;
  document.getElementById('btn-audio').disabled = true;
  document.getElementById('video-progress').style.display = 'block';
  document.getElementById('video-progress-text').textContent =
    format === 'audio' ? 'Enviando trabajo al servidor...' : 'Enviando trabajo al servidor...';
  showStatus('video-status', 'loading', '⏳ Añadiendo a la cola de descarga...');

  try {
    // 1. Encolar el trabajo
    const fd = new FormData();
    fd.append('action', 'download');
    fd.append('url', url);
    fd.append('format', format);
    const res  = await fetch(VIDEO_API, { method: 'POST', body: fd });
    const data = await res.json();

    if (!data.success) {
      showStatus('video-status', 'error', '❌ ' + (data.error || 'Error al encolar'));
      resetButtons(); return;
    }

    // 2. Polling hasta que esté listo (máx 5 min)
    showStatus('video-status', 'loading', '⏳ Procesando... el servidor está descargando el vídeo. Puede tardar hasta 2 minutos.');
    document.getElementById('video-progress-text').textContent = 'El cron job procesará tu solicitud en breve...';
    await pollForResult(data.jobid);

  } catch (e) {
    showStatus('video-status', 'error', '❌ Error: ' + e.message);
    resetButtons();
  }
}

async function pollForResult(jobId, attempts = 0) {
  if (attempts > 60) { // 5 minutos máximo (60 x 5s)
    showStatus('video-status', 'error', '❌ Tiempo de espera agotado. Inténtalo de nuevo.');
    resetButtons(); return;
  }

  await new Promise(r => setTimeout(r, 5000)); // esperar 5 segundos

  const fd = new FormData();
  fd.append('action', 'status');
  fd.append('jobid', jobId);

  try {
    const res    = await fetch(VIDEO_API, { method: 'POST', body: fd });
    const result = await res.json();

    const mins = Math.floor((attempts * 5) / 60);
    const secs = (attempts * 5) % 60;
    document.getElementById('video-progress-text').textContent =
      `Esperando resultado... ${mins}:${String(secs).padStart(2,'0')} — Estado: ${result.status || 'en cola'}`;

    if (result.status === 'done') {
      showStatus('video-status', 'success', `✅ Listo — descargando "${result.filename}"`);
      document.getElementById('video-progress-text').textContent = 'Descargando archivo...';
      window.location.href = VIDEO_API + '?serve=1&jobid=' + encodeURIComponent(jobId);
      resetButtons();
    } else if (result.status === 'error') {
      showStatus('video-status', 'error', '❌ Error: ' + (result.error || 'Error desconocido'));
      resetButtons();
    } else {
      // queued o processing — seguir esperando
      await pollForResult(jobId, attempts + 1);
    }
  } catch (e) {
    await pollForResult(jobId, attempts + 1);
  }
}

function resetButtons() {
  document.getElementById('btn-video').disabled = false;
  document.getElementById('btn-audio').disabled = false;
  document.getElementById('video-progress').style.display = 'none';
}

function clearVideoUrl() {
  document.getElementById('video-url').value = '';
  resetVideoInfo();
}

document.addEventListener('DOMContentLoaded', () => {
  const inp = document.getElementById('video-url');
  if (inp) inp.addEventListener('keypress', e => {
    if (e.key === 'Enter') fetchVideoInfo();
  });
});
