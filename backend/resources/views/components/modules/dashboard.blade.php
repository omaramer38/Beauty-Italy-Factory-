@verbatim
// Module 1: Dashboard / Command Center
function Dashboard({ navigate, notify, onOpenSearch, onOpenScanner }) {
  const [data, setData] = useState(null);

  useEffect(() => {
    fetch('/api/v1/dashboard')
      .then(res => res.json())
      .then(json => setData(json))
      .catch(e => console.error(e));
  }, []);

  if (!data) return <div style={{padding: '40px', textAlign: 'center'}}>Loading live data from MySQL...</div>;

  return (
    <div>
      <div className="page-header">
        <div>
          <div className="eyebrow">PHASE 1 · CONTROLLED CORE · MYSQL LIVE</div>
          <h1>Operations Command Center</h1>
          <p>Connected to MySQL (beauty_italy_erp) · Real-time manufacturing flow</p>
        </div>
        <div className="header-actions">
          <Button variant="secondary" icon="search" onClick={onOpenSearch}>Search Omnibox</Button>
          <Button variant="secondary" icon="qr" onClick={onOpenScanner}>Scan QR / Barcode</Button>
          <div className="live-pill"><span/> MySQL Active</div>
        </div>
      </div>

      <div className="metrics-grid">
        {data.metrics.map((m, i) => (
          <div className="metric-card" key={m.label}>
            <div className={`metric-icon ${m.tone}`}><Icon name={m.icon}/></div>
            <div className="metric-top"><span>{m.label}</span></div>
            <strong className="metric-value">{m.value}</strong>
            <div className="metric-bottom">
              <span>{m.detail}</span>
              <b>{m.delta}</b>
            </div>
          </div>
        ))}
      </div>

      <div className="section-card quick-actions">
        <div className="section-heading"><div><h2>Quick actions</h2><p>Start a controlled GMP operation</p></div></div>
        <div className="quick-grid">
          <button onClick={() => navigate('inventory')}><span className="quick-icon"><Icon name="plus"/></span><span><strong>New goods receipt</strong><small>Receive supplier materials</small></span></button>
          <button onClick={() => navigate('formulation')}><span className="quick-icon"><Icon name="flask"/></span><span><strong>Formulation Studio</strong><small>R&D & scale batches</small></span></button>
          <button onClick={() => navigate('dispensing')}><span className="quick-icon"><Icon name="scale"/></span><span><strong>Start batch dispensing</strong><small>Open weighing station</small></span></button>
          <button onClick={() => navigate('quality')}><span className="quick-icon"><Icon name="shield"/></span><span><strong>Log QC sample</strong><small>Record laboratory result</small></span></button>
        </div>
      </div>

      <div className="dashboard-lower">
        <div className="section-card">
          <div className="section-heading"><div><h2>Today's production lines</h2><p>Live MES status from database</p></div></div>
          <div className="timeline-head"><span>Line / Product</span><div><span>06:00</span><span>12:00</span><span>18:00</span></div><span>Status</span></div>
          {data.lines.map(line => (
            <div className="timeline-row" key={line.line}>
              <div className="line-name"><b>{line.line}</b><span><strong>{line.product}</strong><small>{line.batch}</small></span></div>
              <div className="timeline-track"><div className={`timeline-bar bar-${line.tone}`} style={{width: `${line.progress}%`}}><b>{line.progress}%</b></div></div>
              <Status type={line.tone}>{line.status}</Status>
            </div>
          ))}
        </div>
        <div className="section-card attention-card">
          <div className="section-heading"><div><h2>Needs attention</h2><p>Prioritized by risk</p></div><span className="count-badge">4</span></div>
          {data.attention.map(a => (
            <div key={a.code} className="attention-item">
              <span className={`attention-mark ${a.tone}`}/>
              <span><strong>{a.title}</strong><small>{a.code}</small></span>
              <b>{a.due}</b>
            </div>
          ))}
        </div>
      </div>
    </div>
  );
}
@endverbatim
