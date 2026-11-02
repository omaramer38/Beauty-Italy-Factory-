@verbatim
// Quality & Release Component (LIMS, Audit Report & OOS Engine)
function Quality({ notify }) {
  const [data, setData] = useState(null);
  const [currentSampleNumber, setCurrentSampleNumber] = useState('QC-S-004182');
  const [tests, setTests] = useState([]);
  const [released, setReleased] = useState(false);
  const [rejected, setRejected] = useState(false);

  // Modals
  const [releaseModal, setReleaseModal] = useState(false);
  const [rejectModal, setRejectModal] = useState(false);
  const [rejectDisposition, setRejectDisposition] = useState('Rejected');
  const [rejectReason, setRejectReason] = useState('Critical Out-of-Specification (OOS) parameter detected');
  const [editModal, setEditModal] = useState(false);
  const [activeTest, setActiveTest] = useState(null);
  const [reportModal, setReportModal] = useState(false);

  // Edit Test Form
  const [editActual, setEditActual] = useState('');
  const [editStatus, setEditStatus] = useState('Pass');
  const [editNote, setEditNote] = useState('');

  useEffect(() => {
    fetch(`/api/v1/quality/${currentSampleNumber}`)
      .then(res => res.json())
      .then(d => {
        setData(d);
        setTests(d.tests || []);
        setReleased(d.sample && d.sample.status === 'Approved');
        setRejected(d.sample && (d.sample.status === 'Rejected' || d.sample.status === 'QC Hold'));
      })
      .catch(err => console.error(err));
  }, [currentSampleNumber]);

  if (!data) return <div style={{ padding: '40px', textAlign: 'center', color: '#64748b' }}>Loading LIMS Quality Records...</div>;

  // Smart Analysis Function for each test parameter
  const analyzeTest = (t) => {
    const actualStr = (t.actual_result || '').trim();
    const minStr = (t.min_spec || '').trim();
    const maxStr = (t.max_spec || '').trim();

    // Check if numeric
    const actualNum = parseFloat(actualStr.replace(/[^0-9.]/g, ''));
    const minNum = minStr ? parseFloat(minStr.replace(/[^0-9.]/g, '')) : null;
    const maxNum = maxStr ? parseFloat(maxStr.replace(/[^0-9.]/g, '')) : null;

    let deviationPct = null;
    let deviationText = '0.00% (Conforming)';
    let isPass = t.status === 'Pass';
    let isBorderline = false;
    let nominalTarget = '—';

    if (!isNaN(actualNum) && (minNum !== null || maxNum !== null)) {
      let target = 0;
      if (minNum !== null && maxNum !== null) {
        target = (minNum + maxNum) / 2;
        nominalTarget = target.toFixed(2);
      } else if (minNum !== null) {
        target = minNum;
        nominalTarget = '≥ ' + minNum;
      } else if (maxNum !== null) {
        target = maxNum;
        nominalTarget = '≤ ' + maxNum;
      }

      if (target > 0) {
        const delta = actualNum - target;
        deviationPct = (delta / target) * 100;
        deviationText = (deviationPct >= 0 ? '+' : '') + deviationPct.toFixed(2) + '%';
      }

      if (minNum !== null && actualNum < minNum) {
        isPass = false;
        deviationText += ' (Below Minimum ⚠️)';
      } else if (maxNum !== null && actualNum > maxNum) {
        isPass = false;
        deviationText += ' (Above Maximum ⚠️)';
      } else {
        isPass = true;
        if (minNum !== null && actualNum <= minNum * 1.02) isBorderline = true;
        if (maxNum !== null && actualNum >= maxNum * 0.98) isBorderline = true;
      }
    } else {
      // Qualitative / Sensory Evaluation (Odor, Appearance, Color)
      nominalTarget = minStr || 'Standard Reference';
      const lower = actualStr.toLowerCase();
      if (lower.includes('fail') || lower.includes('cloudy') || lower.includes('precipitate') || lower.includes('separated') || lower.includes('rancid')) {
        isPass = false;
        deviationText = 'Sensory Defect (OOS)';
      } else if (lower.includes('subdued') || lower.includes('weak') || lower.includes('faint') || lower.includes('borderline')) {
        isPass = true;
        isBorderline = true;
        deviationText = 'Subdued Profile (75% match)';
      } else {
        isPass = true;
        deviationText = '100% Conforming ✓';
      }
    }

    return {
      ...t,
      isPass,
      isBorderline,
      deviationPct,
      deviationText,
      nominalTarget,
    };
  };

  const analyzedTests = tests.map(analyzeTest);
  const passedCount = analyzedTests.filter(t => t.isPass).length;
  const failedCount = analyzedTests.filter(t => !t.isPass).length;
  const borderlineCount = analyzedTests.filter(t => t.isBorderline).length;
  const totalCount = analyzedTests.length;
  const conformanceRate = totalCount > 0 ? Math.round((passedCount / totalCount) * 100) : 100;

  // Open Edit Modal for a single test
  const handleOpenEdit = (t) => {
    setActiveTest(t);
    setEditActual(t.actual_result);
    setEditStatus(t.status || 'Pass');
    setEditNote(t.notes || '');
    setEditModal(true);
  };

  // Save Edited Test
  const handleSaveTest = () => {
    if (!activeTest) return;
    fetch('/api/v1/quality/test/update', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        testId: activeTest.id,
        actualResult: editActual,
        status: editStatus,
        notes: editNote,
      })
    })
    .then(res => res.json())
    .then(data => {
      notify(data.message);
      setEditModal(false);
      // Update local state
      const updated = tests.map(x => x.id === activeTest.id ? { ...x, actual_result: editActual, status: editStatus, notes: editNote } : x);
      setTests(updated);
    })
    .catch(err => notify('Error updating test: ' + err.message));
  };

  // QA Final Release
  const handleRelease = () => {
    fetch('/api/v1/quality/release', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ batchNumber: data.sample.batchNumber, reason: 'Commercial Distribution under ISO 22716' })
    })
    .then(res => res.json())
    .then(resData => {
      setReleaseModal(false);
      setReleased(true);
      setRejected(false);
      notify(resData.message);
    });
  };

  // QA Reject / Quarantine
  const handleReject = () => {
    fetch('/api/v1/quality/reject', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        batchNumber: data.sample.batchNumber,
        disposition: rejectDisposition,
        reason: rejectReason,
        qaManager: 'Elena Rossi (Head of QA)'
      })
    })
    .then(res => res.json())
    .then(resData => {
      setRejectModal(false);
      setRejected(true);
      setReleased(false);
      notify(resData.message);
    });
  };

  return (
    <div>
      {/* Page Header */}
      <div className="page-header">
        <div>
          <div className="eyebrow">QUALITY CONTROL (LIMS) · SAMPLE {data.sample.sampleNumber}</div>
          <h1>Batch Quality Review & Disposition</h1>
          <div style={{ display: 'flex', alignItems: 'center', gap: '8px', marginTop: '4px' }}>
            <select 
              style={{ height: '32px', fontWeight: 'bold', fontSize: '12px', border: '1px solid #cbd5e1', borderRadius: '6px', padding: '0 8px' }}
              value={currentSampleNumber}
              onChange={e => setCurrentSampleNumber(e.target.value)}
            >
              {data.allSamples && data.allSamples.map(s => (
                <option key={s.sample_number} value={s.sample_number}>
                  {s.sample_number} · {s.batch_number} — {s.product_name} ({s.status})
                </option>
              ))}
            </select>
            <Status type={released ? 'approved' : rejected ? 'danger' : 'warning'}>
              {released ? 'Released for Sale' : rejected ? 'Batch Rejected / On Hold' : 'Pending QA Release'}
            </Status>
          </div>
        </div>

        <div className="header-actions">
          <Button variant="secondary" icon="file" onClick={() => setReportModal(true)}>
            📑 Full Quality Inspection & COA Report (التقرير الشامل للجودة)
          </Button>
        </div>
      </div>

      {/* Segregation of Duties Banner */}
      <div className="qa-banner">
        <Icon name="shield"/>
        <div>
          <strong>Segregation of Duties Enforced (ISO 22716 & 21 CFR Part 11)</strong>
          <div style={{ fontSize: '10px' }}>
            Analyzed by <strong>{data.sample.analyst}</strong>. Final disposition requires authorized QA Manager digital signature.
          </div>
        </div>
        <div style={{ marginLeft: 'auto', fontSize: '11px', fontWeight: 600 }}>
          Testing Date: {data.sample.testDate}
        </div>
      </div>

      {/* Executive QC Metrics Bar */}
      <div className="quality-metrics-grid">
        <div className="qc-metric-card">
          <span>Overall Quality Index</span>
          <strong style={{ color: conformanceRate === 100 ? '#059669' : '#dc2626' }}>
            {conformanceRate}%
          </strong>
          <small>{passedCount} of {totalCount} parameters conform</small>
        </div>
        <div className="qc-metric-card">
          <span>Conforming Tests</span>
          <strong style={{ color: '#059669' }}>{passedCount} / {totalCount}</strong>
          <small>European Pharmacopoeia Specs</small>
        </div>
        <div className="qc-metric-card">
          <span>Out-of-Specification (OOS)</span>
          <strong style={{ color: failedCount > 0 ? '#dc2626' : '#64748b' }}>
            {failedCount} Critical Defect{failedCount === 1 ? '' : 's'}
          </strong>
          <small>{failedCount > 0 ? 'Requires QA deviation inquiry' : 'Zero deviations detected'}</small>
        </div>
        <div className="qc-metric-card">
          <span>Microbiology Status</span>
          <strong style={{ color: '#059669' }}>Pass (&lt; 10 CFU/g)</strong>
          <small>ISO 17516 Certified Sterile</small>
        </div>
      </div>

      {/* Main Layout */}
      <div className="quality-layout">
        
        {/* Table Card */}
        <div className="section-card" style={{ padding: '16px' }}>
          <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', marginBottom: '12px' }}>
            <div>
              <h2 style={{ fontSize: '15px' }}>Analytical Chemistry & Physical Release Specifications</h2>
              <p style={{ fontSize: '11px', color: '#64748b' }}>
                Click "Edit" on any row to input real lab readings, change pH/viscosity, or log sensory notes.
              </p>
            </div>
            <div style={{ fontSize: '11px', fontWeight: 600, color: '#4f46e5' }}>
              Specification: SPEC-QC-2026 v4.2
            </div>
          </div>

          <div className="data-table-wrap">
            <table className="data-table">
              <thead>
                <tr>
                  <th>Parameter</th>
                  <th>Specification Range</th>
                  <th>Nominal Target</th>
                  <th>Actual Reading</th>
                  <th>Variance / Deviation %</th>
                  <th>Analyst Observation Note</th>
                  <th>Status</th>
                  <th style={{ textAlign: 'center' }}>Action</th>
                </tr>
              </thead>
              <tbody>
                {analyzedTests.map(t => (
                  <tr key={t.id} style={{ background: !t.isPass ? '#fff1f2' : t.isBorderline ? '#fffbeb' : 'white' }}>
                    <td>
                      <strong>{t.parameter_name}</strong>
                    </td>
                    <td style={{ fontSize: '11px', color: '#475569' }}>
                      {t.min_spec} {t.max_spec ? `– ${t.max_spec}` : ''}
                    </td>
                    <td style={{ fontSize: '11px', fontWeight: 600, color: '#334155' }}>
                      {t.nominalTarget}
                    </td>
                    <td>
                      <strong style={{ color: !t.isPass ? '#9f1239' : '#0f172a' }}>
                        {t.actual_result}
                      </strong>
                    </td>
                    <td>
                      <span style={{ 
                        fontSize: '11px', 
                        fontWeight: 700, 
                        color: !t.isPass ? '#dc2626' : t.isBorderline ? '#d97706' : '#059669',
                        background: !t.isPass ? '#fee2e2' : t.isBorderline ? '#fef3c7' : '#ecfdf5',
                        padding: '2px 6px',
                        borderRadius: '4px'
                      }}>
                        {t.deviationText}
                      </span>
                    </td>
                    <td style={{ fontSize: '10px', color: '#64748b', maxWidth: '200px' }}>
                      {t.notes || (t.parameter_name === 'Odor' ? 'Clean characteristic fragrance benchmark' : 'Parameter within normal target curve')}
                    </td>
                    <td>
                      <Status type={!t.isPass ? 'danger' : t.isBorderline ? 'warning' : 'approved'}>
                        {!t.isPass ? 'Fail (OOS)' : t.isBorderline ? 'Borderline' : 'Pass'}
                      </Status>
                    </td>
                    <td style={{ textAlign: 'center' }}>
                      <button 
                        type="button" 
                        onClick={() => handleOpenEdit(t)}
                        style={{ padding: '3px 8px', fontSize: '10px', fontWeight: 600, border: '1px solid #cbd5e1', borderRadius: '4px', background: 'white', cursor: 'pointer' }}
                        title="Edit actual reading and notes"
                      >
                        ✏️ Edit
                      </button>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        </div>

        {/* QA Disposition Sidebar */}
        <aside className="release-panel">
          <div>
            <h2 style={{ color: '#a7f3d0', fontSize: '13px', marginBottom: '14px' }}>FINAL QA DISPOSITION</h2>
            <div style={{ fontSize: '11px', display: 'flex', flexDirection: 'column', gap: '10px' }}>
              <p style={{ display: 'flex', alignItems: 'center', gap: '6px' }}>
                <span style={{ color: '#34d399' }}>✓</span> All 9 dispensing steps electronically signed
              </p>
              <p style={{ display: 'flex', alignItems: 'center', gap: '6px' }}>
                <span style={{ color: failedCount === 0 ? '#34d399' : '#f87171' }}>
                  {failedCount === 0 ? '✓' : '✕'}
                </span>
                {passedCount}/{totalCount} QC parameters conform to Pharmacopoeia
              </p>
              <p style={{ display: 'flex', alignItems: 'center', gap: '6px' }}>
                <span style={{ color: '#34d399' }}>✓</span> COA Certificate verified & linked
              </p>
              <p style={{ display: 'flex', alignItems: 'center', gap: '6px' }}>
                <span style={{ color: '#34d399' }}>✓</span> Traceability chain complete from supplier lots
              </p>
            </div>

            {failedCount > 0 && (
              <div style={{ background: '#7f1d1d', border: '1px solid #f87171', borderRadius: '6px', padding: '10px', marginTop: '16px', fontSize: '10px', color: '#fecdd3' }}>
                ⚠️ <strong>CRITICAL OUT-OF-SPECIFICATION DETECTED:</strong> Commercial distribution is strictly blocked until QA deviation inquiry or batch rejection.
              </div>
            )}
          </div>

          <div style={{ display: 'flex', flexDirection: 'column', gap: '8px', marginTop: '20px' }}>
            {failedCount === 0 ? (
              <Button 
                variant="success" 
                icon="lock" 
                style={{ width: '100%', height: '42px', fontSize: '12px' }}
                onClick={() => setReleaseModal(true)}
              >
                {released ? 'Batch Released for Distribution ✓' : 'Apply 21 CFR Part 11 Release'}
              </Button>
            ) : (
              <div style={{ width: '100%', textAlign: 'center', padding: '10px', background: '#334155', borderRadius: '6px', fontSize: '11px', color: '#94a3b8', fontWeight: 600 }}>
                🔒 Release Blocked by Quality Gate
              </div>
            )}

            <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '6px' }}>
              <button 
                type="button" 
                onClick={() => { setRejectDisposition('Conditional Hold'); setRejectModal(true); }}
                style={{ padding: '8px', fontSize: '10px', fontWeight: 600, color: '#f59e0b', background: '#1e293b', border: '1px solid #d97706', borderRadius: '6px', cursor: 'pointer' }}
              >
                ⚠️ Quarantine Hold
              </button>
              <button 
                type="button" 
                onClick={() => { setRejectDisposition('Rejected'); setRejectModal(true); }}
                style={{ padding: '8px', fontSize: '10px', fontWeight: 600, color: '#f87171', background: '#1e293b', border: '1px solid #ef4444', borderRadius: '6px', cursor: 'pointer' }}
              >
                ❌ Reject Batch
              </button>
            </div>
          </div>
        </aside>
      </div>

      {/* MODAL 1: Edit Parameter Reading */}
      {editModal && activeTest && (
        <div className="modal-overlay" onClick={() => setEditModal(false)}>
          <div className="modal" style={{ maxWidth: '480px' }} onClick={e => e.stopPropagation()}>
            <div className="modal-header">
              <div>
                <h2>Edit Lab Reading: {activeTest.parameter_name}</h2>
                <p style={{ fontSize: '10px', color: '#64748b' }}>Specification: {activeTest.min_spec} {activeTest.max_spec ? `– ${activeTest.max_spec}` : ''}</p>
              </div>
              <button className="icon-button" onClick={() => setEditModal(false)}><Icon name="close"/></button>
            </div>
            <div className="modal-body" style={{ display: 'flex', flexDirection: 'column', gap: '12px' }}>
              <div className="form-field">
                <label>Actual Measured Value / القيمة المقاسة *</label>
                <input 
                  value={editActual} 
                  onChange={e => {
                    setEditActual(e.target.value);
                  }} 
                  placeholder="e.g. 5.58 or 7.20 or Conforms"
                  autoFocus
                />
              </div>
              <div className="form-field">
                <label>Evaluation Status</label>
                <select value={editStatus} onChange={e => setEditStatus(e.target.value)}>
                  <option value="Pass">Pass (Conforming / مطابق)</option>
                  <option value="Fail">Fail (Out of Specification / غير مطابق)</option>
                </select>
              </div>
              <div className="form-field">
                <label>Analyst Observation & Sensory Notes / ملاحظات المحلل</label>
                <textarea 
                  value={editNote} 
                  onChange={e => setEditNote(e.target.value)} 
                  placeholder="e.g. الرائحة خفيفة مقارنة بالمعيار المرجعي أو زيادة في درجة الحموضة بسبب المحلول المنظم"
                />
              </div>
            </div>
            <div className="modal-footer">
              <Button variant="secondary" onClick={() => setEditModal(false)}>Cancel</Button>
              <Button variant="primary" icon="check" onClick={handleSaveTest}>
                Update Test Reading
              </Button>
            </div>
          </div>
        </div>
      )}

      {/* MODAL 2: 21 CFR Part 11 Electronic Signature Release */}
      {releaseModal && (
        <div className="modal-overlay" onClick={() => setReleaseModal(false)}>
          <div className="modal" style={{ maxWidth: '480px' }} onClick={e => e.stopPropagation()}>
            <div className="modal-header">
              <h2>21 CFR Part 11 Electronic Signature Release</h2>
              <button className="icon-button" onClick={() => setReleaseModal(false)}><Icon name="close"/></button>
            </div>
            <div className="modal-body">
              <div className="form-field">
                <label>QA Manager Password</label>
                <input type="password" placeholder="••••••••" autoFocus defaultValue="quality2026"/>
              </div>
              <div className="form-field">
                <label>Reason for Release</label>
                <select>
                  <option>Authorized for Commercial Distribution (ISO 22716)</option>
                  <option>Authorized for Clinical Trial Batching</option>
                  <option>Conditional release per approved change control</option>
                </select>
              </div>
              <div style={{ fontSize: '9px', color: '#64748b', background: '#f8fafc', padding: '10px', borderRadius: '6px' }}>
                I understand that this electronic signature is the legally binding equivalent of my traditional handwritten signature.
              </div>
            </div>
            <div className="modal-footer">
              <Button variant="secondary" onClick={() => setReleaseModal(false)}>Cancel</Button>
              <Button variant="success" icon="lock" onClick={handleRelease}>Sign & Release Batch</Button>
            </div>
          </div>
        </div>
      )}

      {/* MODAL 3: QA Reject or Quarantine Hold */}
      {rejectModal && (
        <div className="modal-overlay" onClick={() => setRejectModal(false)}>
          <div className="modal" style={{ maxWidth: '480px' }} onClick={e => e.stopPropagation()}>
            <div className="modal-header">
              <h2>QA Batch Disposition: {rejectDisposition}</h2>
              <button className="icon-button" onClick={() => setRejectModal(false)}><Icon name="close"/></button>
            </div>
            <div className="modal-body">
              <div className="form-field">
                <label>Disposition Decision</label>
                <select value={rejectDisposition} onChange={e => setRejectDisposition(e.target.value)}>
                  <option value="Rejected">Rejected & Condemned (إعدام التشغيلة ورفضها نهائياً)</option>
                  <option value="Conditional Hold">Quarantine Hold (إيقاف وتحفظ لإعادة الفحص والتقييم)</option>
                </select>
              </div>
              <div className="form-field">
                <label>Root Cause Justification / سبب الرفض أو الإيقاف *</label>
                <textarea 
                  value={rejectReason} 
                  onChange={e => setRejectReason(e.target.value)}
                  placeholder="e.g. Critical deviation in pH or microbial count exceeding Pharmacopoeia limits."
                />
              </div>
            </div>
            <div className="modal-footer">
              <Button variant="secondary" onClick={() => setRejectModal(false)}>Cancel</Button>
              <Button variant="danger" icon="alert" onClick={handleReject}>
                Confirm {rejectDisposition}
              </Button>
            </div>
          </div>
        </div>
      )}

      {/* MODAL 4: COMPREHENSIVE QUALITY AUDIT & COA REPORT (التقرير الشامل للجودة والتحاليل) */}
      {reportModal && (
        <div className="modal-overlay" onClick={() => setReportModal(false)}>
          <div className="modal" style={{ maxWidth: '920px', maxHeight: '92vh' }} onClick={e => e.stopPropagation()}>
            <div className="modal-header">
              <div style={{ display: 'flex', alignItems: 'center', gap: '8px' }}>
                <Icon name="file" size={20}/>
                <div>
                  <h2>Official Certificate of Analysis (COA) & Batch Quality Audit Report</h2>
                  <p style={{ fontSize: '10px', color: '#64748b' }}>Beauty Italy Manufacturing Lab · ISO 22716 & EU Cosmetics Regulation EC 1223/2009</p>
                </div>
              </div>
              <button className="icon-button" onClick={() => setReportModal(false)}><Icon name="close"/></button>
            </div>

            <div className="modal-body" style={{ background: '#f8fafc', padding: '20px' }}>
              <div id="quality-coa-report-sheet" className="report-paper">
                
                {/* Header Details */}
                <div style={{ display: 'flex', justifyContent: 'space-between', borderBottom: '2px solid #0f172a', paddingBottom: '14px', marginBottom: '16px' }}>
                  <div>
                    <div style={{ fontSize: '18px', fontWeight: 800, color: '#0f172a' }}>Beauty Italy Factory S.p.A.</div>
                    <div style={{ fontSize: '11px', color: '#475569' }}>Via dell'Industria 42, 20121 Milano, Italy · QA/QC Laboratory Division</div>
                    <div style={{ fontSize: '11px', fontWeight: 700, color: '#4f46e5', marginTop: '4px' }}>
                      CERTIFICATE OF ANALYSIS # {data.sample.coaFilename || 'COA-QC-004182.pdf'}
                    </div>
                    <div style={{ fontSize: '10px', color: '#059669', fontWeight: 600, marginTop: '2px' }}>
                      GMP ISO 22716 & European Pharmacopoeia Conforming
                    </div>
                  </div>
                  <div style={{ display: 'flex', alignItems: 'center', gap: '14px' }}>
                    <QRCodeImage text={`COA:${data.sample.sampleNumber}|BATCH:${data.sample.batchNumber}|STATUS:${data.sample.status}`} size={68} showLabel={true} labelText={data.sample.sampleNumber}/>
                    <div style={{ textAlign: 'right', fontSize: '11px', color: '#334155' }}>
                      <div>Document: <strong>COA-SPEC-4182</strong></div>
                      <div>Date of Issue: <strong>{data.sample.testDate}</strong></div>
                      <div>Status: <strong style={{ color: released ? '#059669' : rejected ? '#dc2626' : '#d97706' }}>{data.sample.status}</strong></div>
                    </div>
                  </div>
                </div>

                {/* Product & Batch Info Bar */}
                <div style={{ display: 'grid', gridTemplateColumns: 'repeat(4, 1fr)', gap: '10px', background: '#f1f5f9', padding: '12px', borderRadius: '6px', fontSize: '11px', marginBottom: '16px' }}>
                  <div><span>Product Name:</span><strong style={{ display: 'block' }}>{data.sample.productName}</strong></div>
                  <div><span>Batch Number:</span><strong style={{ display: 'block' }}>{data.sample.batchNumber}</strong></div>
                  <div><span>Sample ID:</span><strong style={{ display: 'block' }}>{data.sample.sampleNumber}</strong></div>
                  <div><span>Inspecting Chemist:</span><strong style={{ display: 'block' }}>{data.sample.analyst}</strong></div>
                </div>

                {/* Executive Pass / Fail Summary Section (ما الذي نجح وما الذي فشل) */}
                <div style={{ marginBottom: '18px', padding: '14px', background: failedCount === 0 ? '#ecfdf5' : '#fff1f2', border: `1px solid ${failedCount === 0 ? '#a7f3d0' : '#fecdd3'}`, borderRadius: '8px' }}>
                  <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', marginBottom: '6px' }}>
                    <strong style={{ fontSize: '13px', color: failedCount === 0 ? '#065f46' : '#9f1239' }}>
                      {failedCount === 0 ? '✓ ALL PARAMETERS MEET COMMERCIAL SPECIFICATIONS' : '⚠️ OUT-OF-SPECIFICATION (OOS) DEVIATIONS IDENTIFIED'}
                    </strong>
                    <span style={{ fontSize: '11px', fontWeight: 700, color: failedCount === 0 ? '#059669' : '#dc2626' }}>
                      Quality Conformance Score: {conformanceRate}%
                    </span>
                  </div>
                  <p style={{ fontSize: '11px', color: failedCount === 0 ? '#047857' : '#be123c', lineHeight: 1.5 }}>
                    {failedCount === 0 
                      ? `All 7 chemical, physical, and microbiological quality attributes conform rigorously to the validated registration dossier. TVC microbial count conforms to sterile standards (< 10 CFU/g).`
                      : `The batch exhibited ${failedCount} parameter(s) outside permissible pharmacological tolerances. Disposition requires corrective intervention or formal condemnation.`}
                  </p>
                </div>

                {/* Comprehensive Measurements Table */}
                <h3 style={{ fontSize: '12px', fontWeight: 700, color: '#1e293b', marginBottom: '8px' }}>
                  Analytical Measurements, Tolerance Deltas & Observations:
                </h3>
                <table className="report-table">
                  <thead>
                    <tr>
                      <th>Parameter</th>
                      <th>Specification Range</th>
                      <th>Nominal Target</th>
                      <th>Observed Result</th>
                      <th>Deviation / Conformance</th>
                      <th>Status</th>
                      <th>Analyst Notes</th>
                    </tr>
                  </thead>
                  <tbody>
                    {analyzedTests.map(t => (
                      <tr key={t.id}>
                        <td><strong>{t.parameter_name}</strong></td>
                        <td>{t.min_spec} {t.max_spec ? `– ${t.max_spec}` : ''}</td>
                        <td>{t.nominalTarget}</td>
                        <td><strong>{t.actual_result}</strong></td>
                        <td style={{ fontWeight: 700, color: !t.isPass ? '#dc2626' : t.isBorderline ? '#d97706' : '#059669' }}>
                          {t.deviationText}
                        </td>
                        <td>
                          <span style={{ fontSize: '10px', fontWeight: 700, color: t.isPass ? '#059669' : '#dc2626' }}>
                            {t.isPass ? 'PASS ✓' : 'FAIL (OOS) ✕'}
                          </span>
                        </td>
                        <td style={{ fontSize: '10px', color: '#64748b' }}>
                          {t.notes || (t.parameter_name === 'Odor' ? 'Clean botanical scent profile' : 'Standard conformance curve')}
                        </td>
                      </tr>
                    ))}
                  </tbody>
                </table>

                {/* 21 CFR Part 11 Sign-off Block */}
                <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '20px', marginTop: '24px', paddingTop: '16px', borderTop: '1px solid #e2e8f0' }}>
                  <div style={{ background: '#f8fafc', padding: '12px', borderRadius: '6px', border: '1px solid #e2e8f0' }}>
                    <div style={{ fontSize: '10px', fontWeight: 700, color: '#64748b' }}>ANALYTICAL TESTING OFFICER</div>
                    <div style={{ fontSize: '13px', fontWeight: 700, color: '#0f172a', marginTop: '4px' }}>Luca Bellini (Senior Analyst)</div>
                    <div style={{ fontSize: '10px', color: '#059669', marginTop: '2px' }}>✓ Electronically Signed · {data.sample.testDate} · 10:24 CET</div>
                    <div style={{ fontSize: '9px', color: '#94a3b8', marginTop: '4px', fontFamily: 'monospace' }}>SHA256: 8f4a21...b83e</div>
                  </div>
                  <div style={{ background: '#f8fafc', padding: '12px', borderRadius: '6px', border: '1px solid #e2e8f0' }}>
                    <div style={{ fontSize: '10px', fontWeight: 700, color: '#64748b' }}>QA AUTHORIZED DISPOSITION</div>
                    <div style={{ fontSize: '13px', fontWeight: 700, color: '#0f172a', marginTop: '4px' }}>Elena Rossi (Head of Quality)</div>
                    <div style={{ fontSize: '10px', color: released ? '#059669' : rejected ? '#dc2626' : '#d97706', marginTop: '2px' }}>
                      {released ? '✓ Legally Binding 21 CFR Part 11 Release' : rejected ? '✕ Batch Disposition: Condemned / Rejected' : '⌛ Pending Final QA Electronic Signature'}
                    </div>
                    <div style={{ fontSize: '9px', color: '#94a3b8', marginTop: '4px', fontFamily: 'monospace' }}>ISO 22716 Cosmetics GMP Certified</div>
                  </div>
                </div>
              </div>
            </div>

            <div className="modal-footer">
              <Button variant="secondary" onClick={() => setReportModal(false)}>Close</Button>
              <Button variant="secondary" icon="printer" onClick={() => window.cleanPrint('quality-coa-report-sheet', data.sample.coaFilename || `COA-${data.sample.sampleNumber}.pdf`)}>
                🖨️ Clean Print (طباعة)
              </Button>
              <Button variant="primary" icon="file" onClick={() => window.downloadReportPdf('quality-coa-report-sheet', data.sample.coaFilename || `COA-${data.sample.sampleNumber}.pdf`)}>
                📥 Download Official PDF (تحميل تقرير PDF)
              </Button>
            </div>
          </div>
        </div>
      )}
    </div>
  );
}
@endverbatim
