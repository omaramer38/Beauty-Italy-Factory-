@verbatim
// Module: Procurement, Suppliers, Invoices & Material Prices
function Procurement({ notify, onReceiveToWarehouse, navigate, initialTarget }) {
  const [subTab, setSubTab] = useState('invoices'); // 'invoices' | 'suppliers' | 'pricing'
  const [invoices, setInvoices] = useState([]);
  const [suppliers, setSuppliers] = useState([]);
  const [itemsList, setItemsList] = useState([]);
  const [valuationData, setValuationData] = useState(null);
  const [receivingInvoice, setReceivingInvoice] = useState(false);
  
  // Search & Filter
  const [invFilter, setInvFilter] = useState('All');
  const [invSearch, setInvSearch] = useState('');
  const [supSearch, setSupSearch] = useState('');
  const [priceSearch, setPriceSearch] = useState('');

  // Modals
  const [viewInvoice, setViewInvoice] = useState(null);
  const [invoiceModal, setInvoiceModal] = useState(false);
  const [supplierModal, setSupplierModal] = useState(false);
  const [editingSupplier, setEditingSupplier] = useState(null);
  const [deleteSupplierModal, setDeleteSupplierModal] = useState(null);
  const [deleteInvoiceModal, setDeleteInvoiceModal] = useState(null);
  const [editPriceModal, setEditPriceModal] = useState(null);
  const [newPriceValue, setNewPriceValue] = useState('');

  // New Invoice Form state
  const [invoiceForm, setInvoiceForm] = useState({
    supplier_id: '',
    invoice_number: '',
    invoice_date: new Date().toISOString().split('T')[0],
    due_date: '',
    status: 'Approved',
    currency: '€',
    tax_percent: '14.00',
    notes: '',
    items: [
      { item_id: '', quantity: 100, unit_price: 0 }
    ]
  });

  // Supplier Form state
  const [supplierForm, setSupplierForm] = useState({
    code: '',
    name: '',
    contact_person: '',
    phone: '',
    email: '',
    address: '',
    materials_supplied: '',
    payment_terms: 'Net 30 Days',
    tax_number: '',
    status: 'Active',
    notes: '',
  });

  const refreshData = () => {
    fetch('/api/v1/invoices')
      .then(res => res.json())
      .then(data => setInvoices(data))
      .catch(err => console.error(err));

    fetch('/api/v1/suppliers')
      .then(res => res.json())
      .then(data => {
        setSuppliers(data);
        if (data.length > 0 && !invoiceForm.supplier_id) {
          setInvoiceForm(f => ({ ...f, supplier_id: data[0].id }));
        }
      })
      .catch(err => console.error(err));

    fetch('/api/v1/items')
      .then(res => res.json())
      .then(data => {
        setItemsList(data);
        if (data.length > 0 && invoiceForm.items[0] && !invoiceForm.items[0].item_id) {
          setInvoiceForm(f => ({
            ...f,
            items: [{ item_id: data[0].id, quantity: 100, unit_price: data[0].cost_per_unit || 0 }]
          }));
        }
      })
      .catch(err => console.error(err));

    fetch('/api/v1/procurement/valuation')
      .then(res => res.json())
      .then(data => setValuationData(data))
      .catch(err => console.error(err));
  };

  useEffect(() => {
    refreshData();
  }, []);

  useEffect(() => {
    if (initialTarget) {
      const tgt = String(initialTarget).trim();
      if (tgt.startsWith('INV-') || tgt.startsWith('PO-')) {
        setSubTab('invoices');
        setInvSearch(tgt);
      } else if (tgt.startsWith('RM-') || tgt.startsWith('PK-') || tgt.startsWith('FG-') || tgt.startsWith('MAT-')) {
        setSubTab('pricing');
        setPriceSearch(tgt);
      } else {
        setSubTab('suppliers');
        setSupSearch(tgt);
      }
    }
  }, [initialTarget]);

  // Direct Warehouse Receiving from Purchase Invoice
  const handleReceiveInvoice = async (invoiceId) => {
    if (!confirm('Receive this invoice into warehouse? This will generate quarantine inventory lots in the warehouse for all line items.')) return;
    setReceivingInvoice(true);
    try {
      const res = await fetch(`/api/v1/invoices/${invoiceId}/receive`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
        body: JSON.stringify({ performed_by: 'Warehouse Receiving Specialist' })
      });
      const data = await res.json();
      if (res.ok && data.success) {
        notify(`✓ ${data.message}`);
        setViewInvoice(prev => prev ? { ...prev, status: 'Received' } : null);
        refreshData();
      } else {
        alert(data.error || 'Failed to receive invoice');
      }
    } catch (e) {
      console.error(e);
      alert('Network or server error while receiving invoice.');
    } finally {
      setReceivingInvoice(false);
    }
  };

  // --- Invoice Operations ---
  const handleAddInvoiceLine = () => {
    const defaultItem = itemsList[0] || { id: '', cost_per_unit: 0 };
    setInvoiceForm(f => ({
      ...f,
      items: [...f.items, { item_id: defaultItem.id, quantity: 50, unit_price: defaultItem.cost_per_unit || 0 }]
    }));
  };

  const handleRemoveInvoiceLine = (idx) => {
    if (invoiceForm.items.length <= 1) return;
    setInvoiceForm(f => ({
      ...f,
      items: f.items.filter((_, i) => i !== idx)
    }));
  };

  const handleInvoiceItemChange = (idx, field, value) => {
    const newItems = [...invoiceForm.items];
    newItems[idx][field] = value;
    if (field === 'item_id') {
      const selected = itemsList.find(it => String(it.id) === String(value));
      if (selected) {
        newItems[idx].unit_price = selected.cost_per_unit || 0;
      }
    }
    setInvoiceForm(f => ({ ...f, items: newItems }));
  };

  const calculateInvoiceTotals = () => {
    let subtotal = 0;
    invoiceForm.items.forEach(it => {
      subtotal += (parseFloat(it.quantity || 0) * parseFloat(it.unit_price || 0));
    });
    const taxRate = parseFloat(invoiceForm.tax_percent || 0);
    const tax = (subtotal * taxRate) / 100;
    const total = subtotal + tax;
    return { subtotal, tax, total };
  };

  const submitInvoice = (e) => {
    e.preventDefault();
    fetch('/api/v1/invoices', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(invoiceForm)
    })
    .then(res => res.json())
    .then(data => {
      if (data.errors || data.error) {
        alert(data.error || Object.values(data.errors)[0][0]);
        return;
      }
      setInvoiceModal(false);
      notify(data.message || 'Invoice generated successfully!');
      refreshData();
    })
    .catch(err => {
      console.error(err);
      notify('Failed to generate invoice');
    });
  };

  const submitDeleteInvoice = () => {
    if (!deleteInvoiceModal) return;
    fetch(`/api/v1/invoices/${deleteInvoiceModal.id}`, { method: 'DELETE' })
      .then(res => res.json())
      .then(data => {
        setDeleteInvoiceModal(null);
        notify(data.message || 'Invoice deleted');
        refreshData();
      })
      .catch(err => console.error(err));
  };

  // --- Supplier Operations ---
  const openNewSupplier = () => {
    setEditingSupplier(null);
    setSupplierForm({
      code: `SUP-${Math.floor(100 + Math.random() * 900)}`,
      name: '',
      contact_person: '',
      phone: '',
      email: '',
      address: '',
      materials_supplied: '',
      payment_terms: 'Net 30 Days',
      tax_number: '',
      status: 'Active',
      notes: '',
    });
    setSupplierModal(true);
  };

  const openEditSupplier = (sup) => {
    setEditingSupplier(sup);
    setSupplierForm({
      code: sup.code || '',
      name: sup.name || '',
      contact_person: sup.contact_person || '',
      phone: sup.phone || '',
      email: sup.email || '',
      address: sup.address || '',
      materials_supplied: sup.materials_supplied || '',
      payment_terms: sup.payment_terms || 'Net 30 Days',
      tax_number: sup.tax_number || '',
      status: sup.status || 'Active',
      notes: sup.notes || '',
    });
    setSupplierModal(true);
  };

  const submitSupplier = (e) => {
    e.preventDefault();
    const url = editingSupplier ? `/api/v1/suppliers/${editingSupplier.id}` : '/api/v1/suppliers';
    const method = editingSupplier ? 'PUT' : 'POST';

    fetch(url, {
      method: method,
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(supplierForm)
    })
    .then(res => res.json())
    .then(data => {
      if (data.errors || data.error) {
        alert(data.error || Object.values(data.errors)[0][0]);
        return;
      }
      setSupplierModal(false);
      notify(data.message || 'Supplier saved successfully!');
      refreshData();
    })
    .catch(err => {
      console.error(err);
      notify('Failed to save supplier');
    });
  };

  const submitDeleteSupplier = () => {
    if (!deleteSupplierModal) return;
    fetch(`/api/v1/suppliers/${deleteSupplierModal.id}`, { method: 'DELETE' })
      .then(res => res.json())
      .then(data => {
        setDeleteSupplierModal(null);
        notify(data.message || 'Supplier deleted');
        refreshData();
      })
      .catch(err => console.error(err));
  };

  // --- Price Update Operation ---
  const submitUpdatePrice = (e) => {
    e.preventDefault();
    if (!editPriceModal) return;
    fetch(`/api/v1/items/${editPriceModal.id}`, {
      method: 'PUT',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        code: editPriceModal.code,
        name: editPriceModal.name,
        type: editPriceModal.type,
        unit: editPriceModal.unit,
        cost_per_unit: parseFloat(newPriceValue || 0),
        function: editPriceModal.function,
        inci_name: editPriceModal.inci_name,
        technical_name: editPriceModal.technical_name,
      })
    })
    .then(res => res.json())
    .then(data => {
      setEditPriceModal(null);
      notify(`Price for ${editPriceModal.code} updated to €${parseFloat(newPriceValue).toFixed(2)}`);
      refreshData();
    })
    .catch(err => console.error(err));
  };

  // --- Filters ---
  const filteredInvoices = invoices.filter(inv => {
    if (invFilter === 'Approved' && inv.status !== 'Approved') return false;
    if (invFilter === 'Received' && inv.status !== 'Received') return false;
    if (invFilter === 'Paid' && inv.status !== 'Paid') return false;

    if (invSearch) {
      const s = invSearch.toLowerCase();
      return inv.invoice_number.toLowerCase().includes(s) ||
             inv.supplier_name.toLowerCase().includes(s);
    }
    return true;
  });

  const filteredSuppliers = suppliers.filter(sup => {
    if (supSearch) {
      const s = supSearch.toLowerCase();
      return sup.name.toLowerCase().includes(s) ||
             sup.code.toLowerCase().includes(s) ||
             (sup.materials_supplied && sup.materials_supplied.toLowerCase().includes(s)) ||
             (sup.contact_person && sup.contact_person.toLowerCase().includes(s));
    }
    return true;
  });

  const filteredPrices = (valuationData ? valuationData.items : itemsList).filter(it => {
    if (priceSearch) {
      const s = priceSearch.toLowerCase();
      return it.name.toLowerCase().includes(s) ||
             it.code.toLowerCase().includes(s) ||
             (it.type && it.type.toLowerCase().includes(s));
    }
    return true;
  });

  return (
    <div>
      {/* Page Header */}
      <div className="page-header">
        <div>
          <div className="eyebrow">PROCUREMENT & PURCHASING · ISO 22716 COMPLIANT ERP</div>
          <h1>Procurement, Suppliers & Invoices</h1>
          <p>Full control over suppliers, materials supplied, purchase orders, printable invoices & material costs</p>
        </div>
        <div className="header-actions">
          {subTab === 'invoices' && (
            <Button variant="primary" icon="plus" onClick={() => setInvoiceModal(true)}>+ New Purchase Invoice / PO</Button>
          )}
          {subTab === 'suppliers' && (
            <Button variant="primary" icon="plus" onClick={openNewSupplier}>+ Register New Supplier</Button>
          )}
        </div>
      </div>

      {/* Styled Modern Sub-Nav Tabs Bar */}
      <div className="proc-tabs-bar">
        <div className="proc-nav-group">
          <button 
            className={`proc-nav-btn ${subTab === 'invoices' ? 'active' : ''}`}
            onClick={() => setSubTab('invoices')}
          >
            <Icon name="file" size={15}/>
            <span>Purchase Invoices & Orders</span>
            <span className="proc-nav-count">{invoices.length}</span>
          </button>
          <button 
            className={`proc-nav-btn ${subTab === 'suppliers' ? 'active' : ''}`}
            onClick={() => setSubTab('suppliers')}
          >
            <Icon name="user" size={15}/>
            <span>Suppliers Directory</span>
            <span className="proc-nav-count">{suppliers.length}</span>
          </button>
          <button 
            className={`proc-nav-btn ${subTab === 'pricing' ? 'active' : ''}`}
            onClick={() => setSubTab('pricing')}
          >
            <Icon name="scale" size={15}/>
            <span>Material Prices & Valuation</span>
          </button>
        </div>

        <div style={{ fontSize: '11px', color: '#64748b', display: 'flex', alignItems: 'center', gap: '6px' }}>
          <span className="warehouse-dot"/>
          <span>Active Currency: <strong>EUR (€)</strong> · Tax Rate: <strong>14% VAT</strong></span>
        </div>
      </div>

      {/* SUBTAB 1: INVOICES & PURCHASE ORDERS */}
      {subTab === 'invoices' && (
        <div className="section-card" style={{ overflow: 'hidden' }}>
          <div style={{ 
            padding: '12px 18px', 
            borderBottom: '1px solid var(--border)', 
            display: 'flex', 
            justifyContent: 'space-between', 
            alignItems: 'center', 
            flexWrap: 'wrap', 
            gap: '12px',
            background: '#ffffff'
          }}>
            <div className="filter-tabs" style={{ marginBottom: 0, padding: '4px 6px' }}>
              <button className={`filter-btn ${invFilter === 'All' ? 'active' : ''}`} onClick={() => setInvFilter('All')}>All Invoices ({invoices.length})</button>
              <button className={`filter-btn ${invFilter === 'Approved' ? 'active' : ''}`} onClick={() => setInvFilter('Approved')}>Approved ({invoices.filter(i => i.status === 'Approved').length})</button>
              <button className={`filter-btn ${invFilter === 'Received' ? 'active' : ''}`} onClick={() => setInvFilter('Received')}>Received to Warehouse ({invoices.filter(i => i.status === 'Received').length})</button>
              <button className={`filter-btn ${invFilter === 'Paid' ? 'active' : ''}`} onClick={() => setInvFilter('Paid')}>Paid ({invoices.filter(i => i.status === 'Paid').length})</button>
            </div>
            <Button variant="secondary" icon="plus" onClick={() => setInvoiceModal(true)}>Create Order</Button>
          </div>

          <div className="table-toolbar" style={{ background: '#f8fafc', padding: '12px 20px', borderBottom: '1px solid var(--border)' }}>
            <div className="search table-search" style={{ width: '380px' }}>
              <Icon name="search" size={16}/>
              <input 
                placeholder="Search invoices by PO/INV number or supplier name..." 
                value={invSearch} 
                onChange={e => setInvSearch(e.target.value)}
              />
            </div>
            <div style={{ fontSize: '11px', color: '#64748b' }}>
              Showing <strong>{filteredInvoices.length}</strong> of {invoices.length} purchase invoices
            </div>
          </div>

          {/* Invoices Table */}
          <div className="data-table-wrap">
            <table className="data-table">
              <thead>
                <tr>
                  <th>Invoice / PO #</th>
                  <th>Supplier Name</th>
                  <th>Order Date</th>
                  <th>Due Date</th>
                  <th>Billed Items</th>
                  <th className="number">Subtotal (€)</th>
                  <th className="number">Total Amount (€)</th>
                  <th>Status</th>
                  <th style={{ textAlign: 'right' }}>Actions</th>
                </tr>
              </thead>
              <tbody>
                {filteredInvoices.map(inv => (
                  <tr key={inv.id}>
                    <td>
                      <button 
                        className="lot-link" 
                        style={{ fontSize: '12px', fontWeight: 800 }} 
                        onClick={() => setViewInvoice(inv)}
                      >
                        {inv.invoice_number}
                      </button>
                    </td>
                    <td>
                      <strong style={{ color: '#0f172a' }}>{inv.supplier_name}</strong>
                    </td>
                    <td>{inv.invoice_date}</td>
                    <td>{inv.due_date ? <span>{inv.due_date}</span> : <span style={{ color: '#94a3b8' }}>—</span>}</td>
                    <td>
                      <span style={{ background: '#f1f5f9', color: '#334155', padding: '3px 8px', borderRadius: '12px', fontSize: '10px', fontWeight: 700, border: '1px solid #e2e8f0' }}>
                        {inv.items_count} items
                      </span>
                    </td>
                    <td className="number">€{parseFloat(inv.subtotal).toLocaleString('en-US', { minimumFractionDigits: 2 })}</td>
                    <td className="number">
                      <strong style={{ color: '#047857', fontSize: '13px' }}>
                        €{parseFloat(inv.total_amount).toLocaleString('en-US', { minimumFractionDigits: 2 })}
                      </strong>
                    </td>
                    <td>
                      <Status type={inv.status === 'Received' ? 'approved' : inv.status === 'Paid' ? 'approved' : inv.status === 'Approved' ? 'info' : 'warning'}>
                        {inv.status}
                      </Status>
                    </td>
                    <td style={{ textAlign: 'right' }}>
                      <div style={{ display: 'flex', gap: '6px', justifyContent: 'flex-end' }}>
                        {inv.status !== 'Received' ? (
                          <button 
                            className="btn btn-secondary" 
                            style={{ height: '28px', padding: '0 8px', fontSize: '10px', borderColor: '#86efac', color: '#15803d', background: '#f0fdf4' }}
                            title="Receive items from this invoice into Warehouse Quarantine"
                            onClick={() => handleReceiveInvoice(inv.id)}
                            disabled={receivingInvoice}
                          >
                            📥 Receive
                          </button>
                        ) : (
                          <button 
                            className="btn btn-secondary" 
                            style={{ height: '28px', padding: '0 8px', fontSize: '10px', color: '#0369a1', borderColor: '#bae6fd' }}
                            title="View received lots in Warehouse Inventory"
                            onClick={() => { if (navigate) navigate('inventory'); }}
                          >
                            📦 Lots
                          </button>
                        )}
                        <button 
                          className="btn btn-secondary" 
                          style={{ height: '28px', padding: '0 10px', fontSize: '10px', borderColor: '#c7d2fe', color: '#4338ca', background: '#eef2ff' }}
                          title="View, Print and Download official PDF invoice"
                          onClick={() => setViewInvoice(inv)}
                        >
                          🖨️ View & Print
                        </button>
                        <button 
                          className="btn btn-secondary" 
                          style={{ height: '28px', padding: '0 8px', fontSize: '10px', color: '#be123c', borderColor: '#fecdd3' }}
                          title="Delete invoice"
                          onClick={() => setDeleteInvoiceModal(inv)}
                        >
                          <Icon name="trash" size={13}/>
                        </button>
                      </div>
                    </td>
                  </tr>
                ))}
                {filteredInvoices.length === 0 && (
                  <tr>
                    <td colSpan="9" style={{ textAlign: 'center', padding: '36px', color: '#94a3b8' }}>
                      No purchase invoices match the current filter.
                    </td>
                  </tr>
                )}
              </tbody>
            </table>
          </div>
        </div>
      )}

      {/* SUBTAB 2: SUPPLIERS DIRECTORY */}
      {subTab === 'suppliers' && (
        <div className="section-card" style={{ overflow: 'hidden' }}>
          <div style={{ 
            padding: '16px 20px', 
            borderBottom: '1px solid var(--border)', 
            display: 'flex', 
            justifyContent: 'space-between', 
            alignItems: 'center', 
            flexWrap: 'wrap', 
            gap: '12px',
            background: '#ffffff'
          }}>
            <div>
              <h2 style={{ fontSize: '15px', fontWeight: 700, color: '#0f172a', margin: 0, letterSpacing: '-0.2px' }}>
                Approved Factory Suppliers & Packaging Vendors
              </h2>
              <p style={{ fontSize: '11px', color: '#64748b', margin: '4px 0 0 0' }}>
                ISO 22716 qualified suppliers of raw materials, active ingredients, and packaging containers
              </p>
            </div>
            <Button variant="primary" icon="plus" onClick={openNewSupplier}>+ Register New Supplier</Button>
          </div>

          <div className="table-toolbar" style={{ background: '#f8fafc', padding: '12px 20px', borderBottom: '1px solid var(--border)' }}>
            <div className="search table-search" style={{ width: '380px' }}>
              <Icon name="search" size={16}/>
              <input 
                placeholder="Search suppliers by name, code, contact, or supplied material..." 
                value={supSearch} 
                onChange={e => setSupSearch(e.target.value)}
              />
            </div>
            <div style={{ fontSize: '11px', color: '#64748b' }}>
              Showing <strong>{filteredSuppliers.length}</strong> of {suppliers.length} certified suppliers
            </div>
          </div>

          {/* Suppliers Table */}
          <div className="data-table-wrap">
            <table className="data-table">
              <thead>
                <tr>
                  <th>Supplier Info</th>
                  <th>Contact Person</th>
                  <th>Contact Details</th>
                  <th>Supplied Materials & Packaging</th>
                  <th>Payment Terms</th>
                  <th>Orders Log</th>
                  <th>Status</th>
                  <th style={{ textAlign: 'right' }}>Actions</th>
                </tr>
              </thead>
              <tbody>
                {filteredSuppliers.map(sup => {
                  const initial = (sup.name || 'S').charAt(0).toUpperCase();
                  const tags = (sup.materials_supplied || '').split(/[,،]/).map(t => t.trim()).filter(Boolean);

                  return (
                    <tr key={sup.id}>
                      <td>
                        <div className="sup-badge">
                          <div className="sup-avatar">{initial}</div>
                          <div>
                            <strong style={{ fontSize: '12px', color: '#0f172a', display: 'block' }}>{sup.name}</strong>
                            <code style={{ fontSize: '10px', color: '#64748b' }}>{sup.code}</code>
                            {sup.tax_number && <small style={{ display: 'block', color: '#94a3b8', fontSize: '9px' }}>VAT: {sup.tax_number}</small>}
                          </div>
                        </div>
                      </td>
                      <td>
                        <strong>{sup.contact_person || '—'}</strong>
                      </td>
                      <td>
                        <div style={{ fontSize: '11px', lineHeight: '1.4' }}>
                          {sup.phone && <div>📞 {sup.phone}</div>}
                          {sup.email && <div style={{ color: '#4f46e5' }}>✉ {sup.email}</div>}
                        </div>
                      </td>
                      <td>
                        <div className="supply-tag-wrap">
                          {tags.length > 0 ? (
                            tags.map((tag, i) => (
                              <span key={i} className="supply-tag">
                                🔹 {tag}
                              </span>
                            ))
                          ) : (
                            <span style={{ color: '#94a3b8', fontSize: '10px' }}>Unspecified</span>
                          )}
                        </div>
                      </td>
                      <td>
                        <span style={{ fontSize: '11px', fontWeight: 600, color: '#334155' }}>
                          {sup.payment_terms}
                        </span>
                      </td>
                      <td>
                        <span style={{ background: '#f1f5f9', color: '#0f172a', padding: '3px 8px', borderRadius: '10px', fontSize: '10px', fontWeight: 700, border: '1px solid #e2e8f0' }}>
                          {sup.invoices_count || 0} Invoices
                        </span>
                      </td>
                      <td>
                        <Status type={sup.status === 'Active' ? 'approved' : 'warning'}>
                          {sup.status}
                        </Status>
                      </td>
                      <td style={{ textAlign: 'right' }}>
                        <div style={{ display: 'flex', gap: '6px', justifyContent: 'flex-end' }}>
                          <button 
                            className="btn btn-secondary" 
                            style={{ height: '28px', padding: '0 8px', fontSize: '10px', color: '#4338ca', borderColor: '#c7d2fe' }}
                            title="View lots received in warehouse from this supplier"
                            onClick={() => { if (navigate) navigate('inventory'); }}
                          >
                            <Icon name="box" size={13}/> Lots
                          </button>
                          <button 
                            className="btn btn-secondary" 
                            style={{ height: '28px', padding: '0 9px', fontSize: '10px' }}
                            title="Edit supplier details and materials"
                            onClick={() => openEditSupplier(sup)}
                          >
                            <Icon name="edit" size={13}/> Edit
                          </button>
                          <button 
                            className="btn btn-secondary" 
                            style={{ height: '28px', padding: '0 8px', fontSize: '10px', color: '#be123c', borderColor: '#fecdd3' }}
                            title="Delete supplier"
                            onClick={() => setDeleteSupplierModal(sup)}
                          >
                            <Icon name="trash" size={13}/>
                          </button>
                        </div>
                      </td>
                    </tr>
                  );
                })}
                {filteredSuppliers.length === 0 && (
                  <tr>
                    <td colSpan="8" style={{ textAlign: 'center', padding: '36px', color: '#94a3b8' }}>
                      No suppliers found matching your query.
                    </td>
                  </tr>
                )}
              </tbody>
            </table>
          </div>
        </div>
      )}

      {/* SUBTAB 3: MATERIAL PRICES & INVENTORY FINANCIAL VALUATION */}
      {subTab === 'pricing' && (
        <>
          {/* Enhanced Metric Cards Grid */}
          {valuationData && (
            <div className="proc-kpi-grid">
              
              <div className="proc-kpi-card emerald">
                <div className="proc-kpi-header">
                  <span className="proc-kpi-title">TOTAL CAPITAL INVENTORY</span>
                  <div className="proc-kpi-icon" style={{ background: '#ecfdf5', color: '#059669' }}>
                    <Icon name="scale" size={16}/>
                  </div>
                </div>
                <div className="proc-kpi-value">
                  €{Number(valuationData.total_valuation || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}
                </div>
                <div className="proc-kpi-sub">
                  <span>💰 Total warehouse financial asset value</span>
                </div>
              </div>

              <div className="proc-kpi-card blue">
                <div className="proc-kpi-header">
                  <span className="proc-kpi-title">RAW MATERIALS STOCK</span>
                  <div className="proc-kpi-icon" style={{ background: '#eff6ff', color: '#2563eb' }}>
                    <Icon name="flask" size={16}/>
                  </div>
                </div>
                <div className="proc-kpi-value">
                  €{Number(valuationData.raw_materials_valuation || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}
                </div>
                <div className="proc-kpi-sub">
                  <span>🧪 Chemicals, actives, extracts & oils</span>
                </div>
              </div>

              <div className="proc-kpi-card amber">
                <div className="proc-kpi-header">
                  <span className="proc-kpi-title">PACKAGING & BOXES STOCK</span>
                  <div className="proc-kpi-icon" style={{ background: '#fffbeb', color: '#d97706' }}>
                    <Icon name="boxes" size={16}/>
                  </div>
                </div>
                <div className="proc-kpi-value">
                  €{Number(valuationData.packaging_valuation || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}
                </div>
                <div className="proc-kpi-sub">
                  <span>📦 Outer boxes, bottles, jars & labels</span>
                </div>
              </div>

              <div className="proc-kpi-card purple">
                <div className="proc-kpi-header">
                  <span className="proc-kpi-title">FINISHED GOODS STOCK</span>
                  <div className="proc-kpi-icon" style={{ background: '#f5f3ff', color: '#7c3aed' }}>
                    <Icon name="sparkles" size={16}/>
                  </div>
                </div>
                <div className="proc-kpi-value">
                  €{Number(valuationData.finished_goods_valuation || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}
                </div>
                <div className="proc-kpi-sub">
                  <span>✨ Released commercial skincare batches</span>
                </div>
              </div>

            </div>
          )}

          {/* Pricing Table */}
          <div className="section-card" style={{ overflow: 'hidden' }}>
            <div style={{ 
              padding: '16px 20px', 
              borderBottom: '1px solid var(--border)', 
              display: 'flex', 
              justifyContent: 'space-between', 
              alignItems: 'center', 
              flexWrap: 'wrap', 
              gap: '12px',
              background: '#ffffff'
            }}>
              <div>
                <h2 style={{ fontSize: '15px', fontWeight: 700, color: '#0f172a', margin: 0, letterSpacing: '-0.2px' }}>
                  Material Price List & Current Inventory Financial Worth
                </h2>
                <p style={{ fontSize: '11px', color: '#64748b', margin: '4px 0 0 0' }}>
                  Standard purchase unit costs and real-time ledger financial valuation for all catalog items
                </p>
              </div>
            </div>

            <div className="table-toolbar" style={{ background: '#f8fafc', padding: '12px 20px', borderBottom: '1px solid var(--border)' }}>
              <div className="search table-search" style={{ width: '380px' }}>
                <Icon name="search" size={16}/>
                <input 
                  placeholder="Search materials by name or code..." 
                  value={priceSearch} 
                  onChange={e => setPriceSearch(e.target.value)}
                />
              </div>
              <div style={{ fontSize: '11px', color: '#64748b' }}>
                Showing <strong>{filteredPrices.length}</strong> items with live valuations
              </div>
            </div>

            <div className="data-table-wrap">
              <table className="data-table">
                <thead>
                  <tr>
                    <th>Item Code</th>
                    <th>Material / Packaging Name</th>
                    <th>Classification</th>
                    <th>Unit</th>
                    <th className="number">Cost / Unit (€)</th>
                    <th className="number">Available Stock</th>
                    <th className="number">Total Value (€)</th>
                    <th style={{ textAlign: 'right' }}>Actions</th>
                  </tr>
                </thead>
                <tbody>
                  {filteredPrices.map(it => (
                    <tr key={it.id}>
                      <td><code>{it.code}</code></td>
                      <td>
                        <strong style={{ color: '#0f172a' }}>{it.name}</strong>
                      </td>
                      <td>
                        <span className={`badge-cat ${it.type === 'packaging' ? 'pk' : it.type === 'finished_good' ? 'fg' : 'rm'}`}>
                          {it.type === 'packaging' ? '📦 Packaging' : it.type === 'finished_good' ? '✨ Finished Good' : '🧪 Raw Material'}
                        </span>
                      </td>
                      <td><code>{it.unit}</code></td>
                      <td className="number">
                        <strong style={{ fontSize: '13px', color: '#0f172a' }}>
                          €{parseFloat(it.cost || it.cost_per_unit || 0).toFixed(2)}
                        </strong>
                      </td>
                      <td className="number">
                        {parseFloat(it.stock_qty || it.total_stock || 0).toLocaleString('en-US', { minimumFractionDigits: 2 })} {it.unit}
                      </td>
                      <td className="number">
                        <strong style={{ color: '#047857', fontSize: '13px' }}>
                          €{parseFloat(it.valuation || (parseFloat(it.total_stock || 0) * parseFloat(it.cost_per_unit || 0))).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}
                        </strong>
                      </td>
                      <td style={{ textAlign: 'right' }}>
                        <div style={{ display: 'flex', gap: '6px', justifyContent: 'flex-end' }}>
                          <button 
                            className="btn btn-secondary" 
                            style={{ height: '28px', padding: '0 8px', fontSize: '10px', color: '#0369a1', borderColor: '#bae6fd' }}
                            title="Jump to Warehouse Lots for this item"
                            onClick={() => { if (navigate) navigate('inventory'); }}
                          >
                            <Icon name="box" size={13}/> Stock
                          </button>
                          <button 
                            className="btn btn-secondary" 
                            style={{ height: '28px', padding: '0 10px', fontSize: '10px' }}
                            title="Edit unit purchase price"
                            onClick={() => {
                              setEditPriceModal(it);
                              setNewPriceValue(String(it.cost || it.cost_per_unit || 0));
                            }}
                          >
                            <Icon name="edit" size={13}/> Edit Price
                          </button>
                        </div>
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          </div>
        </>
      )}

      {/* MODAL 1: VIEW & PRINT INVOICE SHEET (OFFICIAL A4 PAPER) */}
      {viewInvoice && (
        <div className="modal-overlay" onClick={() => setViewInvoice(null)}>
          <div className="modal" style={{ maxWidth: '880px' }} onClick={e => e.stopPropagation()}>
            <div className="modal-header">
              <div>
                <h2>Official Invoice / Purchase Order Document</h2>
                <p style={{ fontSize: '11px', color: '#64748b' }}>وثيقة الفاتورة الرسمية لمصنع Beauty Italy · جاهزة للطباعة والتنزيل PDF</p>
              </div>
              <button className="icon-button" onClick={() => setViewInvoice(null)}><Icon name="close"/></button>
            </div>
            
            <div className="modal-body" style={{ maxHeight: '72vh', overflowY: 'auto', background: '#f1f5f9', padding: '24px' }}>
              
              {/* PRINTABLE PAPER CONTAINER */}
              <div id="factory-invoice-sheet" className="report-paper invoice-sheet-box">
                
                {/* Header */}
                <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'flex-start', borderBottom: '2px solid #0f172a', paddingBottom: '18px', marginBottom: '22px' }}>
                  <div>
                    <div style={{ display: 'flex', alignItems: 'center', gap: '10px', marginBottom: '4px' }}>
                      <div style={{ width: '34px', height: '34px', background: 'linear-gradient(135deg, #4f46e5, #3730a3)', color: 'white', borderRadius: '8px', display: 'grid', placeItems: 'center', fontWeight: 'bold', fontSize: '16px' }}>B</div>
                      <h1 style={{ fontSize: '22px', fontWeight: 800, margin: 0, letterSpacing: '-0.5px' }}>BEAUTY ITALY FACTORY S.r.l.</h1>
                    </div>
                    <div style={{ fontSize: '11px', color: '#475569', fontWeight: 500 }}>Cosmetics & Skincare Manufacturing Plant</div>
                    <div style={{ fontSize: '10px', color: '#64748b' }}>Via dell'Industria 88, 20121 Milano, Italy · VAT: IT09841120159</div>
                    <div style={{ fontSize: '10px', color: '#047857', fontWeight: 700, marginTop: '4px', display: 'flex', alignItems: 'center', gap: '4px' }}>
                      <span>✓ ISO 22716 Cosmetics GMP Certified</span>
                      <span>· Regulated Manufacturing Order</span>
                    </div>
                  </div>

                  <div style={{ textAlign: 'right' }}>
                    <div className="invoice-stamp">PURCHASE INVOICE / PO</div>
                    <div style={{ fontSize: '16px', fontWeight: 800, margin: '8px 0 4px', color: '#0f172a' }}>{viewInvoice.invoice_number}</div>
                    <div style={{ fontSize: '11px', color: '#475569' }}>Date: <strong>{viewInvoice.invoice_date}</strong></div>
                    {viewInvoice.due_date && <div style={{ fontSize: '11px', color: '#475569' }}>Due Date: <strong>{viewInvoice.due_date}</strong></div>}
                    <div style={{ marginTop: '6px' }}>
                      <Status type={viewInvoice.status === 'Received' ? 'approved' : 'info'}>{viewInvoice.status}</Status>
                    </div>
                  </div>
                </div>

                {/* Two Column Bill To / Ship To Grid */}
                <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '20px', background: '#f8fafc', padding: '16px', borderRadius: '8px', border: '1px solid #e2e8f0', marginBottom: '22px' }}>
                  <div>
                    <span style={{ fontSize: '10px', fontWeight: 700, color: '#64748b', letterSpacing: '0.8px', textTransform: 'uppercase' }}>ISSUED TO (SUPPLIER):</span>
                    <h3 style={{ fontSize: '15px', fontWeight: 700, margin: '6px 0 4px', color: '#0f172a' }}>{viewInvoice.supplier_name}</h3>
                    {(() => {
                      const sup = suppliers.find(s => s.id === viewInvoice.supplier_id || s.name === viewInvoice.supplier_name);
                      return sup ? (
                        <div style={{ fontSize: '11px', color: '#475569', lineHeight: '1.5' }}>
                          {sup.contact_person && <div><strong>Attn:</strong> {sup.contact_person}</div>}
                          {sup.phone && <div><strong>Phone:</strong> {sup.phone}</div>}
                          {sup.email && <div><strong>Email:</strong> {sup.email}</div>}
                          {sup.address && <div><strong>Address:</strong> {sup.address}</div>}
                          {sup.tax_number && <div><strong>VAT/Tax:</strong> {sup.tax_number}</div>}
                        </div>
                      ) : null;
                    })()}
                  </div>

                  <div>
                    <span style={{ fontSize: '10px', fontWeight: 700, color: '#64748b', letterSpacing: '0.8px', textTransform: 'uppercase' }}>SHIP TO & RECEIVING DOCK:</span>
                    <div style={{ fontSize: '14px', fontWeight: 700, margin: '6px 0 4px', color: '#0f172a' }}>Beauty Italy Factory · Receiving Dock WH-Q</div>
                    <div style={{ fontSize: '11px', color: '#475569', lineHeight: '1.5' }}>
                      Quarantine & Incoming Material Inspection Hall<br/>
                      Via dell'Industria 88, 20121 Milano, Italy<br/>
                      <strong>Dock Master:</strong> Giulia Romano (Warehouse Lead)
                    </div>
                  </div>
                </div>

                {/* Items Table */}
                <table className="report-table" style={{ width: '100%', borderCollapse: 'collapse', marginBottom: '22px' }}>
                  <thead>
                    <tr style={{ background: '#0f172a', color: 'white' }}>
                      <th style={{ padding: '9px 12px', textAlign: 'left', fontSize: '11px' }}>#</th>
                      <th style={{ padding: '9px 12px', textAlign: 'left', fontSize: '11px' }}>Item Code</th>
                      <th style={{ padding: '9px 12px', textAlign: 'left', fontSize: '11px' }}>Material / Packaging Description</th>
                      <th style={{ padding: '9px 12px', textAlign: 'right', fontSize: '11px' }}>Quantity</th>
                      <th style={{ padding: '9px 12px', textAlign: 'right', fontSize: '11px' }}>Unit</th>
                      <th style={{ padding: '9px 12px', textAlign: 'right', fontSize: '11px' }}>Unit Price (€)</th>
                      <th style={{ padding: '9px 12px', textAlign: 'right', fontSize: '11px' }}>Total Price (€)</th>
                    </tr>
                  </thead>
                  <tbody>
                    {(viewInvoice.items || []).map((line, idx) => (
                      <tr key={line.id || idx} style={{ borderBottom: '1px solid #e2e8f0' }}>
                        <td style={{ padding: '9px 12px', fontSize: '11px' }}>{idx + 1}</td>
                        <td style={{ padding: '9px 12px', fontSize: '11px' }}><code>{line.item_code}</code></td>
                        <td style={{ padding: '9px 12px', fontSize: '11px' }}><strong>{line.item_name}</strong></td>
                        <td style={{ padding: '9px 12px', textAlign: 'right', fontSize: '11px' }}>{parseFloat(line.quantity).toLocaleString()}</td>
                        <td style={{ padding: '9px 12px', textAlign: 'right', fontSize: '11px' }}>{line.unit}</td>
                        <td style={{ padding: '9px 12px', textAlign: 'right', fontSize: '11px' }}>€{parseFloat(line.unit_price).toFixed(2)}</td>
                        <td style={{ padding: '9px 12px', textAlign: 'right', fontSize: '11px', fontWeight: 'bold' }}>€{parseFloat(line.total_price).toFixed(2)}</td>
                      </tr>
                    ))}
                  </tbody>
                </table>

                {/* Calculation Box */}
                <div style={{ display: 'flex', justifyContent: 'flex-end', marginBottom: '24px' }}>
                  <div className="invoice-calc-box">
                    <div className="invoice-calc-row">
                      <span>Subtotal:</span>
                      <strong>€{parseFloat(viewInvoice.subtotal).toFixed(2)}</strong>
                    </div>
                    <div className="invoice-calc-row">
                      <span>VAT / Tax ({parseFloat(viewInvoice.tax_percent || 14).toFixed(0)}%):</span>
                      <span>€{parseFloat(viewInvoice.tax_amount).toFixed(2)}</span>
                    </div>
                    <div className="invoice-calc-row total">
                      <span>Grand Total:</span>
                      <span style={{ color: '#4f46e5' }}>€{parseFloat(viewInvoice.total_amount).toFixed(2)}</span>
                    </div>
                  </div>
                </div>

                {/* Notes and GMP Signatures */}
                {viewInvoice.notes && (
                  <div style={{ fontSize: '11px', color: '#475569', background: '#f8fafc', padding: '10px 14px', borderRadius: '6px', border: '1px solid #e2e8f0', marginBottom: '22px' }}>
                    <strong>Terms & Notes:</strong> {viewInvoice.notes}
                  </div>
                )}

                <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '30px', borderTop: '1px dashed #cbd5e1', paddingTop: '18px', fontSize: '10px', color: '#64748b' }}>
                  <div>
                    <strong style={{ color: '#0f172a' }}>PROCUREMENT AUTHORIZATION:</strong><br/>
                    Marco Conti (Factory Operations Director)<br/>
                    Electronic Approval: <em>VALIDATED ERP PO</em>
                  </div>
                  <div style={{ textAlign: 'right' }}>
                    <strong style={{ color: '#0f172a' }}>INCOMING QUALITY CONTROL (ISO 22716):</strong><br/>
                    Elena Rossi (QA Manager)<br/>
                    Disposition: <em>Controlled Material Receiving Required</em>
                  </div>
                </div>

              </div>
            </div>

            <div className="modal-footer" style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', flexWrap: 'wrap', gap: '10px' }}>
              <div style={{ display: 'flex', gap: '8px', alignItems: 'center' }}>
                <Button variant="secondary" onClick={() => setViewInvoice(null)}>Close</Button>
                {viewInvoice.status !== 'Received' ? (
                  <Button 
                    variant="primary" 
                    icon="box" 
                    onClick={() => handleReceiveInvoice(viewInvoice.id)}
                    disabled={receivingInvoice}
                  >
                    {receivingInvoice ? 'Receiving into Warehouse...' : '📥 Receive into Warehouse (استلام في المخزن)'}
                  </Button>
                ) : (
                  <Button 
                    variant="secondary" 
                    icon="box" 
                    onClick={() => {
                      setViewInvoice(null);
                      if (navigate) navigate('inventory');
                    }}
                  >
                    📦 View Lots in Warehouse
                  </Button>
                )}
              </div>
              <div style={{ display: 'flex', gap: '8px' }}>
                <Button 
                  variant="secondary" 
                  icon="printer" 
                  onClick={() => window.downloadReportPdf('factory-invoice-sheet', `Invoice-${viewInvoice.invoice_number}.pdf`, true)}
                >
                  🖨️ Clean Print Invoice
                </Button>
                <Button 
                  variant="primary" 
                  icon="file" 
                  onClick={() => window.downloadReportPdf('factory-invoice-sheet', `Invoice-${viewInvoice.invoice_number}.pdf`)}
                >
                  📥 Download Invoice PDF
                </Button>
              </div>
            </div>
          </div>
        </div>
      )}

      {/* MODAL 2: CREATE NEW PURCHASE INVOICE / PO */}
      {invoiceModal && (
        <div className="modal-overlay" onClick={() => setInvoiceModal(false)}>
          <div className="modal" style={{ maxWidth: '720px' }} onClick={e => e.stopPropagation()}>
            <div className="modal-header">
              <div>
                <h2>Create New Purchase Invoice / Order</h2>
                <p style={{ fontSize: '11px', color: '#64748b' }}>إصدار أمر شراء / فاتورة توريد جديدة للمورد</p>
              </div>
              <button className="icon-button" onClick={() => setInvoiceModal(false)}><Icon name="close"/></button>
            </div>
            <form onSubmit={submitInvoice}>
              <div className="modal-body" style={{ maxHeight: '68vh', overflowY: 'auto' }}>
                
                <div className="form-grid-2">
                  <div className="form-field">
                    <label>Select Supplier (المورد) *</label>
                    <select 
                      value={invoiceForm.supplier_id} 
                      onChange={e => setInvoiceForm({ ...invoiceForm, supplier_id: e.target.value })}
                      required
                    >
                      {suppliers.map(sup => (
                        <option key={sup.id} value={sup.id}>
                          {sup.name} ({sup.code})
                        </option>
                      ))}
                    </select>
                  </div>

                  <div className="form-field">
                    <label>Invoice / PO Number (رقم الفاتورة أو الإذن)</label>
                    <input 
                      placeholder="e.g. INV-2026-0099 (Leave blank for auto)" 
                      value={invoiceForm.invoice_number} 
                      onChange={e => setInvoiceForm({ ...invoiceForm, invoice_number: e.target.value })}
                    />
                  </div>
                </div>

                <div className="form-grid-2">
                  <div className="form-field">
                    <label>Invoice Date (تاريخ الفاتورة) *</label>
                    <input 
                      type="date" 
                      value={invoiceForm.invoice_date} 
                      onChange={e => setInvoiceForm({ ...invoiceForm, invoice_date: e.target.value })}
                      required
                    />
                  </div>
                  <div className="form-field">
                    <label>Payment Due Date (تاريخ الاستحقاق)</label>
                    <input 
                      type="date" 
                      value={invoiceForm.due_date} 
                      onChange={e => setInvoiceForm({ ...invoiceForm, due_date: e.target.value })}
                    />
                  </div>
                </div>

                {/* Line Items Repeater */}
                <div style={{ marginTop: '16px' }}>
                  <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: '10px' }}>
                    <label style={{ fontSize: '11px', fontWeight: 700, color: '#0f172a', textTransform: 'uppercase', letterSpacing: '0.5px' }}>
                      Line Items · Materials & Quantities (الأصناف المطلوبة) *
                    </label>
                    <button 
                      type="button" 
                      className="btn btn-secondary" 
                      style={{ height: '26px', padding: '0 10px', fontSize: '10px' }}
                      onClick={handleAddInvoiceLine}
                    >
                      + Add Item Row
                    </button>
                  </div>

                  {invoiceForm.items.map((line, idx) => {
                    const rowLineTotal = (parseFloat(line.quantity || 0) * parseFloat(line.unit_price || 0)).toFixed(2);
                    return (
                      <div key={idx} className="repeater-row">
                        <div className="repeater-num">#{idx + 1}</div>
                        <div>
                          <select 
                            value={line.item_id} 
                            onChange={e => handleInvoiceItemChange(idx, 'item_id', e.target.value)}
                            required
                            style={{ width: '100%', fontSize: '11px', height: '36px' }}
                          >
                            {itemsList.map(it => (
                              <option key={it.id} value={it.id}>
                                {it.code} — {it.name} [{it.type}]
                              </option>
                            ))}
                          </select>
                        </div>

                        <div>
                          <input 
                            type="number" 
                            step="0.01" 
                            min="0.01" 
                            placeholder="Quantity" 
                            value={line.quantity} 
                            onChange={e => handleInvoiceItemChange(idx, 'quantity', e.target.value)}
                            required
                            style={{ width: '100%', fontSize: '12px', fontWeight: 700 }}
                          />
                        </div>

                        <div>
                          <input 
                            type="number" 
                            step="0.01" 
                            min="0" 
                            placeholder="Unit Price (€)" 
                            value={line.unit_price} 
                            onChange={e => handleInvoiceItemChange(idx, 'unit_price', e.target.value)}
                            required
                            style={{ width: '100%', fontSize: '12px' }}
                          />
                        </div>

                        <div className="repeater-total">
                          €{rowLineTotal}
                        </div>

                        <div>
                          <button 
                            type="button" 
                            className="btn-remove-row"
                            onClick={() => handleRemoveInvoiceLine(idx)}
                            disabled={invoiceForm.items.length <= 1}
                            title="Remove this line"
                          >
                            ✕
                          </button>
                        </div>
                      </div>
                    );
                  })}
                </div>

                {/* Realtime Totals preview */}
                {(() => {
                  const { subtotal, tax, total } = calculateInvoiceTotals();
                  return (
                    <div style={{ background: '#f8fafc', border: '1px solid #e2e8f0', borderRadius: '8px', padding: '12px 16px', marginTop: '14px', fontSize: '12px' }}>
                      <div style={{ display: 'flex', justifyContent: 'space-between', marginBottom: '4px' }}>
                        <span>Subtotal:</span>
                        <strong>€{subtotal.toFixed(2)}</strong>
                      </div>
                      <div style={{ display: 'flex', justifyContent: 'space-between', marginBottom: '4px', color: '#64748b' }}>
                        <span>Tax (14% VAT):</span>
                        <span>€{tax.toFixed(2)}</span>
                      </div>
                      <div style={{ display: 'flex', justifyContent: 'space-between', fontSize: '14px', borderTop: '1px solid #cbd5e1', paddingTop: '6px', marginTop: '6px' }}>
                        <strong style={{ color: '#0f172a' }}>Estimated Total:</strong>
                        <strong style={{ color: '#4f46e5' }}>€{total.toFixed(2)}</strong>
                      </div>
                    </div>
                  );
                })()}

                <div className="form-field" style={{ marginTop: '14px' }}>
                  <label>Notes / Delivery Terms (ملاحظات أمر الشراء)</label>
                  <input 
                    placeholder="e.g. Standard delivery within 5 business days to Milano dock." 
                    value={invoiceForm.notes} 
                    onChange={e => setInvoiceForm({ ...invoiceForm, notes: e.target.value })}
                  />
                </div>

              </div>
              <div className="modal-footer">
                <Button variant="secondary" onClick={() => setInvoiceModal(false)}>Cancel</Button>
                <Button variant="primary" type="submit">Generate Official Invoice</Button>
              </div>
            </form>
          </div>
        </div>
      )}

      {/* MODAL 3: ADD / EDIT SUPPLIER */}
      {supplierModal && (
        <div className="modal-overlay" onClick={() => setSupplierModal(false)}>
          <div className="modal" style={{ maxWidth: '600px' }} onClick={e => e.stopPropagation()}>
            <div className="modal-header">
              <div>
                <h2>{editingSupplier ? `Edit Supplier: ${editingSupplier.name}` : 'Register New Supplier'}</h2>
                <p style={{ fontSize: '11px', color: '#64748b' }}>
                  {editingSupplier ? 'تعديل بيانات المورد والخامات التي يوردها' : 'إضافة مورد جديد وتحديد ما يورده للمصنع'}
                </p>
              </div>
              <button className="icon-button" onClick={() => setSupplierModal(false)}><Icon name="close"/></button>
            </div>
            <form onSubmit={submitSupplier}>
              <div className="modal-body">
                <div className="form-grid-2">
                  <div className="form-field">
                    <label>Supplier Name (اسم المورد / الشركة) *</label>
                    <input 
                      value={supplierForm.name} 
                      onChange={e => setSupplierForm({ ...supplierForm, name: e.target.value })}
                      placeholder="e.g. BASF, Lonza, Vetro Packaging"
                      required
                    />
                  </div>
                  <div className="form-field">
                    <label>Supplier Code (كود المورد)</label>
                    <input 
                      value={supplierForm.code} 
                      onChange={e => setSupplierForm({ ...supplierForm, code: e.target.value })}
                      placeholder="e.g. SUP-BASF"
                    />
                  </div>
                </div>

                <div className="form-field">
                  <label>What Materials Do They Supply? (بيوردلي ايه بالظبط؟) *</label>
                  <textarea 
                    rows="3"
                    value={supplierForm.materials_supplied} 
                    onChange={e => setSupplierForm({ ...supplierForm, materials_supplied: e.target.value })}
                    placeholder="مثال: علب كرتون خارجية 30 و 50 مل، ملصقات فينيل رولات، أو مواد خام كيميائية وزيوت طبيعية"
                    required
                  />
                </div>

                <div className="form-grid-2">
                  <div className="form-field">
                    <label>Contact Person (الشخص المسؤول)</label>
                    <input 
                      value={supplierForm.contact_person} 
                      onChange={e => setSupplierForm({ ...supplierForm, contact_person: e.target.value })}
                      placeholder="e.g. Marco Rossi"
                    />
                  </div>
                  <div className="form-field">
                    <label>Phone Number (الهاتف)</label>
                    <input 
                      value={supplierForm.phone} 
                      onChange={e => setSupplierForm({ ...supplierForm, phone: e.target.value })}
                      placeholder="e.g. +39 02 4810 4110"
                    />
                  </div>
                </div>

                <div className="form-grid-2">
                  <div className="form-field">
                    <label>Email Address (البريد الإلكتروني)</label>
                    <input 
                      type="email"
                      value={supplierForm.email} 
                      onChange={e => setSupplierForm({ ...supplierForm, email: e.target.value })}
                      placeholder="orders@supplier.com"
                    />
                  </div>
                  <div className="form-field">
                    <label>Payment Terms (شروط الدفع)</label>
                    <select 
                      value={supplierForm.payment_terms} 
                      onChange={e => setSupplierForm({ ...supplierForm, payment_terms: e.target.value })}
                    >
                      <option value="Net 30 Days">Net 30 Days (آجل 30 يوم)</option>
                      <option value="Net 60 Days">Net 60 Days (آجل 60 يوم)</option>
                      <option value="Cash on Delivery">Cash on Delivery (دفع عند الاستلام)</option>
                      <option value="100% Advance">100% Advance (مقدماً بالكامل)</option>
                    </select>
                  </div>
                </div>

                <div className="form-field">
                  <label>Full Address / Plant Location (العنوان)</label>
                  <input 
                    value={supplierForm.address} 
                    onChange={e => setSupplierForm({ ...supplierForm, address: e.target.value })}
                    placeholder="e.g. Via Marconato 8, Milano, Italy"
                  />
                </div>
              </div>
              <div className="modal-footer">
                <Button variant="secondary" onClick={() => setSupplierModal(false)}>Cancel</Button>
                <Button variant="primary" type="submit">{editingSupplier ? 'Save Changes' : 'Register Supplier'}</Button>
              </div>
            </form>
          </div>
        </div>
      )}

      {/* MODAL 4: EDIT MATERIAL UNIT PRICE */}
      {editPriceModal && (
        <div className="modal-overlay" onClick={() => setEditPriceModal(null)}>
          <div className="modal" style={{ maxWidth: '420px' }} onClick={e => e.stopPropagation()}>
            <div className="modal-header">
              <div>
                <h2>Update Unit Price / Cost</h2>
                <p style={{ fontSize: '11px', color: '#64748b' }}>تعديل سعر شراء الوحدة لـ {editPriceModal.code}</p>
              </div>
              <button className="icon-button" onClick={() => setEditPriceModal(null)}><Icon name="close"/></button>
            </div>
            <form onSubmit={submitUpdatePrice}>
              <div className="modal-body">
                <div style={{ background: '#f8fafc', padding: '12px', borderRadius: '8px', border: '1px solid #e2e8f0', marginBottom: '14px', fontSize: '11px' }}>
                  <strong style={{ fontSize: '13px', color: '#0f172a' }}>{editPriceModal.name}</strong>
                  <div style={{ color: '#64748b', marginTop: '3px' }}>Classification: <strong>{editPriceModal.type}</strong> · Unit: <strong>{editPriceModal.unit}</strong></div>
                </div>

                <div className="form-field">
                  <label>New Cost / Unit (€ / {editPriceModal.unit}) *</label>
                  <input 
                    type="number" 
                    step="0.01" 
                    min="0"
                    value={newPriceValue} 
                    onChange={e => setNewPriceValue(e.target.value)}
                    required
                    style={{ fontSize: '16px', fontWeight: 800, color: '#0f172a' }}
                  />
                </div>
              </div>
              <div className="modal-footer">
                <Button variant="secondary" onClick={() => setEditPriceModal(null)}>Cancel</Button>
                <Button variant="primary" type="submit">Save New Price</Button>
              </div>
            </form>
          </div>
        </div>
      )}

      {/* MODAL 5: DELETE SUPPLIER CONFIRM */}
      {deleteSupplierModal && (
        <div className="modal-overlay" onClick={() => setDeleteSupplierModal(null)}>
          <div className="modal" style={{ maxWidth: '440px', borderTop: '4px solid #ef4444' }} onClick={e => e.stopPropagation()}>
            <div className="modal-header">
              <h2 style={{ color: '#be123c' }}>Delete Supplier</h2>
              <button className="icon-button" onClick={() => setDeleteSupplierModal(null)}><Icon name="close"/></button>
            </div>
            <div className="modal-body">
              <p style={{ fontSize: '12px', color: '#334155' }}>
                Are you sure you want to permanently delete supplier <strong style={{ color: '#0f172a' }}>{deleteSupplierModal.name}</strong>?
              </p>
            </div>
            <div className="modal-footer">
              <Button variant="secondary" onClick={() => setDeleteSupplierModal(null)}>Cancel</Button>
              <button className="btn btn-danger" onClick={submitDeleteSupplier}>Delete Supplier</button>
            </div>
          </div>
        </div>
      )}

      {/* MODAL 6: DELETE INVOICE CONFIRM */}
      {deleteInvoiceModal && (
        <div className="modal-overlay" onClick={() => setDeleteInvoiceModal(null)}>
          <div className="modal" style={{ maxWidth: '440px', borderTop: '4px solid #ef4444' }} onClick={e => e.stopPropagation()}>
            <div className="modal-header">
              <h2 style={{ color: '#be123c' }}>Delete Invoice</h2>
              <button className="icon-button" onClick={() => setDeleteInvoiceModal(null)}><Icon name="close"/></button>
            </div>
            <div className="modal-body">
              <p style={{ fontSize: '12px', color: '#334155' }}>
                Are you sure you want to delete invoice <strong style={{ color: '#0f172a' }}>{deleteInvoiceModal.invoice_number}</strong>?
              </p>
            </div>
            <div className="modal-footer">
              <Button variant="secondary" onClick={() => setDeleteInvoiceModal(null)}>Cancel</Button>
              <button className="btn btn-danger" onClick={submitDeleteInvoice}>Delete Invoice</button>
            </div>
          </div>
        </div>
      )}

    </div>
  );
}
@endverbatim
