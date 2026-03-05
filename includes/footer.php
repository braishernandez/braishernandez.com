<div class="contact-strip">
  <h2>¿Hablamos?</h2>
  <p>Siempre abierto a nuevos proyectos y oportunidades.</p>
  <div class="contact-links">
    <a href="mailto:brais@braishernandez.com" class="contact-link">✉ brais@braishernandez.com</a>
    <a href="https://linkedin.com/in/brais-hernandez-89890335" target="_blank" class="contact-link">in LinkedIn</a>
  </div>
  <p class="footer-copy">© <?= date('Y') ?> Brais Hernández Facal · <a href="index.php?page=cookies" style="color:var(--muted);text-decoration:none;font-size:.8rem">Política de cookies y privacidad</a></p>
</div>

<!-- BANNER COOKIES -->
<div id="cookie-banner" style="display:none;position:fixed;bottom:0;left:0;right:0;background:#1a1a1a;color:#fff;padding:1rem 1.5rem;z-index:9999;display:flex;align-items:center;justify-content:space-between;gap:1rem;flex-wrap:wrap;font-size:.85rem">
  <span>🍪 Esta web usa cookies técnicas necesarias para su funcionamiento. No usamos cookies analíticas ni publicitarias. <a href="index.php?page=cookies" style="color:#93c5fd">Más info</a></span>
  <button onclick="aceptarCookies()" style="background:#2563eb;color:#fff;border:none;padding:.5rem 1.25rem;border-radius:8px;cursor:pointer;font-weight:600;white-space:nowrap">Entendido</button>
</div>
<script>
(function() {
  if (!localStorage.getItem('cookies_ok')) {
    document.getElementById('cookie-banner').style.display = 'flex';
  }
})();
function aceptarCookies() {
  localStorage.setItem('cookies_ok', '1');
  document.getElementById('cookie-banner').style.display = 'none';
}
</script>
