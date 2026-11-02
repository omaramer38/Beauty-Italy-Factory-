@verbatim
// Root Application Shell (App Component, Sidebar, Topbar, Role Switcher, Route Dispatcher)
function App() {
  const [page, setPage] = useState('formulation'); // Default to formulation studio as requested by user
  const [role, setRole] = useState('Admin');
  const [roleOpen, setRoleOpen] = useState(false);
  const [toast, setToast] = useState('');
  const [omniboxOpen, setOmniboxOpen] = useState(false);
  const [scannerOpen, setScannerOpen] = useState(false);
  const [targetEntityId, setTargetEntityId] = useState(null);

  const notify = (msg) => { setToast(msg); setTimeout(() => setToast(''), 3500); };

  // Handle keyboard shortcut Ctrl+K / Cmd+K
  useEffect(() => {
    const handleKeyDown = (e) => {
      if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'k') {
        e.preventDefault();
        setOmniboxOpen(prev => !prev);
      }
    };
    window.addEventListener('keydown', handleKeyDown);
    return () => window.removeEventListener('keydown', handleKeyDown);
  }, []);

  const handleOmniboxSelect = (item) => {
    if (item.target === 'formulation') {
      setTargetEntityId(item.payload);
      setPage('formulation');
    } else if (item.target === 'inventory') {
      setTargetEntityId(item.payload);
      setPage('inventory');
    } else if (item.target === 'dispensing') {
      setTargetEntityId(item.payload);
      setPage('dispensing');
    } else if (item.target === 'quality') {
      setPage('quality');
    }
  };

  const handleNavigateFromScanner = (targetPage, targetId) => {
    setTargetEntityId(targetId);
    setPage(targetPage);
  };

  const content = {
    dashboard: <Dashboard navigate={setPage} notify={notify} onOpenSearch={() => setOmniboxOpen(true)} onOpenScanner={() => setScannerOpen(true)}/>,
    inventory: <Inventory notify={notify} initialLotNumber={page === 'inventory' ? targetEntityId : null}/>,
    formulation: (
      <Formulation 
        activeCode={page === 'formulation' ? targetEntityId : 'FRM-SC-0042'} 
        notify={notify}
        onLaunchBatch={(batchNum) => {
          setTargetEntityId(batchNum);
          setPage('dispensing');
        }}
        onOpenLot={(lotNum) => {
          setTargetEntityId(lotNum);
          setPage('inventory');
        }}
      />
    ),
    dispensing: <Dispensing notify={notify} initialBatchNumber={page === 'dispensing' ? targetEntityId : null} onOpenScanner={() => setScannerOpen(true)}/>,
    quality: <Quality notify={notify}/>,
    trace: <Traceability notify={notify} onOpenScanner={() => setScannerOpen(true)}/>,
  };

  return (
    <div className="app-shell">
      <aside className="sidebar">
        <div className="brand">
          <div className="brand-mark">B</div>
          <div><strong>Beauty Italy</strong><span>Factory ERP</span></div>
        </div>
        <div className="nav-section-label">{role === 'Admin' ? 'ALL OPERATIONS' : `${role.toUpperCase()} VIEW`}</div>
        <nav>
          {nav.map(item => (
            <button key={item.id} className={`nav-item ${page === item.id ? 'active' : ''}`} onClick={() => setPage(item.id)}>
              <Icon name={item.icon}/><span>{item.label}</span>
            </button>
          ))}
        </nav>
        <div className="sidebar-spacer"/>
        <div className="compliance-card">
          <div className="compliance-icon"><Icon name="shield" size={16}/></div>
          <div><strong>GMP ISO 22716</strong><span>MySQL Connected</span></div>
          <Status type="approved">Live</Status>
        </div>
      </aside>

      <div className="main-shell">
        <header className="topbar">
          <div className="search" onClick={() => setOmniboxOpen(true)}>
            <Icon name="search" size={17}/>
            <input placeholder="Search lots, batches, formulations in MySQL..." readOnly/>
            <kbd>Ctrl K</kbd>
          </div>

          <button className="btn-qr-scan" onClick={() => setScannerOpen(true)}>
            <Icon name="qr" size={17}/>
            <span>Scan QR / Barcode</span>
          </button>

          <div className="topbar-right">
            <div className="warehouse"><span className="warehouse-dot"/><span>IT · Main Plant</span></div>
            <div className="role-switcher">
              <button className="user-profile" onClick={() => setRoleOpen(!roleOpen)}>
                <div className="avatar">{roleProfiles[role].initials}</div>
                <div><strong>{role === 'Admin' ? 'Marco Conti' : role}</strong><span>Active role · <b>{role}</b></span></div>
              </button>
              {roleOpen && (
                <div className="role-menu">
                  <div className="role-menu-head"><span>SWITCH USER ROLE (RBAC)</span></div>
                  {roles.map(r => (
                    <button key={r} className={role === r ? 'active' : ''} onClick={() => { setRole(r); setPage(roleProfiles[r].landing); setRoleOpen(false); notify(`Switched to ${r}`); }}>
                      <span className="role-avatar">{roleProfiles[r].initials}</span>
                      <span><strong>{r}</strong><small>{roleProfiles[r].label}</small></span>
                    </button>
                  ))}
                </div>
              )}
            </div>
          </div>
        </header>

        <div className="role-context">
          <span>{roleProfiles[role].label}</span>
          <p>{roleProfiles[role].detail}</p>
          <b>RBAC MYSQL ACTIVE</b>
        </div>

        <main>{content[page]}</main>
      </div>

      {/* Omnibox Modal */}
      <OmniboxModal 
        isOpen={omniboxOpen} 
        onClose={() => setOmniboxOpen(false)} 
        onSelectResult={handleOmniboxSelect}
      />

      {/* QR Scanner Studio Modal */}
      <QRScannerModal 
        isOpen={scannerOpen} 
        onClose={() => setScannerOpen(false)} 
        onNavigateEntity={handleNavigateFromScanner}
      />

      {toast && <div className="toast"><span><Icon name="check" size={15}/></span>{toast}</div>}
    </div>
  );
}

ReactDOM.createRoot(document.getElementById('root')).render(<App />);
@endverbatim
