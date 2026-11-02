@verbatim
// Module 3: Formulation Studio & BOM Master Recipe Editor

/* -------------------------------------------------------------
   PRINTABLE GMP MASTER FORMULATION SHEET MODAL
-------------------------------------------------------------- */
function MasterFormulaPrintModal({ form, scaledSize, onClose }) {
  if (!form) return null;
  return (
    <div className="modal-overlay" onClick={onClose}>
      <div className="modal" style={{ maxWidth: '820px' }} onClick={e => e.stopPropagation()}>
        <div className="modal-header">
          <h2>Official GMP Master Formulation Specification (ISO 22716)</h2>
          <button className="icon-button" onClick={onClose}><Icon name="close" size={16}/></button>
        </div>
        <div className="modal-body" id="print-area">
          <div id="master-formula-report-sheet" className="report-paper" style={{ border: '2px solid #0f172a', padding: '24px', background: 'white', fontFamily: 'serif' }}>
            <div style={{ display: 'flex', justifyContent: 'space-between', borderBottom: '2px solid #0f172a', paddingBottom: '12px', marginBottom: '14px' }}>
              <div>
                <h1 style={{ fontSize: '18px', fontWeight: 'bold' }}>BEAUTY ITALY FACTORY S.P.A. · MILANO</h1>
                <p style={{ fontSize: '11px', color: '#475569' }}>Standard Operating Procedure: GMP-SOP-FORM-042 · Controlled Copy</p>
                <div style={{ fontSize: '10px', color: '#059669', fontWeight: 'bold', marginTop: '4px' }}>ISO 22716 & EU Cosmetics Regulation (EC) 1223/2009 Certified</div>
              </div>
              <QRCodeImage text={`FORMULA:${form.code}|V:${form.version}|BATCH:${scaledSize}KG`} size={75} showLabel={true} labelText={form.code}/>
            </div>

            <div style={{ display: 'grid', gridTemplateColumns: 'repeat(4,1fr)', gap: '10px', fontSize: '10px', background: '#f8fafc', padding: '10px', border: '1px solid #cbd5e1', marginBottom: '14px' }}>
              <div><strong>Formula Code:</strong> {form.code}</div>
              <div><strong>Product:</strong> {form.productName}</div>
              <div><strong>Active Version:</strong> {form.version} ({form.status})</div>
              <div><strong>Batch Target:</strong> {scaledSize} kg</div>
              <div><strong>Author:</strong> {form.author}</div>
              <div><strong>Effective:</strong> {form.effectiveDate}</div>
              <div><strong>Target pH:</strong> {form.phSpec}</div>
              <div><strong>Viscosity:</strong> {form.viscositySpec}</div>
            </div>

            <h3 style={{ fontSize: '12px', marginBottom: '8px', textTransform: 'uppercase' }}>Formula Composition Checklist ({scaledSize} kg scaled)</h3>
            <table style={{ width: '100%', borderCollapse: 'collapse', fontSize: '10px', marginBottom: '16px' }} border="1">
              <thead>
                <tr style={{ background: '#f1f5f9' }}>
                  <th style={{ padding: '6px' }}>Phase</th>
                  <th style={{ padding: '6px' }}>Code</th>
                  <th style={{ padding: '6px' }}>INCI Name</th>
                  <th style={{ padding: '6px' }}>Technical Name</th>
                  <th style={{ padding: '6px', textAlign: 'right' }}>% w/w</th>
                  <th style={{ padding: '6px', textAlign: 'right' }}>Scale Qty (kg)</th>
                  <th style={{ padding: '6px' }}>Assigned Lot</th>
                  <th style={{ padding: '6px' }}>Weighed / Sign</th>
                </tr>
              </thead>
              <tbody>
                {form.lines.map((l, i) => (
                  <tr key={i}>
                    <td style={{ padding: '6px', textAlign: 'center', fontWeight: 'bold' }}>{l.phase}</td>
                    <td style={{ padding: '6px' }}><code>{l.item_code}</code></td>
                    <td style={{ padding: '6px' }}><strong>{l.inci}</strong></td>
                    <td style={{ padding: '6px' }}>{l.tech}</td>
                    <td style={{ padding: '6px', textAlign: 'right' }}>{parseFloat(l.percentage).toFixed(3)}%</td>
                    <td style={{ padding: '6px', textAlign: 'right', fontWeight: 'bold' }}>{((l.percentage / 100) * scaledSize).toFixed(2)} kg</td>
                    <td style={{ padding: '6px', fontSize: '9px' }}>{l.stock.lot_number}</td>
                    <td style={{ padding: '6px', width: '80px', textAlign: 'center' }}>[ &nbsp; &nbsp; &nbsp; ]</td>
                  </tr>
                ))}
              </tbody>
            </table>

            <div style={{ display: 'grid', gridTemplateColumns: 'repeat(3, 1fr)', gap: '12px', borderTop: '1px solid #cbd5e1', paddingTop: '12px', fontSize: '9px' }}>
              {form.approvals.map((app, i) => (
                <div key={i} style={{ border: '1px solid #e2e8f0', padding: '8px', borderRadius: '4px' }}>
                  <div><strong>{app.role}:</strong> {app.name}</div>
                  <div style={{ color: '#047857', marginTop: '2px' }}>✓ 21 CFR Part 11 Electronically Signed</div>
                  <div style={{ color: '#64748b' }}>Date: {app.signedAt}</div>
                </div>
              ))}
            </div>
          </div>
        </div>
        <div className="modal-footer">
          <Button variant="secondary" onClick={onClose}>Close</Button>
          <Button variant="secondary" icon="printer" onClick={() => window.cleanPrint('master-formula-report-sheet', `Master-Formula-${form.code}.pdf`)}>
            🖨️ Clean Print (طباعة)
          </Button>
          <Button variant="primary" icon="file" onClick={() => window.downloadReportPdf('master-formula-report-sheet', `Master-Formula-${form.code}.pdf`)}>
            📥 Download Official PDF (تحميل تقرير PDF)
          </Button>
        </div>
      </div>
    </div>
  );
}

/* -------------------------------------------------------------
   FORMULATION STUDIO MAIN COMPONENT
-------------------------------------------------------------- */
function Formulation({ activeCode, onLaunchBatch, notify, onOpenLot }) {
  const [form, setForm] = useState(null);
  const [currentCode, setCurrentCode] = useState(activeCode || 'FRM-SC-0042');
  const [activeSubtab, setActiveSubtab] = useState('composition');
  const [scaledBatchSize, setScaledBatchSize] = useState(1000.00);
  const [tableSearch, setTableSearch] = useState('');
  const [phaseFilter, setPhaseFilter] = useState('ALL');
  const [printModal, setPrintModal] = useState(false);
  const [launchModal, setLaunchModal] = useState(false);
  const [newRevisionModal, setNewRevisionModal] = useState(false);
  const [launchLine, setLaunchLine] = useState('L01');

  // Master Items List & Modals
  const [availableItems, setAvailableItems] = useState([]);
  const [newFormulaModal, setNewFormulaModal] = useState(false);
  const [addIngModal, setAddIngModal] = useState(false);

  // New Formulation Builder State
  const [nfCode, setNfCode] = useState('FRM-SK-00' + Math.floor(Math.random() * 899 + 100));
  const [nfName, setNfName] = useState('Hydra-Luxe Botanical Face Cream 50ml');
  const [nfCategory, setNfCategory] = useState('Skincare');
  const [nfBatchSize, setNfBatchSize] = useState(1000.00);
  const [nfAuthor, setNfAuthor] = useState('Marco Conti (Admin)');
  const [nfPh, setNfPh] = useState('5.50 - 6.50');
  const [nfViscosity, setNfViscosity] = useState('18,000 - 24,000 cP');
  const [nfDensity, setNfDensity] = useState('0.98 - 1.02 g/ml');
  
  const defaultLines = [
    { phase: 'A', item_name: 'Purified Water EP/USP', item_code: 'RM-00001', inci_name: 'Aqua', function: 'Solvent', percentage: 67.5, cost_per_unit: 0.15 },
    { phase: 'A', item_name: 'Vegetable Glycerin 99.5%', item_code: 'RM-00002', inci_name: 'Glycerin', function: 'Humectant', percentage: 5.0, cost_per_unit: 2.80 },
    { phase: 'A', item_name: 'Niacinamide USP (Vitamin B3)', item_code: 'RM-00003', inci_name: 'Niacinamide', function: 'Active', percentage: 4.0, cost_per_unit: 14.50 },
    { phase: 'A', item_name: 'Xanthan Gum Transparent', item_code: 'RM-00004', inci_name: 'Xanthan Gum', function: 'Thickener', percentage: 0.5, cost_per_unit: 12.00 },
    { phase: 'B', item_name: 'Cetearyl Alcohol (Lanette O)', item_code: 'RM-00005', inci_name: 'Cetearyl Alcohol', function: 'Emulsifier', percentage: 6.0, cost_per_unit: 4.20 },
    { phase: 'B', item_name: 'Caprylic/Capric Triglyceride (MCT)', item_code: 'RM-00006', inci_name: 'Caprylic/Capric Triglyceride', function: 'Emollient', percentage: 8.0, cost_per_unit: 6.50 },
    { phase: 'B', item_name: 'Organic Shea Butter Refined', item_code: 'RM-00007', inci_name: 'Butyrospermum Parkii Butter', function: 'Emollient', percentage: 5.0, cost_per_unit: 9.80 },
    { phase: 'C', item_name: 'Retinol 10% Encapsulated', item_code: 'RM-00008', inci_name: 'Retinol', function: 'Active', percentage: 2.0, cost_per_unit: 120.00 },
    { phase: 'C', item_name: 'Euxyl PE 9010 Preservative', item_code: 'RM-00009', inci_name: 'Phenoxyethanol', function: 'Preservative', percentage: 1.0, cost_per_unit: 16.50 },
    { phase: 'C', item_name: 'Signature Fragrance Bloom', item_code: 'RM-00010', inci_name: 'Parfum', function: 'Fragrance', percentage: 1.0, cost_per_unit: 45.00 },
  ];
  const [nfLines, setNfLines] = useState(defaultLines);

  // Single Ingredient Add State
  const [singlePhase, setSinglePhase] = useState('A');
  const [singleItemId, setSingleItemId] = useState('');
  const [singleItemName, setSingleItemName] = useState('');
  const [singleItemCode, setSingleItemCode] = useState('');
  const [singlePct, setSinglePct] = useState('2.0');
  const [singleFunction, setSingleFunction] = useState('Active');
  const [singleCost, setSingleCost] = useState('10.0');

  useEffect(() => {
    fetch('/api/v1/items')
      .then(r => r.json())
      .then(data => setAvailableItems(data || []))
      .catch(e => console.error(e));
  }, []);

  useEffect(() => {
    if (activeCode && activeCode !== currentCode) {
      setCurrentCode(activeCode);
    }
  }, [activeCode]);

  useEffect(() => {
    fetch(`/api/v1/formulations/${currentCode}`)
      .then(res => res.json())
      .then(data => {
        setForm(data);
        setScaledBatchSize(data.targetBatchSize || 1000.00);
      })
      .catch(err => console.error(err));
  }, [currentCode]);

  if (!form) return <div style={{ padding: '40px', textAlign: 'center', color: '#64748b' }}>Connecting to MySQL Formulation Repository...</div>;

  // Scale multiplier
  const scaleMultiplier = scaledBatchSize / (form.targetBatchSize || 1000.00);

  // Filter lines
  const filteredLines = form.lines.filter(l => {
    const matchesPhase = phaseFilter === 'ALL' || l.phase === phaseFilter;
    const q = tableSearch.toLowerCase();
    const matchesSearch = !q || 
      l.inci.toLowerCase().includes(q) || 
      l.tech.toLowerCase().includes(q) || 
      l.item_code.toLowerCase().includes(q) ||
      l.function.toLowerCase().includes(q) ||
      l.cas_number.toLowerCase().includes(q);
    return matchesPhase && matchesSearch;
  });

  // Recalculated total cost for the current scaled batch
  const currentScaledTotalCost = form.lines.reduce((acc, l) => {
    const scaledQty = (l.percentage / 100.0) * scaledBatchSize;
    return acc + (scaledQty * l.cost_per_unit);
  }, 0);

  const costPerKg = scaledBatchSize > 0 ? (currentScaledTotalCost / scaledBatchSize).toFixed(2) : '0.00';
  const costPerUnit = ((costPerKg * 0.05) + 0.65).toFixed(2); // 50ml + jar

  const handleConfirmLaunch = () => {
    fetch(`/api/v1/formulations/${form.code}/create-batch`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ target_size: scaledBatchSize, line_code: launchLine })
    })
    .then(res => res.json())
    .then(data => {
      setLaunchModal(false);
      notify(data.message);
      if (onLaunchBatch) onLaunchBatch(data.batch_number);
    })
    .catch(err => {
      notify('Error launching batch: ' + err.message);
    });
  };

  // Template Presets
  const loadTemplate = (type) => {
    if (type === 'cream') {
      setNfName('Hydra-Luxe Botanical Face Cream 50ml');
      setNfCategory('Skincare');
      setNfLines([
        { phase: 'A', item_name: 'Purified Water EP/USP', item_code: 'RM-00001', inci_name: 'Aqua', function: 'Solvent', percentage: 67.5, cost_per_unit: 0.15 },
        { phase: 'A', item_name: 'Vegetable Glycerin 99.5%', item_code: 'RM-00002', inci_name: 'Glycerin', function: 'Humectant', percentage: 5.0, cost_per_unit: 2.80 },
        { phase: 'A', item_name: 'Niacinamide USP (Vitamin B3)', item_code: 'RM-00003', inci_name: 'Niacinamide', function: 'Active', percentage: 4.0, cost_per_unit: 14.50 },
        { phase: 'A', item_name: 'Xanthan Gum Transparent', item_code: 'RM-00004', inci_name: 'Xanthan Gum', function: 'Thickener', percentage: 0.5, cost_per_unit: 12.00 },
        { phase: 'B', item_name: 'Cetearyl Alcohol (Lanette O)', item_code: 'RM-00005', inci_name: 'Cetearyl Alcohol', function: 'Emulsifier', percentage: 6.0, cost_per_unit: 4.20 },
        { phase: 'B', item_name: 'Caprylic/Capric Triglyceride (MCT)', item_code: 'RM-00006', inci_name: 'Caprylic/Capric Triglyceride', function: 'Emollient', percentage: 8.0, cost_per_unit: 6.50 },
        { phase: 'B', item_name: 'Organic Shea Butter Refined', item_code: 'RM-00007', inci_name: 'Butyrospermum Parkii Butter', function: 'Emollient', percentage: 5.0, cost_per_unit: 9.80 },
        { phase: 'C', item_name: 'Retinol 10% Encapsulated', item_code: 'RM-00008', inci_name: 'Retinol', function: 'Active', percentage: 2.0, cost_per_unit: 120.00 },
        { phase: 'C', item_name: 'Euxyl PE 9010 Preservative', item_code: 'RM-00009', inci_name: 'Phenoxyethanol', function: 'Preservative', percentage: 1.0, cost_per_unit: 16.50 },
        { phase: 'C', item_name: 'Signature Fragrance Bloom', item_code: 'RM-00010', inci_name: 'Parfum', function: 'Fragrance', percentage: 1.0, cost_per_unit: 45.00 },
      ]);
    } else if (type === 'serum') {
      setNfName('Vitamin C 15% & Hyaluronic Radiance Serum 30ml');
      setNfCategory('Skincare');
      setNfLines([
        { phase: 'A', item_name: 'Purified Water EP/USP', item_code: 'RM-00001', inci_name: 'Aqua', function: 'Solvent', percentage: 68.0, cost_per_unit: 0.15 },
        { phase: 'A', item_name: 'Vegetable Glycerin 99.5%', item_code: 'RM-00002', inci_name: 'Glycerin', function: 'Humectant', percentage: 7.0, cost_per_unit: 2.80 },
        { phase: 'A', item_name: 'Sodium Hyaluronate Multi-Molecular', item_code: 'RM-00011', inci_name: 'Sodium Hyaluronate', function: 'Active', percentage: 2.0, cost_per_unit: 85.00 },
        { phase: 'B', item_name: 'Ascorbyl Glucoside (Stable Vit C)', item_code: 'RM-00012', inci_name: 'Ascorbyl Glucoside', function: 'Active', percentage: 15.0, cost_per_unit: 48.00 },
        { phase: 'B', item_name: 'Ferulic Acid 98%', item_code: 'RM-00013', inci_name: 'Ferulic Acid', function: 'Antioxidant', percentage: 0.5, cost_per_unit: 95.00 },
        { phase: 'C', item_name: 'Euxyl PE 9010 Preservative', item_code: 'RM-00009', inci_name: 'Phenoxyethanol', function: 'Preservative', percentage: 1.0, cost_per_unit: 16.50 },
        { phase: 'C', item_name: 'Citric Acid / Sodium Citrate Buffer', item_code: 'RM-00014', inci_name: 'Citric Acid', function: 'pH Buffer', percentage: 6.5, cost_per_unit: 3.50 },
      ]);
    } else if (type === 'micellar') {
      setNfName('Gentle Purifying Micellar Cleanser 200ml');
      setNfCategory('Cleansers');
      setNfLines([
        { phase: 'A', item_name: 'Purified Water EP/USP', item_code: 'RM-00001', inci_name: 'Aqua', function: 'Solvent', percentage: 88.0, cost_per_unit: 0.15 },
        { phase: 'A', item_name: 'Hexylene Glycol', item_code: 'RM-00015', inci_name: 'Hexylene Glycol', function: 'Humectant', percentage: 3.5, cost_per_unit: 4.80 },
        { phase: 'A', item_name: 'Glycerin 99.5%', item_code: 'RM-00002', inci_name: 'Glycerin', function: 'Humectant', percentage: 3.0, cost_per_unit: 2.80 },
        { phase: 'B', item_name: 'Poloxamer 184 (Mild Micellar Surfactant)', item_code: 'RM-00016', inci_name: 'Poloxamer 184', function: 'Surfactant', percentage: 3.5, cost_per_unit: 14.00 },
        { phase: 'C', item_name: 'Disodium Cocoamphodiacetate', item_code: 'RM-00017', inci_name: 'Disodium Cocoamphodiacetate', function: 'Surfactant', percentage: 1.0, cost_per_unit: 7.20 },
        { phase: 'C', item_name: 'Euxyl PE 9010 Preservative', item_code: 'RM-00009', inci_name: 'Phenoxyethanol', function: 'Preservative', percentage: 1.0, cost_per_unit: 16.50 },
      ]);
    } else if (type === 'empty') {
      setNfLines([
        { phase: 'A', item_name: '', item_code: '', inci_name: '', function: 'Solvent', percentage: 0, cost_per_unit: 0 }
      ]);
    }
  };

  const handleNfLineChange = (index, field, value) => {
    const updated = [...nfLines];
    updated[index] = { ...updated[index], [field]: value };
    setNfLines(updated);
  };

  const handleAddNfLine = () => {
    setNfLines([
      ...nfLines,
      { phase: 'B', item_name: '', item_code: '', inci_name: '', function: 'Active', percentage: 1.0, cost_per_unit: 5.0 }
    ]);
  };

  const handleRemoveNfLine = (index) => {
    setNfLines(nfLines.filter((_, i) => i !== index));
  };

  const nfTotalPercentage = nfLines.reduce((acc, l) => acc + (parseFloat(l.percentage) || 0), 0);
  const nfTotalBatchCost = nfLines.reduce((acc, l) => {
    const qty = ((parseFloat(l.percentage) || 0) / 100.0) * nfBatchSize;
    return acc + (qty * (parseFloat(l.cost_per_unit) || 0));
  }, 0);
  const nfCostPerKg = nfBatchSize > 0 ? (nfTotalBatchCost / nfBatchSize).toFixed(2) : '0.00';
  const nfCostPerUnit = ((parseFloat(nfCostPerKg) * 0.05) + 0.65).toFixed(2);

  const handleSaveNewFormula = () => {
    if (!nfName.trim()) {
      notify('Please provide a Product Name');
      return;
    }
    if (nfLines.length === 0) {
      notify('Please add at least one ingredient line');
      return;
    }

    fetch('/api/v1/formulations', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        code: nfCode,
        product_name: nfName,
        category: nfCategory,
        version: 'v1.0',
        target_batch_size: nfBatchSize,
        author_name: nfAuthor,
        ph_spec: nfPh,
        viscosity_spec: nfViscosity,
        density_spec: nfDensity,
        lines: nfLines
      })
    })
    .then(res => res.json())
    .then(data => {
      if (data.success) {
        notify(data.message);
        setNewFormulaModal(false);
        setCurrentCode(data.code);
        // Refresh
        fetch(`/api/v1/formulations/${data.code}`)
          .then(res => res.json())
          .then(d => {
            setForm(d);
            setScaledBatchSize(d.targetBatchSize || 1000.00);
          });
      } else {
        notify('Error: ' + (data.error || 'Failed to create formulation'));
      }
    })
    .catch(err => notify('Error: ' + err.message));
  };

  const handleSaveSingleIngredient = () => {
    if (!singleItemName.trim()) {
      notify('Please enter an ingredient name');
      return;
    }
    fetch(`/api/v1/formulations/${form.code}/lines`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        phase: singlePhase,
        item_name: singleItemName,
        item_code: singleItemCode,
        percentage: parseFloat(singlePct) || 1.0,
        function: singleFunction,
        cost_per_unit: parseFloat(singleCost) || 5.0,
      })
    })
    .then(res => res.json())
    .then(data => {
      notify(data.message);
      setAddIngModal(false);
      // Refresh formula
      fetch(`/api/v1/formulations/${form.code}`)
        .then(res => res.json())
        .then(d => setForm(d));
    })
    .catch(err => notify('Error adding ingredient: ' + err.message));
  };

  return (
    <div>
      {/* Header Card with Dropdown Formula Switcher & Actions */}
      <div className="formula-header-card">
        <div className="formula-top-row">
          <div className="formula-title-area">
            <div className="eyebrow">R&D · COSMETIC FORMULATION & LIFE-CYCLE MANAGEMENT</div>
            <div className="formula-selector">
              <select 
                className="formula-dropdown" 
                value={currentCode} 
                onChange={e => setCurrentCode(e.target.value)}
              >
                {form.allFormulations && form.allFormulations.map(f => (
                  <option key={f.code} value={f.code}>
                    {f.code} · {f.product_name} ({f.version}) — {f.status}
                  </option>
                ))}
              </select>
              <Status type={form.status === 'Approved' ? 'approved' : 'warning'}>{form.status}</Status>
              <span style={{ fontSize: '10px', background: '#e0e7ff', color: '#3730a3', padding: '3px 8px', borderRadius: '4px', fontWeight: 600 }}>
                {form.category}
              </span>
            </div>
            <p style={{ fontSize: '11px', color: '#64748b', marginTop: '4px' }}>
              Author: <strong>{form.author}</strong> · Effective Date: <strong>{form.effectiveDate}</strong> · EU Cosmetics Regulation EC 1223/2009 & ISO 22716 Validated
            </p>
          </div>

          <div className="header-actions">
            <Button variant="primary" icon="plus" onClick={() => setNewFormulaModal(true)}>
              + New Product Formulation (إضافة تركيبة جديدة)
            </Button>
            <Button variant="secondary" icon="printer" onClick={() => setPrintModal(true)}>
              Print Master Sheet
            </Button>
            <Button variant="secondary" icon="plus" onClick={() => setNewRevisionModal(true)}>
              New Revision
            </Button>
            <Button variant="success" icon="scale" onClick={() => setLaunchModal(true)}>
              ⚡ Launch Production Batch (eBMR)
            </Button>
          </div>
        </div>

        {/* KPI Banner */}
        <div className="formula-kpi-bar">
          <div>
            <span>ACTIVE VERSION</span>
            <strong>{form.version}</strong>
            <small>21 CFR Part 11 Signed</small>
          </div>
          <div>
            <span>BATCH SCALE WEIGHT</span>
            <strong>{scaledBatchSize.toLocaleString()} kg</strong>
            <small>{(scaledBatchSize / 0.05).toLocaleString()} Units (50ml)</small>
          </div>
          <div>
            <span>FORMULA INTEGRITY</span>
            <strong style={{ color: Math.abs(form.totalPercentage - 100) < 0.01 ? '#34d399' : '#f87171' }}>
              {form.totalPercentage.toFixed(3)}%
            </strong>
            <small>GMP 100.000% Balance Check</small>
          </div>
          <div>
            <span>ESTIMATED BATCH COST</span>
            <strong style={{ color: '#38bdf8' }}>€{currentScaledTotalCost.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</strong>
            <small>€{costPerKg} / kg raw material</small>
          </div>
          <div>
            <span>EST. UNIT COST</span>
            <strong>€{costPerUnit} / jar</strong>
            <small>Includes €0.65 Packaging</small>
          </div>
        </div>

        {/* Interactive Batch Scaler Toolbar */}
        <div className="batch-scaler-wrap">
          <span>⚡ Interactive Batch Scaler:</span>
          <div className="scaler-btn-group">
            {[100, 250, 500, 1000, 2000, 5000].map(size => (
              <button 
                key={size} 
                className={`scale-chip ${scaledBatchSize === size ? 'active' : ''}`}
                onClick={() => setScaledBatchSize(size)}
              >
                {size >= 1000 ? `${size / 1000}T Bulk` : `${size} kg`}
              </button>
            ))}
          </div>
          <div style={{ marginLeft: 'auto', display: 'flex', alignItems: 'center', gap: '8px' }}>
            <span style={{ fontSize: '10px', color: '#64748b' }}>Custom batch kg:</span>
            <input 
              type="number" 
              className="custom-scale-input" 
              value={scaledBatchSize} 
              onChange={e => setScaledBatchSize(parseFloat(e.target.value) || 0)}
            />
            <span style={{ fontSize: '10px', fontWeight: 600 }}>kg</span>
          </div>
        </div>
      </div>

      {/* Subtabs: Composition, SOP Instructions, Regulatory, Approvals */}
      <div className="formula-subtabs">
        <button className={`subtab-btn ${activeSubtab === 'composition' ? 'active' : ''}`} onClick={() => setActiveSubtab('composition')}>
          <Icon name="boxes" size={15}/> Formula Composition & Warehouse Stock ({form.lines.length} Ingredients)
        </button>
        <button className={`subtab-btn ${activeSubtab === 'sop' ? 'active' : ''}`} onClick={() => setActiveSubtab('sop')}>
          <Icon name="file" size={15}/> Manufacturing SOP & Reactor Profile ({form.instructions ? form.instructions.length : 0} Steps)
        </button>
        <button className={`subtab-btn ${activeSubtab === 'regulatory' ? 'active' : ''}`} onClick={() => setActiveSubtab('regulatory')}>
          <Icon name="shield" size={15}/> EU Regulatory, CAS & Allergens
        </button>
        <button className={`subtab-btn ${activeSubtab === 'approvals' ? 'active' : ''}`} onClick={() => setActiveSubtab('approvals')}>
          <Icon name="check" size={15}/> Electronic Signatures & Audit Trail ({form.approvals.length})
        </button>
      </div>

      {/* SUBTAB 1: FORMULA COMPOSITION TABLE */}
      {activeSubtab === 'composition' && (
        <div className="formula-layout">
          <div className="section-card">
            {/* Table Filters */}
            <div className="table-toolbar">
              <div style={{ display: 'flex', gap: '6px' }}>
                <button className={`filter-btn ${phaseFilter === 'ALL' ? 'active' : ''}`} onClick={() => setPhaseFilter('ALL')}>All Phases ({form.lines.length})</button>
                <button className={`filter-btn ${phaseFilter === 'A' ? 'active' : ''}`} onClick={() => setPhaseFilter('A')}>Phase A · Aqueous</button>
                <button className={`filter-btn ${phaseFilter === 'B' ? 'active' : ''}`} onClick={() => setPhaseFilter('B')}>Phase B · Lipid</button>
                <button className={`filter-btn ${phaseFilter === 'C' ? 'active' : ''}`} onClick={() => setPhaseFilter('C')}>Phase C · Actives</button>
              </div>

              <div style={{ display: 'flex', alignItems: 'center', gap: '8px' }}>
                <div className="search table-search">
                  <Icon name="search" size={15}/>
                  <input 
                    placeholder="Filter by INCI, technical name, CAS, function..." 
                    value={tableSearch} 
                    onChange={e => setTableSearch(e.target.value)}
                  />
                </div>
                <Button variant="secondary" icon="plus" onClick={() => setAddIngModal(true)}>
                  + Add Ingredient
                </Button>
              </div>
            </div>

            {/* Main Composition Table */}
            <div className="data-table-wrap">
              <table className="data-table">
                <thead>
                  <tr>
                    <th>Phase</th>
                    <th>INCI Name & CAS</th>
                    <th>Technical Trade Name</th>
                    <th>Function</th>
                    <th className="number">% w/w</th>
                    <th className="number">Scale Qty ({scaledBatchSize} kg)</th>
                    <th className="number">Cost (€)</th>
                    <th>Live Warehouse Stock</th>
                    <th style={{ textAlign: 'right' }}>Action</th>
                  </tr>
                </thead>
                <tbody>
                  {filteredLines.map((l, i) => {
                    const scaledQty = (l.percentage / 100.0) * scaledBatchSize;
                    const rowCost = scaledQty * l.cost_per_unit;
                    return (
                      <tr key={i}>
                        <td>
                          <span className={`phase-badge phase-${l.phase.toLowerCase()}`}>{l.phase}</span>
                        </td>
                        <td>
                          <strong>{l.inci}</strong>
                          <div style={{ fontSize: '8px', color: '#64748b', marginTop: '1px' }}>CAS: {l.cas_number}</div>
                        </td>
                        <td>
                          <span>{l.tech}</span>
                          <div style={{ fontSize: '8px', color: '#94a3b8' }}><code>{l.item_code}</code></div>
                        </td>
                        <td>
                          <span style={{ fontSize: '9px', background: '#f1f5f9', padding: '2px 6px', borderRadius: '4px', fontWeight: 600 }}>
                            {l.function}
                          </span>
                        </td>
                        <td className="number" style={{ fontWeight: 600 }}>
                          {parseFloat(l.percentage).toFixed(3)}%
                        </td>
                        <td className="number">
                          <strong style={{ color: '#0f172a', fontSize: '12px' }}>{scaledQty.toFixed(2)}</strong> kg
                          <div style={{ fontSize: '7px', color: '#64748b' }}>Temp: {l.target_temp}</div>
                        </td>
                        <td className="number">
                          <div style={{ fontWeight: 600 }}>€{rowCost.toFixed(2)}</div>
                          <small style={{ fontSize: '7px', color: '#64748b' }}>€{l.cost_per_unit.toFixed(2)}/kg</small>
                        </td>
                        <td>
                          <span className={`stock-indicator stock-${l.stock.status === 'Sufficient' ? 'sufficient' : l.stock.status === 'Low Stock' ? 'low' : 'out'}`}>
                            {l.stock.status === 'Sufficient' ? '✓ ' : '⚠ '} {l.stock.status} ({l.stock.approved} kg)
                          </span>
                          <div style={{ fontSize: '8px', color: '#475569', marginTop: '2px' }}>
                            Lot: <button className="lot-link" onClick={() => onOpenLot && onOpenLot(l.stock.lot_number)}>{l.stock.lot_number}</button>
                          </div>
                        </td>
                        <td style={{ textAlign: 'right' }}>
                          <button 
                            className="btn btn-secondary" 
                            style={{ height: '24px', padding: '0 6px', fontSize: '8px' }}
                            title="Inspect lot stock in warehouse"
                            onClick={() => onOpenLot && onOpenLot(l.stock.lot_number)}
                          >
                            View Lot
                          </button>
                        </td>
                      </tr>
                    );
                  })}
                </tbody>
                <tfoot>
                  <tr style={{ background: '#f8fafc', fontWeight: 700, borderTop: '2px solid #cbd5e1' }}>
                    <td colSpan={4} style={{ padding: '12px 14px' }}>
                      TOTAL FORMULA BATCH ACCUMULATION (ISO 22716 VERIFIED)
                    </td>
                    <td className="number" style={{ color: '#047857' }}>
                      {form.totalPercentage.toFixed(3)}%
                    </td>
                    <td className="number" style={{ fontSize: '13px' }}>
                      {scaledBatchSize.toFixed(2)} kg
                    </td>
                    <td className="number" style={{ fontSize: '13px', color: '#2563eb' }}>
                      €{currentScaledTotalCost.toFixed(2)}
                    </td>
                    <td colSpan={2} style={{ fontSize: '9px', color: '#047857' }}>
                      ✓ All Raw Material Lots Validated
                    </td>
                  </tr>
                </tfoot>
              </table>
            </div>
          </div>

          {/* Sidebar with Phase Breakdown & QC Specs */}
          <aside style={{ display: 'flex', flexDirection: 'column', gap: '14px' }}>
            <div className="section-card" style={{ padding: '16px' }}>
              <h3 style={{ fontSize: '12px', fontWeight: 700, marginBottom: '10px' }}>Target QC Physical Specs</h3>
              <div style={{ display: 'flex', flexDirection: 'column', gap: '8px', fontSize: '11px' }}>
                <div style={{ display: 'flex', justifyContent: 'space-between', borderBottom: '1px solid #f1f5f9', paddingBottom: '6px' }}>
                  <span style={{ color: '#64748b' }}>Target pH @ 25°C:</span>
                  <strong>{form.phSpec}</strong>
                </div>
                <div style={{ display: 'flex', justifyContent: 'space-between', borderBottom: '1px solid #f1f5f9', paddingBottom: '6px' }}>
                  <span style={{ color: '#64748b' }}>Viscosity @ 25°C:</span>
                  <strong>{form.viscositySpec}</strong>
                </div>
                <div style={{ display: 'flex', justifyContent: 'space-between', borderBottom: '1px solid #f1f5f9', paddingBottom: '6px' }}>
                  <span style={{ color: '#64748b' }}>Density @ 20°C:</span>
                  <strong>{form.densitySpec}</strong>
                </div>
                <div style={{ display: 'flex', justifyContent: 'space-between', paddingBottom: '6px' }}>
                  <span style={{ color: '#64748b' }}>Microbiology:</span>
                  <strong style={{ color: '#047857' }}>&lt; 100 CFU/g (ISO 17516)</strong>
                </div>
              </div>
            </div>

            <div className="section-card" style={{ padding: '16px' }}>
              <h3 style={{ fontSize: '12px', fontWeight: 700, marginBottom: '10px' }}>Phase Weight Breakdown</h3>
              {['A', 'B', 'C'].map(p => {
                const phaseLines = form.lines.filter(l => l.phase === p);
                const phasePct = phaseLines.reduce((acc, l) => acc + l.percentage, 0);
                const phaseKg = (phasePct / 100) * scaledBatchSize;
                return (
                  <div key={p} style={{ marginBottom: '10px' }}>
                    <div style={{ display: 'flex', justifyContent: 'space-between', fontSize: '10px', marginBottom: '3px' }}>
                      <span>Phase {p} ({p === 'A' ? 'Aqueous' : p === 'B' ? 'Lipid' : 'Cool Down'})</span>
                      <strong>{phaseKg.toFixed(1)} kg ({phasePct.toFixed(1)}%)</strong>
                    </div>
                    <div style={{ height: '6px', background: '#f1f5f9', borderRadius: '4px', overflow: 'hidden' }}>
                      <div style={{ height: '100%', width: `${phasePct}%`, background: p === 'A' ? '#3b82f6' : p === 'B' ? '#a855f7' : '#f59e0b' }}/>
                    </div>
                  </div>
                );
              })}
            </div>

            <div className="section-card" style={{ padding: '16px', background: '#f0fdf4', borderColor: '#bbf7d0' }}>
              <div style={{ display: 'flex', gap: '8px', alignItems: 'center', marginBottom: '8px' }}>
                <Icon name="check" size={16}/>
                <strong style={{ fontSize: '11px', color: '#166534' }}>Inventory Feasibility</strong>
              </div>
              <p style={{ fontSize: '10px', color: '#15803d', lineHeight: '1.4' }}>
                All raw material lots required for <strong>{scaledBatchSize} kg</strong> are present in warehouse. Production batch can be safely dispensed.
              </p>
              <Button variant="success" icon="scale" style={{ width: '100%', marginTop: '10px' }} onClick={() => setLaunchModal(true)}>
                Schedule Batch Now
              </Button>
            </div>
          </aside>
        </div>
      )}

      {/* SUBTAB 2: MANUFACTURING SOP INSTRUCTIONS */}
      {activeSubtab === 'sop' && (
        <div className="section-card" style={{ padding: '24px' }}>
          <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: '18px' }}>
            <div>
              <h2 style={{ fontSize: '15px' }}>Master Manufacturing SOP & Temperature Profile</h2>
              <p style={{ fontSize: '11px', color: '#64748b' }}>SOP-MFG-042: Step-by-step compounding protocol for {form.productName}</p>
            </div>
            <div className="live-pill"><span/> Vacuum Homogenizer L01 Ready</div>
          </div>

          <div style={{ display: 'flex', flexDirection: 'column', gap: '16px' }}>
            {form.instructions && form.instructions.map(inst => (
              <div key={inst.step} style={{ border: '1px solid #e2e8f0', borderRadius: '8px', padding: '16px', background: '#fafafa', display: 'flex', gap: '16px' }}>
                <div style={{ width: '36px', height: '36px', borderRadius: '50%', background: '#1e293b', color: 'white', display: 'grid', placeItems: 'center', fontWeight: 'bold', fontSize: '14px', flexShrink: 0 }}>
                  {inst.step}
                </div>
                <div style={{ flex: 1 }}>
                  <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: '4px' }}>
                    <h4 style={{ fontSize: '13px', color: '#0f172a' }}>{inst.phase}</h4>
                    <span style={{ fontSize: '10px', fontWeight: 'bold', background: '#e0e7ff', color: '#3730a3', padding: '2px 8px', borderRadius: '4px' }}>
                      Target Temp: {inst.temp}
                    </span>
                  </div>
                  <p style={{ fontSize: '11px', color: '#334155', lineHeight: '1.5' }}>{inst.text}</p>
                </div>
              </div>
            ))}
          </div>
        </div>
      )}

      {/* SUBTAB 3: REGULATORY & ALLERGENS */}
      {activeSubtab === 'regulatory' && (
        <div className="section-card" style={{ padding: '24px' }}>
          <h2 style={{ fontSize: '15px', marginBottom: '4px' }}>EU Cosmetic Regulation (EC 1223/2009) & Allergen Dossier</h2>
          <p style={{ fontSize: '11px', color: '#64748b', marginBottom: '16px' }}>Product Information File (PIF) Safety Assessment Checklist</p>

          <div style={{ display: 'grid', gridTemplateColumns: 'repeat(3, 1fr)', gap: '14px', marginBottom: '20px' }}>
            <div style={{ padding: '14px', border: '1px solid #cbd5e1', borderRadius: '8px', background: '#f8fafc' }}>
              <span style={{ fontSize: '9px', fontWeight: 'bold', color: '#64748b' }}>SAFETY ASSESSMENT (CPSR)</span>
              <div style={{ fontSize: '13px', fontWeight: 'bold', color: '#047857', marginTop: '4px' }}>✓ Approved Part A & B</div>
              <small style={{ fontSize: '8px', color: '#64748b' }}>Assessor: Dr. A. Lombardi (Eurotox)</small>
            </div>
            <div style={{ padding: '14px', border: '1px solid #cbd5e1', borderRadius: '8px', background: '#f8fafc' }}>
              <span style={{ fontSize: '9px', fontWeight: 'bold', color: '#64748b' }}>PRESERVATIVE CHALLENGE (ISO 11930)</span>
              <div style={{ fontSize: '13px', fontWeight: 'bold', color: '#047857', marginTop: '4px' }}>✓ Passed (Log 4 Reduction)</div>
              <small style={{ fontSize: '8px', color: '#64748b' }}>Euxyl PE 9010 @ 1.00%</small>
            </div>
            <div style={{ padding: '14px', border: '1px solid #cbd5e1', borderRadius: '8px', background: '#f8fafc' }}>
              <span style={{ fontSize: '9px', fontWeight: 'bold', color: '#64748b' }}>CPNP NOTIFICATION PORTAL</span>
              <div style={{ fontSize: '13px', fontWeight: 'bold', color: '#2563eb', marginTop: '4px' }}>CPNP # 3948210</div>
              <small style={{ fontSize: '8px', color: '#64748b' }}>Valid in 27 EU Member States</small>
            </div>
          </div>

          <h3 style={{ fontSize: '12px', fontWeight: 700, marginBottom: '8px' }}>EU 26 Fragrance Allergens Breakdown</h3>
          <table className="data-table" style={{ border: '1px solid #e2e8f0' }}>
            <thead>
              <tr>
                <th>Raw Material</th>
                <th>INCI Name</th>
                <th>Declared Fragrance Allergens</th>
                <th>Concentration in Product</th>
                <th>Labeling Requirement</th>
              </tr>
            </thead>
            <tbody>
              {form.lines.map((l, idx) => (
                <tr key={idx}>
                  <td><strong>{l.tech}</strong></td>
                  <td>{l.inci}</td>
                  <td>{l.allergens}</td>
                  <td>{l.allergens !== 'None' ? `${(l.percentage * 0.05).toFixed(3)}%` : '0.00%'}</td>
                  <td>
                    <Status type={l.allergens === 'None' ? 'approved' : 'warning'}>
                      {l.allergens === 'None' ? 'Not Required' : 'Must Declare on Box'}
                    </Status>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      )}

      {/* SUBTAB 4: APPROVAL WORKFLOW & AUDIT TRAIL */}
      {activeSubtab === 'approvals' && (
        <div className="section-card" style={{ padding: '24px' }}>
          <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: '16px' }}>
            <div>
              <h2 style={{ fontSize: '15px' }}>21 CFR Part 11 Electronic Signature Log</h2>
              <p style={{ fontSize: '11px', color: '#64748b' }}>Tamper-evident audit trail for revision {form.version}</p>
            </div>
            <div style={{ fontSize: '9px', background: '#f1f5f9', padding: '4px 8px', borderRadius: '4px', fontFamily: 'monospace' }}>
              SHA-256: 8a4f9b2c7e108849c90f...
            </div>
          </div>

          <div style={{ display: 'flex', flexDirection: 'column', gap: '14px' }}>
            {form.approvals.map((app, idx) => (
              <div key={idx} style={{ display: 'flex', alignItems: 'center', gap: '14px', border: '1px solid #e2e8f0', padding: '14px', borderRadius: '8px', background: '#fafafa' }}>
                <div style={{ width: '38px', height: '38px', borderRadius: '50%', background: '#d1fae5', color: '#047857', display: 'grid', placeItems: 'center', fontWeight: 'bold' }}>
                  {app.initials}
                </div>
                <div style={{ flex: 1 }}>
                  <div style={{ display: 'flex', justifyContent: 'space-between' }}>
                    <strong style={{ fontSize: '12px' }}>{app.name}</strong>
                    <span style={{ fontSize: '9px', color: '#64748b' }}>{app.signedAt}</span>
                  </div>
                  <div style={{ fontSize: '10px', color: '#4338ca', fontWeight: 600 }}>{app.role}</div>
                  <p style={{ fontSize: '9px', color: '#64748b', marginTop: '2px' }}>
                    "I verify under 21 CFR Part 11 that this formulation specification conforms to all quality and safety benchmarks."
                  </p>
                </div>
                <Status type="approved">Validated</Status>
              </div>
            ))}
          </div>
        </div>
      )}

      {/* MODAL: Launch Production Batch */}
      {launchModal && (
        <div className="modal-overlay" onClick={() => setLaunchModal(false)}>
          <div className="modal" style={{ maxWidth: '520px' }} onClick={e => e.stopPropagation()}>
            <div className="modal-header">
              <h2>Launch Production Batch (eBMR)</h2>
              <button className="icon-button" onClick={() => setLaunchModal(false)}><Icon name="close" size={16}/></button>
            </div>
            <div className="modal-body">
              <p style={{ fontSize: '12px', color: '#475569', marginBottom: '16px' }}>
                This operation will schedule a new production batch for <strong>{form.productName}</strong> and automatically generate all shopfloor dispensing steps from this approved formula.
              </p>

              <div className="form-field">
                <label>Target Batch Scale Size (kg)</label>
                <input type="number" value={scaledBatchSize} onChange={e => setScaledBatchSize(parseFloat(e.target.value) || 0)}/>
              </div>

              <div className="form-field">
                <label>Assigned Production Line</label>
                <select value={launchLine} onChange={e => setLaunchLine(e.target.value)}>
                  <option value="L01">Line L01 · Main Vacuum Homogenizer 1,000L (Creams & Emulsions)</option>
                  <option value="L02">Line L02 · Hot Melt Kettle 500L (Lip Balms & Sticks)</option>
                  <option value="L03">Line L03 · High Speed Agitator 500L (Concentrated Serums)</option>
                  <option value="L04">Line L04 · Continuous Bulk Tank 2,000L (Cleansers & Toners)</option>
                </select>
              </div>

              <div style={{ background: '#ecfdf5', border: '1px solid #a7f3d0', padding: '10px 12px', borderRadius: '6px', fontSize: '11px', color: '#065f46' }}>
                ✓ <strong>FEFO Allocation:</strong> Approved raw material lots from Warehouse WH-A will be automatically allocated to the weighing steps.
              </div>
            </div>
            <div className="modal-footer">
              <Button variant="secondary" onClick={() => setLaunchModal(false)}>Cancel</Button>
              <Button variant="success" icon="scale" onClick={handleConfirmLaunch}>
                Confirm & Generate eBMR
              </Button>
            </div>
          </div>
        </div>
      )}

      {/* MODAL: Printable Master Sheet */}
      {printModal && (
        <MasterFormulaPrintModal 
          form={form} 
          scaledSize={scaledBatchSize} 
          onClose={() => setPrintModal(false)}
        />
      )}

      {/* MODAL: New Revision */}
      {newRevisionModal && (
        <div className="modal-overlay" onClick={() => setNewRevisionModal(false)}>
          <div className="modal" style={{ maxWidth: '480px' }} onClick={e => e.stopPropagation()}>
            <div className="modal-header">
              <h2>Create New Formulation Revision</h2>
              <button className="icon-button" onClick={() => setNewRevisionModal(false)}><Icon name="close" size={16}/></button>
            </div>
            <div className="modal-body">
              <div className="form-field">
                <label>Source Formula</label>
                <input value={`${form.code} (${form.productName})`} readOnly style={{ background: '#f8fafc' }}/>
              </div>
              <div className="form-field">
                <label>New Revision Version</label>
                <input defaultValue="v2.5" placeholder="e.g. v2.5"/>
              </div>
              <div className="form-field">
                <label>Reason for Revision (Change Control)</label>
                <textarea placeholder="e.g. Upgrade active Retinol concentration or replace fragrance supplier for hypoallergenic certification."/>
              </div>
            </div>
            <div className="modal-footer">
              <Button variant="secondary" onClick={() => setNewRevisionModal(false)}>Cancel</Button>
              <Button variant="primary" onClick={() => {
                setNewRevisionModal(false);
                notify('New revision draft initiated in R&D sandbox.');
              }}>
                Create Revision Draft
              </Button>
            </div>
          </div>
        </div>
      )}

      {/* MODAL: Create Brand New Formulation (Full BOM & Recipe Builder) */}
      {newFormulaModal && (
        <div className="modal-overlay" onClick={() => setNewFormulaModal(false)}>
          <div className="modal" style={{ maxWidth: '960px', maxHeight: '94vh' }} onClick={e => e.stopPropagation()}>
            <div className="modal-header">
              <div>
                <h2 style={{ fontSize: '15px' }}>Create New Cosmetic Product Formulation (BOM & Master Recipe)</h2>
                <p style={{ fontSize: '11px', color: '#64748b', marginTop: '2px' }}>
                  Define raw material percentages, phases, unit costs, and quality specs to save in MySQL
                </p>
              </div>
              <button className="icon-button" onClick={() => setNewFormulaModal(false)}><Icon name="close" size={16}/></button>
            </div>

            <div className="modal-body" style={{ display: 'flex', flexDirection: 'column', gap: '16px' }}>
              {/* Template Quick Loader Bar */}
              <div style={{ background: '#f1f5f9', border: '1px solid #cbd5e1', borderRadius: '8px', padding: '10px 14px', display: 'flex', alignItems: 'center', justifyContent: 'space-between', flexWrap: 'wrap', gap: '8px' }}>
                <span style={{ fontSize: '11px', fontWeight: 700, color: '#334155' }}>
                  ⚡ Quick-Load Recipe Templates (نماذج جاهزة سريعة):
                </span>
                <div style={{ display: 'flex', gap: '6px', flexWrap: 'wrap' }}>
                  <button type="button" onClick={() => loadTemplate('cream')} style={{ padding: '4px 10px', fontSize: '11px', fontWeight: 600, border: '1px solid #cbd5e1', borderRadius: '4px', background: 'white', cursor: 'pointer' }}>
                    🌿 Face Cream (100%)
                  </button>
                  <button type="button" onClick={() => loadTemplate('serum')} style={{ padding: '4px 10px', fontSize: '11px', fontWeight: 600, border: '1px solid #cbd5e1', borderRadius: '4px', background: 'white', cursor: 'pointer' }}>
                    ✨ Vitamin C Serum (100%)
                  </button>
                  <button type="button" onClick={() => loadTemplate('micellar')} style={{ padding: '4px 10px', fontSize: '11px', fontWeight: 600, border: '1px solid #cbd5e1', borderRadius: '4px', background: 'white', cursor: 'pointer' }}>
                    💧 Micellar Cleanser (100%)
                  </button>
                  <button type="button" onClick={() => loadTemplate('empty')} style={{ padding: '4px 8px', fontSize: '11px', color: '#64748b', border: '1px dashed #cbd5e1', borderRadius: '4px', background: 'transparent', cursor: 'pointer' }}>
                    Clear
                  </button>
                </div>
              </div>

              {/* General Product Info Grid */}
              <div style={{ display: 'grid', gridTemplateColumns: '2fr 1fr 1fr 1fr', gap: '12px' }}>
                <div className="form-field" style={{ margin: 0 }}>
                  <label>Product Name (اسم المنتج) *</label>
                  <input value={nfName} onChange={e => setNfName(e.target.value)} placeholder="e.g. 24K Gold Collagen Cream 50ml"/>
                </div>
                <div className="form-field" style={{ margin: 0 }}>
                  <label>Formula Code *</label>
                  <input value={nfCode} onChange={e => setNfCode(e.target.value)} placeholder="e.g. FRM-SK-0099"/>
                </div>
                <div className="form-field" style={{ margin: 0 }}>
                  <label>Category</label>
                  <select value={nfCategory} onChange={e => setNfCategory(e.target.value)}>
                    <option value="Skincare">Skincare</option>
                    <option value="Haircare">Haircare</option>
                    <option value="Bodycare">Bodycare</option>
                    <option value="Cleansers">Cleansers</option>
                    <option value="Color Cosmetics">Color Cosmetics</option>
                  </select>
                </div>
                <div className="form-field" style={{ margin: 0 }}>
                  <label>Std Batch Size (kg) *</label>
                  <input type="number" value={nfBatchSize} onChange={e => setNfBatchSize(parseFloat(e.target.value) || 1000)}/>
                </div>
              </div>

              {/* Quality Specs & Author */}
              <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr 1fr 1fr', gap: '12px' }}>
                <div className="form-field" style={{ margin: 0 }}>
                  <label>Author / Chemist</label>
                  <input value={nfAuthor} onChange={e => setNfAuthor(e.target.value)}/>
                </div>
                <div className="form-field" style={{ margin: 0 }}>
                  <label>pH Specification</label>
                  <input value={nfPh} onChange={e => setNfPh(e.target.value)} placeholder="5.50 - 6.50"/>
                </div>
                <div className="form-field" style={{ margin: 0 }}>
                  <label>Viscosity Specification</label>
                  <input value={nfViscosity} onChange={e => setNfViscosity(e.target.value)} placeholder="18,000 - 24,000 cP"/>
                </div>
                <div className="form-field" style={{ margin: 0 }}>
                  <label>Density Specification</label>
                  <input value={nfDensity} onChange={e => setNfDensity(e.target.value)} placeholder="0.98 - 1.02 g/ml"/>
                </div>
              </div>

              {/* Recipe Lines Table (Ingredients Editor) */}
              <div>
                <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', marginBottom: '8px' }}>
                  <strong style={{ fontSize: '12px', color: '#1e293b' }}>
                    Ingredients Composition (% w/w & Costs) — {nfLines.length} Items:
                  </strong>
                  <Button variant="secondary" icon="plus" onClick={handleAddNfLine} style={{ height: '32px', fontSize: '11px' }}>
                    + Add Ingredient Line (إضافة مادة خام)
                  </Button>
                </div>

                <div style={{ border: '1px solid #e2e8f0', borderRadius: '8px', overflow: 'hidden' }}>
                  <table style={{ width: '100%', borderCollapse: 'collapse', fontSize: '11px' }}>
                    <thead style={{ background: '#f8fafc', borderBottom: '1px solid #e2e8f0' }}>
                      <tr>
                        <th style={{ padding: '8px 10px', textAlign: 'left', width: '70px' }}>Phase</th>
                        <th style={{ padding: '8px 10px', textAlign: 'left' }}>Raw Material Name</th>
                        <th style={{ padding: '8px 10px', textAlign: 'left', width: '110px' }}>Function</th>
                        <th style={{ padding: '8px 10px', textAlign: 'right', width: '90px' }}>% w/w</th>
                        <th style={{ padding: '8px 10px', textAlign: 'right', width: '90px' }}>Scale Qty</th>
                        <th style={{ padding: '8px 10px', textAlign: 'right', width: '90px' }}>Cost (€/kg)</th>
                        <th style={{ padding: '8px 10px', textAlign: 'right', width: '90px' }}>Line Cost</th>
                        <th style={{ padding: '8px 10px', textAlign: 'center', width: '40px' }}></th>
                      </tr>
                    </thead>
                    <tbody>
                      {nfLines.map((line, idx) => {
                        const scaleKg = ((parseFloat(line.percentage) || 0) / 100.0) * nfBatchSize;
                        const lineCost = scaleKg * (parseFloat(line.cost_per_unit) || 0);
                        return (
                          <tr key={idx} style={{ borderBottom: '1px solid #f1f5f9' }}>
                            <td style={{ padding: '6px 8px' }}>
                              <select 
                                value={line.phase} 
                                onChange={e => handleNfLineChange(idx, 'phase', e.target.value)}
                                style={{ width: '100%', height: '30px', fontWeight: 'bold', borderRadius: '4px', border: '1px solid #cbd5e1' }}
                              >
                                <option value="A">Phase A</option>
                                <option value="B">Phase B</option>
                                <option value="C">Phase C</option>
                              </select>
                            </td>
                            <td style={{ padding: '6px 8px' }}>
                              <input 
                                value={line.item_name} 
                                onChange={e => handleNfLineChange(idx, 'item_name', e.target.value)}
                                placeholder="Ingredient name (e.g. Purified Water)"
                                style={{ width: '100%', height: '30px', padding: '0 8px', borderRadius: '4px', border: '1px solid #cbd5e1', fontSize: '11px', fontWeight: 600 }}
                              />
                            </td>
                            <td style={{ padding: '6px 8px' }}>
                              <select 
                                value={line.function} 
                                onChange={e => handleNfLineChange(idx, 'function', e.target.value)}
                                style={{ width: '100%', height: '30px', borderRadius: '4px', border: '1px solid #cbd5e1', fontSize: '10px' }}
                              >
                                <option value="Solvent">Solvent</option>
                                <option value="Humectant">Humectant</option>
                                <option value="Active">Active</option>
                                <option value="Emulsifier">Emulsifier</option>
                                <option value="Emollient">Emollient</option>
                                <option value="Thickener">Thickener</option>
                                <option value="Preservative">Preservative</option>
                                <option value="Fragrance">Fragrance</option>
                                <option value="pH Buffer">pH Buffer</option>
                              </select>
                            </td>
                            <td style={{ padding: '6px 8px', textAlign: 'right' }}>
                              <input 
                                type="number" 
                                step="0.01" 
                                value={line.percentage} 
                                onChange={e => handleNfLineChange(idx, 'percentage', parseFloat(e.target.value) || 0)}
                                style={{ width: '75px', height: '30px', textAlign: 'right', fontWeight: 'bold', padding: '0 6px', borderRadius: '4px', border: '1px solid #cbd5e1' }}
                              /> %
                            </td>
                            <td style={{ padding: '6px 8px', textAlign: 'right', fontWeight: 600, color: '#334155' }}>
                              {scaleKg.toFixed(2)} kg
                            </td>
                            <td style={{ padding: '6px 8px', textAlign: 'right' }}>
                              € <input 
                                type="number" 
                                step="0.01" 
                                value={line.cost_per_unit} 
                                onChange={e => handleNfLineChange(idx, 'cost_per_unit', parseFloat(e.target.value) || 0)}
                                style={{ width: '65px', height: '30px', textAlign: 'right', padding: '0 6px', borderRadius: '4px', border: '1px solid #cbd5e1' }}
                              />
                            </td>
                            <td style={{ padding: '6px 8px', textAlign: 'right', fontWeight: 'bold', color: '#0f172a' }}>
                              €{lineCost.toFixed(2)}
                            </td>
                            <td style={{ padding: '6px 8px', textAlign: 'center' }}>
                              <button 
                                type="button" 
                                onClick={() => handleRemoveNfLine(idx)}
                                style={{ border: 0, background: 'transparent', color: '#ef4444', cursor: 'pointer', fontSize: '13px' }}
                                title="Delete line"
                              >
                                ✕
                              </button>
                            </td>
                          </tr>
                        );
                      })}
                    </tbody>
                  </table>
                </div>
              </div>

              {/* Formula Integrity & Live Costing Bar */}
              <div style={{ display: 'grid', gridTemplateColumns: '1.2fr 1fr 1fr 1fr', gap: '10px', background: '#f8fafc', padding: '12px 16px', borderRadius: '8px', border: '1px solid #e2e8f0', alignItems: 'center' }}>
                <div>
                  <span style={{ fontSize: '10px', fontWeight: 700, color: '#64748b', display: 'block' }}>FORMULA BALANCE CHECK</span>
                  <strong style={{ fontSize: '16px', color: Math.abs(nfTotalPercentage - 100) < 0.05 ? '#059669' : '#dc2626' }}>
                    {nfTotalPercentage.toFixed(3)}%
                  </strong>
                  <div style={{ fontSize: '9px', fontWeight: 600, color: Math.abs(nfTotalPercentage - 100) < 0.05 ? '#059669' : '#dc2626' }}>
                    {Math.abs(nfTotalPercentage - 100) < 0.05 ? '✓ Balanced 100.000%' : `Difference: ${(100 - nfTotalPercentage).toFixed(3)}%`}
                  </div>
                </div>
                <div>
                  <span style={{ fontSize: '10px', fontWeight: 700, color: '#64748b', display: 'block' }}>EST. RAW MATERIAL COST</span>
                  <strong style={{ fontSize: '16px', color: '#0284c7' }}>
                    €{nfTotalBatchCost.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}
                  </strong>
                  <div style={{ fontSize: '9px', color: '#64748b' }}>For {nfBatchSize} kg batch</div>
                </div>
                <div>
                  <span style={{ fontSize: '10px', fontWeight: 700, color: '#64748b', display: 'block' }}>COST / KG</span>
                  <strong style={{ fontSize: '16px', color: '#334155' }}>€{nfCostPerKg}</strong>
                  <div style={{ fontSize: '9px', color: '#64748b' }}>Raw materials only</div>
                </div>
                <div>
                  <span style={{ fontSize: '10px', fontWeight: 700, color: '#64748b', display: 'block' }}>EST. UNIT COST</span>
                  <strong style={{ fontSize: '16px', color: '#10b981' }}>€{nfCostPerUnit}</strong>
                  <div style={{ fontSize: '9px', color: '#64748b' }}>Includes €0.65 Primary Jar</div>
                </div>
              </div>
            </div>

            <div className="modal-footer">
              <Button variant="secondary" onClick={() => setNewFormulaModal(false)}>Cancel</Button>
              <Button variant="primary" icon="check" onClick={handleSaveNewFormula} style={{ padding: '0 20px', height: '40px' }}>
                Save & Approve Formulation (حفظ واعتماد التركيبة)
              </Button>
            </div>
          </div>
        </div>
      )}

      {/* MODAL: Add Single Ingredient to Active Formulation */}
      {addIngModal && (
        <div className="modal-overlay" onClick={() => setAddIngModal(false)}>
          <div className="modal" style={{ maxWidth: '500px' }} onClick={e => e.stopPropagation()}>
            <div className="modal-header">
              <div>
                <h2>Add Raw Material Ingredient</h2>
                <p style={{ fontSize: '10px', color: '#64748b' }}>Target Formula: {form.code} ({form.productName})</p>
              </div>
              <button className="icon-button" onClick={() => setAddIngModal(false)}><Icon name="close" size={16}/></button>
            </div>
            <div className="modal-body" style={{ display: 'flex', flexDirection: 'column', gap: '12px' }}>
              <div className="form-field">
                <label>Select Existing Raw Material from Inventory</label>
                <select 
                  onChange={e => {
                    const itm = availableItems.find(x => x.id === parseInt(e.target.value));
                    if (itm) {
                      setSingleItemName(itm.name);
                      setSingleItemCode(itm.code);
                      setSingleCost(itm.cost_per_unit || '5.0');
                      setSingleFunction(itm.function || 'Active');
                    }
                  }}
                >
                  <option value="">-- Choose from Master Items or enter custom below --</option>
                  {availableItems.map(itm => (
                    <option key={itm.id} value={itm.id}>
                      {itm.code} · {itm.name} (€{itm.cost_per_unit}/kg)
                    </option>
                  ))}
                </select>
              </div>
              <div className="form-field">
                <label>Ingredient Name *</label>
                <input 
                  value={singleItemName} 
                  onChange={e => setSingleItemName(e.target.value)} 
                  placeholder="e.g. Hyaluronic Acid Multi-Molecular"
                />
              </div>
              <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '10px' }}>
                <div className="form-field">
                  <label>Phase *</label>
                  <select value={singlePhase} onChange={e => setSinglePhase(e.target.value)}>
                    <option value="A">Phase A (Aqueous)</option>
                    <option value="B">Phase B (Lipid / Oil)</option>
                    <option value="C">Phase C (Actives / Perfume)</option>
                  </select>
                </div>
                <div className="form-field">
                  <label>Percentage (% w/w) *</label>
                  <input 
                    type="number" 
                    step="0.01" 
                    value={singlePct} 
                    onChange={e => setSinglePct(e.target.value)}
                  />
                </div>
              </div>
              <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '10px' }}>
                <div className="form-field">
                  <label>Function</label>
                  <select value={singleFunction} onChange={e => setSingleFunction(e.target.value)}>
                    <option value="Active">Active</option>
                    <option value="Humectant">Humectant</option>
                    <option value="Emulsifier">Emulsifier</option>
                    <option value="Emollient">Emollient</option>
                    <option value="Thickener">Thickener</option>
                    <option value="Preservative">Preservative</option>
                    <option value="Fragrance">Fragrance</option>
                    <option value="Solvent">Solvent</option>
                  </select>
                </div>
                <div className="form-field">
                  <label>Cost (€ / kg)</label>
                  <input 
                    type="number" 
                    step="0.01" 
                    value={singleCost} 
                    onChange={e => setSingleCost(e.target.value)}
                  />
                </div>
              </div>
            </div>
            <div className="modal-footer">
              <Button variant="secondary" onClick={() => setAddIngModal(false)}>Cancel</Button>
              <Button variant="primary" icon="check" onClick={handleSaveSingleIngredient}>
                Add to Formula
              </Button>
            </div>
          </div>
        </div>
      )}
    </div>
  );
}
@endverbatim
