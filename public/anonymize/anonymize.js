// Anonimizacja tekstu i PDF w przeglądarce (panel /admin/anonimizacja). Żadne dane nie opuszczają
// przeglądarki. PDF: pdf.js renderuje strony i podaje położenie tekstu, wykryte fragmenty są
// zamalowywane na obrazie strony, a wynik składa pdf-lib z samych obrazów — bez warstwy tekstowej
// i metadanych, więc zamalowanych danych nie da się odzyskać.
import * as pdfjs from './vendor/pdf.min.mjs';

pdfjs.GlobalWorkerOptions.workerSrc = new URL('./vendor/pdf.worker.min.mjs', import.meta.url).href;

const $ = (id) => document.getElementById(id);
const PHRASES_KEY = 'radzymin:anonimizacja:frazy';
const PREVIEW_SCALE = 1.5;
const PAD = 2; // margines wokół zamalowywanego tekstu, w punktach PDF

// --- Frazy do zamazania (wspólne dla tekstu i PDF) ---------------------------------------------
const phrasesInput = $('anon-phrases');
try { phrasesInput.value = localStorage.getItem(PHRASES_KEY) || ''; } catch { /* brak storage */ }
phrasesInput.addEventListener('input', () => {
  try { localStorage.setItem(PHRASES_KEY, phrasesInput.value); } catch { /* brak storage */ }
});
const phrases = () => phrasesInput.value.split(',').map((x) => x.trim()).filter(Boolean);

// --- Tekst -------------------------------------------------------------------------------------
$('anon-text-run').addEventListener('click', () => {
  const { text, replaced } = PiiCheck.anonymizeText($('anon-text-in').value, phrases());
  $('anon-text-out').value = text;
  $('anon-text-copy').disabled = false;
  $('anon-text-status').textContent = replaced.length
    ? `Zamieniono: ${replaced.length} (${[...new Set(replaced.map((r) => r.type))].join(', ')})`
    : 'Nie wykryto niczego do zamiany.';
});
$('anon-text-copy').addEventListener('click', async () => {
  try {
    await navigator.clipboard.writeText($('anon-text-out').value);
    $('anon-text-status').textContent = 'Skopiowano.';
  } catch {
    $('anon-text-out').select();
    $('anon-text-status').textContent = 'Zaznaczono wynik — skopiuj ręcznie (Ctrl/Cmd+C).';
  }
});

// --- PDF ---------------------------------------------------------------------------------------
/** @type {import('pdfjs-dist').PDFDocumentProxy | null} */
let doc = null;
let docTask = null;
let fileName = 'dokument';
let pageNo = 1;
let hasTextLayer = false;
/** Prostokąty w punktach PDF (widok 1:1): numer strony -> [{x, y, w, h, auto}] */
let boxes = new Map();
let viewport = null;
let renderToken = 0;

const canvas = $('anon-canvas');
const stage = $('anon-stage');
const status = (msg) => { $('anon-pdf-status').textContent = msg; };

$('anon-pdf-file').addEventListener('change', async (event) => {
  const file = event.target.files[0];
  if (!file) return;
  fileName = file.name.replace(/\.pdf$/i, '');
  status('Wczytywanie…');
  try {
    if (docTask) await docTask.destroy();
    docTask = pdfjs.getDocument({ data: new Uint8Array(await file.arrayBuffer()) });
    doc = await docTask.promise;
  } catch {
    doc = null;
    status('Nie udało się otworzyć pliku (uszkodzony lub zaszyfrowany PDF).');
    return;
  }
  boxes = new Map();
  pageNo = 1;
  $('anon-pdf-ui').hidden = false;
  const format = $('anon-pdf-format');
  format.querySelector('option[value="webp-anim"]').disabled = doc.numPages < 2;
  if (format.value === 'webp-anim' && doc.numPages < 2) format.value = 'pdf';
  await detectAll();
  await showPage();
});

/** Tekst strony + położenie każdego elementu, żeby wykryty przedział zamienić na prostokąty. */
async function pageTextItems(page) {
  const vp = page.getViewport({ scale: 1 });
  const content = await page.getTextContent();
  let text = '';
  const items = [];
  for (const item of content.items) {
    if (typeof item.str !== 'string') continue;
    const tx = pdfjs.Util.transform(vp.transform, item.transform);
    const start = text.length;
    text += item.str;
    items.push({ start, end: text.length, x: tx[4], y: tx[5] - item.height, w: item.width, h: item.height });
    if (item.hasEOL) text += '\n';
  }
  return { text, items };
}

function rangesToBoxes(ranges, items) {
  const found = [];
  for (const r of ranges) {
    for (const it of items) {
      const len = it.end - it.start;
      if (len === 0 || r.end <= it.start || r.start >= it.end) continue;
      const a = (Math.max(r.start, it.start) - it.start) / len;
      const b = (Math.min(r.end, it.end) - it.start) / len;
      found.push({ x: it.x + it.w * a - PAD, y: it.y - PAD, w: it.w * (b - a) + 2 * PAD, h: it.h + 2 * PAD, auto: true });
    }
  }
  return found;
}

async function detectAll() {
  hasTextLayer = false;
  for (let n = 1; n <= doc.numPages; n++) {
    status(`Wykrywanie danych: strona ${n} z ${doc.numPages}…`);
    const page = await doc.getPage(n);
    const { text, items } = await pageTextItems(page);
    page.cleanup();
    if (text.trim() !== '') hasTextLayer = true;
    const manual = (boxes.get(n) || []).filter((b) => !b.auto);
    boxes.set(n, manual.concat(rangesToBoxes(PiiCheck.findRanges(text, phrases()), items)));
  }
  const total = [...boxes.values()].reduce((sum, list) => sum + list.length, 0);
  status(hasTextLayer
    ? `Wykryto fragmentów: ${total}. Sprawdź każdą stronę i dorysuj to, czego brakuje.`
    : 'Brak warstwy tekstowej (skan?) — nic nie wykryto automatycznie, zamaluj dane ręcznie.');
}

async function showPage() {
  const token = ++renderToken;
  const page = await doc.getPage(pageNo);
  viewport = page.getViewport({ scale: PREVIEW_SCALE });
  canvas.width = Math.floor(viewport.width);
  canvas.height = Math.floor(viewport.height);
  await page.render({ canvas, canvasContext: canvas.getContext('2d'), viewport }).promise;
  page.cleanup();
  if (token !== renderToken) return;
  $('anon-page-info').textContent = `Strona ${pageNo} z ${doc.numPages}`;
  $('anon-prev').disabled = pageNo <= 1;
  $('anon-next').disabled = pageNo >= doc.numPages;
  drawBoxes();
}

function boxElement(box) {
  const el = document.createElement('div');
  el.className = 'anon-box' + (box.auto ? '' : ' anon-box-manual');
  el.title = 'Kliknij, aby usunąć';
  el.style.cssText = `left:${(box.x / (viewport.width / PREVIEW_SCALE)) * 100}%;top:${(box.y / (viewport.height / PREVIEW_SCALE)) * 100}%;` +
    `width:${(box.w / (viewport.width / PREVIEW_SCALE)) * 100}%;height:${(box.h / (viewport.height / PREVIEW_SCALE)) * 100}%`;
  el.addEventListener('pointerdown', (e) => e.stopPropagation());
  el.addEventListener('click', () => {
    boxes.set(pageNo, boxes.get(pageNo).filter((b) => b !== box));
    drawBoxes();
  });
  return el;
}

function drawBoxes() {
  stage.querySelectorAll('.anon-box').forEach((el) => el.remove());
  (boxes.get(pageNo) || []).forEach((box) => stage.appendChild(boxElement(box)));
}

// Rysowanie własnych prostokątów (mysz/dotyk) w punktach PDF.
let drag = null;
stage.addEventListener('pointerdown', (e) => {
  if (!viewport) return;
  const rect = canvas.getBoundingClientRect();
  drag = { rect, x0: e.clientX - rect.left, y0: e.clientY - rect.top, el: document.createElement('div') };
  drag.el.className = 'anon-box anon-box-draft';
  stage.appendChild(drag.el);
  stage.setPointerCapture(e.pointerId);
});
stage.addEventListener('pointermove', (e) => {
  if (!drag) return;
  const x = Math.min(Math.max(e.clientX - drag.rect.left, 0), drag.rect.width);
  const y = Math.min(Math.max(e.clientY - drag.rect.top, 0), drag.rect.height);
  drag.x1 = x; drag.y1 = y;
  Object.assign(drag.el.style, {
    left: `${Math.min(drag.x0, x)}px`, top: `${Math.min(drag.y0, y)}px`,
    width: `${Math.abs(x - drag.x0)}px`, height: `${Math.abs(y - drag.y0)}px`,
  });
});
stage.addEventListener('pointerup', () => {
  if (!drag) return;
  const k = viewport.width / PREVIEW_SCALE / drag.rect.width; // px na ekranie -> punkty PDF
  const { x0, y0, x1 = x0, y1 = y0 } = drag;
  drag.el.remove();
  const w = Math.abs(x1 - x0) * k;
  const h = Math.abs(y1 - y0) * k;
  if (w > 3 && h > 3) {
    boxes.get(pageNo).push({ x: Math.min(x0, x1) * k, y: Math.min(y0, y1) * k, w, h, auto: false });
  }
  drag = null;
  drawBoxes();
});

$('anon-prev').addEventListener('click', () => { pageNo -= 1; showPage(); });
$('anon-next').addEventListener('click', () => { pageNo += 1; showPage(); });
$('anon-clear-page').addEventListener('click', () => { boxes.set(pageNo, []); drawBoxes(); });
$('anon-redetect').addEventListener('click', async () => { await detectAll(); drawBoxes(); });

// --- Eksport: strony jako obrazy z zamalowanymi prostokątami -> PDF bez tekstu (lub WebP: osobne pliki albo animacja)
const EXPORT_QUALITY = 0.85;

function download(blob, name) {
  const url = URL.createObjectURL(blob);
  Object.assign(document.createElement('a'), { href: url, download: name }).click();
  setTimeout(() => URL.revokeObjectURL(url), 10_000);
}

/** Strona z zamalowanymi prostokątami jako obraz (JPEG do PDF, WebP do obrazów). */
async function renderRedactedPage(n, scale, mime) {
  const page = await doc.getPage(n);
  const base = page.getViewport({ scale: 1 });
  const vp = page.getViewport({ scale });
  const c = document.createElement('canvas');
  c.width = Math.floor(vp.width);
  c.height = Math.floor(vp.height);
  const pixelWidth = c.width;
  const pixelHeight = c.height;
  const ctx = c.getContext('2d');
  await page.render({ canvas: c, canvasContext: ctx, viewport: vp }).promise;
  page.cleanup();
  ctx.fillStyle = '#000';
  for (const b of boxes.get(n) || []) ctx.fillRect(b.x * scale, b.y * scale, b.w * scale, b.h * scale);
  const blob = await new Promise((resolve) => c.toBlob(resolve, mime, EXPORT_QUALITY));
  c.width = c.height = 0; // zwolnij pamięć od razu
  if (!blob || blob.type !== mime) {
    throw new Error(`ta przeglądarka nie potrafi zapisać ${mime} — wybierz PDF albo inną przeglądarkę`);
  }
  return { blob, width: base.width, height: base.height, pixelWidth, pixelHeight };
}

async function exportPdf(scale) {
  const out = await PDFLib.PDFDocument.create({ updateMetadata: false });
  for (let n = 1; n <= doc.numPages; n++) {
    status(`Składanie PDF: strona ${n} z ${doc.numPages}…`);
    const { blob, width, height } = await renderRedactedPage(n, scale, 'image/jpeg');
    const image = await out.embedJpg(new Uint8Array(await blob.arrayBuffer()));
    out.addPage([width, height]).drawImage(image, { x: 0, y: 0, width, height });
  }
  const bytes = await out.save();

  // Kontrola: wynik nie może zawierać żadnego tekstu.
  const checkTask = pdfjs.getDocument({ data: bytes.slice() });
  const check = await checkTask.promise;
  let leaked = 0;
  for (let n = 1; n <= check.numPages; n++) {
    const content = await (await check.getPage(n)).getTextContent();
    leaked += content.items.reduce((sum, it) => sum + (it.str || '').length, 0);
  }
  await checkTask.destroy();
  if (leaked > 0) throw new Error('wynik nadal zawiera tekst');

  download(new Blob([bytes], { type: 'application/pdf' }), `${fileName}-zanonimizowany.pdf`);
  return `Gotowe (${(bytes.length / 1048576).toFixed(1)} MB). Sprawdzono: wynik nie zawiera warstwy tekstowej.`;
}

async function exportWebpFiles(scale) {
  const pad = String(doc.numPages).length;
  for (let n = 1; n <= doc.numPages; n++) {
    status(`Zapisywanie WebP: strona ${n} z ${doc.numPages}…`);
    const { blob } = await renderRedactedPage(n, scale, 'image/webp');
    download(blob, `${fileName}-zanonimizowany-${String(n).padStart(pad, '0')}.webp`);
    await new Promise((resolve) => setTimeout(resolve, 300)); // przeglądarki blokują lawinę pobrań
  }
  return doc.numPages > 1
    ? `Gotowe: ${doc.numPages} plików WebP. Jeśli przeglądarka zapytała o zgodę na wiele pobrań — zezwól.`
    : 'Gotowe.';
}

// --- Animowany WebP (slajdy): sklejamy pojedyncze obrazy WebP w kontener z klatkami ANMF -------
const u32 = (n) => [n & 255, (n >>> 8) & 255, (n >>> 16) & 255, (n >>> 24) & 255];
const u24 = (n) => u32(n).slice(0, 3);
const ascii = (text) => [...text].map((ch) => ch.charCodeAt(0));

function riffChunk(fourcc, data) {
  const bytes = [...ascii(fourcc), ...u32(data.length)];
  const out = new Uint8Array(bytes.length + data.length + (data.length & 1));
  out.set(bytes);
  out.set(data, bytes.length);
  return out;
}

/** Wyciąga z pojedynczego WebP tylko dane obrazu (ALPH + VP8/VP8L), bez nagłówków VP8X i metadanych. */
function webpImageChunks(file) {
  const chunks = [];
  for (let pos = 12; pos + 8 <= file.length;) {
    const tag = String.fromCharCode(...file.subarray(pos, pos + 4));
    const size = file[pos + 4] | (file[pos + 5] << 8) | (file[pos + 6] << 16) | (file[pos + 7] << 24);
    const end = pos + 8 + size + (size & 1);
    if (tag === 'VP8 ' || tag === 'VP8L' || tag === 'ALPH') chunks.push(file.subarray(pos, end));
    pos = end;
  }
  if (chunks.length === 0) throw new Error('nieznany format obrazu WebP');
  return chunks;
}

function concat(parts) {
  const out = new Uint8Array(parts.reduce((sum, p) => sum + p.length, 0));
  let offset = 0;
  for (const p of parts) { out.set(p, offset); offset += p.length; }
  return out;
}

function buildAnimatedWebp(frames, durationMs) {
  const width = Math.max(...frames.map((f) => f.width));
  const height = Math.max(...frames.map((f) => f.height));
  const body = [
    ...ascii('WEBP'),
  ];
  const parts = [
    Uint8Array.from(body),
    riffChunk('VP8X', Uint8Array.from([0x02, 0, 0, 0, ...u24(width - 1), ...u24(height - 1)])),
    riffChunk('ANIM', Uint8Array.from([255, 255, 255, 255, 0, 0])), // białe tło, pętla bez końca
  ];
  for (const frame of frames) {
    const header = Uint8Array.from([0, 0, 0, 0, 0, 0, ...u24(frame.width - 1), ...u24(frame.height - 1), ...u24(durationMs), 0x03]);
    parts.push(riffChunk('ANMF', concat([header, ...webpImageChunks(frame.bytes)])));
  }
  const payload = concat(parts);
  return new Blob([Uint8Array.from([...ascii('RIFF'), ...u32(payload.length)]), payload], { type: 'image/webp' });
}

async function exportWebpAnimation(scale) {
  const seconds = Math.min(60, Math.max(1, Number($('anon-slide-seconds').value) || 4));
  const frames = [];
  for (let n = 1; n <= doc.numPages; n++) {
    status(`Składanie animacji: strona ${n} z ${doc.numPages}…`);
    const { blob, pixelWidth, pixelHeight } = await renderRedactedPage(n, scale, 'image/webp');
    frames.push({ bytes: new Uint8Array(await blob.arrayBuffer()), width: pixelWidth, height: pixelHeight });
  }
  const result = buildAnimatedWebp(frames, seconds * 1000);
  download(result, `${fileName}-zanonimizowany-slajdy.webp`);
  return `Gotowe (${(result.size / 1048576).toFixed(1)} MB, ${frames.length} slajdów po ${seconds} s).`;
}

$('anon-export').addEventListener('click', async () => {
  const button = $('anon-export');
  button.disabled = true;
  try {
    const scale = Number($('anon-pdf-quality').value);
    const exporters = { pdf: exportPdf, 'webp-files': exportWebpFiles, 'webp-anim': exportWebpAnimation };
    status(await exporters[$('anon-pdf-format').value](scale));
  } catch (error) {
    status(`Nie udało się zapisać: ${error.message}`);
  } finally {
    button.disabled = false;
  }
});
