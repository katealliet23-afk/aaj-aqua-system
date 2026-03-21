// js/main.js — AAJ AQUA v2

// ── CLOCK ──────────────────────────────────
function tick() {
    const n = new Date();
    const el = document.getElementById('tbTime');
    if (el) el.textContent = n.toLocaleDateString('en-PH', {month:'short',day:'numeric'}) + ' ' +
                             n.toLocaleTimeString('en-PH', {hour:'2-digit',minute:'2-digit'});
}
setInterval(tick, 1000); tick();

// ── TOAST ──────────────────────────────────
function showToast(msg, isErr = false) {
    const t = document.getElementById('toast');
    if (!t) return;
    document.getElementById('toastMsg').textContent = msg;
    t.className = 'toast' + (isErr ? ' err' : '');
    t.classList.add('show');
    setTimeout(() => t.classList.remove('show'), 3000);
}

// ── MODALS ─────────────────────────────────
function openModal(id) {
    const m = document.getElementById('modal-' + id);
    if (m) m.classList.add('open');
}
function closeModal(id) {
    const m = document.getElementById('modal-' + id);
    if (m) m.classList.remove('open');
}
document.addEventListener('click', e => {
    if (e.target.classList.contains('modal-ov')) e.target.classList.remove('open');
});

// ── QR CODE GENERATOR ──────────────────────
function generateQRCode(containerId, url, size) {
    const c = document.getElementById(containerId);
    console.log(`[QR] generateQRCode called with: containerId=${containerId}, url=${url}, size=${size}`);
    
    if (!c) {
        console.error(`[QR] ERROR: Container #${containerId} not found in DOM`);
        return false;
    }
    if (!url || url.trim() === '') {
        console.error(`[QR] ERROR: URL is empty or invalid`);
        return false;
    }
    
    // Check if QRCode library is available
    if (typeof QRCode === 'undefined') {
        console.error('[QR] ERROR: QRCode library not loaded');
        c.innerHTML = '<div style="padding:20px;color:red;text-align:center;font-size:12px;">QRCode library failed to load</div>';
        return false;
    }
    
    try {
        // Clear the container
        c.innerHTML = '';
        
        // Create QR code
        const qrSize = size || 160;
        console.log(`[QR] Creating QRCode with text: ${url}`);
        
        new QRCode(c, {
            text: url,
            width: qrSize,
            height: qrSize,
            colorDark: '#0c4a6e',
            colorLight: '#ffffff',
            correctLevel: QRCode.CorrectLevel.H
        });
        
        const status = document.getElementById(containerId + 'Status');
        if (status) {
            status.remove();
        }

        console.log(`[QR] SUCCESS: QR Code generated for ${containerId}`);
        return true;
        
    } catch (e) {
        console.error(`[QR] ERROR: Exception during QR generation:`, e.message);
        const status = document.getElementById(containerId + 'Status');
        if (status) {
            status.textContent = 'Failed to generate QR code: ' + e.message;
            status.style.color = '#c62828';
        }
        c.innerHTML = `<div style="padding:20px;color:#e74c3c;text-align:center;font-size:11px;">Error: ${e.message}</div>`;
        return false;
    }
}

// ── QR CODE DOWNLOAD ──────────────────────
function downloadQRCode(containerId, filename = 'qr-code.png') {
    const container = document.getElementById(containerId);
    if (!container) {
        console.error('[QR] ERROR: Container not found');
        return;
    }
    
    const qrImg = container.querySelector('img');
    if (!qrImg) {
        console.error('[QR] ERROR: QR image not found');
        return;
    }
    
    // Create a canvas to add background and styling
    const canvas = document.createElement('canvas');
    const ctx = canvas.getContext('2d');
    
    // Set canvas size (QR + padding)
    const padding = 24;
    const qrSize = qrImg.naturalWidth;
    canvas.width = qrSize + (padding * 2);
    canvas.height = qrSize + (padding * 2);
    
    // Draw background
    ctx.fillStyle = '#0c4a6e';
    ctx.fillRect(0, 0, canvas.width, canvas.height);
    
    // Draw QR code with rounded corners
    ctx.save();
    ctx.beginPath();
    ctx.roundRect(padding, padding, qrSize, qrSize, 16);
    ctx.clip();
    ctx.drawImage(qrImg, padding, padding, qrSize, qrSize);
    ctx.restore();
    
    // Convert to blob and download
    canvas.toBlob(function(blob) {
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = filename;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        URL.revokeObjectURL(url);
        console.log('[QR] QR code downloaded:', filename);
    }, 'image/png');
}

// ── ORDER TOTAL ─────────────────────────────
function calcOrderTotal(priceInputId, qtyInputId, outputId) {
    const price = parseFloat(document.getElementById(priceInputId)?.value || 0);
    const qty   = parseInt(document.getElementById(qtyInputId)?.value   || 0);
    const out   = document.getElementById(outputId);
    if (out) out.textContent = '₱' + (price * qty).toFixed(2);
}

// ── AUTO-DISMISS ALERTS ─────────────────────
document.addEventListener('DOMContentLoaded', () => {
    const a = document.querySelector('.alert');
    if (a) setTimeout(() => { a.style.opacity = '0'; a.style.transition = 'opacity .5s'; setTimeout(() => a.remove(), 500); }, 4000);

    // Init QR on pages that need it
    if (window.QR_URL && document.getElementById('qrContainer'))
        generateQRCode('qrContainer', window.QR_URL, 160);
    if (window.QR_URL && document.getElementById('qrBig'))
        generateQRCode('qrBig', window.QR_URL, 220);
});
