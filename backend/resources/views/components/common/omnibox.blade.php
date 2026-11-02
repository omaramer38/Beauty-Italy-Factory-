@verbatim
// Omnibox Search & QR Scanner Modals

/* -------------------------------------------------------------
   OMNIBOX SEARCH MODAL
-------------------------------------------------------------- */
function OmniboxModal({ isOpen, onClose, onSelectResult }) {
  const [query, setQuery] = useState('');
  const [results, setResults] = useState([]);
  const [filter, setFilter] = useState('all');
  const [loading, setLoading] = useState(false);
  const inputRef = useRef(null);

  useEffect(() => {
    if (isOpen) {
      setTimeout(() => inputRef.current && inputRef.current.focus(), 50);
    }
  }, [isOpen]);

  useEffect(() => {
    if (!query.trim()) {
      setResults([]);
      return;
    }
    setLoading(true);
    const timer = setTimeout(() => {
      fetch(`/api/v1/search?q=${encodeURIComponent(query.trim())}`)
        .then(res => res.json())
        .then(data => {
          setResults(data.results || []);
          setLoading(false);
        })
        .catch(() => setLoading(false));
    }, 200);
    return () => clearTimeout(timer);
  }, [query]);

  if (!isOpen) return null;

  const filteredResults = filter === 'all' ? results : results.filter(r => r.type === filter);

  return (
    <div className="omnibox-backdrop" onClick={onClose}>
      <div className="omnibox-card" onClick={e => e.stopPropagation()}>
        <div className="omnibox-input-box">
          <Icon name="search" size={20}/>
          <input 
            ref={inputRef} 
            placeholder="Search lots, formulations, batches, items, QC samples... (Press ESC to close)" 
            value={query}
            onChange={e => setQuery(e.target.value)}
          />
          {loading && <span style={{ fontSize: '10px', color: '#6366f1' }}>Searching...</span>}
          <kbd onClick={onClose} style={{ cursor: 'pointer' }}>ESC</kbd>
        </div>

        <div className="omnibox-filter-row">
          <button className={`omnibox-filter-chip ${filter === 'all' ? 'active' : ''}`} onClick={() => setFilter('all')}>All Results ({results.length})</button>
          <button className={`omnibox-filter-chip ${filter === 'formulation' ? 'active' : ''}`} onClick={() => setFilter('formulation')}>🧪 Formulations ({results.filter(r => r.type === 'formulation').length})</button>
          <button className={`omnibox-filter-chip ${filter === 'lot' ? 'active' : ''}`} onClick={() => setFilter('lot')}>📦 Lots ({results.filter(r => r.type === 'lot').length})</button>
          <button className={`omnibox-filter-chip ${filter === 'batch' ? 'active' : ''}`} onClick={() => setFilter('batch')}>⚖ Batches ({results.filter(r => r.type === 'batch').length})</button>
          <button className={`omnibox-filter-chip ${filter === 'item' ? 'active' : ''}`} onClick={() => setFilter('item')}>🏷 Materials ({results.filter(r => r.type === 'item').length})</button>
        </div>

        <div className="omnibox-results-list">
          {filteredResults.map((item, idx) => (
            <button 
              key={idx} 
              className="omnibox-result-item" 
              onClick={() => {
                onSelectResult(item);
                onClose();
              }}
            >
              <div className="omnibox-item-icon">
                <Icon name={item.type === 'formulation' ? 'flask' : item.type === 'lot' ? 'boxes' : item.type === 'batch' ? 'scale' : item.type === 'qc' ? 'shield' : 'file'} size={17}/>
              </div>
              <div className="omnibox-item-text">
                <strong>{item.title}</strong>
                <p>{item.subtitle}</p>
              </div>
              <Status type={item.badgeTone || 'approved'}>{item.badge}</Status>
            </button>
          ))}

          {query && filteredResults.length === 0 && !loading && (
            <div style={{ padding: '36px', textAlign: 'center', color: '#94a3b8', fontSize: '12px' }}>
              No records found matching "<strong>{query}</strong>" across MySQL database.
            </div>
          )}

          {!query && (
            <div style={{ padding: '24px 20px', color: '#64748b', fontSize: '11px' }}>
              <div style={{ fontWeight: 700, marginBottom: '8px', color: '#334155' }}>⚡ Quick Factory Suggestions:</div>
              <div style={{ display: 'flex', gap: '8px', flexWrap: 'wrap' }}>
                {['Anti-Aging', 'Glycerin', 'GLY-8821-IT', 'B-25-0415', 'Retinol', 'QC-S-004182'].map(s => (
                  <span key={s} onClick={() => setQuery(s)} style={{ cursor: 'pointer', padding: '4px 9px', background: '#f1f5f9', borderRadius: '4px', border: '1px solid #e2e8f0', fontSize: '10px' }}>
                    🔍 {s}
                  </span>
                ))}
              </div>
            </div>
          )}
        </div>
      </div>
    </div>
  );
}

/* -------------------------------------------------------------
   PHYSICAL QR CODE & BARCODE SCANNER STUDIO MODAL
   (Webcam / Mobile Camera Stream + Native BarcodeDetector + Image Upload)
-------------------------------------------------------------- */
function QRScannerModal({ isOpen, onClose, onNavigateEntity }) {
  const [inputCode, setInputCode] = useState('');
  const [decoded, setDecoded] = useState(null);
  const [loading, setLoading] = useState(false);
  const [cameraActive, setCameraActive] = useState(false);
  const [facingMode, setFacingMode] = useState('environment'); // 'environment' (back) or 'user' (front)
  const [cameraError, setCameraError] = useState('');
  const [scanStatus, setScanStatus] = useState('');

  const videoRef = useRef(null);
  const streamRef = useRef(null);
  const intervalRef = useRef(null);
  const fileInputRef = useRef(null);

  // Stop camera when modal closes
  const stopCamera = () => {
    if (intervalRef.current) {
      clearInterval(intervalRef.current);
      intervalRef.current = null;
    }
    if (streamRef.current) {
      streamRef.current.getTracks().forEach(track => track.stop());
      streamRef.current = null;
    }
    setCameraActive(false);
    setScanStatus('');
  };

  useEffect(() => {
    if (!isOpen) {
      stopCamera();
    }
    return () => stopCamera();
  }, [isOpen]);

  if (!isOpen) return null;

  // Start live webcam or mobile phone camera (Standard Promises for Babel Standalone)
  const startCamera = (mode) => {
    const targetMode = mode || facingMode;
    setCameraError('');
    setScanStatus('Initializing camera...');
    
    if (streamRef.current) {
      streamRef.current.getTracks().forEach(t => t.stop());
    }

    const constraints = {
      video: {
        facingMode: { ideal: targetMode },
        width: { ideal: 1280 },
        height: { ideal: 720 },
      }
    };

    if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
      setCameraError('Camera API not supported in this browser. You can enter code manually or upload an image.');
      return;
    }

    navigator.mediaDevices.getUserMedia(constraints)
      .then(stream => {
        streamRef.current = stream;
        if (videoRef.current) {
          videoRef.current.srcObject = stream;
          videoRef.current.play();
        }
        setCameraActive(true);
        setScanStatus('Camera active · Align QR / Barcode in box');

        // Setup live scanning loop with dual BarcodeDetector + jsQR fallback
        const frameCanvas = document.createElement('canvas');
        intervalRef.current = setInterval(() => {
          if (videoRef.current && videoRef.current.readyState >= 2) {
            // Priority 1: native BarcodeDetector if supported in browser
            if ('BarcodeDetector' in window) {
              if (!window.__activeBarcodeDetector) {
                try {
                  window.__activeBarcodeDetector = new window.BarcodeDetector({
                    formats: ['qr_code', 'code_128', 'code_39', 'ean_13', 'data_matrix']
                  });
                } catch (e) {}
              }
              if (window.__activeBarcodeDetector) {
                window.__activeBarcodeDetector.detect(videoRef.current)
                  .then(barcodes => {
                    if (barcodes.length > 0 && barcodes[0].rawValue) {
                      setScanStatus('Scanned: ' + barcodes[0].rawValue);
                      handleScan(barcodes[0].rawValue);
                      stopCamera();
                    }
                  })
                  .catch(() => {});
              }
            } 
            // Priority 2: jsQR video frame snapshot (cross-platform desktop & mobile)
            else if (window.jsQR) {
              const v = videoRef.current;
              frameCanvas.width = v.videoWidth || 640;
              frameCanvas.height = v.videoHeight || 480;
              const ctx = frameCanvas.getContext('2d');
              ctx.drawImage(v, 0, 0, frameCanvas.width, frameCanvas.height);
              const imgData = ctx.getImageData(0, 0, frameCanvas.width, frameCanvas.height);
              const code = window.jsQR(imgData.data, imgData.width, imgData.height, { inversionAttempts: 'dontInvert' });
              if (code && code.data) {
                setScanStatus('Scanned: ' + code.data);
                handleScan(code.data);
                stopCamera();
              }
            }
          }
        }, 250);
      })
      .catch(err => {
        console.warn('Camera access error:', err);
        setCameraError('Camera access unavailable or permission denied. You can enter code manually or upload an image.');
        setCameraActive(false);
      });
  };

  const toggleCameraFacing = () => {
    const next = facingMode === 'environment' ? 'user' : 'environment';
    setFacingMode(next);
    if (cameraActive) {
      startCamera(next);
    }
  };

  // Handle uploaded barcode or QR file (Image or PDF)
  const handleFileUpload = (e) => {
    const file = e.target.files && e.target.files[0];
    if (!file) return;

    setScanStatus('Scanning document/image...');
    window.scanFileForCode(
      file,
      (scannedCode) => {
        setScanStatus('Scanned: ' + scannedCode);
        handleScan(scannedCode);
      },
      (errMessage) => {
        alert(errMessage || 'Could not decode a QR code or barcode from this file.');
        setScanStatus('Waiting for input...');
      }
    );
  };

  const handleScan = (codeToLookup) => {
    const val = codeToLookup || inputCode;
    if (!val || !val.trim()) return;
    setInputCode(val.trim());
    setLoading(true);
    fetch(`/api/v1/qr/lookup?code=${encodeURIComponent(val.trim())}`)
      .then(res => res.json())
      .then(data => {
        setDecoded(data);
        setLoading(false);
      })
      .catch(() => setLoading(false));
  };

  return (
    <div className="modal-overlay" onClick={() => { stopCamera(); onClose(); }}>
      <div className="modal" style={{ maxWidth: '600px' }} onClick={e => e.stopPropagation()}>
        <div className="modal-header">
          <div style={{ display: 'flex', alignItems: 'center', gap: '8px' }}>
            <Icon name="qr" size={19}/>
            <div>
              <h2>Optical Barcode & QR Scanner Studio</h2>
              <p style={{ fontSize: '10px', color: '#64748b' }}>Live Camera Hardware Scanner · Mobile & Desktop Compatible</p>
            </div>
          </div>
          <button className="icon-button" onClick={() => { stopCamera(); onClose(); }}><Icon name="close" size={16}/></button>
        </div>

        <div className="modal-body">
          
          {/* Live Camera Feed / Visual Viewfinder */}
          <div className="camera-container">
            {cameraActive ? (
              <video 
                ref={videoRef} 
                className="camera-video-feed" 
                autoPlay 
                playsInline 
                muted
              />
            ) : (
              <div style={{ textAlign: 'center', color: '#94a3b8' }}>
                <Icon name="scan" size={48}/>
                <div style={{ fontSize: '11px', marginTop: '8px', color: '#cbd5e1' }}>
                  Laser Viewfinder Standby
                </div>
                <div style={{ fontSize: '9px', color: '#64748b', marginTop: '2px' }}>
                  Click "Start Camera" below to scan with webcam or phone
                </div>
              </div>
            )}
            
            {/* Viewfinder Target & Animated Laser */}
            <div className="camera-overlay-box">
              <div className="camera-laser-bar"/>
            </div>
          </div>

          {/* Camera Hardware Controls */}
          <div className="camera-controls-bar">
            <div style={{ display: 'flex', alignItems: 'center', gap: '6px' }}>
              {!cameraActive ? (
                <button 
                  type="button" 
                  onClick={() => startCamera(facingMode)}
                  style={{ display: 'inline-flex', alignItems: 'center', gap: '6px', padding: '6px 12px', fontSize: '11px', fontWeight: 600, background: '#4f46e5', color: 'white', border: 'none', borderRadius: '6px', cursor: 'pointer' }}
                >
                  📷 Start Camera (تشغيل الكاميرا الحقيقية)
                </button>
              ) : (
                <button 
                  type="button" 
                  onClick={stopCamera}
                  style={{ display: 'inline-flex', alignItems: 'center', gap: '6px', padding: '6px 12px', fontSize: '11px', fontWeight: 600, background: '#dc2626', color: 'white', border: 'none', borderRadius: '6px', cursor: 'pointer' }}
                >
                  ⏹️ Stop Camera
                </button>
              )}

              {cameraActive && (
                <button 
                  type="button" 
                  onClick={toggleCameraFacing}
                  style={{ padding: '6px 10px', fontSize: '11px', fontWeight: 600, background: 'white', border: '1px solid #cbd5e1', borderRadius: '6px', cursor: 'pointer' }}
                  title="Switch between front and back camera"
                >
                  🔄 Switch Camera ({facingMode === 'environment' ? 'Back' : 'Front'})
                </button>
              )}
            </div>

            <div style={{ display: 'flex', alignItems: 'center', gap: '6px' }}>
              <input 
                type="file" 
                ref={fileInputRef} 
                style={{ display: 'none' }} 
                accept="image/*" 
                onChange={handleFileUpload}
              />
              <button 
                type="button" 
                onClick={() => fileInputRef.current && fileInputRef.current.click()}
                style={{ padding: '6px 10px', fontSize: '11px', fontWeight: 600, background: 'white', border: '1px solid #cbd5e1', borderRadius: '6px', cursor: 'pointer' }}
                title="Upload image of a barcode or QR tag"
              >
                🖼️ Upload Image (رفع صورة)
              </button>

              <input 
                type="file" 
                id="omnibox-pdf-input" 
                style={{ display: 'none' }} 
                accept="application/pdf" 
                onChange={handleFileUpload}
              />
              <button 
                type="button" 
                onClick={() => document.getElementById('omnibox-pdf-input').click()}
                style={{ padding: '6px 10px', fontSize: '11px', fontWeight: 600, background: 'white', border: '1px solid #cbd5e1', borderRadius: '6px', cursor: 'pointer' }}
                title="Scan barcode/QR code from PDF document"
              >
                📄 Upload PDF (مسح ملف PDF)
              </button>
            </div>
          </div>

          {scanStatus && (
            <div style={{ fontSize: '10px', color: '#4f46e5', fontWeight: 600, marginTop: '6px', display: 'flex', alignItems: 'center', gap: '4px' }}>
              <span className="status-dot"/> {scanStatus}
            </div>
          )}

          {cameraError && (
            <div style={{ background: '#fff1f2', border: '1px solid #fecdd3', color: '#be123c', padding: '8px 12px', borderRadius: '6px', fontSize: '10px', marginTop: '6px' }}>
              ⚠️ {cameraError}
            </div>
          )}

          {/* Barcode input field */}
          <div className="form-field" style={{ marginTop: '12px' }}>
            <label>Scan Result / Manual Barcode Input</label>
            <div style={{ display: 'flex', gap: '8px' }}>
              <input 
                placeholder="Scan or type barcode (e.g. GLY-8821-IT, LOT:GLY-8821-IT|ITEM:RM-03001, FRM-SC-0042)" 
                value={inputCode} 
                onChange={e => setInputCode(e.target.value)}
                onKeyDown={e => e.key === 'Enter' && handleScan()}
                autoFocus
              />
              <Button icon="scan" onClick={() => handleScan()} disabled={loading}>
                {loading ? 'Decoding...' : 'Lookup & Verify'}
              </Button>
            </div>
          </div>

          {/* Factory Quick-Scan Presets */}
          <div style={{ marginTop: '12px' }}>
            <span style={{ fontSize: '9px', fontWeight: 700, color: '#64748b', textTransform: 'uppercase' }}>🏭 Or Test Factory Scanned Physical Tags:</span>
            <div className="quick-preset-grid">
              <button className="preset-btn" onClick={() => { setInputCode('LOT:GLY-8821-IT|ITEM:RM-03001|QTY:1240kg'); handleScan('LOT:GLY-8821-IT|ITEM:RM-03001|QTY:1240kg'); }}>
                <span>📦 QR: Glycerin Lot</span><small style={{ color: '#047857' }}>Approved</small>
              </button>
              <button className="preset-btn" onClick={() => { setInputCode('LOT:RET-24018'); handleScan('LOT:RET-24018'); }}>
                <span>📦 QR: Retinol Lot</span><small style={{ color: '#b45309' }}>Quarantine</small>
              </button>
              <button className="preset-btn" onClick={() => { setInputCode('FORMULA:FRM-SC-0042|V:4.2'); handleScan('FORMULA:FRM-SC-0042|V:4.2'); }}>
                <span>🧪 Formula: FRM-SC-0042</span><small style={{ color: '#4f46e5' }}>Cream</small>
              </button>
              <button className="preset-btn" onClick={() => { setInputCode('BATCH:B-25-0415'); handleScan('BATCH:B-25-0415'); }}>
                <span>⚖ Batch: B-25-0415</span><small style={{ color: '#2563eb' }}>Line L01</small>
              </button>
              <button className="preset-btn" onClick={() => { setInputCode('QC-S-004182'); handleScan('QC-S-004182'); }}>
                <span>🔬 QC: QC-S-004182</span><small style={{ color: '#059669' }}>LIMS</small>
              </button>
              <button className="preset-btn" onClick={() => { setInputCode('FG-250415-A'); handleScan('FG-250415-A'); }}>
                <span>🏷️ Lot: FG-250415-A</span><small style={{ color: '#059669' }}>Finished</small>
              </button>
            </div>
          </div>

          {/* Decoded Entity Card */}
          {decoded && (
            <div style={{ marginTop: '16px', background: decoded.found ? '#f8fafc' : '#fff1f2', border: `1px solid ${decoded.found ? '#cbd5e1' : '#fecdd3'}`, borderRadius: '9px', padding: '14px' }}>
              {decoded.found ? (
                <div>
                  <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: '8px' }}>
                    <div>
                      <span style={{ fontSize: '9px', fontWeight: 700, color: '#4f46e5', letterSpacing: '0.8px' }}>{decoded.entityType}</span>
                      <h3 style={{ fontSize: '15px', color: '#0f172a' }}>{decoded.header} — {decoded.title}</h3>
                    </div>
                    <Status type={decoded.statusTone}>{decoded.status}</Status>
                  </div>

                  <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '8px', fontSize: '10px', background: 'white', padding: '10px', borderRadius: '6px', border: '1px solid #e2e8f0', margin: '10px 0' }}>
                    {decoded.details.map((d, i) => (
                      <div key={i}>
                        <span style={{ color: '#64748b' }}>{d.label}: </span>
                        <strong style={{ color: '#0f172a' }}>{d.value}</strong>
                      </div>
                    ))}
                  </div>

                  <Button icon="arrow" variant="primary" style={{ width: '100%' }} onClick={() => {
                    stopCamera();
                    onNavigateEntity(decoded.targetPage, decoded.targetId);
                    onClose();
                  }}>
                    {decoded.actionText} →
                  </Button>
                </div>
              ) : (
                <div style={{ color: '#991b1b', fontSize: '11px' }}>
                  <strong>Scan Error:</strong> {decoded.message}
                </div>
              )}
            </div>
          )}
        </div>

        <div className="modal-footer">
          <Button variant="secondary" onClick={() => { stopCamera(); onClose(); }}>Close</Button>
        </div>
      </div>
    </div>
  );
}
@endverbatim
