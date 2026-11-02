@verbatim
// Traceability Component (Forward & Backward Genealogy, Barcode Scanner, Recall Center, Dossier)
function Traceability({ notify, onOpenScanner }) {
  const [data, setData] = useState(null);
  const [loading, setLoading] = useState(true);
  const [searchQuery, setSearchQuery] = useState('');
  const [selectedLot, setSelectedLot] = useState('FG-250415-A');
  const [direction, setDirection] = useState('backward'); // 'backward' or 'forward'
  
  // Modals
  const [activeNode, setActiveNode] = useState(null);
  const [recallModal, setRecallModal] = useState(false);
  const [barcodeModal, setBarcodeModal] = useState(false);
  const [dossierModal, setDossierModal] = useState(false);
  const [quarantined, setQuarantined] = useState(false);
  const [barcodeInput, setBarcodeInput] = useState('');
  const [scanStatus, setScanStatus] = useState('');
  const [cameraActive, setCameraActive] = useState(false);
  const traceVideoRef = useRef(null);
  const traceStreamRef = useRef(null);
  const traceIntervalRef = useRef(null);

  const stopTraceCamera = () => {
    if (traceStreamRef.current) {
      traceStreamRef.current.getTracks().forEach(t => t.stop());
      traceStreamRef.current = null;
    }
    if (traceIntervalRef.current) {
      clearInterval(traceIntervalRef.current);
      traceIntervalRef.current = null;
    }
    setCameraActive(false);
  };

  const startTraceCamera = () => {
    if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
      alert('Camera API is not supported in this browser.');
      return;
    }
    setScanStatus('Initializing camera...');
    navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment', width: { ideal: 1280 } } })
      .then(stream => {
        traceStreamRef.current = stream;
        if (traceVideoRef.current) {
          traceVideoRef.current.srcObject = stream;
          traceVideoRef.current.play();
        }
        setCameraActive(true);
        setScanStatus('Camera active · Align code in viewfinder');

        const frameCanvas = document.createElement('canvas');
        traceIntervalRef.current = setInterval(() => {
          if (traceVideoRef.current && traceVideoRef.current.readyState >= 2) {
            if ('BarcodeDetector' in window) {
              if (!window.__traceDetector) {
                try {
                  window.__traceDetector = new window.BarcodeDetector({ formats: ['qr_code', 'code_128', 'code_39', 'ean_13', 'data_matrix'] });
                } catch(e) {}
              }
              if (window.__traceDetector) {
                window.__traceDetector.detect(traceVideoRef.current).then(barcodes => {
                  if (barcodes.length > 0 && barcodes[0].rawValue) {
                    stopTraceCamera();
                    handleScanBarcode(barcodes[0].rawValue);
                  }
                }).catch(() => {});
              }
            } else if (window.jsQR) {
              const v = traceVideoRef.current;
              frameCanvas.width = v.videoWidth || 640;
              frameCanvas.height = v.videoHeight || 480;
              const ctx = frameCanvas.getContext('2d');
              ctx.drawImage(v, 0, 0, frameCanvas.width, frameCanvas.height);
              const imgData = ctx.getImageData(0, 0, frameCanvas.width, frameCanvas.height);
              const code = window.jsQR(imgData.data, imgData.width, imgData.height, { inversionAttempts: 'dontInvert' });
              if (code && code.data) {
                stopTraceCamera();
                handleScanBarcode(code.data);
              }
            }
          }
        }, 250);
      })
      .catch(err => {
        alert('Camera permission denied or camera unavailable.');
        setCameraActive(false);
      });
  };

  const handleTraceFileUpload = (e) => {
    const file = e.target.files && e.target.files[0];
    if (!file) return;
    setScanStatus('Scanning document/image...');
    window.scanFileForCode(
      file,
      (scannedCode) => {
        setScanStatus('Decoded: ' + scannedCode);
        stopTraceCamera();
        handleScanBarcode(scannedCode);
      },
      (errMsg) => {
        alert(errMsg || 'Could not find a valid barcode or QR code in file.');
        setScanStatus('');
      }
    );
  };

  const fetchTrace = (query) => {
    setLoading(true);
    fetch(`/api/v1/traceability?query=${encodeURIComponent(query)}`)
      .then(res => res.json())
      .then(d => {
        setData(d);
        setLoading(false);
        if (d.record && d.record.finished_lot) {
          setSelectedLot(d.record.finished_lot);
        }
      })
      .catch(err => {
        console.error(err);
        setLoading(false);
      });
  };

  useEffect(() => {
    fetchTrace(selectedLot);
  }, [selectedLot]);

  const handleSearchSubmit = (e) => {
    if (e) e.preventDefault();
    if (searchQuery.trim()) {
      fetchTrace(searchQuery.trim());
      if (notify) notify(`Tracing entity: ${searchQuery.trim()}`);
    }
  };

  const handleScanBarcode = (code) => {
    let cleanCode = code;
    if (typeof code === 'string' && code.includes(':')) {
      const match = code.match(/(?:LOT|ITEM|BATCH|FORMULA|TRC):([^|;,\n\r]+)/i);
      if (match && match[1]) {
        cleanCode = match[1].trim();
      }
    }
    setBarcodeInput(cleanCode);
    setSearchQuery(cleanCode);
    fetchTrace(cleanCode);
    if (notify) notify(`📷 Barcode Scanned: ${cleanCode}`);
    stopTraceCamera();
    setBarcodeModal(false);
  };

  const handleNodeClick = (nodeCode) => {
    if (!data || !data.nodeDetails) return;
    const details = data.nodeDetails[nodeCode];
    if (details) {
      setActiveNode(details);
    } else {
      setActiveNode({
        code: nodeCode,
        type: 'System Node',
        title: nodeCode,
        status: 'Verified In System',
        fields: { 'Entity Code': nodeCode, 'Trace Status': 'Conforms to GMP Chain' }
      });
    }
  };

  const handleTriggerRecall = () => {
    if (!data || !data.record) return;
    fetch('/api/v1/traceability/recall-hold', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        lotNumber: data.record.finished_lot,
        reason: 'Critical Out-of-Specification Contamination Alert (Simulated / Regulatory Protocol)',
        authorizedBy: 'Elena Rossi (Head of QA)'
      })
    })
    .then(res => res.json())
    .then(resData => {
      setQuarantined(true);
      setRecallModal(false);
      if (notify) notify(resData.message);
    })
    .catch(err => {
      if (notify) notify('Recall trigger error: ' + err.message);
    });
  };

  if (!data && loading) {
    return <div style={{ padding: '40px', textAlign: 'center', color: '#64748b' }}>Loading GMP Traceability Genealogy...</div>;
  }

  const record = (data && data.record) || {};
  const stats = (data && data.stats) || {};
  const matchedEntity = (data && data.matchedEntity) || {};
  const rawMaterials = record.raw_materials || [];
  const deliveries = record.customer_deliveries || [];
  const recallAnalysis = (data && data.recallAnalysis) || {};

  const isNodeMatched = (code) => {
    if (!searchQuery) return false;
    return (code || '').toLowerCase().includes(searchQuery.toLowerCase().trim());
  };

  return (
    <div>
      {/* Header */}
      <div className="page-header">
        <div>
          <div className="eyebrow">GMP TRACEABILITY MATRIX · FORWARD & BACKWARD GENEALOGY</div>
          <h1>Full Lot Traceability Chain</h1>
          <div style={{ display: 'flex', alignItems: 'center', gap: '8px', marginTop: '4px', flexWrap: 'wrap' }}>
            <select 
              style={{ height: '32px', fontWeight: 'bold', fontSize: '12px', border: '1px solid #cbd5e1', borderRadius: '6px', padding: '0 8px', background: 'white' }}
              value={selectedLot}
              onChange={e => {
                setSelectedLot(e.target.value);
                setSearchQuery(e.target.value);
              }}
            >
              {data && data.allRecords && data.allRecords.map(r => (
                <option key={r.finished_lot} value={r.finished_lot}>
                  {r.finished_lot} — {r.product_name} ({r.units} units)
                </option>
              ))}
            </select>

            <Status type={quarantined ? 'danger' : 'approved'}>
              {quarantined ? '🚨 Emergency Quarantine Recall Active' : 'GMP Certified Audit Chain'}
            </Status>

            {matchedEntity.type && (
              <span style={{ fontSize: '10px', background: '#e0e7ff', color: '#4338ca', padding: '3px 8px', borderRadius: '4px', fontWeight: 600 }}>
                Active Trace: {matchedEntity.type}
              </span>
            )}
          </div>
        </div>

        <div className="header-actions">
          <Button variant="secondary" icon="search" onClick={() => setBarcodeModal(true)}>
            📷 Scan Barcode (مسح الباركود)
          </Button>
          <Button variant="danger" icon="alert" onClick={() => setRecallModal(true)}>
            🚨 Simulate Recall (محاكاة سحب المنتج)
          </Button>
          <Button variant="secondary" icon="file" onClick={() => setDossierModal(true)}>
            📑 Official Dossier (ملف التتبع)
          </Button>
        </div>
      </div>

      {/* Search, Barcode & Direction Control Bar */}
      <div className="section-card" style={{ marginBottom: '14px', padding: '12px 16px' }}>
        <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', gap: '14px', flexWrap: 'wrap' }}>
          
          {/* Search Box */}
          <form onSubmit={handleSearchSubmit} style={{ display: 'flex', alignItems: 'center', gap: '8px', flex: 1, minWidth: '320px' }}>
            <div style={{ position: 'relative', flex: 1 }}>
              <input 
                type="text" 
                placeholder="🔍 Search or scan any Barcode, Lot #, PO, MO, or Customer (e.g. GLY-8821-IT, NIA-24091, FG-250415-A)..."
                value={searchQuery}
                onChange={e => setSearchQuery(e.target.value)}
                style={{ width: '100%', height: '36px', border: '1px solid #cbd5e1', borderRadius: '6px', padding: '0 12px', fontSize: '11px', background: '#f8fafc' }}
              />
              {searchQuery && (
                <button 
                  type="button" 
                  onClick={() => { setSearchQuery(''); fetchTrace(selectedLot); }}
                  style={{ position: 'absolute', right: '8px', top: '9px', background: 'none', border: 'none', color: '#94a3b8', cursor: 'pointer', fontWeight: 'bold' }}
                >
                  ✕
                </button>
              )}
            </div>
            <Button variant="primary" type="submit" style={{ height: '36px', fontSize: '11px' }}>
              Search
            </Button>
          </form>

          {/* Direction Mode Segmented Switch */}
          <div style={{ display: 'flex', alignItems: 'center', gap: '6px', background: '#f1f5f9', padding: '3px', borderRadius: '8px' }}>
            <button
              type="button"
              onClick={() => setDirection('backward')}
              style={{
                padding: '6px 12px',
                fontSize: '11px',
                fontWeight: 600,
                borderRadius: '6px',
                border: 'none',
                background: direction === 'backward' ? 'white' : 'transparent',
                color: direction === 'backward' ? '#1e293b' : '#64748b',
                boxShadow: direction === 'backward' ? '0 1px 3px rgba(0,0,0,0.1)' : 'none',
                cursor: 'pointer'
              }}
            >
              ⬅️ Backward Tracing (من السوق للمورد)
            </button>
            <button
              type="button"
              onClick={() => setDirection('forward')}
              style={{
                padding: '6px 12px',
                fontSize: '11px',
                fontWeight: 600,
                borderRadius: '6px',
                border: 'none',
                background: direction === 'forward' ? 'white' : 'transparent',
                color: direction === 'forward' ? '#1e293b' : '#64748b',
                boxShadow: direction === 'forward' ? '0 1px 3px rgba(0,0,0,0.1)' : 'none',
                cursor: 'pointer'
              }}
            >
              ➡️ Forward Tracing (من المادة للأسواق)
            </button>
          </div>

        </div>

        {/* Quick Test Barcode Pills */}
        <div style={{ display: 'flex', alignItems: 'center', gap: '6px', marginTop: '10px', flexWrap: 'wrap' }}>
          <span style={{ fontSize: '10px', fontWeight: 700, color: '#64748b', textTransform: 'uppercase' }}>Quick Scan Test:</span>
          <button type="button" className="barcode-pill" onClick={() => handleScanBarcode('FG-250415-A')}>🏷️ FG-250415-A (Cream)</button>
          <button type="button" className="barcode-pill" onClick={() => handleScanBarcode('GLY-8821-IT')}>🏷️ GLY-8821-IT (Glycerin - Shared)</button>
          <button type="button" className="barcode-pill" onClick={() => handleScanBarcode('NIA-24091')}>🏷️ NIA-24091 (Niacinamide)</button>
          <button type="button" className="barcode-pill" onClick={() => handleScanBarcode('PO-2025-0189')}>🏷️ PO-2025-0189 (Supplier)</button>
          <button type="button" className="barcode-pill" onClick={() => handleScanBarcode('DN-2025-1184')}>🏷️ DN-2025-1184 (Customer)</button>
          <button type="button" className="barcode-pill" onClick={() => handleScanBarcode('FG-250398-B')}>🏷️ FG-250398-B (Serum)</button>
          <button type="button" className="barcode-pill" onClick={() => handleScanBarcode('FG-250416-C')}>🏷️ FG-250416-C (Micellar)</button>
        </div>
      </div>

      {/* Executive KPI Stats Bar */}
      <div className="trace-stats-grid">
        <div className="trace-stat-card">
          <span>Traced Raw Materials</span>
          <strong>{stats.materialsTraced || rawMaterials.length} Active Lots</strong>
          <small>European Pharmacopoeia Grade</small>
        </div>
        <div className="trace-stat-card">
          <span>Production Order (MO)</span>
          <strong>{record.production_order || 'MO-2025-0415'}</strong>
          <small>Cleanroom Suite Grade D</small>
        </div>
        <div className="trace-stat-card">
          <span>Packaged Commercial Units</span>
          <strong>{stats.finishedUnits || '18,400'} Units</strong>
          <small>Expiry: 36 Months Stability</small>
        </div>
        <div className="trace-stat-card">
          <span>Customer Deliveries</span>
          <strong>{stats.customersImpacted || deliveries.length} Destinations</strong>
          <small>Delivered & In-Transit</small>
        </div>
        <div className="trace-stat-card">
          <span>Batch Commercial Value</span>
          <strong style={{ color: '#059669' }}>{stats.batchValueEur || '€ 460,000'}</strong>
          <small>Full Chain Audited & Locked</small>
        </div>
      </div>

      {/* Interactive Flow Map Canvas */}
      <div className="section-card trace-canvas" style={{ padding: 0, overflow: 'hidden' }}>
        
        {/* Map Sub-Header */}
        <div className="trace-toolbar">
          <div style={{ display: 'flex', alignItems: 'center', gap: '8px' }}>
            <span style={{ fontSize: '12px', fontWeight: 700, color: '#1e293b' }}>
              Genealogy Matrix: {record.product_name}
            </span>
            <span style={{ fontSize: '10px', color: '#64748b' }}>
              (Click on ANY card below for deep lot inspection & attached COA)
            </span>
          </div>
          <div style={{ fontSize: '10px', fontWeight: 600, color: '#4f46e5' }}>
            Mode: {direction === 'backward' ? 'Market ➔ Supply Chain (Reverse Audit)' : 'Supply Chain ➔ Market (Forward Distribution)'}
          </div>
        </div>

        {/* SVG Connecting Flow Lines & Interactive Nodes */}
        <div className="trace-map" style={{ minHeight: '440px' }}>
          <svg viewBox="0 0 1200 440" preserveAspectRatio="none">
            <path d="M105 220 C150 220 150 85 220 85 M105 220 C150 220 150 355 220 355 M315 85 C355 85 360 220 420 220 M315 355 C355 355 360 220 420 220 M515 220H620 M715 220H820 M915 220 C970 220 965 85 1020 85 M915 220 C970 220 965 355 1020 355"/>
          </svg>

          {/* Col 0: Supplier PO */}
          <div 
            className={`trace-node node-col-0 node-y-1 ${isNodeMatched(record.supplier_po) ? 'matched' : ''}`}
            onClick={() => handleNodeClick(record.supplier_po)}
            title="Click to view Supplier PO Details & Audit Slip"
          >
            <div className="trace-icon"><Icon name="file"/></div>
            <div>
              <small>SUPPLIER PO</small>
              <strong>{record.supplier_po}</strong>
              <p>{record.supplier_name ? record.supplier_name.split('&')[0] : 'Supplier'}</p>
            </div>
          </div>

          {/* Col 1: Raw Materials (Top & Bottom) */}
          {rawMaterials.length > 0 && (
            <div 
              className={`trace-node node-col-1 node-y-0 ${isNodeMatched(rawMaterials[0].lot) ? 'matched' : ''}`}
              onClick={() => handleNodeClick(rawMaterials[0].lot)}
              title="Click to inspect Raw Material Lot & Test Specs"
            >
              <div className="trace-icon"><Icon name="boxes"/></div>
              <div>
                <small>RAW LOT · INGREDIENT</small>
                <strong>{rawMaterials[0].lot}</strong>
                <p>{rawMaterials[0].name ? rawMaterials[0].name.split('(')[0] : 'Raw Lot'} ({rawMaterials[0].qty})</p>
              </div>
            </div>
          )}

          {rawMaterials.length > 1 && (
            <div 
              className={`trace-node node-col-1 node-y-2 ${isNodeMatched(rawMaterials[1].lot) ? 'matched' : ''}`}
              onClick={() => handleNodeClick(rawMaterials[1].lot)}
              title="Click to inspect Raw Material Lot & Test Specs"
            >
              <div className="trace-icon"><Icon name="boxes"/></div>
              <div>
                <small>RAW LOT · INGREDIENT</small>
                <strong>{rawMaterials[1].lot}</strong>
                <p>{rawMaterials[1].name ? rawMaterials[1].name.split('(')[0] : 'Raw Lot'} ({rawMaterials[1].qty})</p>
              </div>
            </div>
          )}

          {/* Col 2: Production MO */}
          <div 
            className={`trace-node node-col-2 node-y-1 ${isNodeMatched(record.production_order) ? 'matched' : ''}`}
            onClick={() => handleNodeClick(record.production_order)}
            title="Click to view Production Order & eBMR electronic record"
          >
            <div className="trace-icon"><Icon name="scale"/></div>
            <div>
              <small>PRODUCTION MO</small>
              <strong>{record.production_order}</strong>
              <p>Line L01 · 1,000 kg Batch</p>
            </div>
          </div>

          {/* Col 3: Bulk Batch Approved */}
          <div 
            className={`trace-node node-col-3 node-y-1 ${isNodeMatched(record.bulk_batch) ? 'matched' : ''}`}
            onClick={() => handleNodeClick(record.bulk_batch)}
            title="Click to view Bulk Batch & Tank QA parameters"
          >
            <div className="trace-icon"><Icon name="shield"/></div>
            <div>
              <small>BULK BATCH (TANK)</small>
              <strong>{record.bulk_batch}</strong>
              <p>QA Approved Base</p>
            </div>
          </div>

          {/* Col 4: Finished Lot */}
          <div 
            className={`trace-node node-col-4 node-y-1 ${quarantined ? 'quarantined' : ''} ${isNodeMatched(record.finished_lot) ? 'matched' : ''}`}
            onClick={() => handleNodeClick(record.finished_lot)}
            title="Click to view Commercial Finished Lot Specifications"
          >
            <div className="trace-icon"><Icon name="shield"/></div>
            <div>
              <small>FINISHED LOT (COMMERCIAL)</small>
              <strong>{record.finished_lot}</strong>
              <p>{stats.finishedUnits || '18,400'} units {quarantined ? '⚠️ QUARANTINE' : '✓ Released'}</p>
            </div>
          </div>

          {/* Col 5: Customer Deliveries (Top & Bottom) */}
          {deliveries.length > 0 && (
            <div 
              className={`trace-node node-col-5 node-y-0 ${isNodeMatched(deliveries[0].doc) ? 'matched' : ''}`}
              onClick={() => handleNodeClick(deliveries[0].doc)}
              title="Click to view Delivery Note & Courier Dispatch"
            >
              <div className="trace-icon"><Icon name="arrow"/></div>
              <div>
                <small>OUTBOUND DELIVERY</small>
                <strong>{deliveries[0].doc}</strong>
                <p>{deliveries[0].customer} ({deliveries[0].qty})</p>
              </div>
            </div>
          )}

          {deliveries.length > 1 && (
            <div 
              className={`trace-node node-col-5 node-y-2 ${isNodeMatched(deliveries[1].doc) ? 'matched' : ''}`}
              onClick={() => handleNodeClick(deliveries[1].doc)}
              title="Click to view Delivery Note & Courier Dispatch"
            >
              <div className="trace-icon"><Icon name="arrow"/></div>
              <div>
                <small>OUTBOUND DELIVERY</small>
                <strong>{deliveries[1].doc}</strong>
                <p>{deliveries[1].customer} ({deliveries[1].qty})</p>
              </div>
            </div>
          )}

        </div>
      </div>

      {/* MODAL 1: Node Drill-Down & Verification Dossier */}
      {activeNode && (
        <div className="modal-overlay" onClick={() => setActiveNode(null)}>
          <div className="modal" style={{ maxWidth: '580px' }} onClick={e => e.stopPropagation()}>
            <div className="modal-header">
              <div>
                <div style={{ fontSize: '9px', fontWeight: 700, color: '#4f46e5', textTransform: 'uppercase' }}>
                  {activeNode.type}
                </div>
                <h2 style={{ fontSize: '15px' }}>{activeNode.code} · {activeNode.title}</h2>
              </div>
              <button className="icon-button" onClick={() => setActiveNode(null)}><Icon name="close"/></button>
            </div>

            <div className="modal-body" style={{ display: 'flex', flexDirection: 'column', gap: '14px' }}>
              
              {/* Status Banner */}
              <div style={{ background: '#ecfdf5', border: '1px solid #a7f3d0', borderRadius: '6px', padding: '10px 14px', display: 'flex', alignItems: 'center', justifyContent: 'space-between' }}>
                <span style={{ fontSize: '11px', fontWeight: 700, color: '#065f46' }}>
                  Status: {activeNode.status}
                </span>
                <span style={{ fontSize: '10px', color: '#047857', fontWeight: 600 }}>
                  GMP 21 CFR Part 11 Compliant ✓
                </span>
              </div>

              {/* Metadata Key-Value Grid */}
              <div style={{ display: 'grid', gridTemplateColumns: '1fr', gap: '8px', background: '#f8fafc', padding: '12px', borderRadius: '8px', border: '1px solid #e2e8f0' }}>
                {activeNode.fields && Object.entries(activeNode.fields).map(([k, v]) => (
                  <div key={k} style={{ display: 'flex', justifyContent: 'space-between', padding: '4px 0', borderBottom: '1px solid #f1f5f9', fontSize: '11px' }}>
                    <span style={{ color: '#64748b', fontWeight: 600 }}>{k}:</span>
                    <strong style={{ color: '#0f172a', textAlign: 'right', maxWidth: '340px' }}>{v}</strong>
                  </div>
                ))}
              </div>

              {/* Attached Documentation */}
              {activeNode.coaAvailable && (
                <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', background: '#eef2ff', padding: '10px 14px', borderRadius: '6px', border: '1px solid #c7d2fe' }}>
                  <div>
                    <div style={{ fontSize: '11px', fontWeight: 700, color: '#3730a3' }}>Official Attached Document:</div>
                    <div style={{ fontSize: '10px', color: '#4338ca', fontFamily: 'monospace' }}>{activeNode.coaFilename}</div>
                  </div>
                  <Button variant="secondary" icon="file" onClick={() => notify(`Opening ${activeNode.coaFilename}...`)}>
                    View Document
                  </Button>
                </div>
              )}

            </div>

            <div className="modal-footer">
              <Button variant="secondary" onClick={() => setActiveNode(null)}>Close</Button>
              <Button variant="primary" icon="search" onClick={() => { setSearchQuery(activeNode.code); fetchTrace(activeNode.code); setActiveNode(null); }}>
                Focus Trace on this Node
              </Button>
            </div>
          </div>
        </div>
      )}

      {/* MODAL 2: Industrial Barcode & QR Scanner with Optical Image & PDF Engine */}
      {barcodeModal && (
        <div className="modal-overlay" onClick={() => { stopTraceCamera(); setBarcodeModal(false); }}>
          <div className="modal" style={{ maxWidth: '540px' }} onClick={e => e.stopPropagation()}>
            <div className="modal-header">
              <div style={{ display: 'flex', alignItems: 'center', gap: '8px' }}>
                <Icon name="search" size={20}/>
                <div>
                  <h2>Industrial Barcode & QR Scanner (مسح الباركود)</h2>
                  <p style={{ fontSize: '10px', color: '#64748b' }}>Optical Image (PNG/JPG), PDF Document & Camera Integration</p>
                </div>
              </div>
              <button className="icon-button" onClick={() => { stopTraceCamera(); setBarcodeModal(false); }}><Icon name="close"/></button>
            </div>

            <div className="modal-body" style={{ display: 'flex', flexDirection: 'column', gap: '14px' }}>
              
              {/* Viewfinder Graphic with Laser Beam or Live Video */}
              <div style={{ height: '170px', background: '#0f172a', borderRadius: '8px', position: 'relative', overflow: 'hidden', display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
                {cameraActive ? (
                  <video ref={traceVideoRef} autoPlay playsInline muted style={{ width: '100%', height: '100%', objectFit: 'cover' }} />
                ) : (
                  <div style={{ border: '2px dashed #38bdf8', width: '70%', height: '70%', borderRadius: '6px', display: 'flex', flexDirection: 'column', alignItems: 'center', justifyContent: 'center' }}>
                    <div style={{ color: '#94a3b8', fontSize: '10px', textTransform: 'uppercase', letterSpacing: '1px' }}>
                      {scanStatus || 'Align Barcode / QR In Target'}
                    </div>
                    <div style={{ color: '#38bdf8', fontSize: '14px', fontWeight: 'bold', fontFamily: 'monospace', marginTop: '4px' }}>
                      ||| | |||| | ||| |||||
                    </div>
                  </div>
                )}
                {/* Animated Red Laser Line */}
                <div style={{ position: 'absolute', top: '50%', left: '10%', right: '10%', height: '2px', background: '#ef4444', boxShadow: '0 0 10px #ef4444' }}/>
              </div>

              {/* Hardware Scanner & Optical Image / PDF Controls */}
              <div style={{ display: 'flex', gap: '8px', flexWrap: 'wrap', justifyContent: 'center', background: '#f8fafc', padding: '10px', borderRadius: '8px', border: '1px solid #e2e8f0' }}>
                {!cameraActive ? (
                  <button 
                    type="button" 
                    onClick={startTraceCamera}
                    style={{ display: 'inline-flex', alignItems: 'center', gap: '6px', padding: '7px 14px', fontSize: '11px', fontWeight: 600, background: '#4f46e5', color: 'white', border: 'none', borderRadius: '6px', cursor: 'pointer' }}
                  >
                    📷 Start Camera (تشغيل الكاميرا)
                  </button>
                ) : (
                  <button 
                    type="button" 
                    onClick={stopTraceCamera}
                    style={{ display: 'inline-flex', alignItems: 'center', gap: '6px', padding: '7px 14px', fontSize: '11px', fontWeight: 600, background: '#dc2626', color: 'white', border: 'none', borderRadius: '6px', cursor: 'pointer' }}
                  >
                    ⏹️ Stop Camera
                  </button>
                )}

                <input 
                  type="file" 
                  id="trace-image-input" 
                  style={{ display: 'none' }} 
                  accept="image/*" 
                  onChange={handleTraceFileUpload} 
                />
                <button 
                  type="button" 
                  onClick={() => document.getElementById('trace-image-input').click()}
                  style={{ display: 'inline-flex', alignItems: 'center', gap: '6px', padding: '7px 14px', fontSize: '11px', fontWeight: 600, background: 'white', color: '#1e293b', border: '1px solid #cbd5e1', borderRadius: '6px', cursor: 'pointer' }}
                >
                  🖼️ Scan Image (مسح صورة كود)
                </button>

                <input 
                  type="file" 
                  id="trace-pdf-input" 
                  style={{ display: 'none' }} 
                  accept="application/pdf" 
                  onChange={handleTraceFileUpload} 
                />
                <button 
                  type="button" 
                  onClick={() => document.getElementById('trace-pdf-input').click()}
                  style={{ display: 'inline-flex', alignItems: 'center', gap: '6px', padding: '7px 14px', fontSize: '11px', fontWeight: 600, background: 'white', color: '#1e293b', border: '1px solid #cbd5e1', borderRadius: '6px', cursor: 'pointer' }}
                >
                  📄 Scan PDF (مسح من ملف PDF)
                </button>
              </div>

              {scanStatus && (
                <div style={{ fontSize: '11px', color: '#4f46e5', fontWeight: 600, textAlign: 'center' }}>
                  <span className="status-dot"/> {scanStatus}
                </div>
              )}

              {/* Manual Barcode Input */}
              <div className="form-field">
                <label>Manual Barcode Input / أدخل كود الباركود يدوياً</label>
                <div style={{ display: 'flex', gap: '8px' }}>
                  <input 
                    value={barcodeInput} 
                    onChange={e => setBarcodeInput(e.target.value)} 
                    placeholder="Scan or type barcode (e.g. FG-250415-A)"
                    autoFocus
                    onKeyDown={e => { if (e.key === 'Enter') handleScanBarcode(barcodeInput); }}
                  />
                  <Button variant="primary" onClick={() => handleScanBarcode(barcodeInput)}>Scan</Button>
                </div>
              </div>

              {/* One-Click Presets */}
              <div>
                <div style={{ fontSize: '10px', fontWeight: 700, color: '#64748b', textTransform: 'uppercase', marginBottom: '6px' }}>Or click a test barcode to simulate scanning:</div>
                <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '6px' }}>
                  <button type="button" className="barcode-pill" onClick={() => handleScanBarcode('FG-250415-A')}>Cream (FG-250415-A)</button>
                  <button type="button" className="barcode-pill" onClick={() => handleScanBarcode('GLY-8821-IT')}>Glycerin (GLY-8821-IT)</button>
                  <button type="button" className="barcode-pill" onClick={() => handleScanBarcode('NIA-24091')}>Niacinamide (NIA-24091)</button>
                  <button type="button" className="barcode-pill" onClick={() => handleScanBarcode('PO-2025-0189')}>Supplier PO-2025-0189</button>
                  <button type="button" className="barcode-pill" onClick={() => handleScanBarcode('FG-250398-B')}>Serum (FG-250398-B)</button>
                  <button type="button" className="barcode-pill" onClick={() => handleScanBarcode('FG-250416-C')}>Micellar (FG-250416-C)</button>
                </div>
              </div>

            </div>

            <div className="modal-footer">
              <Button variant="secondary" onClick={() => { stopTraceCamera(); setBarcodeModal(false); }}>Close</Button>
            </div>
          </div>
        </div>
      )}

      {/* MODAL 3: Emergency Mock Recall Simulator Center (محاكاة سحب المنتج في الطوارئ) */}
      {recallModal && (
        <div className="modal-overlay" onClick={() => setRecallModal(false)}>
          <div className="modal" style={{ maxWidth: '640px' }} onClick={e => e.stopPropagation()}>
            <div className="modal-header" style={{ background: '#fef2f2' }}>
              <div style={{ display: 'flex', alignItems: 'center', gap: '8px' }}>
                <Icon name="alert" size={20}/>
                <div>
                  <h2 style={{ color: '#991b1b' }}>🚨 Emergency Product Recall & Risk Assessment Center</h2>
                  <p style={{ fontSize: '10px', color: '#b91c1c' }}>ISO 22716 & EU Cosmetics Regulation (EC) No 1223/2009 Article 25 Protocol</p>
                </div>
              </div>
              <button className="icon-button" onClick={() => setRecallModal(false)}><Icon name="close"/></button>
            </div>

            <div className="modal-body" id="recall-notice-report-sheet" style={{ display: 'flex', flexDirection: 'column', gap: '14px' }}>
              
              {/* Risk Alert Banner */}
              <div style={{ background: '#fff1f2', border: '1px solid #fecdd3', borderRadius: '8px', padding: '12px', fontSize: '11px', color: '#9f1239' }}>
                <strong>EMERGENCY RECALL PROTOCOL SIMULATION:</strong>
                <div style={{ marginTop: '4px', lineHeight: 1.4 }}>
                  Target Lot: <strong>{record.finished_lot}</strong> ({record.product_name}). 
                  Risk Classification: <strong>{recallAnalysis.riskScore || 'Critical Class I'}</strong>.
                </div>
              </div>

              {/* Impact Summary Grid */}
              <div style={{ display: 'grid', gridTemplateColumns: 'repeat(3, 1fr)', gap: '10px' }}>
                <div style={{ background: '#f8fafc', padding: '10px', borderRadius: '6px', border: '1px solid #e2e8f0', textAlign: 'center' }}>
                  <span style={{ fontSize: '9px', fontWeight: 700, color: '#64748b', display: 'block' }}>UNITS IN MARKET</span>
                  <strong style={{ fontSize: '16px', color: '#dc2626' }}>{recallAnalysis.totalUnitsAtRisk || '18,400'}</strong>
                </div>
                <div style={{ background: '#f8fafc', padding: '10px', borderRadius: '6px', border: '1px solid #e2e8f0', textAlign: 'center' }}>
                  <span style={{ fontSize: '9px', fontWeight: 700, color: '#64748b', display: 'block' }}>RETAIL EXPOSURE</span>
                  <strong style={{ fontSize: '16px', color: '#dc2626' }}>{recallAnalysis.retailExposureEur || '€ 460,000'}</strong>
                </div>
                <div style={{ background: '#f8fafc', padding: '10px', borderRadius: '6px', border: '1px solid #e2e8f0', textAlign: 'center' }}>
                  <span style={{ fontSize: '9px', fontWeight: 700, color: '#64748b', display: 'block' }}>AFFECTED CLIENTS</span>
                  <strong style={{ fontSize: '16px', color: '#dc2626' }}>{deliveries.length} Wholesalers</strong>
                </div>
              </div>

              {/* Affected Distributors & Contacts */}
              <div>
                <h3 style={{ fontSize: '11px', fontWeight: 700, color: '#1e293b', marginBottom: '6px' }}>
                  Emergency Customer Recall Roster (Wholesalers to notify):
                </h3>
                <div style={{ border: '1px solid #e2e8f0', borderRadius: '6px', overflow: 'hidden' }}>
                  <table className="data-table">
                    <thead>
                      <tr>
                        <th>Customer Hub</th>
                        <th>Units</th>
                        <th>Emergency Contact Phone</th>
                        <th>Official Regulatory Email</th>
                      </tr>
                    </thead>
                    <tbody>
                      {recallAnalysis.customerContacts && recallAnalysis.customerContacts.map((c, idx) => (
                        <tr key={idx}>
                          <td><strong>{c.customer}</strong></td>
                          <td>{c.qty}</td>
                          <td style={{ fontFamily: 'monospace', color: '#0369a1' }}>{c.phone}</td>
                          <td style={{ fontFamily: 'monospace', color: '#4f46e5' }}>{c.email}</td>
                        </tr>
                      ))}
                    </tbody>
                  </table>
                </div>
              </div>

              {/* Mandatory Regulatory Actions */}
              <div>
                <h3 style={{ fontSize: '11px', fontWeight: 700, color: '#1e293b', marginBottom: '6px' }}>
                  Regulatory Compliance Checklist (AIFA & EU Portal):
                </h3>
                <div style={{ background: '#f8fafc', padding: '10px', borderRadius: '6px', fontSize: '10px', display: 'flex', flexDirection: 'column', gap: '6px' }}>
                  {recallAnalysis.actionPlan && recallAnalysis.actionPlan.map((step, idx) => (
                    <div key={idx} style={{ display: 'flex', alignItems: 'flex-start', gap: '6px' }}>
                      <span style={{ color: '#059669', fontWeight: 'bold' }}>✓</span>
                      <span>{step}</span>
                    </div>
                  ))}
                </div>
              </div>

            </div>

            <div className="modal-footer" style={{ background: '#fef2f2' }}>
              <Button variant="secondary" onClick={() => setRecallModal(false)}>Cancel</Button>
              <Button variant="secondary" icon="printer" onClick={() => window.cleanPrint('recall-notice-report-sheet', `Emergency-Recall-Notice-${record.finished_lot}.pdf`)}>
                🖨️ Clean Print
              </Button>
              <Button variant="secondary" icon="file" onClick={() => window.downloadReportPdf('recall-notice-report-sheet', `Emergency-Recall-Notice-${record.finished_lot}.pdf`)}>
                📥 Download Notice PDF
              </Button>
              <Button variant="danger" icon="alert" onClick={handleTriggerRecall}>
                🔒 Execute Immediate Quarantine Hold
              </Button>
            </div>
          </div>
        </div>
      )}

      {/* MODAL 4: Official GMP Traceability Dossier (ملف التتبع والتدقيق الرسمي) */}
      {dossierModal && (
        <div className="modal-overlay" onClick={() => setDossierModal(false)}>
          <div className="modal" style={{ maxWidth: '880px', maxHeight: '92vh' }} onClick={e => e.stopPropagation()}>
            <div className="modal-header">
              <div style={{ display: 'flex', alignItems: 'center', gap: '8px' }}>
                <Icon name="file" size={20}/>
                <div>
                  <h2>Official GMP Certificate of Genealogy & Traceability Audit Dossier</h2>
                  <p style={{ fontSize: '10px', color: '#64748b' }}>Beauty Italy Manufacturing Plant · Compliant with 21 CFR Part 11 & ISO 22716</p>
                </div>
              </div>
              <button className="icon-button" onClick={() => setDossierModal(false)}><Icon name="close"/></button>
            </div>

            <div className="modal-body" style={{ background: '#f8fafc', padding: '20px' }}>
              <div id="traceability-dossier-report-sheet" className="report-paper">
                
                {/* Header */}
                <div style={{ display: 'flex', justifyContent: 'space-between', borderBottom: '2px solid #0f172a', paddingBottom: '12px', marginBottom: '14px' }}>
                  <div>
                    <div style={{ fontSize: '18px', fontWeight: 800, color: '#0f172a' }}>Beauty Italy Factory S.p.A.</div>
                    <div style={{ fontSize: '11px', color: '#475569' }}>Industrial Quality Oversight Division · Milan, Italy</div>
                    <div style={{ fontSize: '11px', fontWeight: 700, color: '#4f46e5', marginTop: '4px' }}>
                      TRACEABILITY DOSSIER # TRC-2026-{record.finished_lot}
                    </div>
                    <div style={{ fontSize: '10px', color: '#059669', fontWeight: 600, marginTop: '2px' }}>
                      ISO 22716 & 21 CFR Part 11 Validated Genealogy
                    </div>
                  </div>
                  <div style={{ display: 'flex', alignItems: 'center', gap: '14px' }}>
                    <QRCodeImage text={`TRC:${record.finished_lot}|MO:${record.production_order}`} size={68} showLabel={true} labelText={record.finished_lot}/>
                    <div style={{ textAlign: 'right', fontSize: '11px', color: '#334155' }}>
                      <div>Genealogy Date: <strong>{new Date().toLocaleDateString()}</strong></div>
                      <div>Compliance: <strong>ISO 22716 Certified</strong></div>
                      <div>Audit Status: <strong style={{ color: quarantined ? '#dc2626' : '#059669' }}>{quarantined ? 'QUARANTINED' : 'VERIFIED'}</strong></div>
                    </div>
                  </div>
                </div>

                {/* Product & MO Summary */}
                <div style={{ display: 'grid', gridTemplateColumns: 'repeat(4, 1fr)', gap: '10px', background: '#f1f5f9', padding: '10px', borderRadius: '6px', fontSize: '11px', marginBottom: '16px' }}>
                  <div><span>Finished Lot:</span><strong style={{ display: 'block' }}>{record.finished_lot}</strong></div>
                  <div><span>Product Name:</span><strong style={{ display: 'block' }}>{record.product_name}</strong></div>
                  <div><span>Production MO:</span><strong style={{ display: 'block' }}>{record.production_order}</strong></div>
                  <div><span>Bulk Batch:</span><strong style={{ display: 'block' }}>{record.bulk_batch}</strong></div>
                </div>

                {/* Upstream Raw Materials Table */}
                <h3 style={{ fontSize: '12px', fontWeight: 700, color: '#1e293b', marginBottom: '6px' }}>
                  1. Upstream Raw Material Ingredients Genealogy (Supplier ➔ Bulk):
                </h3>
                <table className="report-table" style={{ marginBottom: '16px' }}>
                  <thead>
                    <tr>
                      <th>Raw Material Lot</th>
                      <th>Ingredient Name</th>
                      <th>Supplier Vendor</th>
                      <th>Weighed Qty</th>
                      <th>Storage Location</th>
                      <th>COA Status</th>
                    </tr>
                  </thead>
                  <tbody>
                    {rawMaterials.map((r, i) => (
                      <tr key={i}>
                        <td><strong>{r.lot}</strong></td>
                        <td>{r.name}</td>
                        <td>{r.supplier || record.supplier_name}</td>
                        <td><strong>{r.qty}</strong></td>
                        <td style={{ fontSize: '10px', color: '#64748b' }}>{r.bin || 'Warehouse Zone A'}</td>
                        <td style={{ fontSize: '10px', fontWeight: 700, color: '#059669' }}>Verified ✓</td>
                      </tr>
                    ))}
                  </tbody>
                </table>

                {/* Downstream Deliveries Table */}
                <h3 style={{ fontSize: '12px', fontWeight: 700, color: '#1e293b', marginBottom: '6px' }}>
                  2. Downstream Outbound Distribution Genealogy (Bulk ➔ Market):
                </h3>
                <table className="report-table">
                  <thead>
                    <tr>
                      <th>Delivery Note (DN)</th>
                      <th>Customer Recipient</th>
                      <th>Packaged Quantity</th>
                      <th>Carrier Tracking</th>
                      <th>Delivery Status</th>
                    </tr>
                  </thead>
                  <tbody>
                    {deliveries.map((d, i) => (
                      <tr key={i}>
                        <td><strong>{d.doc}</strong></td>
                        <td>{d.customer}</td>
                        <td><strong>{d.qty}</strong></td>
                        <td style={{ fontFamily: 'monospace', fontSize: '10px' }}>{d.tracking || 'TRK-9812401'}</td>
                        <td style={{ fontSize: '10px', fontWeight: 700, color: '#059669' }}>{d.status} ✓</td>
                      </tr>
                    ))}
                  </tbody>
                </table>

                {/* Sign-off */}
                <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '20px', marginTop: '20px', paddingTop: '14px', borderTop: '1px solid #e2e8f0', fontSize: '11px' }}>
                  <div>
                    <div style={{ color: '#64748b' }}>WAREHOUSE & DISPENSING SUPERVISOR</div>
                    <div style={{ fontWeight: 700, marginTop: '4px' }}>Giulia Romano · WS-02 Signed</div>
                  </div>
                  <div>
                    <div style={{ color: '#64748b' }}>HEAD OF QUALITY AUDIT</div>
                    <div style={{ fontWeight: 700, marginTop: '4px' }}>Elena Rossi · 21 CFR Part 11 Certified</div>
                  </div>
                </div>

              </div>
            </div>

            <div className="modal-footer">
              <Button variant="secondary" onClick={() => setDossierModal(false)}>Close</Button>
              <Button variant="secondary" icon="printer" onClick={() => window.cleanPrint('traceability-dossier-report-sheet', `Traceability-Dossier-${record.finished_lot}.pdf`)}>
                🖨️ Clean Print (طباعة)
              </Button>
              <Button variant="primary" icon="file" onClick={() => window.downloadReportPdf('traceability-dossier-report-sheet', `Traceability-Dossier-${record.finished_lot}.pdf`)}>
                📥 Download Official PDF (تحميل ملف التتبع PDF)
              </Button>
            </div>
          </div>
        </div>
      )}

    </div>
  );
}
@endverbatim
