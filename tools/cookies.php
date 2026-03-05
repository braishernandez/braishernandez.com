<section class="section-page">
  <div class="section-header">
    <p class="section-label">Legal</p>
    <h2>🍪 Política de Cookies y Privacidad</h2>
    <p>Información sobre el uso de cookies y el tratamiento de datos en esta web.</p>
  </div>

  <div class="tool-box">
    <h3>¿Qué son las cookies?</h3>
    <p class="desc">Las cookies son pequeños archivos de texto que los sitios web almacenan en tu navegador para recordar información entre visitas. Esta web hace un uso mínimo y responsable de las cookies.</p>
  </div>

  <div class="tool-box">
    <h3>Cookies que utiliza esta web</h3>
    <p class="desc">Esta web utiliza únicamente cookies <strong>técnicas y necesarias</strong> para su correcto funcionamiento. No se utilizan cookies analíticas, de seguimiento ni publicitarias.</p>

    <table class="cookie-table">
      <thead>
        <tr><th>Nombre</th><th>Tipo</th><th>Finalidad</th><th>Duración</th></tr>
      </thead>
      <tbody>
        <tr>
          <td><code>PHPSESSID</code></td>
          <td>Técnica</td>
          <td>Sesión del navegador. Necesaria para el funcionamiento del monitor de URLs y el procesamiento de pedidos.</td>
          <td>Sesión (se elimina al cerrar el navegador)</td>
        </tr>
        <tr>
          <td><code>farmasi_datos</code></td>
          <td>Preferencia (localStorage)</td>
          <td>Guarda opcionalmente tus datos de envío para facilitar futuros pedidos en la sección Farmasi. Solo se activa si marcas "Recordar mis datos".</td>
          <td>Permanente hasta que la elimines manualmente</td>
        </tr>
        <tr>
          <td>Cookies de PayPal</td>
          <td>Terceros (necesaria)</td>
          <td>Cuando realizas un pago a través de PayPal en la sección Farmasi, PayPal establece sus propias cookies para gestionar el proceso de pago de forma segura.</td>
          <td>Según la política de PayPal</td>
        </tr>
      </tbody>
    </table>

    <div class="info-note" style="margin-top:1rem">
      ℹ️ Al no utilizarse cookies analíticas ni publicitarias, <strong>no es necesario tu consentimiento explícito</strong> según la normativa española (LSSI) y el RGPD para las cookies técnicas.
    </div>
  </div>

  <div class="tool-box">
    <h3>Tratamiento de datos personales</h3>
    <p class="desc">Los únicos datos personales que esta web puede recoger son los que introduzcas voluntariamente en el formulario de pedido de la sección Farmasi (nombre, email, teléfono y dirección de entrega).</p>
    <ul style="margin-top:.75rem;padding-left:1.25rem;font-size:.9rem;line-height:2">
      <li><strong>Responsable:</strong> Brais Hernández Facal</li>
      <li><strong>Finalidad:</strong> Gestión del pedido y comunicación sobre el mismo</li>
      <li><strong>Legitimación:</strong> Ejecución de un contrato (tu pedido)</li>
      <li><strong>Conservación:</strong> Durante el tiempo necesario para gestionar el pedido</li>
      <li><strong>Derechos:</strong> Puedes ejercer tus derechos de acceso, rectificación, supresión y portabilidad escribiendo a <a href="mailto:farmasi@braishernandez.com">farmasi@braishernandez.com</a></li>
    </ul>
  </div>

  <div class="tool-box">
    <h3>Cómo gestionar o eliminar las cookies</h3>
    <p class="desc">Puedes configurar tu navegador para bloquear o eliminar las cookies en cualquier momento:</p>
    <div class="btn-row" style="margin-top:.75rem;flex-wrap:wrap">
      <a href="https://support.google.com/chrome/answer/95647" target="_blank" class="btn btn-outline btn-sm">Chrome</a>
      <a href="https://support.mozilla.org/es/kb/habilitar-y-deshabilitar-cookies-sitios-web-rastrear-preferencias" target="_blank" class="btn btn-outline btn-sm">Firefox</a>
      <a href="https://support.apple.com/es-es/guide/safari/sfri11471/mac" target="_blank" class="btn btn-outline btn-sm">Safari</a>
      <a href="https://support.microsoft.com/es-es/windows/eliminar-y-administrar-cookies-168dab11-0753-043d-7c16-ede5947fc64d" target="_blank" class="btn btn-outline btn-sm">Edge</a>
    </div>
    <p class="desc" style="margin-top:.75rem">Para eliminar los datos guardados en localStorage (datos de envío Farmasi), puedes hacerlo desde las herramientas de desarrollador de tu navegador (F12 → Application → Local Storage) o pulsando el botón de abajo:</p>
    <button class="btn btn-outline" onclick="borrarDatosGuardados()" style="margin-top:.5rem">🗑️ Eliminar mis datos guardados</button>
    <div class="status-box" id="cookies-status"></div>
  </div>

  <div class="tool-box">
    <h3>Contacto</h3>
    <p class="desc">Para cualquier consulta sobre privacidad o el uso de cookies puedes contactar en: <a href="mailto:farmasi@braishernandez.com">farmasi@braishernandez.com</a></p>
    <p class="desc" style="margin-top:.5rem;font-size:.8rem;color:var(--muted)">Última actualización: <?= date('d/m/Y') ?></p>
  </div>
</section>

<style>
.cookie-table { width: 100%; border-collapse: collapse; font-size: .85rem; margin-top: .75rem; }
.cookie-table th { background: var(--bg); padding: .6rem .75rem; text-align: left; font-size: .78rem; color: var(--muted); border-bottom: 2px solid var(--border); }
.cookie-table td { padding: .65rem .75rem; border-bottom: 1px solid var(--border); vertical-align: top; line-height: 1.5; }
.cookie-table code { background: var(--bg); padding: .15rem .4rem; border-radius: 4px; font-size: .82rem; }
</style>

<script>
function borrarDatosGuardados() {
  localStorage.removeItem('farmasi_datos');
  showStatus('cookies-status', 'success', '✅ Datos de envío eliminados correctamente.');
}
</script>
