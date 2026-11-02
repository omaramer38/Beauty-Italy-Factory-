@verbatim
// Common UI Primitives Component
function Button({ children, variant = 'primary', icon, onClick, className = '', type = 'button', disabled = false, style }) {
  return (
    <button type={type} onClick={onClick} disabled={disabled} style={style} className={`btn btn-${variant} ${className}`}>
      {icon && <Icon name={icon} size={15}/>}
      <span>{children}</span>
    </button>
  );
}

function Status({ type, children }) {
  return (
    <span className={`status status-${type}`}>
      <span className="status-dot"/>
      {children}
    </span>
  );
}

// High-Reliability QRCode renderer with auto-retry & industrial label support
function QRCodeImage({ text, size = 110, showLabel = false, labelText = '' }) {
  const containerRef = useRef(null);

  useEffect(() => {
    if (!containerRef.current) return;
    
    let attempts = 0;
    const renderQR = () => {
      if (!containerRef.current) return;
      if (window.QRCode) {
        containerRef.current.innerHTML = '';
        try {
          new window.QRCode(containerRef.current, {
            text: text || 'BEAUTY-ITALY-ERP',
            width: size,
            height: size,
            colorDark: '#0f172a',
            colorLight: '#ffffff',
            correctLevel: window.QRCode.CorrectLevel.M,
          });
        } catch (e) {
          console.warn('QR render error:', e);
        }
      } else if (attempts < 10) {
        attempts++;
        setTimeout(renderQR, 150);
      }
    };

    renderQR();
  }, [text, size]);

  return (
    <div style={{ display: 'inline-flex', flexDirection: 'column', alignItems: 'center', background: 'white', padding: '6px', borderRadius: '6px', border: '1px solid #e2e8f0', boxShadow: '0 1px 2px rgba(0,0,0,0.05)' }}>
      <div ref={containerRef} style={{ width: size, height: size, display: 'flex', alignItems: 'center', justifyContent: 'center' }} />
      {showLabel && (
        <span style={{ fontSize: '9px', fontWeight: 'bold', color: '#334155', fontFamily: 'monospace', marginTop: '4px', textAlign: 'center' }}>
          {labelText || text}
        </span>
      )}
    </div>
  );
}

// Direct Official PDF & Clean Print Export Engine (Generates isolated A4 PDF sheet without web UI)
window.downloadReportPdf = function(elementId, filename, isPrintAction) {
  const el = document.getElementById(elementId);
  if (!el) {
    alert('Report sheet not found: ' + elementId);
    return;
  }
  
  const printWindow = window.open('', '_blank', 'width=950,height=850');
  if (!printWindow) {
    window.print();
    return;
  }
  
  const title = filename ? filename.replace('.pdf', '') : 'Official Factory Report';
  const htmlContent = [
    '<!DOCTYPE html>',
    '<html><head>',
    '<meta charset="UTF-8">',
    '<title>' + title + '</title>',
    '<link rel="stylesheet" href="/css/erp.css">',
    '<style>',
    '@page { size: A4 portrait; margin: 8mm; }',
    'body { background: white !important; padding: 16px !important; margin: 0 !important; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif; color: #0f172a; -webkit-print-color-adjust: exact; print-color-adjust: exact; }',
    '.report-paper { margin: 0 auto !important; box-shadow: none !important; border: 1px solid #cbd5e1 !important; max-width: 850px; padding: 24px; background: white !important; }',
    '.report-table { width: 100%; border-collapse: collapse; margin-top: 12px; font-size: 11px; }',
    '.report-table th, .report-table td { border: 1px solid #cbd5e1 !important; padding: 6px 10px; text-align: left; }',
    '.report-table th { background: #f8fafc !important; font-weight: 700; color: #334155; }',
    '@media print {',
    '  .no-print { display: none !important; }',
    '  body { padding: 0 !important; }',
    '  .report-paper { border: none !important; padding: 0 !important; max-width: 100% !important; }',
    '}',
    '</style>',
    '</head><body>',
    '<div class="no-print" style="max-width:850px;margin:0 auto 16px;display:flex;justify-content:space-between;align-items:center;padding:12px 18px;background:#f8fafc;border:1px solid #cbd5e1;border-radius:8px;box-shadow:0 2px 6px rgba(0,0,0,0.05);">',
    '  <div style="display:flex;align-items:center;gap:10px;">',
    '    <span style="font-size:16px;">📄</span>',
    '    <div>',
    '      <strong style="font-size:13px;color:#0f172a;display:block;">' + title + '</strong>',
    '      <small style="color:#64748b;font-size:11px;">Beauty Italy Factory Official ISO 22716 Document</small>',
    '    </div>',
    '  </div>',
    '  <div style="display:flex;gap:8px;">',
    '    <button onclick="window.print()" style="padding:8px 16px;background:#4f46e5;color:white;border:none;border-radius:6px;font-weight:600;font-size:12px;cursor:pointer;display:flex;align-items:center;gap:6px;">🖨️ Print / Save as PDF</button>',
    '    <button onclick="window.close()" style="padding:8px 14px;background:#e2e8f0;color:#334155;border:none;border-radius:6px;font-weight:600;font-size:12px;cursor:pointer;">Close</button>',
    '  </div>',
    '</div>',
    el.outerHTML,
    '</body></html>'
  ].join('\n');

  printWindow.document.write(htmlContent);
  printWindow.document.close();
  printWindow.focus();
  setTimeout(() => {
    printWindow.print();
  }, 400);
};

window.cleanPrint = function(elementId, filename) {
  window.downloadReportPdf(elementId, filename, true);
};

// High-Resolution Image Exporter for Industrial Drum Labels & QR codes
window.saveQrLabelImage = function(modalData, qrContainerId, filename) {
  if (!modalData) return;
  const qrContainer = qrContainerId ? document.getElementById(qrContainerId) : document.getElementById('drum-qr-container');
  const qrCanvas = qrContainer ? qrContainer.querySelector('canvas') : null;
  const qrImg = qrContainer ? qrContainer.querySelector('img') : null;

  const canvas = document.createElement('canvas');
  const ctx = canvas.getContext('2d');
  
  canvas.width = 680;
  canvas.height = 780;

  // Background
  ctx.fillStyle = '#ffffff';
  ctx.fillRect(0, 0, canvas.width, canvas.height);

  // Border
  ctx.strokeStyle = '#94a3b8';
  ctx.lineWidth = 3;
  if (ctx.setLineDash) {
    ctx.setLineDash([8, 6]);
  }
  ctx.strokeRect(20, 20, canvas.width - 40, canvas.height - 40);
  if (ctx.setLineDash) {
    ctx.setLineDash([]);
  }

  // Header
  ctx.fillStyle = '#64748b';
  ctx.font = 'bold 16px sans-serif';
  ctx.textAlign = 'center';
  ctx.fillText('BEAUTY ITALY FACTORY · MILANO', canvas.width / 2, 65);

  // Lot
  ctx.fillStyle = '#0f172a';
  ctx.font = 'bold 36px sans-serif';
  ctx.fillText(modalData.lot || 'LOT-UNKNOWN', canvas.width / 2, 115);

  // Material Name & Code
  ctx.fillStyle = '#334155';
  ctx.font = '600 18px sans-serif';
  const nameCode = (modalData.name || '') + (modalData.code ? ' (' + modalData.code + ')' : '');
  ctx.fillText(nameCode, canvas.width / 2, 150);

  // QR Code
  const qrSize = 250;
  const qrX = (canvas.width - qrSize) / 2;
  const qrY = 175;

  if (qrCanvas) {
    ctx.drawImage(qrCanvas, qrX, qrY, qrSize, qrSize);
  } else if (qrImg && qrImg.src) {
    ctx.drawImage(qrImg, qrX, qrY, qrSize, qrSize);
  } else {
    ctx.strokeStyle = '#cbd5e1';
    ctx.strokeRect(qrX, qrY, qrSize, qrSize);
  }

  // Monospace Lot text under QR
  ctx.fillStyle = '#475569';
  ctx.font = 'bold 15px monospace';
  ctx.fillText(modalData.lot || '', canvas.width / 2, qrY + qrSize + 24);

  // Status Badge Pill
  const isApproved = modalData.status === 'Approved';
  const badgeY = qrY + qrSize + 48;
  const badgeWidth = 380;
  const badgeHeight = 42;
  const badgeX = (canvas.width - badgeWidth) / 2;

  ctx.fillStyle = isApproved ? '#dcfce7' : '#fef3c7';
  ctx.beginPath();
  if (typeof ctx.roundRect === 'function') {
    ctx.roundRect(badgeX, badgeY, badgeWidth, badgeHeight, 21);
  } else {
    ctx.rect(badgeX, badgeY, badgeWidth, badgeHeight);
  }
  ctx.fill();
  ctx.strokeStyle = isApproved ? '#86efac' : '#fde68a';
  ctx.lineWidth = 1.5;
  ctx.stroke();

  ctx.fillStyle = isApproved ? '#166534' : '#92400e';
  ctx.font = 'bold 15px sans-serif';
  ctx.fillText(isApproved ? '✓ APPROVED FOR MES USE' : '⚠ QUARANTINE — DO NOT WEIGH', canvas.width / 2, badgeY + 26);

  // Divider Line
  const divY = badgeY + 70;
  ctx.strokeStyle = '#e2e8f0';
  ctx.lineWidth = 1.5;
  ctx.beginPath();
  ctx.moveTo(50, divY);
  ctx.lineTo(canvas.width - 50, divY);
  ctx.stroke();

  // Bottom Meta Info (3 Columns)
  ctx.fillStyle = '#334155';
  ctx.font = '16px sans-serif';
  ctx.textAlign = 'left';
  ctx.fillText('Qty: ' + (modalData.qty || '') + ' ' + (modalData.unit || ''), 60, divY + 36);

  ctx.textAlign = 'center';
  ctx.fillText('Loc: ' + (modalData.location || '-'), canvas.width / 2, divY + 36);

  ctx.textAlign = 'right';
  ctx.fillText('Exp: ' + (modalData.expiry || '-'), canvas.width - 60, divY + 36);

  // Compliance Footer
  ctx.fillStyle = '#94a3b8';
  ctx.font = '12px sans-serif';
  ctx.textAlign = 'center';
  ctx.fillText('GMP ISO 22716 Controlled Material · 21 CFR Part 11 Certified', canvas.width / 2, canvas.height - 35);

  // Trigger download
  const link = document.createElement('a');
  link.download = filename || ('Label-' + (modalData.lot || 'Lot') + '.png');
  link.href = canvas.toDataURL('image/png');
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
};

// Direct QR Code Only Image Exporter
window.saveQrOnlyImage = function(qrContainerId, filename) {
  const qrContainer = qrContainerId ? document.getElementById(qrContainerId) : document.getElementById('drum-qr-container');
  if (!qrContainer) {
    alert('QR Container not found');
    return;
  }
  const qrCanvas = qrContainer.querySelector('canvas');
  const qrImg = qrContainer.querySelector('img');

  let dataUrl = null;
  if (qrCanvas) {
    const c = document.createElement('canvas');
    c.width = 440;
    c.height = 440;
    const ctx = c.getContext('2d');
    ctx.fillStyle = '#ffffff';
    ctx.fillRect(0, 0, 440, 440);
    ctx.drawImage(qrCanvas, 20, 20, 400, 400);
    dataUrl = c.toDataURL('image/png');
  } else if (qrImg && qrImg.src && qrImg.src.startsWith('data:')) {
    dataUrl = qrImg.src;
  }

  if (!dataUrl) {
    alert('QR Code is still rendering, please click again in a second.');
    return;
  }

  const link = document.createElement('a');
  link.download = filename || 'QR-Code.png';
  link.href = dataUrl;
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
};

// Universal Scanner for Images (PNG/JPG) and PDF Documents (uses jsQR + pdf.js)
window.scanFileForCode = function(file, onSuccess, onError) {
  if (!file) return;

  const isPdf = file.type === 'application/pdf' || (file.name && file.name.toLowerCase().endsWith('.pdf'));

  if (isPdf) {
    if (!window.pdfjsLib) {
      const lotMatch = file.name.match(/[A-Z0-9]{2,5}-\d{4,8}(-[A-Z0-9]+)?/i);
      if (lotMatch) {
        onSuccess(lotMatch[0]);
      } else if (onError) {
        onError('PDF reader engine is loading, please try again or enter code manually.');
      }
      return;
    }

    const fileReader = new FileReader();
    fileReader.onload = function(ev) {
      const typedArray = new Uint8Array(ev.target.result);
      window.pdfjsLib.getDocument({ data: typedArray }).promise
        .then(function(pdf) {
          const numPages = Math.min(pdf.numPages, 3);
          let pageIndex = 1;

          function scanNextPage() {
            if (pageIndex > numPages) {
              const lotMatch = file.name.match(/[A-Z0-9]{2,5}-\d{4,8}(-[A-Z0-9]+)?/i);
              if (lotMatch) {
                onSuccess(lotMatch[0]);
              } else if (onError) {
                onError('No barcode or QR code found in uploaded PDF.');
              }
              return;
            }

            pdf.getPage(pageIndex).then(function(page) {
              const viewport = page.getViewport({ scale: 2.0 });
              const canvas = document.createElement('canvas');
              const ctx = canvas.getContext('2d');
              canvas.width = viewport.width;
              canvas.height = viewport.height;

              page.render({ canvasContext: ctx, viewport: viewport }).promise.then(function() {
                if (window.jsQR) {
                  const imgData = ctx.getImageData(0, 0, canvas.width, canvas.height);
                  const qr = window.jsQR(imgData.data, imgData.width, imgData.height, { inversionAttempts: 'attemptBoth' });
                  if (qr && qr.data) {
                    onSuccess(qr.data);
                    return;
                  }
                }

                page.getTextContent().then(function(tc) {
                  const fullText = tc.items.map(function(item) { return item.str; }).join(' ');
                  const qrMatch = fullText.match(/(?:LOT:|ITEM:|FORMULA:|BATCH:|TRC:)?([A-Z0-9]{2,5}-\d{4,8}(-[A-Z0-9]+)?)/i);
                  if (qrMatch) {
                    onSuccess(qrMatch[0]);
                    return;
                  }
                  pageIndex++;
                  scanNextPage();
                }).catch(function() {
                  pageIndex++;
                  scanNextPage();
                });
              }).catch(function() {
                pageIndex++;
                scanNextPage();
              });
            }).catch(function() {
              pageIndex++;
              scanNextPage();
            });
          }

          scanNextPage();
        })
        .catch(function(err) {
          console.warn('PDF parse error:', err);
          const lotMatch = file.name.match(/[A-Z0-9]{2,5}-\d{4,8}(-[A-Z0-9]+)?/i);
          if (lotMatch) {
            onSuccess(lotMatch[0]);
          } else if (onError) {
            onError('Could not parse PDF document.');
          }
        });
    };
    fileReader.readAsArrayBuffer(file);
    return;
  }

  // Handle standard images (PNG, JPG, WEBP, etc.)
  const reader = new FileReader();
  reader.onload = function(event) {
    const img = new Image();
    img.onload = function() {
      let decodedText = null;

      if (window.jsQR) {
        try {
          const canvas = document.createElement('canvas');
          let w = img.naturalWidth || img.width;
          let h = img.naturalHeight || img.height;
          if (w > 1200 || h > 1200) {
            const scale = Math.min(1200 / w, 1200 / h);
            w = Math.round(w * scale);
            h = Math.round(h * scale);
          }
          canvas.width = w;
          canvas.height = h;
          const ctx = canvas.getContext('2d');
          ctx.drawImage(img, 0, 0, w, h);
          const imageData = ctx.getImageData(0, 0, w, h);

          const qr = window.jsQR(imageData.data, imageData.width, imageData.height, { inversionAttempts: 'attemptBoth' });
          if (qr && qr.data) {
            decodedText = qr.data;
          } else {
            const cw = Math.round(w * 0.7);
            const ch = Math.round(h * 0.7);
            const cx = Math.round((w - cw) / 2);
            const cy = Math.round((h - ch) / 2);
            const cdata = ctx.getImageData(cx, cy, cw, ch);
            const cqr = window.jsQR(cdata.data, cw, ch, { inversionAttempts: 'attemptBoth' });
            if (cqr && cqr.data) decodedText = cqr.data;
          }
        } catch (e) {
          console.warn('Image decode error:', e);
        }
      }

      if (!decodedText && 'BarcodeDetector' in window) {
        try {
          const detector = new window.BarcodeDetector({ formats: ['qr_code', 'code_128', 'code_39', 'ean_13', 'data_matrix'] });
          detector.detect(img).then(function(barcodes) {
            if (barcodes.length > 0 && barcodes[0].rawValue) {
              onSuccess(barcodes[0].rawValue);
            } else {
              fallbackFile(file);
            }
          }).catch(function() { fallbackFile(file); });
          return;
        } catch (e) {}
      }

      if (decodedText) {
        onSuccess(decodedText);
      } else {
        fallbackFile(file);
      }
    };
    img.src = event.target.result;
  };

  function fallbackFile(f) {
    const lotMatch = f.name.match(/[A-Z0-9]{2,5}-\d{4,8}(-[A-Z0-9]+)?/i);
    if (lotMatch) {
      onSuccess(lotMatch[0]);
    } else if (onError) {
      onError('Could not find a valid barcode or QR code in this image.');
    }
  }

  reader.readAsDataURL(file);
};

const nav = [
  { id: 'dashboard', label: 'Command Center', icon: 'grid' },
  { id: 'inventory', label: 'Inventory & Lots', icon: 'boxes' },
  { id: 'formulation', label: 'Formulation Studio', icon: 'flask' },
  { id: 'dispensing', label: 'Dispensing & eBMR', icon: 'scale' },
  { id: 'quality', label: 'Quality & Release', icon: 'shield', count: 1 },
  { id: 'trace', label: 'Traceability', icon: 'trace' },
];

const roles = ['Admin', 'Warehouse Operator', 'R&D Chemist', 'Shopfloor Operator', 'QC Lab Analyst', 'QA Manager'];
const roleProfiles = {
  Admin: { landing: 'dashboard', label: 'Full system oversight', detail: 'All controlled modules and administrative permissions are active on MySQL.', initials: 'MC' },
  'Warehouse Operator': { landing: 'inventory', label: 'Warehouse workspace', detail: 'Receive materials, manage locations, and review FEFO and quarantine alerts.', initials: 'GR' },
  'R&D Chemist': { landing: 'formulation', label: 'R&D controlled workspace', detail: 'Create formula revisions and route specifications through approval.', initials: 'SB' },
  'Shopfloor Operator': { landing: 'dispensing', label: 'Shopfloor tablet mode', detail: 'Large touch controls and the active dispensing sequence are prioritized.', initials: 'GR' },
  'QC Lab Analyst': { landing: 'quality', label: 'Laboratory testing workspace', detail: 'Record controlled results and submit completed samples to QA.', initials: 'LB' },
  'QA Manager': { landing: 'quality', label: 'QA disposition workspace', detail: 'Review completed records and apply legally binding release signatures.', initials: 'ER' },
};
@endverbatim
