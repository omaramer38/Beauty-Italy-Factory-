@verbatim
// Module 2: Inventory & Lots (WMS) & Master Items Catalog
function Inventory({ notify, initialLotNumber, navigate }) {
  const [activeView, setActiveView] = useState('lots'); // 'lots' | 'catalog'
  const [lots, setLots] = useState([]);
  const [stats, setStats] = useState(null);
  const [filter, setFilter] = useState('All');
  const [itemFilter, setItemFilter] = useState('All');
  const [search, setSearch] = useState(initialLotNumber || '');
  const [itemSearch, setItemSearch] = useState('');
  const [selected, setSelected] = useState(null);
  const [ledger, setLedger] = useState([]);
  const [itemsList, setItemsList] = useState([]);
  const [suppliersList, setSuppliersList] = useState([]);
  const [invoicesList, setInvoicesList] = useState([]);

  // Modals state
  const [barcodeModal, setBarcodeModal] = useState(null);
  const [receiptModal, setReceiptModal] = useState(false);
  const [transferModal, setTransferModal] = useState(null);
  const [newLocation, setNewLocation] = useState('');

  // Edit Lot state
  const [editLotModal, setEditLotModal] = useState(null);
  const [editLotForm, setEditLotForm] = useState({
    quantity: '',
    storage_location: '',
    expiry_date: '',
    status: 'Approved',
    supplier_name: '',
    source_document: '',
    reason: '',
  });

  // Delete Lot state
  const [deleteLotModal, setDeleteLotModal] = useState(null);

  // Master Item modal state (create / edit)
  const [itemModal, setItemModal] = useState(false);
  const [editingItem, setEditingItem] = useState(null);
  const [itemForm, setItemForm] = useState({
    code: '',
    name: '',
    type: 'packaging',
    unit: 'pcs',
    cost_per_unit: '0.00',
    function: '',
    inci_name: '',
    technical_name: '',
    cas_number: '',
    allergens: 'None',
  });

  // Delete Item state
  const [deleteItemModal, setDeleteItemModal] = useState(null);

  // Goods Receipt Form
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
      })
      .catch(err => console.error('Error fetching lots:', err));

    fetch('/api/v1/warehouse/stats')
      .then(res => res.json())
      .then(data => setStats(data))
      .catch(err => console.error('Error fetching stats:', err));

    fetch('/api/v1/items')
      .then(res => res.json())
      .then(data => {
        setItemsList(data);
        if (data.length > 0 && !receiptForm.item_id) {
          setReceiptForm(f => ({ ...f, item_id: data[0].id }));
        }
      })
      .catch(err => console.error('Error fetching items:', err));

    fetch('/api/v1/suppliers')
      .then(res => res.json())
      .then(data => {
        setSuppliersList(data);
        if (data.length > 0 && !receiptForm.supplier_name) {
          setReceiptForm(f => ({ ...f, supplier_name: data[0].name }));
        }
      })
      .catch(err => console.error('Error fetching suppliers:', err));

    fetch('/api/v1/invoices')
      .then(res => res.json())
      .then(data => setInvoicesList(data))
      .catch(err => console.error('Error fetching invoices:', err));
  };

  useEffect(() => {
    refreshData();
  }, []);

  const openLot = (lot) => {
    setSelected(lot);
    fetch(`/api/v1/lots/${lot.lot}/ledger`)
      .then(res => res.json())
      .then(data => setLedger(data.ledger || []))
      .catch(err => console.error('Error fetching ledger:', err));
  };

  // --- Lot Operations ---
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
      notify(data.message || 'Goods received successfully!');
      refreshData();
    })
    .catch(err => {
      console.error(err);
      notify('Failed to process goods receipt');
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
      notify(data.message || 'Location updated successfully');
      refreshData();
    })
    .catch(err => {
      console.error(err);
      notify('Failed to update location');
    });
  };

  const openEditLot = (lot) => {
    setEditLotModal(lot);
    setEditLotForm({
      quantity: lot.raw_qty !== undefined ? lot.raw_qty : parseFloat(String(lot.qty).replace(/,/g, '')),
      storage_location: lot.location || '',
      expiry_date: lot.raw_expiry || '',
      status: lot.status || 'Approved',
      supplier_name: lot.supplier || '',
      source_document: lot.sourceDocument || '',
      reason: 'Physical weight / stock adjustment (تسوية وتصحيح رصيد)',
    });
  };

  const submitEditLot = (e) => {
    e.preventDefault();
    if (!editLotModal) return;
    fetch(`/api/v1/lots/${editLotModal.id}`, {
      method: 'PUT',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(editLotForm)
    })
    .then(res => res.json())
    .then(data => {
      if (data.error) {
        alert(data.error);
        return;
      }
      setEditLotModal(null);
      notify(data.message || 'Lot details and quantity updated successfully!');
      refreshData();
      if (selected && selected.id === editLotModal.id) {
        setSelected(null);
      }
    })
    .catch(err => {
      console.error(err);
      notify('Failed to update lot details');
    });
  };

  const submitDeleteLot = () => {
    if (!deleteLotModal) return;
    fetch(`/api/v1/lots/${deleteLotModal.id}`, {
      method: 'DELETE',
    })
    .then(res => res.json())
    .then(data => {
      if (data.error) {
        alert(data.error);
        return;
      }
      setDeleteLotModal(null);
      if (selected && selected.id === deleteLotModal.id) {
        setSelected(null);
      }
      notify(data.message || 'Lot deleted permanently!');
      refreshData();
    })
    .catch(err => {
      console.error(err);
      notify('Failed to delete lot');
    });
  };

  // --- Item Operations ---
  const openNewItem = (presetType) => {
    setEditingItem(null);
    const type = presetType || 'packaging';
    const unit = (type === 'packaging' || type === 'finished_good') ? 'pcs' : 'kg';
    const prefix = type === 'packaging' ? 'PK' : type === 'finished_good' ? 'FG' : type === 'raw_material' ? 'RM' : 'BLK';
    const randomCode = `${prefix}-${Math.floor(1000 + Math.random() * 9000)}`;

    setItemForm({
      code: randomCode,
      name: '',
      type: type,
      unit: unit,
      cost_per_unit: '0.00',
      function: type === 'packaging' ? 'Secondary Packaging / Carton Box / Label' : '',
      inci_name: '',
      technical_name: '',
      cas_number: '',
      allergens: 'None',
    });
    setItemModal(true);
  };

  const openEditItem = (item) => {
    setEditingItem(item);
    setItemForm({
      code: item.code || '',
      name: item.name || '',
      type: item.type || 'packaging',
      unit: item.unit || 'pcs',
      cost_per_unit: item.cost_per_unit !== undefined ? String(item.cost_per_unit) : '0.00',
      function: item.function || '',
      inci_name: item.inci_name || '',
      technical_name: item.technical_name || '',
      cas_number: item.cas_number || '',
      allergens: item.allergens || 'None',
    });
    setItemModal(true);
  };

  const submitItemForm = (e) => {
    e.preventDefault();
    const url = editingItem ? `/api/v1/items/${editingItem.id}` : '/api/v1/items';
    const method = editingItem ? 'PUT' : 'POST';

    fetch(url, {
      method: method,
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(itemForm)
    })
    .then(res => res.json())
    .then(data => {
      if (data.errors) {
        const firstErr = Object.values(data.errors)[0][0];
        alert(firstErr);
        return;
      }
      if (data.error) {
        alert(data.error);
        return;
      }
      setItemModal(false);
      notify(data.message || 'Item saved successfully!');
      refreshData();
    })
    .catch(err => {
      console.error(err);
      notify('Failed to save item');
    });
  };

  const submitDeleteItem = () => {
    if (!deleteItemModal) return;
    fetch(`/api/v1/items/${deleteItemModal.id}`, {
      method: 'DELETE',
    })
    .then(res => res.json())
    .then(data => {
      if (data.error) {
        alert(data.error);
        return;
      }
      setDeleteItemModal(null);
      notify(data.message || 'Item deleted permanently!');
      refreshData();
    })
    .catch(err => {
      console.error(err);
      notify('Failed to delete item');
    });
  };

  const handleQuickReceive = (item) => {
    setReceiptForm({
      item_id: item.id,
      quantity: '',
      supplier_name: '',
      source_document: `GR-${new Date().getFullYear()}-${Math.floor(1000 + Math.random() * 9000)}`,
      expiry_date: '',
      storage_location: item.type === 'packaging' ? 'WH-P · Packaging Hall · P01' : 'WH-Q · Quarantine Bay · Q01',
    });
    setActiveView('lots');
    setReceiptModal(true);
  };

  // --- Filtering ---
  const filteredLots = lots.filter(l => {
    if (filter === 'Quarantine' && l.status !== 'Quarantine') return false;
    if (filter === 'Approved' && l.status !== 'Approved') return false;
    if (filter === 'FEFO' && !l.fefo) return false;
    if (filter === 'RM' && l.type !== 'raw_material') return false;
    if (filter === 'PK' && l.type !== 'packaging') return false;
    if (filter === 'FG' && l.type !== 'finished_good') return false;

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

  const filteredItems = itemsList.filter(it => {
    if (itemFilter === 'RM' && it.type !== 'raw_material') return false;
    if (itemFilter === 'PK' && it.type !== 'packaging') return false;
    if (itemFilter === 'FG' && it.type !== 'finished_good') return false;
    if (itemFilter === 'BULK' && it.type !== 'bulk_semi_finished') return false;

    if (itemSearch) {
      const s = itemSearch.toLowerCase();
      return it.code.toLowerCase().includes(s) ||
             it.name.toLowerCase().includes(s) ||
             (it.inci_name && it.inci_name.toLowerCase().includes(s)) ||
             (it.function && it.function.toLowerCase().includes(s));
    }
    return true;
  });

  const rawMaterialsCount = itemsList.filter(i => i.type === 'raw_material').length;
  const packagingCount = itemsList.filter(i => i.type === 'packaging').length;
  const finishedGoodsCount = itemsList.filter(i => i.type === 'finished_good').length;

  return (
    <div>
      {/* Top Header */}
      <div className="page-header">
        <div>
          <div className="eyebrow">WAREHOUSE MANAGEMENT SYSTEM (WMS) · LOT LEDGER & MASTER CATALOG</div>
          <h1>Inventory, Lots & Master Catalog</h1>
          <p>Complete control over Raw Materials, Packaging (Boxes, Bottles, Labels), and Finished Goods</p>
        </div>
        <div className="header-actions">
          {activeView === 'lots' ? (
            <Button icon="plus" onClick={() => setReceiptModal(true)}>New Goods Receipt</Button>
          ) : (
            <Button icon="plus" onClick={() => openNewItem('packaging')}>New Master Item</Button>
          )}
        </div>
      </div>

      {/* Styled Modern Sub-Nav Tabs Bar */}
      <div className="proc-tabs-bar">
        <div className="proc-nav-group">
          <button 
            className={`proc-nav-btn ${activeView === 'lots' ? 'active' : ''}`}
            onClick={() => setActiveView('lots')}
          >
            <Icon name="boxes" size={15}/>
            <span>Lots Inventory</span>
            <span className="proc-nav-count">{lots.length}</span>
          </button>
          <button 
            className={`proc-nav-btn ${activeView === 'catalog' ? 'active' : ''}`}
            onClick={() => setActiveView('catalog')}
          >
            <Icon name="file" size={15}/>
            <span>Master Items Catalog</span>
            <span className="proc-nav-count">{itemsList.length}</span>
          </button>
        </div>
        <div style={{ fontSize: '11px', color: '#64748b' }}>
          <span>Storage Zones: <strong>WH-A, WH-Q, WH-P, WH-R</strong> · GMP Segregation</span>
        </div>
      </div>

      {/* VIEW 1: LOTS INVENTORY */}
      {activeView === 'lots' && (
        <>
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
              <button className={`filter-btn ${filter === 'RM' ? 'active' : ''}`} onClick={() => setFilter('RM')}>🧪 Raw Materials ({lots.filter(l => l.type === 'raw_material').length})</button>
              <button className={`filter-btn ${filter === 'PK' ? 'active' : ''}`} onClick={() => setFilter('PK')}>📦 Packaging ({lots.filter(l => l.type === 'packaging').length})</button>
              <button className={`filter-btn ${filter === 'FG' ? 'active' : ''}`} onClick={() => setFilter('FG')}>✨ Finished Goods ({lots.filter(l => l.type === 'finished_good').length})</button>
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
              <div style={{ fontSize: '11px', color: '#64748b' }}>
                Showing <strong>{filteredLots.length}</strong> of {lots.length} active lots in MySQL
              </div>
            </div>

            {/* Data Table */}
            <div className="data-table-wrap">
              <table className="data-table">
                <thead>
                  <tr>
                    <th>Item Code</th>
                    <th>Product / Material</th>
                    <th>Category</th>
                    <th>Lot Number</th>
                    <th>Expiry Date</th>
                    <th>Storage Location</th>
                    <th>Status</th>
                    <th className="number">Available Qty / Weight</th>
                    <th>Unit</th>
                    <th style={{ textAlign: 'right' }}>Actions</th>
                  </tr>
                </thead>
                <tbody>
                  {filteredLots.map(lot => (
                    <tr key={lot.lot} onClick={() => openLot(lot)}>
                      <td><code>{lot.code}</code></td>
                      <td><strong>{lot.name}</strong></td>
                      <td>
                        <span style={{ 
                          fontSize: '9px', 
                          fontWeight: 700, 
                          padding: '2px 6px', 
                          borderRadius: '4px',
                          background: lot.type === 'packaging' ? '#fef3c7' : lot.type === 'finished_good' ? '#f5f3ff' : '#eff6ff',
                          color: lot.type === 'packaging' ? '#92400e' : lot.type === 'finished_good' ? '#6b21a8' : '#1e40af'
                        }}>
                          {lot.type === 'packaging' ? '📦 Packaging' : lot.type === 'finished_good' ? '✨ Finished Good' : '🧪 Raw Material'}
                        </span>
                      </td>
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
                            title="Edit quantity, weight, location or status"
                            onClick={() => openEditLot(lot)}
                          >
                            <Icon name="edit" size={13}/> Edit / Weight
                          </button>
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
                          <button 
                            className="btn btn-secondary" 
                            style={{ height: '26px', padding: '0 8px', fontSize: '9px', color: '#be123c' }}
                            title="Delete this lot permanently"
                            onClick={() => setDeleteLotModal(lot)}
                          >
                            <Icon name="trash" size={13}/> Del
                          </button>
                        </div>
                      </td>
                    </tr>
                  ))}
                  {filteredLots.length === 0 && (
                    <tr>
                      <td colSpan="10" style={{ textAlign: 'center', padding: '32px', color: '#94a3b8' }}>
                        No lots match the current filter or search criteria.
                      </td>
                    </tr>
                  )}
                </tbody>
              </table>
            </div>
          </div>
        </>
      )}

      {/* VIEW 2: MASTER ITEMS & CATALOG */}
      {activeView === 'catalog' && (
        <>
          {/* Summary Cards */}
          <div className="proc-kpi-grid">
            <div className="proc-kpi-card blue">
              <div className="proc-kpi-header">
                <span className="proc-kpi-title">TOTAL MASTER ITEMS</span>
                <div className="proc-kpi-icon" style={{ background: '#eff6ff', color: '#2563eb' }}>
                  <Icon name="file" size={16}/>
                </div>
              </div>
              <div className="proc-kpi-value">{itemsList.length}</div>
              <div className="proc-kpi-sub">
                <span>📋 Registered in factory master database</span>
              </div>
            </div>

            <div className="proc-kpi-card emerald">
              <div className="proc-kpi-header">
                <span className="proc-kpi-title">RAW MATERIALS (RM)</span>
                <div className="proc-kpi-icon" style={{ background: '#ecfdf5', color: '#059669' }}>
                  <Icon name="flask" size={16}/>
                </div>
              </div>
              <div className="proc-kpi-value">{rawMaterialsCount}</div>
              <div className="proc-kpi-sub">
                <span>🧪 Actives, humectants & natural oils</span>
              </div>
            </div>

            <div className="proc-kpi-card amber">
              <div className="proc-kpi-header">
                <span className="proc-kpi-title">PACKAGING & LABELS (PK)</span>
                <div className="proc-kpi-icon" style={{ background: '#fffbeb', color: '#d97706' }}>
                  <Icon name="boxes" size={16}/>
                </div>
              </div>
              <div className="proc-kpi-value">{packagingCount}</div>
              <div className="proc-kpi-sub">
                <span>📦 Boxes, bottles, jars & stickers</span>
              </div>
            </div>

            <div className="proc-kpi-card purple">
              <div className="proc-kpi-header">
                <span className="proc-kpi-title">FINISHED PRODUCTS (FG)</span>
                <div className="proc-kpi-icon" style={{ background: '#f5f3ff', color: '#7c3aed' }}>
                  <Icon name="sparkles" size={16}/>
                </div>
              </div>
              <div className="proc-kpi-value">{finishedGoodsCount}</div>
              <div className="proc-kpi-sub">
                <span>✨ Commercial released cosmetic items</span>
              </div>
            </div>
          </div>

          {/* Section Card */}
          <div className="section-card">
            <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: '14px', flexWrap: 'wrap', gap: '10px' }}>
              <div className="filter-tabs" style={{ marginBottom: 0 }}>
                <button className={`filter-btn ${itemFilter === 'All' ? 'active' : ''}`} onClick={() => setItemFilter('All')}>All Catalog ({itemsList.length})</button>
                <button className={`filter-btn ${itemFilter === 'RM' ? 'active' : ''}`} onClick={() => setItemFilter('RM')}>🧪 Raw Materials ({rawMaterialsCount})</button>
                <button className={`filter-btn ${itemFilter === 'PK' ? 'active' : ''}`} onClick={() => setItemFilter('PK')}>📦 Packaging & Boxes & Labels ({packagingCount})</button>
                <button className={`filter-btn ${itemFilter === 'FG' ? 'active' : ''}`} onClick={() => setItemFilter('FG')}>✨ Finished Goods ({finishedGoodsCount})</button>
                <button className={`filter-btn ${itemFilter === 'BULK' ? 'active' : ''}`} onClick={() => setItemFilter('BULK')}>⚗️ Bulk</button>
              </div>

              <div style={{ display: 'flex', gap: '8px' }}>
                <Button variant="secondary" icon="plus" onClick={() => openNewItem('packaging')}>+ Add Packaging (Box/Sticker)</Button>
                <Button variant="primary" icon="plus" onClick={() => openNewItem('raw_material')}>+ Add Raw Material / Product</Button>
              </div>
            </div>

            <div className="table-toolbar">
              <div className="search table-search">
                <Icon name="search" size={16}/>
                <input 
                  placeholder="Search catalog by code, item name, INCI, or function..." 
                  value={itemSearch} 
                  onChange={e => setItemSearch(e.target.value)}
                />
              </div>
              <div style={{ fontSize: '11px', color: '#64748b' }}>
                Showing <strong>{filteredItems.length}</strong> of {itemsList.length} items registered
              </div>
            </div>

            {/* Catalog Table */}
            <div className="data-table-wrap">
              <table className="data-table">
                <thead>
                  <tr>
                    <th>Code</th>
                    <th>Item / Product Name</th>
                    <th>Classification</th>
                    <th>Unit</th>
                    <th className="number">Cost / Unit</th>
                    <th className="number">Total On-Hand Stock</th>
                    <th className="number">Lots Count</th>
                    <th>Technical / Function</th>
                    <th style={{ textAlign: 'right' }}>Actions</th>
                  </tr>
                </thead>
                <tbody>
                  {filteredItems.map(item => (
                    <tr key={item.id}>
                      <td><code>{item.code}</code></td>
                      <td>
                        <strong>{item.name}</strong>
                        {item.inci_name && <small style={{ display: 'block', color: '#64748b' }}>INCI: {item.inci_name}</small>}
                      </td>
                      <td>
                        <span className={`badge-cat ${item.type === 'packaging' ? 'pk' : item.type === 'finished_good' ? 'fg' : item.type === 'bulk_semi_finished' ? 'bulk' : 'rm'}`}>
                          {item.type === 'packaging' ? '📦 Packaging' : item.type === 'finished_good' ? '✨ Finished Good' : item.type === 'bulk_semi_finished' ? '⚗️ Bulk' : '🧪 Raw Material'}
                        </span>
                      </td>
                      <td><code>{item.unit}</code></td>
                      <td className="number"><strong>€{parseFloat(item.cost_per_unit || 0).toFixed(2)}</strong></td>
                      <td className="number">
                        <strong style={{ color: item.total_stock > 0 ? '#047857' : '#94a3b8' }}>
                          {item.total_stock !== undefined ? Number(item.total_stock).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) : '0.00'} {item.unit}
                        </strong>
                      </td>
                      <td className="number">
                        <span style={{ background: '#f8fafc', padding: '2px 7px', borderRadius: '10px', border: '1px solid #e2e8f0', fontSize: '10px' }}>
                          {item.lots_count || 0}
                        </span>
                      </td>
                      <td>
                        <span style={{ fontSize: '11px', color: '#475569' }}>
                          {item.function || item.technical_name || '—'}
                        </span>
                      </td>
                      <td style={{ textAlign: 'right' }}>
                        <div style={{ display: 'flex', gap: '5px', justifyContent: 'flex-end' }}>
                          <button 
                            className="btn btn-secondary" 
                            style={{ height: '26px', padding: '0 8px', fontSize: '9px', color: '#047857' }}
                            title="View pricing, purchase orders and financial valuation in Procurement"
                            onClick={() => { if (navigate) navigate('procurement'); }}
                          >
                            <Icon name="scale" size={12}/> Price & POs
                          </button>
                          <button 
                            className="btn btn-secondary" 
                            style={{ height: '26px', padding: '0 8px', fontSize: '9px', color: '#4f46e5' }}
                            title="Receive new physical stock for this item"
                            onClick={() => handleQuickReceive(item)}
                          >
                            <Icon name="plus" size={13}/> Receive
                          </button>
                          <button 
                            className="btn btn-secondary" 
                            style={{ height: '26px', padding: '0 8px', fontSize: '9px' }}
                            title="Edit item code, name, unit or cost"
                            onClick={() => openEditItem(item)}
                          >
                            <Icon name="edit" size={13}/> Edit
                          </button>
                          <button 
                            className="btn btn-secondary" 
                            style={{ height: '26px', padding: '0 8px', fontSize: '9px', color: '#be123c' }}
                            title="Delete this master item"
                            onClick={() => setDeleteItemModal(item)}
                          >
                            <Icon name="trash" size={13}/> Del
                          </button>
                        </div>
                      </td>
                    </tr>
                  ))}
                  {filteredItems.length === 0 && (
                    <tr>
                      <td colSpan="9" style={{ textAlign: 'center', padding: '32px', color: '#94a3b8' }}>
                        No items found matching the selected filter or search.
                      </td>
                    </tr>
                  )}
                </tbody>
              </table>
            </div>
          </div>
        </>
      )}

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

            <div style={{ display: 'flex', gap: '8px', marginBottom: '14px' }}>
              <button 
                className="btn btn-secondary" 
                style={{ flex: 1, height: '30px', fontSize: '10px' }}
                onClick={() => openEditLot(selected)}
              >
                <Icon name="edit" size={13}/> Edit / Adjust Weight
              </button>
              <button 
                className="btn btn-secondary" 
                style={{ height: '30px', fontSize: '10px', color: '#be123c' }}
                onClick={() => setDeleteLotModal(selected)}
              >
                <Icon name="trash" size={13}/> Delete Lot
              </button>
            </div>

            <div className="drawer-section">
              <h3 style={{ fontSize: '11px', fontWeight: 700, marginBottom: '8px' }}>Physical Lot Master</h3>
              <dl>
                <div><dt>Item Code</dt><dd>{selected.code}</dd></div>
                <div><dt>Current Quantity</dt><dd>{selected.qty} {selected.unit}</dd></div>
                <div><dt>Current Location</dt><dd>{selected.location}</dd></div>
                <div><dt>Expiry Date</dt><dd>{selected.expiry}</dd></div>
                <div>
                  <dt>Supplier / Origin</dt>
                  <dd style={{ display: 'flex', alignItems: 'center', gap: '6px' }}>
                    <span>{selected.supplier || 'Factory Generated'}</span>
                    {selected.supplier && navigate && (
                      <button 
                        type="button" 
                        onClick={() => { setSelected(null); navigate('procurement'); }}
                        style={{ border: 'none', background: '#e0e7ff', color: '#4338ca', padding: '2px 6px', borderRadius: '4px', fontSize: '9px', fontWeight: 700, cursor: 'pointer' }}
                        title="View Supplier Profile & Pricing in Procurement"
                      >
                        Supplier Profile ↗
                      </button>
                    )}
                  </dd>
                </div>
                <div>
                  <dt>Source PO / GRN</dt>
                  <dd style={{ display: 'flex', alignItems: 'center', gap: '6px' }}>
                    <span>{selected.sourceDocument}</span>
                    {selected.sourceDocument && selected.sourceDocument.startsWith('INV-') && navigate && (
                      <button 
                        type="button" 
                        onClick={() => { setSelected(null); navigate('procurement'); }}
                        style={{ border: 'none', background: '#e0e7ff', color: '#4338ca', padding: '2px 6px', borderRadius: '4px', fontSize: '9px', fontWeight: 700, cursor: 'pointer' }}
                        title="View Purchase Invoice & Audit Slip"
                      >
                        View PO ↗
                      </button>
                    )}
                  </dd>
                </div>
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
              <div>
                <h2>New Goods Receipt · Receive Stock</h2>
                <p style={{ fontSize: '11px', color: '#64748b' }}>إذن استلام بضاعة للمخزن وتوليد تشغيلة جديدة</p>
              </div>
              <button className="icon-button" onClick={() => setReceiptModal(false)}><Icon name="close"/></button>
            </div>
            <form onSubmit={submitReceipt}>
              <div className="modal-body">
                <div className="form-field">
                  <label>Material / Packaging / Product Master Item *</label>
                  <select 
                    value={receiptForm.item_id} 
                    onChange={e => setReceiptForm({ ...receiptForm, item_id: e.target.value })}
                    required
                  >
                    {itemsList.map(it => (
                      <option key={it.id} value={it.id}>
                        {it.code} — {it.name} [{it.type}] ({it.unit})
                      </option>
                    ))}
                  </select>
                </div>

                <div className="form-grid-2">
                  <div className="form-field">
                    <label>Received Quantity / Weight (الكمية أو الوزن) *</label>
                    <input 
                      type="number" 
                      step="0.001" 
                      placeholder="e.g. 500" 
                      value={receiptForm.quantity} 
                      onChange={e => setReceiptForm({ ...receiptForm, quantity: e.target.value })}
                      required
                      style={{ fontWeight: 700 }}
                    />
                  </div>
                  <div className="form-field">
                    <label>Expiration Date (تاريخ الصلاحية)</label>
                    <input 
                      type="date" 
                      value={receiptForm.expiry_date} 
                      onChange={e => setReceiptForm({ ...receiptForm, expiry_date: e.target.value })}
                    />
                  </div>
                </div>

                <div className="form-field">
                  <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: '4px' }}>
                    <label style={{ margin: 0 }}>Approved Factory Supplier *</label>
                    {navigate && (
                      <button 
                        type="button" 
                        onClick={() => { setReceiptModal(false); navigate('procurement'); }}
                        style={{ border: 'none', background: 'none', color: '#4f46e5', fontSize: '10px', cursor: 'pointer', textDecoration: 'underline' }}
                      >
                        + Manage Suppliers in Procurement ↗
                      </button>
                    )}
                  </div>
                  <select 
                    value={receiptForm.supplier_name} 
                    onChange={e => {
                      const val = e.target.value;
                      setReceiptForm({ ...receiptForm, supplier_name: val });
                      const matchingInv = invoicesList.find(i => i.supplier_name === val && i.status !== 'Received');
                      if (matchingInv) {
                        setReceiptForm(prev => ({ ...prev, supplier_name: val, source_document: matchingInv.invoice_number }));
                      }
                    }}
                    required
                  >
                    <option value="">-- Select Factory Supplier --</option>
                    {suppliersList.map(s => (
                      <option key={s.id} value={s.name}>
                        {s.name} ({s.code}) {s.materials_supplied ? `· Supplies: ${s.materials_supplied.substring(0, 32)}...` : ''}
                      </option>
                    ))}
                    <option value="Custom">Custom / Other Supplier...</option>
                  </select>
                </div>

                {receiptForm.supplier_name === 'Custom' && (
                  <div className="form-field">
                    <label>Enter Custom Supplier Name *</label>
                    <input 
                      placeholder="e.g. Lonza, Givaudan, BASF, Bormioli Luigi" 
                      value={receiptForm.custom_name || ''} 
                      onChange={e => setReceiptForm({ ...receiptForm, custom_name: e.target.value, supplier_name: e.target.value })}
                      required
                    />
                  </div>
                )}

                <div className="form-field">
                  <label>Source Document (Purchase Order / Invoice #) *</label>
                  <div style={{ display: 'flex', gap: '8px' }}>
                    <input 
                      placeholder="e.g. INV-2026-0881 / PO-2026-0041" 
                      value={receiptForm.source_document} 
                      onChange={e => setReceiptForm({ ...receiptForm, source_document: e.target.value })}
                      required
                      style={{ flex: 1 }}
                    />
                    {invoicesList.length > 0 && (
                      <select 
                        style={{ width: '160px', fontSize: '11px' }}
                        onChange={e => {
                          if (e.target.value) setReceiptForm({ ...receiptForm, source_document: e.target.value });
                        }}
                        value=""
                      >
                        <option value="">Pick From POs...</option>
                        {invoicesList.map(inv => (
                          <option key={inv.id} value={inv.invoice_number}>
                            {inv.invoice_number} ({inv.supplier_name.split(' ')[0]})
                          </option>
                        ))}
                      </select>
                    )}
                  </div>
                </div>

                <div className="form-field">
                  <label>Storage Bay / Rack (مكان التخزين الأولي)</label>
                  <input 
                    value={receiptForm.storage_location} 
                    onChange={e => setReceiptForm({ ...receiptForm, storage_location: e.target.value })}
                    placeholder="WH-Q · Quarantine Bay · Q01"
                  />
                </div>

                <div style={{ background: '#fef3c7', border: '1px solid #fde68a', padding: '10px 12px', borderRadius: '6px', fontSize: '10px', color: '#92400e' }}>
                  ⚠ <strong>ISO 22716 Mandatory Hold:</strong> All newly received lots automatically enter Quarantine in Zone WH-Q until QC inspection release.
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

      {/* Modal: Edit Lot & Weight Adjustment */}
      {editLotModal && (
        <div className="modal-overlay" onClick={() => setEditLotModal(null)}>
          <div className="modal" style={{ maxWidth: '520px' }} onClick={e => e.stopPropagation()}>
            <div className="modal-header">
              <div>
                <h2>Edit Lot & Adjust Weight / Quantity</h2>
                <p style={{ fontSize: '11px', color: '#64748b', marginTop: '2px' }}>
                  تعديل بيانات التشغيلة والوزن/الكمية والحالة · Lot: <strong>{editLotModal.lot}</strong>
                </p>
              </div>
              <button className="icon-button" onClick={() => setEditLotModal(null)}><Icon name="close"/></button>
            </div>
            <form onSubmit={submitEditLot}>
              <div className="modal-body">
                <div style={{ background: '#f8fafc', padding: '10px 14px', borderRadius: '8px', border: '1px solid #e2e8f0', marginBottom: '14px', fontSize: '11px' }}>
                  <div style={{ display: 'flex', justifyContent: 'space-between', marginBottom: '4px' }}>
                    <span style={{ color: '#64748b' }}>Item Code / Name:</span>
                    <strong>{editLotModal.code} — {editLotModal.name}</strong>
                  </div>
                  <div style={{ display: 'flex', justifyContent: 'space-between' }}>
                    <span style={{ color: '#64748b' }}>Current Registered Qty:</span>
                    <strong>{editLotModal.qty} {editLotModal.unit}</strong>
                  </div>
                </div>

                <div className="form-grid-2">
                  <div className="form-field">
                    <label>New Quantity / Weight (الوزن أو الكمية الجديدة) *</label>
                    <input 
                      type="number" 
                      step="0.001" 
                      min="0"
                      value={editLotForm.quantity} 
                      onChange={e => setEditLotForm({ ...editLotForm, quantity: e.target.value })}
                      required
                      style={{ fontWeight: 700, fontSize: '14px' }}
                    />
                  </div>
                  <div className="form-field">
                    <label>Unit of Measure (وحدة القياس)</label>
                    <input value={editLotModal.unit} readOnly style={{ background: '#f8fafc', color: '#64748b' }}/>
                  </div>
                </div>

                <div className="form-grid-2">
                  <div className="form-field">
                    <label>Status (حالة التشغيلة)</label>
                    <select 
                      value={editLotForm.status} 
                      onChange={e => setEditLotForm({ ...editLotForm, status: e.target.value })}
                    >
                      <option value="Approved">Approved (معتمد للإنتاج/البيع)</option>
                      <option value="Quarantine">Quarantine (حجر صحي QC)</option>
                      <option value="Rejected">Rejected (مرفوض)</option>
                    </select>
                  </div>
                  <div className="form-field">
                    <label>Expiry Date (تاريخ الصلاحية)</label>
                    <input 
                      type="date" 
                      value={editLotForm.expiry_date || ''} 
                      onChange={e => setEditLotForm({ ...editLotForm, expiry_date: e.target.value })}
                    />
                  </div>
                </div>

                <div className="form-field">
                  <label>Storage Location (موقع التخزين والرف)</label>
                  <input 
                    value={editLotForm.storage_location} 
                    onChange={e => setEditLotForm({ ...editLotForm, storage_location: e.target.value })}
                    placeholder="e.g. WH-A · A03 · S12"
                  />
                </div>

                <div className="form-grid-2">
                  <div className="form-field">
                    <label>Supplier / Manufacturer (المورد)</label>
                    <input 
                      value={editLotForm.supplier_name} 
                      onChange={e => setEditLotForm({ ...editLotForm, supplier_name: e.target.value })}
                      placeholder="e.g. Lonza, BASF, Vetro Italia"
                    />
                  </div>
                  <div className="form-field">
                    <label>Source Document (مستند الإذن)</label>
                    <input 
                      value={editLotForm.source_document} 
                      onChange={e => setEditLotForm({ ...editLotForm, source_document: e.target.value })}
                      placeholder="e.g. PO-2026-0041"
                    />
                  </div>
                </div>

                <div className="form-field">
                  <label>Adjustment Reason / Audit Note (سبب التعديل / ملاحظات الجرد) *</label>
                  <input 
                    value={editLotForm.reason} 
                    onChange={e => setEditLotForm({ ...editLotForm, reason: e.target.value })}
                    placeholder="مثال: تصحيح وزن فعلي بالميزان / كسر عبوات / تسوية جردية"
                    required
                  />
                </div>
              </div>
              <div className="modal-footer">
                <Button variant="secondary" onClick={() => setEditLotModal(null)}>Cancel</Button>
                <Button variant="primary" type="submit">Save Changes & Log Ledger</Button>
              </div>
            </form>
          </div>
        </div>
      )}

      {/* Modal: Delete Lot Confirmation */}
      {deleteLotModal && (
        <div className="modal-overlay" onClick={() => setDeleteLotModal(null)}>
          <div className="modal" style={{ maxWidth: '440px', borderTop: '4px solid #ef4444' }} onClick={e => e.stopPropagation()}>
            <div className="modal-header">
              <div>
                <h2 style={{ color: '#be123c' }}>Delete Lot Permanently</h2>
                <p style={{ fontSize: '11px', color: '#64748b' }}>حذف التشغيلة نهائياً من قاعدة البيانات</p>
              </div>
              <button className="icon-button" onClick={() => setDeleteLotModal(null)}><Icon name="close"/></button>
            </div>
            <div className="modal-body">
              <p style={{ fontSize: '12px', color: '#334155', lineHeight: '1.6' }}>
                Are you sure you want to completely delete Lot <strong style={{ color: '#0f172a' }}>{deleteLotModal.lot}</strong> (<span style={{ color: '#64748b' }}>{deleteLotModal.name}</span>)?
              </p>
              <div style={{ background: '#fef2f2', border: '1px solid #fecaca', padding: '10px 14px', borderRadius: '8px', marginTop: '12px', fontSize: '11px', color: '#991b1b' }}>
                ⚠ <strong>Warning:</strong> This will erase the remaining <strong>{deleteLotModal.qty} {deleteLotModal.unit}</strong> and all linked immutable movement ledger history for this lot.
              </div>
            </div>
            <div className="modal-footer">
              <Button variant="secondary" onClick={() => setDeleteLotModal(null)}>Cancel</Button>
              <button 
                className="btn btn-danger" 
                onClick={submitDeleteLot}
                style={{ height: '34px', padding: '0 14px', fontWeight: 700 }}
              >
                <Icon name="trash" size={15}/>
                <span>Delete Lot Completely</span>
              </button>
            </div>
          </div>
        </div>
      )}

      {/* Modal: Create / Edit Master Item */}
      {itemModal && (
        <div className="modal-overlay" onClick={() => setItemModal(false)}>
          <div className="modal" style={{ maxWidth: '580px' }} onClick={e => e.stopPropagation()}>
            <div className="modal-header">
              <div>
                <h2>{editingItem ? `Edit Item: ${editingItem.code}` : 'Create New Master Item / Product'}</h2>
                <p style={{ fontSize: '11px', color: '#64748b', marginTop: '2px' }}>
                  {editingItem ? 'تعديل بيانات الصنف أو المنتج أو مادة التعبئة' : 'إضافة صنف جديد (مادة خام، علبة كرتون، ستيكر، أو منتج نهائي)'}
                </p>
              </div>
              <button className="icon-button" onClick={() => setItemModal(false)}><Icon name="close"/></button>
            </div>
            <form onSubmit={submitItemForm}>
              <div className="modal-body">
                <div className="form-grid-2">
                  <div className="form-field">
                    <label>Item Code (كود الصنف الفريد) *</label>
                    <input 
                      value={itemForm.code} 
                      onChange={e => setItemForm({ ...itemForm, code: e.target.value })}
                      placeholder="e.g. PK-00520, RM-00210, FG-250430"
                      required
                    />
                  </div>
                  <div className="form-field">
                    <label>Classification / Type (نوع الصنف) *</label>
                    <select 
                      value={itemForm.type} 
                      onChange={e => {
                        const newType = e.target.value;
                        const newUnit = (newType === 'packaging' || newType === 'finished_good') ? 'pcs' : 'kg';
                        setItemForm({ ...itemForm, type: newType, unit: newUnit });
                      }}
                      required
                    >
                      <option value="packaging">📦 Packaging (علبة، كرتونة، برطمان، زجاجة، ستيكر/ملصق)</option>
                      <option value="raw_material">🧪 Raw Material (مادة خام كيميائية أو طبيعية)</option>
                      <option value="finished_good">✨ Finished Good (منتج نهائي تام الصنع)</option>
                      <option value="bulk_semi_finished">⚗️ Bulk Semi-Finished (مستحضر نصف مصنّع)</option>
                    </select>
                  </div>
                </div>

                <div className="form-field">
                  <label>Item Name (اسم الصنف بالعربية أو الإنجليزية) *</label>
                  <input 
                    value={itemForm.name} 
                    onChange={e => setItemForm({ ...itemForm, name: e.target.value })}
                    placeholder="e.g. Folding Outer Carton Box 50ml / علبة كرتون سيروم 30 مل"
                    required
                  />
                </div>

                <div className="form-grid-2">
                  <div className="form-field">
                    <label>Unit of Measure (وحدة القياس) *</label>
                    <select 
                      value={itemForm.unit} 
                      onChange={e => setItemForm({ ...itemForm, unit: e.target.value })}
                      required
                    >
                      <option value="pcs">pcs (قطعة / علبة / ستيكر)</option>
                      <option value="kg">kg (كيلوجرام)</option>
                      <option value="g">g (جرام)</option>
                      <option value="L">L (لتر)</option>
                      <option value="ml">ml (ملليلتر)</option>
                    </select>
                  </div>
                  <div className="form-field">
                    <label>Cost per Unit (التكلفة للوحدة)</label>
                    <input 
                      type="number" 
                      step="0.01" 
                      min="0"
                      value={itemForm.cost_per_unit} 
                      onChange={e => setItemForm({ ...itemForm, cost_per_unit: e.target.value })}
                      placeholder="e.g. 0.45"
                    />
                  </div>
                </div>

                <div className="form-field">
                  <label>Function / Role (الوظيفة أو الاستخدام)</label>
                  <input 
                    value={itemForm.function} 
                    onChange={e => setItemForm({ ...itemForm, function: e.target.value })}
                    placeholder="e.g. Secondary Packaging, Primary Bottle, Humectant, Finished Cream"
                  />
                </div>

                <div className="form-grid-2">
                  <div className="form-field">
                    <label>INCI / Scientific Name (الاسم العلمي / INCI)</label>
                    <input 
                      value={itemForm.inci_name} 
                      onChange={e => setItemForm({ ...itemForm, inci_name: e.target.value })}
                      placeholder="e.g. Aqua, Glycerin, Niacinamide (للمواد الخام)"
                    />
                  </div>
                  <div className="form-field">
                    <label>Technical Specification (المواصفة الفنية)</label>
                    <input 
                      value={itemForm.technical_name} 
                      onChange={e => setItemForm({ ...itemForm, technical_name: e.target.value })}
                      placeholder="e.g. 350gsm Matte Lamination Foil Embossed"
                    />
                  </div>
                </div>
              </div>
              <div className="modal-footer">
                <Button variant="secondary" onClick={() => setItemModal(false)}>Cancel</Button>
                <Button variant="primary" type="submit">{editingItem ? 'Save Item Changes' : 'Create Master Item'}</Button>
              </div>
            </form>
          </div>
        </div>
      )}

      {/* Modal: Delete Item Confirmation */}
      {deleteItemModal && (
        <div className="modal-overlay" onClick={() => setDeleteItemModal(null)}>
          <div className="modal" style={{ maxWidth: '440px', borderTop: '4px solid #ef4444' }} onClick={e => e.stopPropagation()}>
            <div className="modal-header">
              <div>
                <h2 style={{ color: '#be123c' }}>Delete Master Item</h2>
                <p style={{ fontSize: '11px', color: '#64748b' }}>حذف الصنف نهائياً من السيستم</p>
              </div>
              <button className="icon-button" onClick={() => setDeleteItemModal(null)}><Icon name="close"/></button>
            </div>
            <div className="modal-body">
              <p style={{ fontSize: '12px', color: '#334155', lineHeight: '1.6' }}>
                Are you sure you want to permanently delete Item <strong style={{ color: '#0f172a' }}>{deleteItemModal.code}</strong> — <strong style={{ color: '#0f172a' }}>{deleteItemModal.name}</strong>?
              </p>
              <div style={{ background: '#fef2f2', border: '1px solid #fecaca', padding: '10px 14px', borderRadius: '8px', marginTop: '12px', fontSize: '11px', color: '#991b1b' }}>
                ⚠ <strong>Critical:</strong> Deleting this item will also remove all associated lots ({deleteItemModal.lots_count || 0} active lots) and formula lines.
              </div>
            </div>
            <div className="modal-footer">
              <Button variant="secondary" onClick={() => setDeleteItemModal(null)}>Cancel</Button>
              <button 
                className="btn btn-danger" 
                onClick={submitDeleteItem}
                style={{ height: '34px', padding: '0 14px', fontWeight: 700 }}
              >
                <Icon name="trash" size={15}/>
                <span>Delete Item Completely</span>
              </button>
            </div>
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
