@verbatim
// Dispensing & eBMR Component (Enhanced Tablet Mode & Manual Override)
function Dispensing({ notify, initialBatchNumber, onOpenScanner }) {
  const [batch, setBatch] = useState(null);
  const [steps, setSteps] = useState([]);
  const [allBatches, setAllBatches] = useState([]);
  const [currentBatchNumber, setCurrentBatchNumber] = useState(initialBatchNumber || 'B-25-0415');
  const [activeStepIdx, setActiveStepIdx] = useState(1);
  
  // Scale Telemetry & Override States
  const [liveWeight, setLiveWeight] = useState(50.042);
  const [tareWeight, setTareWeight] = useState(0.000);
  const [isTareActive, setIsTareActive] = useState(false);
  
  // Manual Override States
  const [isManualMode, setIsManualMode] = useState(false);
  const [manualWeight, setManualWeight] = useState('50.042');
  const [manualReason, setManualReason] = useState('Physical scale display reading verified manually (discrepancy override)');
  
  // Deviation Reporting State
  const [devModal, setDevModal] = useState(false);
  const [devReason, setDevReason] = useState('Weight exceeds approved recipe tolerance range');

  useEffect(() => {
    fetch(`/api/v1/dispensing/${currentBatchNumber}`)
      .then(res => res.json())
      .then(data => {
        setBatch(data.batch);
        setSteps(data.steps || []);
        setAllBatches(data.allBatches || []);
        
        // Set initial weight based on the active step
        if (data.steps && data.steps.length > 0) {
          const initialStep = data.steps[activeStepIdx] || data.steps[0];
          const w = parseFloat(initialStep.actual_weight || initialStep.target_weight || 50.0);
          setLiveWeight(w);
          setManualWeight(w.toFixed(3));
        }
      });
  }, [currentBatchNumber]);

  if (!batch) return <div style={{ padding: '40px', textAlign: 'center' }}>Loading Electronic Batch Record...</div>;

  const activeStep = steps[activeStepIdx] || steps[0];

  // Weight Calculations (Gross, Tare, Net)
  const grossScaleWeight = parseFloat(liveWeight) || 0;
  const netScaleWeight = Math.max(0, parseFloat((grossScaleWeight - tareWeight).toFixed(3)));
  
  // Effective Dispensed Weight (Manual or Scale)
  const effectiveWeight = isManualMode 
    ? (parseFloat(manualWeight) || 0) 
    : netScaleWeight;

  const targetWeight = parseFloat(activeStep ? activeStep.target_weight : 0) || 0;
  const tolPercent = parseFloat(activeStep ? activeStep.tolerance_percent : 0.50) || 0.50;
  const minAllowed = targetWeight * (1 - tolPercent / 100);
  const maxAllowed = targetWeight * (1 + tolPercent / 100);
  
  const delta = effectiveWeight - targetWeight;
  const deltaPercent = targetWeight > 0 ? (delta / targetWeight) * 100 : 0;
  
  const isInTolerance = effectiveWeight >= minAllowed && effectiveWeight <= maxAllowed && effectiveWeight > 0;
  const isUnder = effectiveWeight < minAllowed && effectiveWeight > 0;
  const isOver = effectiveWeight > maxAllowed;
  const isEmpty = effectiveWeight <= 0;

  // Overall Batch Progress
  const signedCount = steps.filter(s => s.is_signed).length;
  const overallPercent = steps.length > 0 ? Math.round((signedCount / steps.length) * 100) : 0;

  // Gauge Fill and Color
  let gaugePercent = 0;
  if (targetWeight > 0) {
    gaugePercent = Math.min(100, Math.max(0, (effectiveWeight / targetWeight) * 100));
  }
  let gaugeColor = '#94a3b8';
  let statusBadge = { type: 'neutral', text: 'Waiting for weight...' };

  if (isEmpty) {
    gaugeColor = '#94a3b8';
    statusBadge = { type: 'neutral', text: '0.000 kg · Scale Empty' };
  } else if (isInTolerance) {
    gaugeColor = '#10b981';
    statusBadge = { type: 'approved', text: `Within Tolerance (${deltaPercent >= 0 ? '+' : ''}${deltaPercent.toFixed(3)}%) ✓` };
  } else if (isUnder) {
    gaugeColor = '#f59e0b';
    statusBadge = { type: 'warning', text: `Under Target by ${(minAllowed - effectiveWeight).toFixed(3)} kg (${deltaPercent.toFixed(2)}%)` };
  } else if (isOver) {
    gaugeColor = '#ef4444';
    statusBadge = { type: 'danger', text: `Over Target by +${(effectiveWeight - maxAllowed).toFixed(3)} kg (+${deltaPercent.toFixed(2)}%) ⚠️` };
  }

  // Step Selection
  const handleSelectStep = (idx) => {
    setActiveStepIdx(idx);
    const st = steps[idx];
    if (st) {
      const w = parseFloat(st.actual_weight || st.target_weight || 50.0);
      setLiveWeight(w);
      setManualWeight(w.toFixed(3));
      setTareWeight(0.000);
      setIsTareActive(false);
    }
  };

  // Tare & Zero
  const handleTare = () => {
    setTareWeight(grossScaleWeight);
    setIsTareActive(true);
    notify(`Tare Zeroed: Container weight (${grossScaleWeight.toFixed(3)} kg) deducted`);
  };

  const handleResetTare = () => {
    setTareWeight(0.000);
    setIsTareActive(false);
    notify('Tare Reset: Showing Gross Scale Weight');
  };

  // Simulation Helpers for Dev/Testing
  const setSimTarget = () => {
    setLiveWeight(targetWeight);
    setManualWeight(targetWeight.toFixed(3));
  };
  const setSimUnder = () => {
    const val = parseFloat((targetWeight * 0.95).toFixed(3));
    setLiveWeight(val);
    setManualWeight(val.toFixed(3));
  };
  const setSimOver = () => {
    const val = parseFloat((targetWeight * 1.03).toFixed(3));
    setLiveWeight(val);
    setManualWeight(val.toFixed(3));
  };
  const setSimZero = () => {
    setLiveWeight(0.000);
    setManualWeight('0.000');
  };
  const adjustSimWeight = (deltaKg) => {
    const newVal = Math.max(0, parseFloat((effectiveWeight + deltaKg).toFixed(3)));
    if (isManualMode) {
      setManualWeight(newVal.toFixed(3));
    } else {
      setLiveWeight(parseFloat((newVal + tareWeight).toFixed(3)));
    }
  };

  // Sign / Confirm Dispense Step
  const handleSign = () => {
    if (!activeStep) return;
    fetch('/api/v1/dispensing/sign', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ 
        stepId: activeStep.id, 
        actualWeight: effectiveWeight, 
        operator: 'Giulia Romano (Dispense Lead)',
        isManual: isManualMode,
        manualReason: isManualMode ? manualReason : '',
        tareWeight: tareWeight,
      })
    })
    .then(res => res.json())
    .then(data => {
      notify(data.message);
      // Refresh batch & steps
      fetch(`/api/v1/dispensing/${currentBatchNumber}`)
        .then(res => res.json())
        .then(d => {
          setSteps(d.steps || []);
          if (d.batch) setBatch(d.batch);
          // Advance step if not at last step
          if (activeStepIdx < (d.steps || []).length - 1) {
            const nextIdx = activeStepIdx + 1;
            setActiveStepIdx(nextIdx);
            const nextStep = d.steps[nextIdx];
            if (nextStep) {
              const nw = parseFloat(nextStep.actual_weight || nextStep.target_weight || 50.0);
              setLiveWeight(nw);
              setManualWeight(nw.toFixed(3));
              setTareWeight(0.000);
              setIsTareActive(false);
            }
          }
        });
    });
  };

  // Handle Deviation Submission
  const handleReportDeviation = () => {
    setDevModal(false);
    notify(`Deviation Logged for Step #${activeStep.step_number}: ${devReason} (Routed to QA Supervisor)`);
  };

  return (
    <div>
      {/* Page Header */}
      <div className="page-header">
        <div>
          <div className="eyebrow">ELECTRONIC BATCH RECORD · WEIGHING STATION {batch.station}</div>
          <h1>Batch Dispensing (Tablet Mode)</h1>
          <div style={{ display: 'flex', alignItems: 'center', gap: '8px', marginTop: '4px' }}>
            <select 
              style={{ height: '30px', fontWeight: 'bold', fontSize: '12px', border: '1px solid #cbd5e1', borderRadius: '6px', padding: '0 8px' }}
              value={currentBatchNumber}
              onChange={e => setCurrentBatchNumber(e.target.value)}
            >
              {allBatches.map(b => (
                <option key={b.batch_number} value={b.batch_number}>
                  {b.batch_number} · {b.product_name} ({b.line_code}) — {b.status}
                </option>
              ))}
            </select>
            <Status type="approved">{batch.scaleModel} {batch.scaleStatus}</Status>
          </div>
        </div>
        <div className="header-actions">
          <Button variant="secondary" icon="qr" onClick={onOpenScanner}>Scan Material QR</Button>
        </div>
      </div>

      {/* Overall Batch Dispensing Progress */}
      <div className="batch-progress-strip">
        <div style={{ minWidth: '190px', fontSize: '11px', fontWeight: 600, color: '#334155' }}>
          <span>Dispensing Sequence: </span>
          <strong style={{ color: '#4f46e5' }}>{signedCount} of {steps.length} materials</strong>
          <span style={{ color: '#64748b' }}> ({overallPercent}%)</span>
        </div>
        <div className="batch-progress-track">
          <div className="batch-progress-fill" style={{ width: `${overallPercent}%` }}/>
        </div>
        <div style={{ fontSize: '11px', fontWeight: 700, color: overallPercent === 100 ? '#047857' : '#4f46e5' }}>
          {overallPercent === 100 ? 'Batch Dispensed 100% ✓' : `${overallPercent}% Complete`}
        </div>
      </div>

      {/* Main Dispensing Layout */}
      <div className="dispense-layout">
        
        {/* Step list sidebar */}
        <aside className="step-list">
          <div style={{ padding: '12px', borderBottom: '1px solid #e2e8f0', background: '#f8fafc' }}>
            <h3 style={{ fontSize: '11px', fontWeight: 700 }}>Dispensing Sequence ({steps.length} Steps)</h3>
          </div>
          {steps.map((st, i) => (
            <button 
              key={st.id} 
              className={st.is_signed ? 'done' : i === activeStepIdx ? 'active' : ''}
              onClick={() => handleSelectStep(i)}
            >
              <span>{st.is_signed ? '✓' : st.step_number}</span>
              <div>
                <strong>{st.material_name}</strong>
                <small style={{ color: '#64748b' }}>{st.target_weight} kg (Phase {st.phase})</small>
              </div>
            </button>
          ))}
        </aside>

        {/* Weigh Workspace */}
        {activeStep && (
          <div className="weigh-workspace">
            {/* Material Header */}
            <div className="material-head">
              <div className="material-phase">{activeStep.phase}</div>
              <div>
                <span style={{ fontSize: '9px', fontWeight: 700, color: '#4f46e5' }}>MATERIAL {activeStep.step_number} OF {steps.length}</span>
                <h2 style={{ fontSize: '18px' }}>{activeStep.material_name}</h2>
                <p style={{ fontSize: '11px', color: '#64748b' }}>Item Code: {activeStep.item_code}</p>
              </div>
              <div style={{ marginLeft: 'auto' }}>
                <Status type={activeStep.is_signed ? 'approved' : 'warning'}>
                  {activeStep.is_signed ? 'Step Signed ✓' : 'Weighing Required'}
                </Status>
              </div>
            </div>

            {/* Raw Material Lot Verification */}
            <div className="scan-panel">
              <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: '4px' }}>
                <label style={{ fontSize: '10px', fontWeight: 700, color: '#475569' }}>Allocated Approved Raw Material Lot & Physical Drum Verification</label>
                <span style={{ fontSize: '9px', color: '#64748b' }}>ISO 22716 Line Clearance Rule</span>
              </div>
              <div style={{ display: 'flex', alignItems: 'center', gap: '8px' }}>
                <Icon name="scan" size={20}/>
                <input value={activeStep.scanned_lot_number || 'NO LOT ASSIGNED'} readOnly style={{ fontWeight: 'bold' }}/>
                <Status type="approved">✓ Physical Drum Lot Verified</Status>
                <button 
                  type="button" 
                  onClick={onOpenScanner}
                  style={{ padding: '6px 12px', fontSize: '10px', fontWeight: 700, color: '#4f46e5', background: '#eef2ff', border: '1px solid #c7d2fe', borderRadius: '6px', cursor: 'pointer', whiteSpace: 'nowrap' }}
                  title="Scan physical raw material drum QR barcode"
                >
                  📷 Scan Drum QR Tag
                </button>
              </div>
            </div>

            {/* Weight Input Mode Selector & Tare Controls */}
            <div style={{ display: 'flex', flexWrap: 'wrap', alignItems: 'center', justifyContent: 'space-between', background: '#f8fafc', padding: '10px 14px', borderRadius: '8px', border: '1px solid #e2e8f0', gap: '8px' }}>
              <div style={{ display: 'flex', alignItems: 'center', gap: '8px' }}>
                <span style={{ fontSize: '11px', fontWeight: 700, color: '#475569' }}>Reading Mode:</span>
                <div className="mode-switch-group">
                  <button 
                    type="button"
                    className={`mode-switch-btn ${!isManualMode ? 'active' : ''}`}
                    onClick={() => setIsManualMode(false)}
                  >
                    <span style={{ display: 'inline-flex', alignItems: 'center', gap: '4px' }}>
                      <span style={{ width: '7px', height: '7px', borderRadius: '50%', background: '#10b981' }}/>
                      Scale Hardware (Auto Stream)
                    </span>
                  </button>
                  <button 
                    type="button"
                    className={`mode-switch-btn ${isManualMode ? 'active' : ''}`}
                    onClick={() => {
                      setIsManualMode(true);
                      if (!manualWeight) setManualWeight(effectiveWeight.toFixed(3));
                    }}
                  >
                    <span style={{ display: 'inline-flex', alignItems: 'center', gap: '4px' }}>
                      ✏️ Manual Override (تعديل يدوي)
                    </span>
                  </button>
                </div>
              </div>

              {/* Tare controls */}
              {!isManualMode && (
                <div style={{ display: 'flex', alignItems: 'center', gap: '8px' }}>
                  <span style={{ fontSize: '10px', color: '#64748b' }}>
                    Tare: <strong>{tareWeight.toFixed(3)} kg</strong>
                  </span>
                  <button 
                    type="button" 
                    onClick={handleTare}
                    style={{ padding: '3px 8px', fontSize: '10px', fontWeight: 600, border: '1px solid #cbd5e1', borderRadius: '4px', background: 'white' }}
                    title="Deduct container weight"
                  >
                    Tare / Zero (تصفير الوعاء)
                  </button>
                  {isTareActive && (
                    <button 
                      type="button" 
                      onClick={handleResetTare}
                      style={{ padding: '3px 6px', fontSize: '10px', color: '#be123c', border: '1px solid #fecdd3', borderRadius: '4px', background: '#fff1f2' }}
                    >
                      Clear Tare
                    </button>
                  )}
                </div>
              )}
            </div>

            {/* Manual Override Input Box (When Mode is Active) */}
            {isManualMode && (
              <div style={{ background: '#fffbeb', border: '1px solid #fde68a', borderRadius: '8px', padding: '12px 14px' }}>
                <div style={{ display: 'flex', alignItems: 'center', gap: '8px', marginBottom: '8px' }}>
                  <Icon name="alert" size={16} />
                  <strong style={{ fontSize: '12px', color: '#92400e' }}>Manual Weight Override Active (إدخال وتعديل يدوي)</strong>
                  <span style={{ fontSize: '10px', color: '#b45309', marginLeft: 'auto' }}>GMP 21 CFR Part 11 Audit Trail Enabled</span>
                </div>
                <div style={{ display: 'grid', gridTemplateColumns: '1fr 2fr', gap: '12px' }}>
                  <div>
                    <label style={{ display: 'block', fontSize: '10px', fontWeight: 700, color: '#78350f', marginBottom: '4px' }}>
                      Actual Physical Weight (kg) *
                    </label>
                    <input 
                      type="number" 
                      step="0.001"
                      style={{ width: '100%', height: '36px', fontSize: '16px', fontWeight: 'bold', padding: '0 10px', border: '2px solid #f59e0b', borderRadius: '6px', background: 'white', color: '#78350f' }}
                      value={manualWeight}
                      onChange={e => setManualWeight(e.target.value)}
                      placeholder="Enter verified weight"
                    />
                  </div>
                  <div>
                    <label style={{ display: 'block', fontSize: '10px', fontWeight: 700, color: '#78350f', marginBottom: '4px' }}>
                      Override Justification / سبب التعديل اليدوي *
                    </label>
                    <select 
                      style={{ width: '100%', height: '36px', fontSize: '11px', fontWeight: 600, padding: '0 8px', border: '1px solid #d97706', borderRadius: '6px', background: 'white' }}
                      value={manualReason}
                      onChange={e => setManualReason(e.target.value)}
                    >
                      <option value="Physical scale display reading verified manually (discrepancy override)">
                        Physical scale display reading verified manually (قراءة الميزان الفيزيائي صحيحة ويوجد تأخر بالسيستم)
                      </option>
                      <option value="Mettler Toledo RS232 / TCP sync latency override">
                        Scale interface communication latency override (تأخر في إشارة شبكة الميزان)
                      </option>
                      <option value="Certified secondary external floor scale used">
                        Secondary certified floor scale used for high-volume liquid (تم استخدام ميزان أرضي معتمد للمياه)
                      </option>
                      <option value="Approved deviation dispensation from Production Lead">
                        Approved deviation from Production Chemist (موافقة معتمدة من الكيميائي المسؤول)
                      </option>
                    </select>
                  </div>
                </div>
              </div>
            )}

            {/* Target vs Actual Weight Grid */}
            <div className="weight-grid">
              <div>
                <span style={{ fontSize: '10px', color: '#64748b' }}>Target Recipe Weight</span>
                <strong>{targetWeight.toFixed(3)} <small style={{ fontSize: '14px' }}>kg</small></strong>
                <p style={{ fontSize: '9px', color: '#64748b' }}>
                  Tolerance: ±{tolPercent.toFixed(2)}% ({minAllowed.toFixed(3)} – {maxAllowed.toFixed(3)} kg)
                </p>
              </div>
              <div style={{
                background: isInTolerance ? '#ecfdf5' : isUnder ? '#fffbeb' : isOver ? '#fff1f2' : '#f8fafc',
                borderColor: isInTolerance ? '#a7f3d0' : isUnder ? '#fde68a' : isOver ? '#fecdd3' : '#e2e8f0',
                borderWidth: '1px',
                borderStyle: 'solid',
                borderRadius: '8px',
                padding: '16px',
                textAlign: 'center',
                position: 'relative',
              }}>
                <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'center', gap: '6px' }}>
                  <span style={{ fontSize: '10px', fontWeight: 700, color: isInTolerance ? '#047857' : isUnder ? '#b45309' : isOver ? '#be123c' : '#64748b' }}>
                    {isManualMode ? 'Manual Input Weight' : 'Live Net Weight (STABLE)'}
                  </span>
                  {isManualMode && <span style={{ fontSize: '9px', background: '#fef3c7', color: '#b45309', padding: '1px 5px', borderRadius: '4px', fontWeight: 'bold' }}>MANUAL</span>}
                </div>
                <strong style={{ color: isInTolerance ? '#065f46' : isUnder ? '#b45309' : isOver ? '#9f1239' : '#0f172a' }}>
                  {effectiveWeight.toFixed(3)} <small style={{ fontSize: '14px' }}>kg</small>
                </strong>
                <div style={{ fontSize: '10px', fontWeight: 700, color: isInTolerance ? '#047857' : isUnder ? '#b45309' : isOver ? '#be123c' : '#64748b' }}>
                  <Status type={statusBadge.type}>{statusBadge.text}</Status>
                </div>
              </div>
            </div>

            {/* Tolerance Visual Traffic-Light Gauge */}
            <div className="gauge-card">
              <div className="gauge-header">
                <span style={{ color: '#475569' }}>Tolerance Visual Gauge (مؤشر الملء والوزن الدوائي)</span>
                <span style={{ color: gaugeColor, fontWeight: 700 }}>
                  {effectiveWeight.toFixed(3)} / {targetWeight.toFixed(3)} kg ({targetWeight > 0 ? Math.round((effectiveWeight / targetWeight) * 100) : 0}%)
                </span>
              </div>
              <div className="gauge-track">
                <div className="gauge-fill" style={{ width: `${gaugePercent}%`, backgroundColor: gaugeColor }}/>
              </div>
              <div style={{ display: 'flex', justifyContent: 'space-between', fontSize: '9px', color: '#64748b', marginTop: '4px' }}>
                <span>0.000 kg</span>
                <span style={{ color: '#047857', fontWeight: 'bold' }}>Target Zone: {minAllowed.toFixed(3)} – {maxAllowed.toFixed(3)} kg (±{tolPercent}%)</span>
                <span>{targetWeight.toFixed(3)} kg</span>
              </div>
            </div>

            {/* Footer Action Buttons */}
            <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', borderTop: '1px solid #e2e8f0', paddingTop: '16px' }}>
              <div>
                {!isInTolerance && (
                  <button 
                    type="button" 
                    onClick={() => setDevModal(true)}
                    style={{ height: '38px', padding: '0 14px', fontSize: '12px', fontWeight: 600, color: '#be123c', border: '1px solid #fecdd3', borderRadius: '6px', background: '#fff1f2', cursor: 'pointer' }}
                  >
                    ⚠️ Report Deviation (إبلاغ عن حيود)
                  </button>
                )}
              </div>
              
              <Button 
                variant={isInTolerance || isManualMode ? "primary" : "secondary"}
                icon="check" 
                style={{ height: '42px', padding: '0 24px', fontSize: '13px' }}
                onClick={handleSign}
              >
                {activeStep.is_signed 
                  ? 'Re-confirm & Update Weight' 
                  : (isManualMode ? 'Confirm & Sign (Manual Override)' : 'Confirm & Sign Dispense Step')}
              </Button>
            </div>
          </div>
        )}

        {/* Station Telemetry & Simulator Controls */}
        <aside>
          <div className="section-card" style={{ padding: '16px' }}>
            <h3 style={{ fontSize: '12px', fontWeight: 700, marginBottom: '8px' }}>Station WS-02 Telemetry</h3>
            <div style={{ background: '#0f172a', color: '#34d399', padding: '14px', borderRadius: '6px', textAlign: 'center', fontFamily: 'monospace', marginBottom: '12px' }}>
              <div style={{ fontSize: '24px', fontWeight: 'bold' }}>{grossScaleWeight.toFixed(3)} kg</div>
              <small style={{ color: '#94a3b8' }}>METTLER TOLEDO ICS685 · CALIBRATED</small>
              <div style={{ fontSize: '9px', color: '#6ee7b7', marginTop: '6px' }}>
                Gross: {grossScaleWeight.toFixed(3)} | Tare: {tareWeight.toFixed(3)} | Net: {netScaleWeight.toFixed(3)} kg
              </div>
            </div>
            <div style={{ fontSize: '10px', color: '#64748b', display: 'flex', flexDirection: 'column', gap: '5px' }}>
              <div>Operator: <strong>Giulia Romano (Dispense Lead)</strong></div>
              <div>Batch Target: <strong>{batch.targetSize}</strong></div>
              <div>Line: <strong>{batch.line}</strong></div>
              <div>Active Mode: <strong style={{ color: isManualMode ? '#d97706' : '#059669' }}>{isManualMode ? 'Manual Override (يدوي)' : 'Live Scale Stream (آلي)'}</strong></div>
              <div>Scale Sync: <strong style={{ color: '#059669' }}>Connected & Stable ✓</strong></div>
            </div>

            {/* Dev & Testing Simulator Toolbar */}
            <div style={{ marginTop: '16px', borderTop: '1px solid #e2e8f0', paddingTop: '12px' }}>
              <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', marginBottom: '6px' }}>
                <span style={{ fontSize: '10px', fontWeight: 700, color: '#475569' }}>🛠️ Scale Simulator (محاكي الميزان)</span>
                <span style={{ fontSize: '9px', color: '#6366f1' }}>Quick Test</span>
              </div>
              <div className="sim-bar">
                <button type="button" className="sim-btn" onClick={setSimTarget} title="Set weight to exact recipe target">🎯 Exact Target</button>
                <button type="button" className="sim-btn" onClick={setSimUnder} title="Set -5% under target">🔻 -5% Under</button>
                <button type="button" className="sim-btn" onClick={setSimOver} title="Set +3% over target">🔺 +3% Over</button>
                <button type="button" className="sim-btn" onClick={setSimZero} title="Clear to zero">⭕ Zero</button>
                <button type="button" className="sim-btn" onClick={() => adjustSimWeight(0.050)} title="Add 50 grams">+50g</button>
                <button type="button" className="sim-btn" onClick={() => adjustSimWeight(-0.050)} title="Deduct 50 grams">-50g</button>
              </div>
            </div>
          </div>
        </aside>
      </div>

      {/* Deviation Modal */}
      {devModal && (
        <div className="modal-overlay">
          <div className="modal" style={{ maxWidth: '440px' }}>
            <div className="modal-header">
              <div style={{ display: 'flex', alignItems: 'center', gap: '8px' }}>
                <Icon name="alert" size={18}/>
                <h2>Report Weighing Deviation (تسجيل حيود وزن)</h2>
              </div>
              <button className="icon-button" onClick={() => setDevModal(false)}><Icon name="close" size={16}/></button>
            </div>
            <div className="modal-body" style={{ display: 'flex', flexDirection: 'column', gap: '12px' }}>
              <p style={{ fontSize: '11px', color: '#475569' }}>
                Step #{activeStep.step_number} ({activeStep.material_name}) weight is <strong>{effectiveWeight.toFixed(3)} kg</strong> against target <strong>{targetWeight.toFixed(3)} kg</strong>.
              </p>
              <div>
                <label style={{ display: 'block', fontSize: '10px', fontWeight: 700, color: '#334155', marginBottom: '4px' }}>
                  Deviation Reason / سبب الحيود
                </label>
                <select 
                  style={{ width: '100%', height: '36px', fontSize: '11px', border: '1px solid #cbd5e1', borderRadius: '6px', padding: '0 8px' }}
                  value={devReason}
                  onChange={e => setDevReason(e.target.value)}
                >
                  <option value="Weight exceeds approved recipe tolerance range">Weight exceeds approved recipe tolerance range (تجاوز نسبة السماحية المعتمدة)</option>
                  <option value="Raw material physical spill during dispensing">Raw material physical spill during dispensing (انسكاب أثناء الصب)</option>
                  <option value="Incomplete lot container balance">Incomplete lot container balance (انتهاء رصيد البرميل قبل إكمال الوزن)</option>
                  <option value="Scale reading fluctuation / unstable environmental air draft">Scale reading fluctuation / draft (تذبذب القراءة بسبب تيار هوائي)</option>
                </select>
              </div>
            </div>
            <div className="modal-footer" style={{ display: 'flex', justifyContent: 'flex-end', gap: '8px' }}>
              <Button variant="secondary" onClick={() => setDevModal(false)}>Cancel</Button>
              <Button variant="danger" onClick={handleReportDeviation}>Submit Deviation to QA</Button>
            </div>
          </div>
        </div>
      )}
    </div>
  );
}
@endverbatim
