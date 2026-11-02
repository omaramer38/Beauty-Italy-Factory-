@verbatim
// Module 2: Inventory & Lots (WMS)
function Inventory({ notify, initialLotNumber }) {
  const [lots, setLots] = useState([]);
  const [stats, setStats] = useState(null);
  const [filter, setFilter] = useState('All');
  const [search, setSearch] = useState(initialLotNumber || '');
  const [selected, setSelected] = useState(null);
  const [ledger, setLedger] = useState([]);
  const [barcodeModal, setBarcodeModal] = useState(null);
  const [receiptModal, setReceiptModal] = useState(false);
  const [transferModal, setTransferModal] = useState(null);
  const [newLocation, setNewLocation] = useState('');
  const [itemsList, setItemsList] = useState([]);

  const [receiptForm, setReceiptForm] = useState({
    item_id: '',
    quantity: '',
    supplier_name: '',
    source_document: '',
    expiry_date: '',
    storage_location: 'WH-Q · Quarantine Bay · Q01',
  });

  const refreshData = () => {
    fetch('/api/v1/lots')
      .then(res => res.json())
      .then(data => {
        setLots(data);
        if (initialLotNumber) {
          const matched = data.find(l => l.lot === initialLotNumber);
          if (matched) openLot(matched);
        }
      });
    fetch('/api/v1/warehouse/stats')
      .then(res => res.json())
      .then(data => setStats(data));
    fetch('/api/v1/items')
      .then(res => res.json())
      .then(data => {
        setItemsList(data);
        if (data.length > 0 && !receiptForm.item_id) {
          setReceiptForm(f => ({ ...f, item_id: data[0].id }));
        }
      });
  };

  useEffect(() => {
    refreshData();
  }, []);

  const openLot = (lot) => {
    setSelected(lot);
    fetch(`/api/v1/lots/${lot.lot}/ledger`)
      .then(res => res.json())
      .then(data => setLedger(data.ledger || []));
  };

  const submitReceipt = (e) => {
    e.preventDefault();
    fetch('/api/v1/lots/receipt', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(receiptForm)
    })
    .then(res => res.json())
    .then(data => {
      setReceiptModal(false);
      notify(data.message);
      refreshData();
    });
  };

  const submitTransfer = () => {
    if (!transferModal || !newLocation) return;
    fetch(`/api/v1/lots/${transferModal.id}/location`, {
      method: 'PUT',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ storage_location: newLocation })
    })
    .then(res => res.json())
    .then(data => {
      setTransferModal(null);
      notify(data.message);
      refreshData();
    });
  };

  const filteredLots = lots.filter(l => {
    if (filter === 'Quarantine' && l.status !== 'Quarantine') return false;
    if (filter === 'Approved' && l.status !== 'Approved') return false;
    if (filter === 'FEFO' && !l.fefo) return false;
    if (filter === 'RM' && l.type !== 'raw_material') return false;
    if (filter === 'PK' && l.type !== 'packaging') return false;

    if (search) {
      const s = search.toLowerCase();
      return l.lot.toLowerCase().includes(s) || 
             l.name.toLowerCase().includes(s) || 
             l.code.toLowerCase().includes(s) || 
             l.location.toLowerCase().includes(s) || 
             (l.supplier && l.supplier.toLowerCase().includes(s));
    }
    return true;
  });

  return (
    <div>
      <div className="page-header">
        <div>
          <div className="eyebrow">WAREHOUSE MANAGEMENT SYSTEM (WMS) · LOT LEDGER</div>
          <h1>Inventory & Lots Repository</h1>
          <p>Segregation of Quarantine, Approved, and Rejected material stocks</p>
        </div>
        <div className="header-actions">
          <Button icon="plus" onClick={() => setReceiptModal(true)}>New Goods Receipt</Button>
        </div>
      </div>

      {/* Warehouse Zones */}
      {stats && stats.zones && (
        <div className="zone-grid">
          {stats.zones.map(z => (
            <div className="zone-card" key={z.code}>
              <div className="zone-top">
                <span>{z.code} · {z.name}</span>
                <b style={{ color: z.status === 'Priority QC' ? '#b45309' : z.status === 'Locked GMP' ? '#be123c' : '#047857' }}>
                  {z.status}
                </b>
              </div>
              <strong>{z.count} / {z.capacity} Lots ({z.occupancy}%)</strong>
              <div className="zone-bar">
                <div 
                  className="zone-fill" 
                  style={{ 
                    width: `${z.occupancy}%`, 
                    background: z.code === 'WH-Q' ? '#f59e0b' : z.code === 'WH-R' ? '#ef4444' : '#10b981' 
                  }}
                />
              </div>
            </div>
          ))}
        </div>
      )}

      {/* Filter Bar & Search */}
      <div className="section-card">
        <div className="filter-tabs">
          <button className={`filter-btn ${filter === 'All' ? 'active' : ''}`} onClick={() => setFilter('All')}>All Lots ({lots.length})</button>
          <button className={`filter-btn ${filter === 'Quarantine' ? 'active' : ''}`} onClick={() => setFilter('Quarantine')}>🟧 Quarantine Only ({lots.filter(l => l.status === 'Quarantine').length})</button>
          <button className={`filter-btn ${filter === 'Approved' ? 'active' : ''}`} onClick={() => setFilter('Approved')}>🟩 Approved ({lots.filter(l => l.status === 'Approved').length})</button>
          <button className={`filter-btn ${filter === 'FEFO' ? 'active' : ''}`} onClick={() => setFilter('FEFO')}>⏳ FEFO Expiring Soon ({lots.filter(l => l.fefo !== '').length})</button>
          <button className={`filter-btn ${filter === 'RM' ? 'active' : ''}`} onClick={() => setFilter('RM')}>🧪 Raw Materials</button>
          <button className={`filter-btn ${filter === 'PK' ? 'active' : ''}`} onClick={() => setFilter('PK')}>📦 Packaging</button>
        </div>

        <div className="table-toolbar">
          <div className="search table-search">
            <Icon name="search" size={16}/>
            <input 
              placeholder="Search by lot number, item name, location, supplier..." 
              value={search} 
              onChange={e => setSearch(e.target.value)}
            />
          </div>
          <div style={{ fontSize: '10px', color: '#64748b' }}>
            Showing <strong>{filteredLots.length}</strong> of {lots.length} lots in MySQL
          </div>
        </div>

        {/* Data Table */}
        <div className="data-table-wrap">
          <table className="data-table">
            <thead>
              <tr>
                <th>Item code</th>
                <th>Product / Material</th>
                <th>Lot number</th>
                <th>Expiry date</th>
                <th>Storage location</th>
                <th>Status</th>
                <th className="number">Available qty</th>
                <th>Unit</th>
                <th style={{ textAlign: 'right' }}>Actions</th>
              </tr>
            </thead>
            <tbody>
              {filteredLots.map(lot => (
                <tr key={lot.lot} onClick={() => openLot(lot)}>
                  <td><code>{lot.code}</code></td>
                  <td><strong>{lot.name}</strong></td>
                  <td><button className="lot-link">{lot.lot}</button></td>
                  <td>
                    <div>
                      {lot.expiry}
                      {lot.fefo && <small style={{ display: 'block', color: '#be123c', fontWeight: 'bold' }}>⏳ {lot.fefo} left (FEFO)</small>}
                    </div>
                  </td>
                  <td><span>{lot.location}</span></td>
                  <td>
                    <Status type={lot.status === 'Approved' ? 'approved' : lot.status === 'Quarantine' ? 'warning' : 'danger'}>
                      {lot.status}
                    </Status>
                  </td>
                  <td className="number"><strong>{lot.qty}</strong></td>
                  <td>{lot.unit}</td>
                  <td onClick={e => e.stopPropagation()} style={{ textAlign: 'right' }}>
                    <div style={{ display: 'flex', gap: '5px', justifyContent: 'flex-end' }}>
                      <button 
                        className="btn btn-secondary" 
                        style={{ height: '26px', padding: '0 8px', fontSize: '9px' }}
                        onClick={() => setBarcodeModal(lot)}
                      >
                        <Icon name="qr" size={13}/> QR Label
                      </button>
                      <button 
                        className="btn btn-secondary" 
                        style={{ height: '26px', padding: '0 8px', fontSize: '9px' }}
                        onClick={() => { setTransferModal(lot); setNewLocation(lot.location); }}
                      >
                        Move
                      </button>
                    </div>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      </div>

      {/* Drawer: Lot Details & Immutable Ledger */}
      {selected && (
        <>
          <div className="drawer-overlay" onClick={() => setSelected(null)}/>
          <aside className="drawer">
            <div className="drawer-head">
              <div>
                <span>LOT DETAILS & IMMUTABLE LEDGER</span>
                <h2>{selected.lot}</h2>
                <p>{selected.name}</p>
              </div>
              <button className="icon-button" onClick={() => setSelected(null)}><Icon name="close"/></button>
            </div>
            
            <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: '14px' }}>
              <Status type={selected.status === 'Approved' ? 'approved' : selected.status === 'Quarantine' ? 'warning' : 'danger'}>
                {selected.status}
              </Status>
              <QRCodeImage text={`LOT:${selected.lot}|CODE:${selected.code}|QTY:${selected.qty}`} size={60}/>
            </div>

            <div className="drawer-section">
              <h3 style={{ fontSize: '11px', fontWeight: 700, marginBottom: '8px' }}>Physical Lot Master</h3>
              <dl>
                <div><dt>Item Code</dt><dd>{selected.code}</dd></div>
                <div><dt>Current Quantity</dt><dd>{selected.qty} {selected.unit}</dd></div>
                <div><dt>Current Location</dt><dd>{selected.location}</dd></div>
                <div><dt>Expiry Date</dt><dd>{selected.expiry}</dd></div>
                <div><dt>Supplier / Origin</dt><dd>{selected.supplier || 'Factory Generated'}</dd></div>
                <div><dt>Source PO / GRN</dt><dd>{selected.sourceDocument}</dd></div>
              </dl>
            </div>

            <div style={{ marginTop: '20px' }}>
              <h3 style={{ fontSize: '11px', fontWeight: 700, marginBottom: '10px' }}>Immutable Movement Ledger</h3>
              {ledger.map((entry, idx) => (
                <div key={idx} className="ledger-entry">
                  <div style={{ display: 'flex', justifyContent: 'space-between', fontSize: '10px' }}>
                    <strong>{entry.action}</strong>
                    <span style={{ color: '#94a3b8' }}>{entry.date}</span>
                  </div>
                  <p style={{ fontSize: '10px', color: '#475569', marginTop: '2px' }}>{entry.detail}</p>
                  <small style={{ fontSize: '8px', color: '#94a3b8' }}>User: {entry.user} · Doc: {entry.document}</small>
                </div>
              ))}
            </div>
          </aside>
        </>
      )}

      {/* Modal: New Goods Receipt */}
      {receiptModal && (
        <div className="modal-overlay" onClick={() => setReceiptModal(false)}>
          <div className="modal" onClick={e => e.stopPropagation()}>
            <div className="modal-header">
              <h2>New Goods Receipt · Raw Materials & Packaging</h2>
              <button className="icon-button" onClick={() => setReceiptModal(false)}><Icon name="close"/></button>
            </div>
            <form onSubmit={submitReceipt}>
              <div className="modal-body">
                <div className="form-field">
                  <label>Material Master Item</label>
                  <select 
                    value={receiptForm.item_id} 
                    onChange={e => setReceiptForm({ ...receiptForm, item_id: e.target.value })}
                    required
                  >
                    {itemsList.map(it => (
                      <option key={it.id} value={it.id}>{it.code} — {it.name} ({it.type})</option>
                    ))}
                  </select>
                </div>

                <div className="form-grid-2">
                  <div className="form-field">
                    <label>Received Quantity</label>
                    <input 
                      type="number" 
                      step="0.01" 
                      placeholder="e.g. 500" 
                      value={receiptForm.quantity} 
                      onChange={e => setReceiptForm({ ...receiptForm, quantity: e.target.value })}
                      required
                    />
                  </div>
                  <div className="form-field">
                    <label>Expiration Date (FEFO)</label>
                    <input 
                      type="date" 
                      value={receiptForm.expiry_date} 
                      onChange={e => setReceiptForm({ ...receiptForm, expiry_date: e.target.value })}
                    />
                  </div>
                </div>

                <div className="form-field">
                  <label>Supplier / Manufacturer Name</label>
                  <input 
                    placeholder="e.g. Lonza, Givaudan, BASF" 
                    value={receiptForm.supplier_name} 
                    onChange={e => setReceiptForm({ ...receiptForm, supplier_name: e.target.value })}
                    required
                  />
                </div>

                <div className="form-field">
                  <label>Source Document (PO / Delivery Note)</label>
                  <input 
                    placeholder="e.g. PO-2026-0041" 
                    value={receiptForm.source_document} 
                    onChange={e => setReceiptForm({ ...receiptForm, source_document: e.target.value })}
                    required
                  />
                </div>

                <div style={{ background: '#fef3c7', border: '1px solid #fde68a', padding: '10px 12px', borderRadius: '6px', fontSize: '10px', color: '#92400e' }}>
                  ⚠ <strong>ISO 22716 Mandatory Hold:</strong> All newly received lots automatically enter Quarantine in Zone WH-Q until QC release.
                </div>
              </div>
              <div className="modal-footer">
                <Button variant="secondary" onClick={() => setReceiptModal(false)}>Cancel</Button>
                <Button variant="primary" type="submit">Receive Goods & Create Lot</Button>
              </div>
            </form>
          </div>
        </div>
      )}

      {/* Modal: QR Barcode Label */}
      {barcodeModal && (
        <div className="modal-overlay" onClick={() => setBarcodeModal(null)}>
          <div className="modal" style={{ maxWidth: '480px' }} onClick={e => e.stopPropagation()}>
            <div className="modal-header">
              <h2>Drum Barcode & QR Label</h2>
              <button className="icon-button" onClick={() => setBarcodeModal(null)}><Icon name="close"/></button>
            </div>
            <div className="modal-body" style={{ textAlign: 'center' }}>
              <div id="drum-label-paper" className="report-paper" style={{ border: '2px dashed #94a3b8', borderRadius: '8px', padding: '16px', background: 'white', maxWidth: '340px', margin: '0 auto' }}>
                <div style={{ fontSize: '9px', fontWeight: 700, letterSpacing: '1px', color: '#64748b' }}>BEAUTY ITALY FACTORY · MILANO</div>
                <h3 style={{ fontSize: '18px', margin: '4px 0', color: '#0f172a' }}>{barcodeModal.lot}</h3>
                <div style={{ fontSize: '11px', fontWeight: 600, color: '#334155' }}>{barcodeModal.name} ({barcodeModal.code})</div>
                <div id="drum-qr-container" style={{ margin: '12px 0', position: 'relative', display: 'inline-block' }}>
                  <QRCodeImage 
                    text={`LOT:${barcodeModal.lot}|ITEM:${barcodeModal.code}|QTY:${barcodeModal.qty}${barcodeModal.unit}`} 
                    size={120} 
                    showLabel={true} 
                    labelText={barcodeModal.lot}
                  />
                  <div style={{ marginTop: '6px' }}>
                    <button 
                      type="button"
                      onClick={() => window.saveQrOnlyImage('drum-qr-container', `QR-${barcodeModal.lot}.png`)}
                      style={{ background: 'none', border: 'none', color: '#4f46e5', fontSize: '11px', cursor: 'pointer', textDecoration: 'underline', fontWeight: 600 }}
                      title="Download isolated QR code square only"
                    >
                      🔳 Save QR Only (PNG)
                    </button>
                  </div>
                </div>
                <Status type={barcodeModal.status === 'Approved' ? 'approved' : 'warning'}>
                  {barcodeModal.status === 'Approved' ? '✓ APPROVED FOR MES USE' : '⚠ QUARANTINE — DO NOT WEIGH'}
                </Status>
                <div style={{ display: 'flex', justifyContent: 'space-between', fontSize: '9px', borderTop: '1px solid #e2e8f0', paddingTop: '8px', marginTop: '12px', color: '#64748b' }}>
                  <div><b>Qty:</b> {barcodeModal.qty} {barcodeModal.unit}</div>
                  <div><b>Loc:</b> {barcodeModal.location}</div>
                  <div><b>Exp:</b> {barcodeModal.expiry}</div>
                </div>
              </div>
            </div>
            <div className="modal-footer" style={{ display: 'flex', justifyContent: 'space-between', gap: '8px', flexWrap: 'wrap' }}>
              <Button variant="secondary" onClick={() => setBarcodeModal(null)}>Close</Button>
              <div style={{ display: 'flex', gap: '8px', flexWrap: 'wrap' }}>
                <Button variant="secondary" icon="printer" onClick={() => window.cleanPrint('drum-label-paper', `Label-${barcodeModal.lot}.pdf`)}>
                  🖨️ Clean Print
                </Button>
                <Button variant="secondary" icon="file" onClick={() => window.saveQrLabelImage(barcodeModal, 'drum-qr-container', `Label-${barcodeModal.lot}.png`)}>
                  🖼️ Save Image (PNG)
                </Button>
                <Button variant="primary" icon="file" onClick={() => window.downloadReportPdf('drum-label-paper', `Label-${barcodeModal.lot}.pdf`)}>
                  📥 Download Label PDF
                </Button>
              </div>
            </div>
          </div>
        </div>
      )}

      {/* Modal: Transfer Location */}
      {transferModal && (
        <div className="modal-overlay" onClick={() => setTransferModal(null)}>
          <div className="modal" style={{ maxWidth: '440px' }} onClick={e => e.stopPropagation()}>
            <div className="modal-header">
              <h2>Transfer Location: Lot {transferModal.lot}</h2>
              <button className="icon-button" onClick={() => setTransferModal(null)}><Icon name="close"/></button>
            </div>
            <div className="modal-body">
              <div className="form-field">
                <label>Current Location</label>
                <input value={transferModal.location} readOnly style={{ background: '#f8fafc' }}/>
              </div>
              <div className="form-field">
                <label>New Storage Bay / Rack</label>
                <select value={newLocation} onChange={e => setNewLocation(e.target.value)}>
                  <optgroup label="Main Warehouse WH-A (Raw Materials)">
                    <option value="WH-A · A01 · S08">WH-A · Rack A01 · Shelf S08</option>
                    <option value="WH-A · A02 · S04">WH-A · Rack A02 · Shelf S04</option>
                    <option value="WH-A · A03 · S12">WH-A · Rack A03 · Shelf S12</option>
                    <option value="WH-A · A04 · S02">WH-A · Rack A04 · Shelf S02</option>
                  </optgroup>
                  <optgroup label="Quarantine Isolation WH-Q">
                    <option value="WH-Q · Quarantine Bay · Q01">WH-Q · Quarantine Bay · Q01</option>
                    <option value="WH-Q · Quarantine Bay · Q02">WH-Q · Quarantine Bay · Q02</option>
                  </optgroup>
                  <optgroup label="Packaging WH-P">
                    <option value="WH-P · P04 · S01">WH-P · Glass Racks · P04</option>
                    <option value="WH-P · P07 · S02">WH-P · Bottles Bay · P07</option>
                  </optgroup>
                  <optgroup label="Rejected Cage WH-R">
                    <option value="WH-R · Isolation Cage · R02">WH-R · Isolation Cage · R02</option>
                  </optgroup>
                </select>
              </div>
            </div>
            <div className="modal-footer">
              <Button variant="secondary" onClick={() => setTransferModal(null)}>Cancel</Button>
              <Button variant="primary" onClick={submitTransfer}>Update Location & Log Ledger</Button>
            </div>
          </div>
        </div>
      )}
    </div>
  );
}
@endverbatim
