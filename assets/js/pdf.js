// ── PDF MERGE ─────────────────────────────────────────────────────────────────
let mergeFiles = [];

function addMergeFiles(files) {
  for (const f of files) {
    if (f.type === 'application/pdf' && !mergeFiles.find(x => x.name === f.name))
      mergeFiles.push(f);
  }
  renderMergeList();
}

function renderMergeList() {
  const ul = document.getElementById('merge-file-list');
  ul.innerHTML = '';
  mergeFiles.forEach((f, i) => {
    const li = document.createElement('li');
    li.innerHTML = `<span>📄 ${f.name} <span style="color:var(--muted)">(${(f.size/1024).toFixed(0)} KB)</span></span>
      <button class="remove" onclick="removeMergeFile(${i})">✕</button>`;
    ul.appendChild(li);
  });
}

function removeMergeFile(i) { mergeFiles.splice(i, 1); renderMergeList(); }
function clearMerge() { mergeFiles = []; renderMergeList(); hideStatus('merge-status'); }

async function mergePDFs() {
  if (mergeFiles.length < 2) {
    showStatus('merge-status', 'error', '⚠️ Necesitas al menos 2 archivos PDF para unirlos.');
    return;
  }
  showStatus('merge-status', 'loading', '⏳ Uniendo PDFs, por favor espera...');
  try {
    const { PDFDocument } = PDFLib;
    const merged = await PDFDocument.create();
    for (const file of mergeFiles) {
      const buf  = await file.arrayBuffer();
      const pdf  = await PDFDocument.load(buf);
      const pages = await merged.copyPages(pdf, pdf.getPageIndices());
      pages.forEach(p => merged.addPage(p));
    }
    const bytes = await merged.save();
    downloadBlob(new Blob([bytes], { type: 'application/pdf' }), 'unido.pdf');
    showStatus('merge-status', 'success', `✅ PDF unido descargado — ${merged.getPageCount()} páginas en total.`);
  } catch (e) {
    showStatus('merge-status', 'error', '❌ Error al unir los PDFs: ' + e.message);
  }
}

// ── PDF CONVERT ───────────────────────────────────────────────────────────────
let convertFile = null;

function selectConvertFile(file) {
  convertFile = file;
  document.getElementById('convert-drop-text').innerHTML =
    `<strong>📄 ${file.name}</strong> — listo para convertir`;
}

function clearConvert() {
  convertFile = null;
  document.getElementById('convert-drop-text').innerHTML =
    `<strong>Haz clic</strong> o arrastra un PDF aquí`;
  document.getElementById('convert-input').value = '';
  hideStatus('convert-status');
}

function convertTo(format) {
  if (!convertFile) {
    showStatus('convert-status', 'error', '⚠️ Por favor selecciona un archivo PDF primero.');
    return;
  }
  if (format === 'jpg') convertToJPG();
  else showStatus('convert-status', 'info',
    '📝 La conversión a Word requiere procesamiento externo. Usa iLovePDF o SmallPDF (enlaces abajo) — son gratuitos y seguros.');
}

async function convertToJPG() {
  showStatus('convert-status', 'loading', '⏳ Renderizando páginas del PDF...');
  try {
    const pdfjsLib = window['pdfjs-dist/build/pdf'];
    pdfjsLib.GlobalWorkerOptions.workerSrc =
      'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';

    const buf  = await convertFile.arrayBuffer();
    const pdf  = await pdfjsLib.getDocument({ data: buf }).promise;
    const n    = pdf.numPages;
    showStatus('convert-status', 'loading', `⏳ Convirtiendo ${n} página(s) a JPG...`);

    for (let i = 1; i <= n; i++) {
      const page     = await pdf.getPage(i);
      const viewport = page.getViewport({ scale: 2.0 });
      const canvas   = document.createElement('canvas');
      canvas.width   = viewport.width;
      canvas.height  = viewport.height;
      await page.render({ canvasContext: canvas.getContext('2d'), viewport }).promise;
      await new Promise(resolve => canvas.toBlob(blob => {
        const name = n > 1
          ? `pagina_${i}.jpg`
          : convertFile.name.replace('.pdf', '') + '.jpg';
        downloadBlob(blob, name);
        resolve();
      }, 'image/jpeg', 0.92));
    }
    showStatus('convert-status', 'success', `✅ ${n} imagen(es) JPG descargada(s) correctamente.`);
  } catch (e) {
    showStatus('convert-status', 'error', '❌ Error al convertir: ' + e.message);
  }
}
