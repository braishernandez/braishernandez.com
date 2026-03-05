<section class="section-page">
  <div class="section-header">
    <p class="section-label">Herramienta</p>
    <h2>📄 Herramientas PDF</h2>
    <p>Une varios PDFs en uno o convierte un PDF a otros formatos, todo en tu navegador.</p>
  </div>

  <!-- UNIR PDFs -->
  <div class="tool-box">
    <h3>📎 Unir varios PDF en uno</h3>
    <p class="desc">Sube múltiples archivos PDF y se combinarán en el orden que aparecen. El procesamiento es 100% local, tus archivos no se suben a ningún servidor.</p>

    <div class="drop-zone" id="merge-drop"
         onclick="document.getElementById('merge-input').click()"
         ondragover="handleDragOver(event,'merge-drop')"
         ondragleave="handleDragLeave(event,'merge-drop')"
         ondrop="handleDrop(event,'merge')">
      <div class="drop-icon">📂</div>
      <p><strong>Haz clic</strong> o arrastra tus PDFs aquí</p>
      <p class="drop-hint">Solo archivos .pdf · Puedes añadir varios a la vez</p>
    </div>
    <input type="file" id="merge-input" multiple accept=".pdf" style="display:none" onchange="addMergeFiles(this.files)">

    <ul class="file-list" id="merge-file-list"></ul>

    <div class="btn-row">
      <button class="btn btn-accent" onclick="mergePDFs()">🔗 Unir y Descargar</button>
      <button class="btn btn-outline" onclick="clearMerge()">✕ Limpiar</button>
    </div>
    <div class="status-box" id="merge-status"></div>
  </div>

  <!-- CONVERTIR PDF -->
  <div class="tool-box">
    <h3>🔄 Convertir PDF</h3>
    <p class="desc">Sube un PDF y conviértelo a imágenes JPG (una por página) directamente en el navegador. La conversión a Word usa un servicio externo de confianza.</p>

    <div class="drop-zone" id="convert-drop"
         onclick="document.getElementById('convert-input').click()"
         ondragover="handleDragOver(event,'convert-drop')"
         ondragleave="handleDragLeave(event,'convert-drop')"
         ondrop="handleDrop(event,'convert')">
      <div class="drop-icon">📄</div>
      <p id="convert-drop-text"><strong>Haz clic</strong> o arrastra un PDF aquí</p>
    </div>
    <input type="file" id="convert-input" accept=".pdf" style="display:none" onchange="selectConvertFile(this.files[0])">

    <div class="btn-row" style="margin-top:0.75rem">
      <button class="btn btn-accent" onclick="convertTo('jpg')">🖼 Convertir a JPG</button>
      <button class="btn btn-outline" onclick="convertTo('docx')">📝 Convertir a Word</button>
      <button class="btn btn-outline" onclick="clearConvert()">✕ Limpiar</button>
    </div>
    <div class="status-box" id="convert-status"></div>

    <div class="info-note" style="margin-top:1rem">
      <strong>Conversión a Word:</strong> Servicios externos gratuitos recomendados:
      <div class="btn-row" style="margin-top:0.5rem">
        <a href="https://ilovepdf.com/es/pdf_a_word" target="_blank" class="btn btn-outline btn-sm">iLovePDF</a>
        <a href="https://smallpdf.com/es/pdf-a-word" target="_blank" class="btn btn-outline btn-sm">SmallPDF</a>
        <a href="https://pdf2doc.com/es" target="_blank" class="btn btn-outline btn-sm">PDF2Doc</a>
      </div>
    </div>
  </div>
</section>
