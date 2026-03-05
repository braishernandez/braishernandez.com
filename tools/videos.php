<section class="section-page">
  <div class="section-header">
    <p class="section-label">Herramienta</p>
    <h2>📥 Descargador de Vídeos y Audio</h2>
    <p>Descarga vídeos de YouTube, Instagram, TikTok y otras redes directamente a tu dispositivo.</p>
  </div>

  <div class="tool-box">
    <h3>Enlace del vídeo</h3>
    <p class="desc">Pega el enlace del vídeo. Pulsa "Info" para previsualizar antes de descargar.</p>

    <div class="input-row">
      <input type="url" id="video-url" placeholder="https://www.youtube.com/watch?v=..." oninput="resetVideoInfo()" />
      <button class="btn btn-outline" onclick="fetchVideoInfo()">🔍 Info</button>
      <button class="btn btn-outline btn-sm" onclick="clearVideoUrl()">✕</button>
    </div>

    <div id="video-info-card" style="display:none" class="video-info-card">
      <img id="video-thumb" src="" alt="thumbnail" />
      <div class="video-info-meta">
        <div id="video-title" class="video-title"></div>
        <div id="video-uploader" class="video-uploader"></div>
        <div id="video-duration" class="video-duration"></div>
      </div>
    </div>

    <div class="btn-row" style="margin-top:1rem">
      <button class="btn btn-accent"  id="btn-video" onclick="startDownload('video')">⬇ Descargar Vídeo</button>
      <button class="btn btn-success" id="btn-audio" onclick="startDownload('audio')">🎵 Descargar MP3</button>
    </div>

    <div id="video-progress" style="display:none; margin-top:0.75rem">
      <div class="progress-bar-wrap">
        <div class="progress-bar-indeterminate"></div>
      </div>
      <p id="video-progress-text" style="font-size:0.78rem;color:var(--muted);margin-top:0.4rem">Procesando...</p>
    </div>

    <div class="status-box" id="video-status"></div>
  </div>

  <div class="tool-box">
    <h3>🔗 Servicios externos alternativos</h3>
    <p class="desc">Si alguna plataforma no es compatible con el descargador directo, usa estos servicios.</p>
    <div class="services-grid">
      <a href="https://cobalt.tools" target="_blank" class="service-card">
        <div class="service-icon">🌐</div>
        <div class="service-name">cobalt.tools</div>
        <div class="service-desc">Universal</div>
      </a>
      <a href="https://snapinsta.app" target="_blank" class="service-card">
        <div class="service-icon">📸</div>
        <div class="service-name">snapinsta</div>
        <div class="service-desc">Instagram</div>
      </a>
      <a href="https://ssstik.io" target="_blank" class="service-card">
        <div class="service-icon">🎵</div>
        <div class="service-name">ssstik</div>
        <div class="service-desc">TikTok</div>
      </a>
      <a href="https://twitsave.com" target="_blank" class="service-card">
        <div class="service-icon">🐦</div>
        <div class="service-name">twitsave</div>
        <div class="service-desc">Twitter / X</div>
      </a>
    </div>
  </div>
</section>

<style>
.video-info-card {
  display: flex; gap: 1rem; align-items: flex-start;
  background: var(--bg); border: 1px solid var(--border);
  border-radius: 10px; padding: 1rem; margin-top: 0.75rem;
}
.video-info-card img {
  width: 140px; height: 80px; object-fit: cover;
  border-radius: 6px; flex-shrink: 0; background: var(--border);
}
.video-info-meta { flex: 1; min-width: 0; }
.video-title    { font-weight: 600; font-size: 0.9rem; line-height: 1.3; margin-bottom: 0.25rem; }
.video-uploader { font-size: 0.78rem; color: var(--muted); }
.video-duration { font-size: 0.78rem; color: var(--accent); margin-top: 0.2rem; font-weight: 500; }
.progress-bar-wrap {
  height: 5px; background: var(--border); border-radius: 3px; overflow: hidden;
}
.progress-bar-indeterminate {
  height: 100%; width: 40%; background: var(--accent); border-radius: 3px;
  animation: progressSlide 1.4s ease-in-out infinite;
}
@keyframes progressSlide {
  0%   { transform: translateX(-100%); }
  100% { transform: translateX(350%); }
}
</style>
