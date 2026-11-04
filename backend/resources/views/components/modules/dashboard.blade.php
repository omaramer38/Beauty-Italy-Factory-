@verbatim
// Module 1: Executive Manufacturing Command Center & MES Analytics
function Dashboard({ navigate, notify, onOpenSearch, onOpenScanner }) {
  const [data, setData] = useState(null);
  const [activeChartMetric, setActiveChartMetric] = useState('output'); // 'output' | 'yield'
  const [hoveredPoint, setHoveredPoint] = useState(null);
  const [donutHover, setDonutHover] = useState(null);
  const [timeRange, setTimeRange] = useState('week'); // 'shift' | 'week' | 'month'

  const fetchDashboardData = () => {
    fetch('/api/v1/dashboard')
      .then(res => res.json())
      .then(json => setData(json))
      .catch(e => console.error(e));
  };

  useEffect(() => {
    fetchDashboardData();
    // Live polling every 15s to keep dashboard stream real-time
    const interval = setInterval(fetchDashboardData, 15000);
    return () => clearInterval(interval);
  }, []);

  if (!data) {
    return (
      <div style={{ minHeight: '60vh', display: 'grid', placeItems: 'center', textAlign: 'center' }}>
        <div>
          <div style={{ width: '42px', height: '42px', border: '3px solid #e2e8f0', borderTopColor: '#4f46e5', borderRadius: '50%', animation: 'spin 1s linear infinite', margin: '0 auto 16px' }}/>
          <strong style={{ fontSize: '15px', color: '#0f172a' }}>Connecting to Live Factory Engine...</strong>
          <p style={{ fontSize: '11px', color: '#64748b', marginTop: '4px' }}>Loading ISO 22716 MES operations & inventory assets</p>
        </div>
      </div>
    );
  }

  // Sparkline Component for Hero Cards
  const Sparkline = ({ points = [10, 25, 40, 30, 60, 75, 80], color = '#3b82f6', id = 'spark' }) => {
    const min = Math.min(...points);
    const max = Math.max(...points);
    const range = (max - min) || 1;
    const w = 120;
    const h = 26;
    const step = w / (points.length - 1);
    const coords = points.map((p, i) => {
      const x = i * step;
      const y = h - ((p - min) / range) * (h - 6) - 3;
      return `${x.toFixed(1)},${y.toFixed(1)}`;
    });
    const pathD = 'M ' + coords.join(' L ');
    const fillD = `${pathD} L ${w},${h} L 0,${h} Z`;

    return (
      <svg width="100%" height={h} viewBox={`0 0 ${w} ${h}`} preserveAspectRatio="none" style={{ overflow: 'visible' }}>
        <defs>
          <linearGradient id={`grad-${id}`} x1="0" y1="0" x2="0" y2="1">
            <stop offset="0%" stopColor={color} stopOpacity="0.3" />
            <stop offset="100%" stopColor={color} stopOpacity="0.0" />
          </linearGradient>
        </defs>
        <path d={fillD} fill={`url(#grad-${id})`} />
        <path d={pathD} fill="none" stroke={color} strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" />
      </svg>
    );
  };

  // Multi-Timeframe active dataset resolver
  const currentRangeData = (data.ranges && data.ranges[timeRange]) || {
    title: timeRange === 'shift' ? 'Shift A (Live Compounding 06:00 - 18:00)' : timeRange === 'month' ? 'Month to Date (30-Day Aggregates)' : 'This Week (7-Day Schedule)',
    chartSubtitle: timeRange === 'shift' ? 'Hourly compounding volume during Shift A (06:00 - 18:00) vs shift plan' : timeRange === 'month' ? 'Weekly cumulative output (Liters) against monthly plant manufacturing quota' : 'Daily compounding volume (Liters) vs target schedule across all lines',
    metrics: data.metrics || [],
    trend: data.weeklyTrend || [],
    oee: data.oee || { availability: 94.2, performance: 89.6, quality: 99.4, overall: 84.0 },
  };

  const activeMetrics = currentRangeData.metrics || data.metrics || [];
  const weekly = currentRangeData.trend || data.weeklyTrend || [];
  const oee = currentRangeData.oee || data.oee || { availability: 94.2, performance: 89.6, quality: 99.4, overall: 84.0 };

  const totalOutputLiters = weekly.reduce((acc, w) => acc + w.output, 0);
  const totalTargetLiters = weekly.reduce((acc, w) => acc + w.target, 0);
  const avgYieldRate = weekly.length ? (weekly.reduce((acc, w) => acc + w.yield, 0) / weekly.length).toFixed(1) : 99.2;

  // SVG Chart Dimensions & Adaptive Scaling per timeframe
  const chartW = 560;
  const chartH = 170;
  const padL = 40;
  const padR = 20;
  const padT = 20;
  const padB = 30;
  const usableW = chartW - padL - padR;
  const usableH = chartH - padT - padB;

  const maxVal = activeChartMetric === 'output' 
    ? (timeRange === 'shift' ? 450 : timeRange === 'month' ? 13000 : 2200) 
    : 100;
  const minVal = activeChartMetric === 'output' 
    ? (timeRange === 'shift' ? 80 : timeRange === 'month' ? 6000 : 600) 
    : 97.5;

  const getX = (idx) => padL + (idx / Math.max(1, weekly.length - 1)) * usableW;
  const getY = (val) => chartH - padB - ((val - minVal) / (maxVal - minVal)) * usableH;

  const actualPoints = weekly.map((w, idx) => ({
    x: getX(idx),
    y: getY(activeChartMetric === 'output' ? w.output : w.yield),
    item: w
  }));

  const targetPoints = weekly.map((w, idx) => ({
    x: getX(idx),
    y: getY(activeChartMetric === 'output' ? w.target : 99.0),
    item: w
  }));

  const actualPathD = actualPoints.reduce((acc, pt, i, arr) => {
    if (i === 0) return `M ${pt.x},${pt.y}`;
    const prev = arr[i - 1];
    const cpX = (prev.x + pt.x) / 2;
    return `${acc} C ${cpX},${prev.y} ${cpX},${pt.y} ${pt.x},${pt.y}`;
  }, '');

  const actualAreaD = actualPoints.length > 0 ? `${actualPathD} L ${actualPoints[actualPoints.length - 1].x},${chartH - padB} L ${actualPoints[0].x},${chartH - padB} Z` : '';

  const targetPathD = targetPoints.reduce((acc, pt, i) => `${acc} ${i === 0 ? 'M' : 'L'} ${pt.x},${pt.y}`, '');

  // Valuation Breakdown for Donut Chart
  const breakdown = data.valuationBreakdown || {
    total: 64991,
    raw_materials: 38420,
    packaging: 18170,
    finished_goods: 8401,
  };

  const donutTotal = breakdown.total || 1;
  const rawPct = ((breakdown.raw_materials / donutTotal) * 100);
  const pkgPct = ((breakdown.packaging / donutTotal) * 100);
  const fgPct = ((breakdown.finished_goods / donutTotal) * 100);

  // SVG Donut calculation (r = 54, C = 339.29)
  const donutR = 54;
  const donutC = 2 * Math.PI * donutR;
  const rawOffset = 0;
  const rawLength = (rawPct / 100) * donutC;
  const pkgOffset = -rawLength;
  const pkgLength = (pkgPct / 100) * donutC;
  const fgOffset = -(rawLength + pkgLength);
  const fgLength = (fgPct / 100) * donutC;

  const handleRangeChange = (rangeKey) => {
    setTimeRange(rangeKey);
    const names = {
      shift: 'Shift A (Live Compounding)',
      week: 'This Week (7-Day Schedule)',
      month: 'Month to Date (30-Day Aggregates)'
    };
    if (notify) notify(`✓ Switched dashboard view to ${names[rangeKey] || rangeKey}`);
  };

  return (
    <div style={{ paddingBottom: '30px' }}>
      
      {/* 1. TOP HEADER & CONTROLS */}
      <div className="page-header" style={{ marginBottom: '14px' }}>
        <div>
          <div style={{ display: 'flex', alignItems: 'center', gap: '8px', marginBottom: '4px' }}>
            <span style={{ background: '#fef3c7', color: '#92400e', fontSize: '9px', fontWeight: 800, padding: '2px 8px', borderRadius: '4px', border: '1px solid #fde68a', letterSpacing: '0.8px' }}>
              🇮🇹 BEAUTY ITALY S.R.L. · MILANO PLANT
            </span>
            <span style={{ fontSize: '11px', color: '#64748b' }}>ISO 22716 Cosmetics GMP Certified</span>
          </div>
          <h1 style={{ fontSize: '20px', fontWeight: 800, color: '#0f172a', margin: 0, letterSpacing: '-0.3px' }}>
            Manufacturing Operations Command Center
          </h1>
          <p style={{ fontSize: '11px', color: '#64748b', margin: '4px 0 0 0' }}>
            Real-time shop floor execution, continuous quality release & inventory asset telemetry
          </p>
        </div>

        <div className="header-actions">
          <div style={{ display: 'inline-flex', background: '#f1f5f9', padding: '3px', borderRadius: '8px', gap: '3px', border: '1px solid #e2e8f0' }}>
            <button 
              className={`filter-btn ${timeRange === 'shift' ? 'active' : ''}`} 
              onClick={() => handleRangeChange('shift')}
              style={{ fontSize: '11px', height: '28px', padding: '0 12px', fontWeight: timeRange === 'shift' ? 700 : 500, cursor: 'pointer' }}
            >
              Shift A (Live)
            </button>
            <button 
              className={`filter-btn ${timeRange === 'week' ? 'active' : ''}`} 
              onClick={() => handleRangeChange('week')}
              style={{ fontSize: '11px', height: '28px', padding: '0 12px', fontWeight: timeRange === 'week' ? 700 : 500, cursor: 'pointer' }}
            >
              This Week
            </button>
            <button 
              className={`filter-btn ${timeRange === 'month' ? 'active' : ''}`} 
              onClick={() => handleRangeChange('month')}
              style={{ fontSize: '11px', height: '28px', padding: '0 12px', fontWeight: timeRange === 'month' ? 700 : 500, cursor: 'pointer' }}
            >
              Month to Date
            </button>
          </div>
          <Button variant="secondary" icon="search" onClick={onOpenSearch}>Search (Ctrl K)</Button>
          <Button variant="secondary" icon="qr" onClick={onOpenScanner}>Scan Barcode</Button>
          <div className="live-pill"><span/> MySQL Stream Active</div>
        </div>
      </div>

      {/* 2. FACILITY & CLEANROOM ENVIRONMENTAL TELEMETRY RIBBON */}
      <div className="dash-telemetry-strip">
        <div style={{ display: 'flex', alignItems: 'center', gap: '8px' }}>
          <span className="telemetry-dot"/>
          <strong style={{ color: '#f8fafc', letterSpacing: '0.5px', textTransform: 'uppercase', fontSize: '10px' }}>
            Cleanroom Telemetry (ISO 22716):
          </strong>
        </div>

        {(data.cleanrooms || []).map((cr, idx) => (
          <div key={idx} className="telemetry-item">
            <span style={{ color: '#94a3b8' }}>{cr.suite.split('(')[0]}:</span>
            <strong style={{ color: '#e2e8f0', fontFamily: 'monospace' }}>{cr.temp} · {cr.humidity} · {cr.pressure}</strong>
            <span style={{ color: '#34d399', fontSize: '9px', fontWeight: 700 }}>✓ {cr.status}</span>
          </div>
        ))}

        <div className="telemetry-item" style={{ borderLeft: '1px solid #334155', paddingLeft: '12px' }}>
          <span style={{ color: '#94a3b8' }}>Plant OEE Benchmark:</span>
          <strong style={{ color: '#38bdf8' }}>{oee.overall}% World-Class</strong>
        </div>
      </div>

      {/* 3. HERO BALANCED 6-KPI CARDS WITH EMBEDDED SPARKLINES */}
      <div className="dash-hero-grid">
        {(activeMetrics || []).map((m, i) => (
          <div className={`dash-kpi-card ${m.tone || 'blue'}`} key={m.label}>
            <div>
              <div className="dash-kpi-header">
                <span className="dash-kpi-title">{m.label}</span>
                <div className={`dash-kpi-icon ${m.tone || 'blue'}`}>
                  <Icon name={m.icon} size={15}/>
                </div>
              </div>
              <div className="dash-kpi-val">{m.value}</div>
            </div>

            <div>
              <div className="dash-sparkline-wrap">
                <Sparkline 
                  points={m.spark || [20, 35, 45, 55, 60, 70, 85]} 
                  color={
                    m.tone === 'blue' ? '#3b82f6' :
                    m.tone === 'indigo' ? '#6366f1' :
                    m.tone === 'green' ? '#10b981' :
                    m.tone === 'amber' ? '#f59e0b' :
                    m.tone === 'rose' ? '#f43f5e' : '#a855f7'
                  }
                  id={`hero-spark-${i}`}
                />
              </div>

              <div className="dash-kpi-footer">
                <span>{m.detail}</span>
                <span className={`dash-kpi-delta ${m.deltaTone || 'up'}`}>{m.delta}</span>
              </div>
            </div>
          </div>
        ))}
      </div>

      {/* 4. VISUAL DATA CHARTS SHOWCASE: WEEKLY OUTPUT & INVENTORY BREAKDOWN */}
      <div className="dash-charts-grid">
        
        {/* CHART 1: PRODUCTION OUTPUT & QUALITY YIELD AREA CHART */}
        <div className="chart-card">
          <div className="chart-header">
            <div>
              <h3>{timeRange === 'shift' ? 'Shift A Compounding Output & Live Yield' : timeRange === 'month' ? 'Monthly Cumulative Production Output & Yield' : 'Plant Production Output & Quality First-Time Yield'}</h3>
              <p>{currentRangeData.chartSubtitle}</p>
            </div>
            
            <div style={{ display: 'flex', alignItems: 'center', gap: '8px' }}>
              <div style={{ display: 'inline-flex', background: '#f1f5f9', padding: '2px', borderRadius: '6px', gap: '2px' }}>
                <button 
                  className={`filter-btn ${activeChartMetric === 'output' ? 'active' : ''}`}
                  style={{ height: '24px', fontSize: '10px', padding: '0 8px' }}
                  onClick={() => setActiveChartMetric('output')}
                >
                  Volume (L)
                </button>
                <button 
                  className={`filter-btn ${activeChartMetric === 'yield' ? 'active' : ''}`}
                  style={{ height: '24px', fontSize: '10px', padding: '0 8px' }}
                  onClick={() => setActiveChartMetric('yield')}
                >
                  Yield (%)
                </button>
              </div>

              <div style={{ display: 'flex', alignItems: 'center', gap: '10px', fontSize: '10px', marginLeft: '6px' }}>
                <span style={{ display: 'flex', alignItems: 'center', gap: '4px', color: '#4338ca', fontWeight: 600 }}>
                  <span style={{ width: '8px', height: '8px', borderRadius: '2px', background: '#4f46e5' }}/> Actual
                </span>
                <span style={{ display: 'flex', alignItems: 'center', gap: '4px', color: '#059669', fontWeight: 600 }}>
                  <span style={{ width: '12px', height: '2px', background: '#10b981', display: 'inline-block' }}/> Target
                </span>
              </div>
            </div>
          </div>

          <div className="chart-body" style={{ padding: '12px 18px 10px' }}>
            {/* Top KPI row inside chart */}
            <div style={{ display: 'grid', gridTemplateColumns: 'repeat(3, 1fr)', gap: '10px', background: '#f8fafc', padding: '8px 14px', borderRadius: '8px', border: '1px solid #f1f5f9', marginBottom: '12px' }}>
              <div>
                <span style={{ fontSize: '9px', color: '#64748b', textTransform: 'uppercase', fontWeight: 700 }}>Total Weekly Volume</span>
                <div style={{ fontSize: '16px', fontWeight: 800, color: '#0f172a' }}>{totalOutputLiters.toLocaleString()} L</div>
              </div>
              <div>
                <span style={{ fontSize: '9px', color: '#64748b', textTransform: 'uppercase', fontWeight: 700 }}>Target Attainment</span>
                <div style={{ fontSize: '16px', fontWeight: 800, color: '#059669' }}>
                  {((totalOutputLiters / totalTargetLiters) * 100).toFixed(1)}% (+{totalOutputLiters - totalTargetLiters} L)
                </div>
              </div>
              <div>
                <span style={{ fontSize: '9px', color: '#64748b', textTransform: 'uppercase', fontWeight: 700 }}>First-Pass Quality Yield</span>
                <div style={{ fontSize: '16px', fontWeight: 800, color: '#4f46e5' }}>{avgYieldRate}% RFT</div>
              </div>
            </div>

            {/* SVG Interactive Area Chart */}
            <div style={{ position: 'relative', height: '170px' }}>
              <svg width="100%" height="100%" viewBox={`0 0 ${chartW} ${chartH}`} preserveAspectRatio="none" style={{ overflow: 'visible' }}>
                <defs>
                  <linearGradient id="prodAreaGrad" x1="0" y1="0" x2="0" y2="1">
                    <stop offset="0%" stopColor="#4f46e5" stopOpacity="0.28" />
                    <stop offset="100%" stopColor="#4f46e5" stopOpacity="0.0" />
                  </linearGradient>
                </defs>

                {/* Horizontal Guide Lines */}
                {[0, 0.25, 0.5, 0.75, 1].map((pct, i) => {
                  const y = padT + pct * usableH;
                  const labelVal = Math.round(maxVal - pct * (maxVal - minVal));
                  return (
                    <g key={i}>
                      <line x1={padL} y1={y} x2={chartW - padR} y2={y} stroke="#f1f5f9" strokeWidth="1" strokeDasharray="3 3"/>
                      <text x={padL - 6} y={y + 3} fill="#94a3b8" fontSize="8" textAnchor="end" fontFamily="sans-serif">
                        {activeChartMetric === 'output' ? labelVal : labelVal + '%'}
                      </text>
                    </g>
                  );
                })}

                {/* Area and Line Paths */}
                <path d={actualAreaD} fill="url(#prodAreaGrad)" />
                <path d={targetPathD} fill="none" stroke="#10b981" strokeWidth="2" strokeDasharray="4 4" opacity="0.85" />
                <path d={actualPathD} fill="none" stroke="#4f46e5" strokeWidth="3" strokeLinecap="round" strokeLinejoin="round" />

                {/* Data Points with Hover Interaction */}
                {actualPoints.map((pt, i) => (
                  <g key={i}>
                    {/* Day label */}
                    <text x={pt.x} y={chartH - 8} fill="#64748b" fontSize="9" fontWeight="600" textAnchor="middle" fontFamily="sans-serif">
                      {pt.item.day}
                    </text>

                    {/* Point Circle */}
                    <circle 
                      cx={pt.x} 
                      cy={pt.y} 
                      r={hoveredPoint && hoveredPoint.item.day === pt.item.day ? '6' : '4'} 
                      fill="#ffffff" 
                      stroke="#4f46e5" 
                      strokeWidth="2.5"
                      style={{ cursor: 'pointer', transition: 'r .15s ease' }}
                      onMouseEnter={() => setHoveredPoint(pt)}
                      onMouseLeave={() => setHoveredPoint(null)}
                    />
                  </g>
                ))}
              </svg>

              {/* Hover Tooltip Popup */}
              {hoveredPoint && (
                <div style={{
                  position: 'absolute',
                  left: `${(hoveredPoint.x / chartW) * 100}%`,
                  top: `${hoveredPoint.y - 12}px`,
                  transform: 'translate(-50%, -100%)',
                  background: '#0f172a',
                  color: 'white',
                  padding: '6px 10px',
                  borderRadius: '6px',
                  fontSize: '10px',
                  boxShadow: '0 4px 14px rgba(0,0,0,0.25)',
                  pointerEvents: 'none',
                  zIndex: 20,
                  whiteSpace: 'nowrap'
                }}>
                  <div style={{ fontWeight: 700, color: '#38bdf8' }}>{hoveredPoint.item.day} Production</div>
                  <div>Actual Output: <strong>{hoveredPoint.item.output} L</strong></div>
                  <div>Target: <span style={{ color: '#94a3b8' }}>{hoveredPoint.item.target} L</span></div>
                  <div>Quality Yield: <strong style={{ color: '#34d399' }}>{hoveredPoint.item.yield}%</strong></div>
                </div>
              )}
            </div>
          </div>
        </div>

        {/* CHART 2: INVENTORY ASSETS VALUATION & OEE BREAKDOWN */}
        <div className="chart-card">
          <div className="chart-header">
            <div>
              <h3>Inventory Financial Worth & OEE</h3>
              <p>Warehouse ledger valuation & overall plant efficiency</p>
            </div>
            <span style={{ fontSize: '11px', fontWeight: 800, color: '#047857', background: '#ecfdf5', padding: '3px 8px', borderRadius: '6px' }}>
              Total: €{Number(breakdown.total || 0).toLocaleString()}
            </span>
          </div>

          <div className="chart-body" style={{ display: 'flex', flexDirection: 'column', justifyContent: 'space-between' }}>
            
            {/* Donut Chart & Category Breakdown */}
            <div style={{ display: 'grid', gridTemplateColumns: '130px 1fr', gap: '16px', alignItems: 'center' }}>
              
              {/* SVG Donut */}
              <div style={{ position: 'relative', width: '130px', height: '130px' }}>
                <svg width="130" height="130" viewBox="0 0 130 130">
                  <circle cx="65" cy="65" r={donutR} fill="none" stroke="#f1f5f9" strokeWidth="16" />
                  
                  {/* Raw Materials Slice (Indigo) */}
                  <circle 
                    cx="65" 
                    cy="65" 
                    r={donutR} 
                    fill="none" 
                    stroke="#4f46e5" 
                    strokeWidth="16"
                    strokeDasharray={`${rawLength} ${donutC - rawLength}`}
                    strokeDashoffset={rawOffset}
                    transform="rotate(-90 65 65)"
                    style={{ transition: 'stroke-width .2s ease', cursor: 'pointer' }}
                    onMouseEnter={() => setDonutHover('raw')}
                    onMouseLeave={() => setDonutHover(null)}
                  />

                  {/* Packaging Slice (Amber) */}
                  <circle 
                    cx="65" 
                    cy="65" 
                    r={donutR} 
                    fill="none" 
                    stroke="#f59e0b" 
                    strokeWidth="16"
                    strokeDasharray={`${pkgLength} ${donutC - pkgLength}`}
                    strokeDashoffset={pkgOffset}
                    transform="rotate(-90 65 65)"
                    style={{ transition: 'stroke-width .2s ease', cursor: 'pointer' }}
                    onMouseEnter={() => setDonutHover('pkg')}
                    onMouseLeave={() => setDonutHover(null)}
                  />

                  {/* Finished Goods Slice (Green) */}
                  <circle 
                    cx="65" 
                    cy="65" 
                    r={donutR} 
                    fill="none" 
                    stroke="#10b981" 
                    strokeWidth="16"
                    strokeDasharray={`${fgLength} ${donutC - fgLength}`}
                    strokeDashoffset={fgOffset}
                    transform="rotate(-90 65 65)"
                    style={{ transition: 'stroke-width .2s ease', cursor: 'pointer' }}
                    onMouseEnter={() => setDonutHover('fg')}
                    onMouseLeave={() => setDonutHover(null)}
                  />
                </svg>

                {/* Center Value */}
                <div style={{ position: 'absolute', inset: 0, display: 'flex', flexDirection: 'column', alignItems: 'center', justifyContent: 'center', pointerEvents: 'none' }}>
                  <span style={{ fontSize: '9px', fontWeight: 700, color: '#64748b' }}>TOTAL ASSETS</span>
                  <strong style={{ fontSize: '13px', color: '#0f172a', fontWeight: 800 }}>€{(breakdown.total / 1000).toFixed(1)}k</strong>
                </div>
              </div>

              {/* Interactive Legend with Progress Bars */}
              <div style={{ display: 'flex', flexDirection: 'column', gap: '8px' }}>
                <div>
                  <div style={{ display: 'flex', justifyContent: 'space-between', fontSize: '11px', marginBottom: '2px' }}>
                    <span style={{ color: '#0f172a', fontWeight: 600 }}>🧪 Raw Materials</span>
                    <strong>€{Number(breakdown.raw_materials || 0).toLocaleString()} ({rawPct.toFixed(0)}%)</strong>
                  </div>
                  <div style={{ height: '5px', background: '#e2e8f0', borderRadius: '4px', overflow: 'hidden' }}>
                    <div style={{ width: `${rawPct}%`, height: '100%', background: '#4f46e5' }}/>
                  </div>
                </div>

                <div>
                  <div style={{ display: 'flex', justifyContent: 'space-between', fontSize: '11px', marginBottom: '2px' }}>
                    <span style={{ color: '#0f172a', fontWeight: 600 }}>📦 Packaging & Bottles</span>
                    <strong>€{Number(breakdown.packaging || 0).toLocaleString()} ({pkgPct.toFixed(0)}%)</strong>
                  </div>
                  <div style={{ height: '5px', background: '#e2e8f0', borderRadius: '4px', overflow: 'hidden' }}>
                    <div style={{ width: `${pkgPct}%`, height: '100%', background: '#f59e0b' }}/>
                  </div>
                </div>

                <div>
                  <div style={{ display: 'flex', justifyContent: 'space-between', fontSize: '11px', marginBottom: '2px' }}>
                    <span style={{ color: '#0f172a', fontWeight: 600 }}>✨ Finished Products</span>
                    <strong>€{Number(breakdown.finished_goods || 0).toLocaleString()} ({fgPct.toFixed(0)}%)</strong>
                  </div>
                  <div style={{ height: '5px', background: '#e2e8f0', borderRadius: '4px', overflow: 'hidden' }}>
                    <div style={{ width: `${fgPct}%`, height: '100%', background: '#10b981' }}/>
                  </div>
                </div>
              </div>

            </div>

            {/* OEE 3-Factor Radial Strip */}
            <div style={{ borderTop: '1px solid #f1f5f9', paddingTop: '10px', marginTop: '12px', display: 'grid', gridTemplateColumns: 'repeat(3, 1fr)', gap: '8px', textAlign: 'center' }}>
              <div style={{ background: '#f8fafc', padding: '6px', borderRadius: '6px', border: '1px solid #f1f5f9' }}>
                <span style={{ fontSize: '9px', color: '#64748b', display: 'block', textTransform: 'uppercase', fontWeight: 700 }}>Line Availability</span>
                <strong style={{ fontSize: '13px', color: '#2563eb' }}>{oee.availability}%</strong>
              </div>
              <div style={{ background: '#f8fafc', padding: '6px', borderRadius: '6px', border: '1px solid #f1f5f9' }}>
                <span style={{ fontSize: '9px', color: '#64748b', display: 'block', textTransform: 'uppercase', fontWeight: 700 }}>Run Performance</span>
                <strong style={{ fontSize: '13px', color: '#d97706' }}>{oee.performance}%</strong>
              </div>
              <div style={{ background: '#f8fafc', padding: '6px', borderRadius: '6px', border: '1px solid #f1f5f9' }}>
                <span style={{ fontSize: '9px', color: '#64748b', display: 'block', textTransform: 'uppercase', fontWeight: 700 }}>Quality Rate</span>
                <strong style={{ fontSize: '13px', color: '#059669' }}>{oee.quality}%</strong>
              </div>
            </div>

          </div>
        </div>

      </div>

      {/* 5. QUICK COMMAND & OPERATIONS HUB */}
      <div className="section-card" style={{ marginBottom: '16px', overflow: 'hidden' }}>
        <div className="section-heading" style={{ background: '#fafbfc' }}>
          <div>
            <h2>Manufacturing Command Operations Hub</h2>
            <p>Access active shop-floor modules and regulated GMP workflows</p>
          </div>
          <span style={{ fontSize: '10px', color: '#64748b', fontWeight: 600 }}>5 Core Operational Centers</span>
        </div>

        <div style={{ padding: '14px 16px', background: 'white' }}>
          <div className="dash-quick-grid">
            
            <button className="dash-quick-btn" onClick={() => navigate('inventory')}>
              <div className="dash-quick-icon" style={{ background: '#eff6ff', color: '#2563eb' }}>
                <Icon name="boxes" size={18}/>
              </div>
              <div className="dash-quick-text">
                <strong>New Goods Receipt</strong>
                <small>Receive & quarantine materials</small>
              </div>
            </button>

            <button className="dash-quick-btn" onClick={() => navigate('procurement')}>
              <div className="dash-quick-icon" style={{ background: '#eef2ff', color: '#4f46e5' }}>
                <Icon name="file" size={18}/>
              </div>
              <div className="dash-quick-text">
                <strong>Procurement & POs</strong>
                <small>Vendors, invoices & pricing</small>
              </div>
            </button>

            <button className="dash-quick-btn" onClick={() => navigate('formulation')}>
              <div className="dash-quick-icon" style={{ background: '#faf5ff', color: '#9333ea' }}>
                <Icon name="flask" size={18}/>
              </div>
              <div className="dash-quick-text">
                <strong>Formulation Studio</strong>
                <small>R&D recipes & scaling lines</small>
              </div>
            </button>

            <button className="dash-quick-btn" onClick={() => navigate('dispensing')}>
              <div className="dash-quick-icon" style={{ background: '#fffbeb', color: '#d97706' }}>
                <Icon name="scale" size={18}/>
              </div>
              <div className="dash-quick-text">
                <strong>Dispensing & eBMR</strong>
                <small>Mettler Toledo weighing station</small>
              </div>
            </button>

            <button className="dash-quick-btn" onClick={() => navigate('quality')}>
              <div className="dash-quick-icon" style={{ background: '#ecfdf5', color: '#059669' }}>
                <Icon name="shield" size={18}/>
              </div>
              <div className="dash-quick-text">
                <strong>Quality & Release</strong>
                <small>Assays, COA & lab release</small>
              </div>
            </button>

          </div>
        </div>
      </div>

      {/* 6. LOWER SHOP FLOOR MONITOR & RISK RADAR */}
      <div className="dashboard-lower">
        
        {/* LEFT: TODAY'S PRODUCTION LINES (LIVE MES GANTT) */}
        <div className="section-card" style={{ overflow: 'hidden' }}>
          <div className="section-heading" style={{ background: '#fafbfc' }}>
            <div>
              <h2>Live MES Production Floor Monitor</h2>
              <p>Active lines connected to automated bulk mixers & packaging lines</p>
            </div>
            <span style={{ fontSize: '10px', color: '#059669', background: '#ecfdf5', padding: '3px 8px', borderRadius: '4px', fontWeight: 700 }}>
              ● 3 Lines Active
            </span>
          </div>

          <div className="timeline-head" style={{ gridTemplateColumns: '230px 1fr 140px 90px' }}>
            <span>Line / Product / Batch</span>
            <div><span>06:00 (Shift Start)</span><span>12:00</span><span>18:00 (Shift End)</span></div>
            <span>Current Stage</span>
            <span style={{ textAlign: 'right' }}>Actions</span>
          </div>

          {data.lines.map(line => (
            <div className="timeline-row" key={line.line} style={{ gridTemplateColumns: '230px 1fr 140px 90px' }}>
              
              <div className="line-name">
                <b style={{ fontWeight: 800 }}>{line.line}</b>
                <span>
                  <strong style={{ fontSize: '11px', color: '#0f172a' }}>{line.product}</strong>
                  <small style={{ color: '#4f46e5', fontWeight: 600 }}>{line.batch} · {line.operator}</small>
                </span>
              </div>

              <div className="timeline-track" style={{ borderRadius: '4px', overflow: 'hidden' }}>
                <div className={`timeline-bar bar-${line.tone}`} style={{ width: `${line.progress}%`, minWidth: '80px', transition: 'width 0.4s ease' }}>
                  <span style={{ fontSize: '8px', fontWeight: 800 }}>{line.status}</span>
                  <b>{line.progress}%</b>
                </div>
              </div>

              <div>
                <span style={{ fontSize: '10px', fontWeight: 600, color: '#334155', display: 'block', lineHeight: '1.2' }}>
                  {line.stage}
                </span>
              </div>

              <div style={{ textAlign: 'right' }}>
                <button 
                  className="btn btn-secondary" 
                  style={{ height: '26px', padding: '0 8px', fontSize: '9px', borderColor: '#c7d2fe', color: '#4338ca' }}
                  onClick={() => navigate('dispensing')}
                  title="Open live batch execution in Dispensing & MES"
                >
                  Open MES ↗
                </button>
              </div>

            </div>
          ))}
        </div>

        {/* RIGHT: RISK RADAR & IMMEDIATE ATTENTION */}
        <div className="section-card attention-card" style={{ overflow: 'hidden' }}>
          <div className="section-heading" style={{ background: '#fafbfc' }}>
            <div>
              <h2>Immediate Attention Radar</h2>
              <p>Risk-prioritized queue for GMP compliance</p>
            </div>
            <span className="count-badge" style={{ background: '#fef2f2', color: '#b91c1c', border: '1px solid #fecaca' }}>
              {(data.attention || []).length}
            </span>
          </div>

          <div style={{ padding: '8px 12px' }}>
            {(data.attention || []).map((a, idx) => (
              <div key={idx} className="attention-item" style={{ padding: '10px 8px', borderBottom: idx < data.attention.length - 1 ? '1px solid #f1f5f9' : 'none' }}>
                <span className={`attention-mark ${a.tone}`}/>
                <span style={{ flex: 1, minWidth: 0 }}>
                  <div style={{ display: 'flex', alignItems: 'center', gap: '6px' }}>
                    <strong style={{ fontSize: '11px', color: '#0f172a' }}>{a.title}</strong>
                    {a.priority && (
                      <span style={{ fontSize: '8px', fontWeight: 800, padding: '1px 5px', borderRadius: '4px', background: a.tone === 'danger' ? '#fee2e2' : a.tone === 'warning' ? '#fef3c7' : '#e0e7ff', color: a.tone === 'danger' ? '#b91c1c' : a.tone === 'warning' ? '#b45309' : '#3730a3' }}>
                        {a.priority}
                      </span>
                    )}
                  </div>
                  <small style={{ fontSize: '9px', color: '#64748b', display: 'block', marginTop: '2px' }}>{a.code}</small>
                </span>
                <b style={{ fontSize: '9px', color: a.tone === 'danger' ? '#b91c1c' : a.tone === 'warning' ? '#b45309' : '#2563eb', whiteSpace: 'nowrap' }}>
                  {a.due}
                </b>
              </div>
            ))}
          </div>

          <div style={{ background: '#f8fafc', padding: '10px 14px', borderTop: '1px solid #f1f5f9', display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
            <span style={{ fontSize: '10px', color: '#64748b' }}>Shift A QA Lead: Elena Rossi</span>
            <button 
              className="lot-link" 
              style={{ fontSize: '10px', fontWeight: 700 }}
              onClick={() => navigate('quality')}
            >
              QA Laboratory Review ↗
            </button>
          </div>
        </div>

      </div>

    </div>
  );
}
@endverbatim