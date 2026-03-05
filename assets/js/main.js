// ── MOBILE MENU ──────────────────────────────────────────────────────────────
function toggleMobileMenu() {
  document.getElementById('mobile-menu').classList.toggle('open');
}

// ── STATUS HELPERS ───────────────────────────────────────────────────────────
function showStatus(id, type, msg) {
  const el = document.getElementById(id);
  if (!el) return;
  el.className = 'status-box ' + type;
  el.textContent = msg;
}
function hideStatus(id) {
  const el = document.getElementById(id);
  if (!el) return;
  el.className = 'status-box';
  el.textContent = '';
}

// ── DOWNLOAD BLOB ─────────────────────────────────────────────────────────────
function downloadBlob(blob, filename) {
  const url = URL.createObjectURL(blob);
  const a = document.createElement('a');
  a.href = url; a.download = filename;
  document.body.appendChild(a); a.click();
  document.body.removeChild(a);
  setTimeout(() => URL.revokeObjectURL(url), 1000);
}

// ── DRAG & DROP (shared) ──────────────────────────────────────────────────────
function handleDragOver(e, id) {
  e.preventDefault();
  document.getElementById(id).classList.add('dragover');
}
function handleDragLeave(e, id) {
  document.getElementById(id).classList.remove('dragover');
}
function handleDrop(e, type) {
  e.preventDefault();
  const id = type === 'merge' ? 'merge-drop' : 'convert-drop';
  document.getElementById(id).classList.remove('dragover');
  const files = e.dataTransfer.files;
  if (type === 'merge') addMergeFiles(files);
  else if (files[0]) selectConvertFile(files[0]);
}
