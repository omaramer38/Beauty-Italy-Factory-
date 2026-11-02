import { useState, type ReactNode } from 'react'

type IconName =
  | 'grid' | 'boxes' | 'flask' | 'scale' | 'shield' | 'trace' | 'search' | 'bell'
  | 'chevron' | 'menu' | 'plus' | 'scan' | 'check' | 'clock' | 'alert' | 'arrow'
  | 'file' | 'download' | 'filter' | 'more' | 'close' | 'lock' | 'upload' | 'user'

const paths: Record<IconName, ReactNode> = {
  grid: <><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></>,
  boxes: <><path d="m21 8-9 5-9-5 9-5 9 5Z"/><path d="m3 8 9 5 9-5v8l-9 5-9-5V8Z"/><path d="M12 13v8"/></>,
  flask: <><path d="M9 3h6M10 3v6l-5 9a2 2 0 0 0 2 3h10a2 2 0 0 0 2-3l-5-9V3"/><path d="M7.5 15h9"/></>,
  scale: <><path d="M6 20h12M12 4v16M4 7h16M4 7l-2 6h4L4 7ZM20 7l-2 6h4l-2-6Z"/></>,
  shield: <><path d="M12 22s8-3.5 8-10V5l-8-3-8 3v7c0 6.5 8 10 8 10Z"/><path d="m9 12 2 2 4-4"/></>,
  trace: <><circle cx="5" cy="5" r="2"/><circle cx="19" cy="12" r="2"/><circle cx="5" cy="19" r="2"/><path d="M7 5h4a4 4 0 0 1 4 4v0a3 3 0 0 0 3 3M7 19h4a4 4 0 0 0 4-4v0a3 3 0 0 1 3-3"/></>,
  search: <><circle cx="11" cy="11" r="7"/><path d="m20 20-4-4"/></>,
  bell: <><path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9ZM10 21h4"/></>,
  chevron: <path d="m9 18 6-6-6-6"/>,
  menu: <><path d="M4 7h16M4 12h16M4 17h16"/></>,
  plus: <><path d="M12 5v14M5 12h14"/></>,
  scan: <><path d="M4 7V4h3M17 4h3v3M20 17v3h-3M7 20H4v-3M7 9v6M10 9v6M14 9v6M17 9v6"/></>,
  check: <path d="m5 12 4 4L19 6"/>,
  clock: <><circle cx="12" cy="12" r="9"/><path d="M12 7v6l4 2"/></>,
  alert: <><path d="M12 3 2.5 20h19L12 3Z"/><path d="M12 9v5M12 17h.01"/></>,
  arrow: <><path d="M5 12h14M14 7l5 5-5 5"/></>,
  file: <><path d="M6 2h8l4 4v16H6V2Z"/><path d="M14 2v5h5M9 12h6M9 16h6"/></>,
  download: <><path d="M12 3v12M7 10l5 5 5-5M4 21h16"/></>,
  filter: <path d="M4 5h16l-6 7v6l-4 2v-8L4 5Z"/>,
  more: <><circle cx="5" cy="12" r="1"/><circle cx="12" cy="12" r="1"/><circle cx="19" cy="12" r="1"/></>,
  close: <><path d="m6 6 12 12M18 6 6 18"/></>,
  lock: <><rect x="4" y="10" width="16" height="11" rx="2"/><path d="M8 10V7a4 4 0 0 1 8 0v3"/></>,
  upload: <><path d="M12 16V4M7 9l5-5 5 5M4 20h16"/></>,
  user: <><circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/></>,
}

function Icon({ name, size = 18 }: { name: IconName; size?: number }) {
  return <svg width={size} height={size} viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true">{paths[name]}</svg>
}

function Button({ children, variant = 'primary', icon, onClick, className = '' }: { children: ReactNode; variant?: 'primary'|'secondary'|'ghost'|'danger'; icon?: IconName; onClick?: () => void; className?: string }) {
  return <button type="button" onClick={onClick} className={`btn btn-${variant} ${className}`}>{icon && <Icon name={icon} size={16}/>}<span>{children}</span></button>
}

function Status({ type, children }: { type: 'approved'|'warning'|'danger'|'neutral'|'info'; children: ReactNode }) {
  return <span className={`status status-${type}`}><span className="status-dot"/>{children}</span>
}

function PageHeader({ eyebrow, title, subtitle, children }: { eyebrow?: string; title: string; subtitle: string; children?: ReactNode }) {
  return <div className="page-header"><div><div className="eyebrow">{eyebrow}</div><h1>{title}</h1><p>{subtitle}</p></div>{children && <div className="header-actions">{children}</div>}</div>
}

const nav = [
  { id: 'dashboard', label: 'Command Center', icon: 'grid' as IconName },
  { id: 'inventory', label: 'Inventory & Lots', icon: 'boxes' as IconName },
  { id: 'formulation', label: 'Formulation Studio', icon: 'flask' as IconName },
  { id: 'dispensing', label: 'Dispensing & eBMR', icon: 'scale' as IconName },
  { id: 'quality', label: 'Quality & Release', icon: 'shield' as IconName, count: 8 },
  { id: 'trace', label: 'Traceability', icon: 'trace' as IconName },
]

const roles = ['Admin', 'Warehouse Operator', 'R&D Chemist', 'Shopfloor Operator', 'QC Lab Analyst', 'QA Manager'] as const
type Role = typeof roles[number]

const roleProfiles: Record<Role, { landing: string; label: string; detail: string; initials: string }> = {
  Admin: { landing: 'dashboard', label: 'Full system oversight', detail: 'All controlled modules and administrative permissions are available.', initials: 'MC' },
  'Warehouse Operator': { landing: 'inventory', label: 'Warehouse workspace', detail: 'Receive materials, manage locations, and review FEFO and quarantine alerts.', initials: 'GR' },
  'R&D Chemist': { landing: 'formulation', label: 'R&D controlled workspace', detail: 'Create formula revisions and route specifications through approval.', initials: 'SB' },
  'Shopfloor Operator': { landing: 'dispensing', label: 'Shopfloor tablet mode', detail: 'Large touch controls and the active dispensing sequence are prioritized.', initials: 'GR' },
  'QC Lab Analyst': { landing: 'quality', label: 'Laboratory testing workspace', detail: 'Record controlled results and submit completed samples to QA.', initials: 'LB' },
  'QA Manager': { landing: 'quality', label: 'QA disposition workspace', detail: 'Review completed records and apply legally binding release signatures.', initials: 'ER' },
}

function Sidebar({ page, setPage, collapsed, setCollapsed, mobileOpen, setMobileOpen, role }: { page: string; setPage: (p:string)=>void; collapsed:boolean; setCollapsed:(v:boolean)=>void; mobileOpen:boolean; setMobileOpen:(v:boolean)=>void; role:Role }) {
  return <aside className={`sidebar ${collapsed ? 'collapsed' : ''} ${mobileOpen ? 'mobile-open' : ''}`}>
    <div className="brand"><div className="brand-mark">B</div>{!collapsed && <div><strong>Beauty Italy</strong><span>Factory ERP</span></div>}<button className="mobile-close" onClick={()=>setMobileOpen(false)}><Icon name="close"/></button></div>
    <div className="nav-section-label">{!collapsed ? role === 'Admin' ? 'ALL OPERATIONS' : `${role.toUpperCase()} VIEW` : '•••'}</div>
    <nav>{nav.map(item => <button key={item.id} className={`nav-item ${page === item.id ? 'active' : ''}`} onClick={()=>{setPage(item.id);setMobileOpen(false)}} title={item.label}>
      <Icon name={item.icon}/>{!collapsed && <><span>{item.label}</span>{item.count && <b>{item.count}</b>}</>}
    </button>)}</nav>
    <div className="sidebar-spacer"/>
    {!collapsed && <div className="compliance-card"><div className="compliance-icon"><Icon name="shield" size={16}/></div><div><strong>GMP Controlled</strong><span>ISO 22716 compliant</span></div><Status type="approved">Live</Status></div>}
    <button className="collapse-button" onClick={()=>setCollapsed(!collapsed)}><Icon name="chevron"/>{!collapsed && <span>Collapse sidebar</span>}</button>
  </aside>
}

function Topbar({ setMobileOpen, role, setRole }: { setMobileOpen:(v:boolean)=>void; role:Role; setRole:(role:Role)=>void }) {
  const [roleOpen, setRoleOpen] = useState(false)
  return <header className="topbar">
    <button className="menu-button" onClick={()=>setMobileOpen(true)}><Icon name="menu"/></button>
    <div className="search"><Icon name="search" size={17}/><input aria-label="Global search" placeholder="Search batches, lots, products…"/><kbd>⌘ K</kbd></div>
    <div className="topbar-right">
      <button className="warehouse"><span className="warehouse-dot"/><span>IT · Main Warehouse</span><Icon name="chevron" size={14}/></button>
      <button className="icon-button notification"><Icon name="bell"/><span/></button>
      <div className="role-switcher">
        <button className="user-profile" onClick={()=>setRoleOpen(!roleOpen)} aria-expanded={roleOpen}>
          <div className="avatar">{roleProfiles[role].initials}</div><div><strong>{role === 'Admin' ? 'Marco Conti' : role}</strong><span>Active role · <b>{role}</b></span></div><Icon name="chevron" size={14}/>
        </button>
        {roleOpen && <div className="role-menu">
          <div className="role-menu-head"><span>SWITCH ACTIVE ROLE</span><small>Permissions and workspace will adapt</small></div>
          {roles.map(item=><button key={item} className={role===item?'active':''} onClick={()=>{setRole(item);setRoleOpen(false)}}>
            <span className="role-avatar">{roleProfiles[item].initials}</span><span><strong>{item}</strong><small>{roleProfiles[item].label}</small></span>{role===item&&<Icon name="check" size={15}/>}
          </button>)}
          <div className="role-menu-foot"><Icon name="shield" size={14}/>RBAC policy · GMP-SOP-017</div>
        </div>}
      </div>
    </div>
  </header>
}

const metrics = [
  { label: 'Active batches on lines', value: '4', detail: '4 batches running now', delta: '2 near completion', icon: 'scale' as IconName, tone: 'blue' },
  { label: 'Lots in quarantine', value: '18', detail: 'Needs urgent QC sampling', delta: '6 due today', icon: 'clock' as IconName, tone: 'amber' },
  { label: 'Expiring lots · FEFO alerts', value: '7', detail: '3 expire within 30 days', delta: 'Review FEFO', icon: 'alert' as IconName, tone: 'rose' },
  { label: 'Pending QA release approvals', value: '8', detail: '€142k controlled inventory', delta: '2 overdue', icon: 'shield' as IconName, tone: 'green' },
]

const lineData = [
  { line: 'L01', product: 'HydraGlow Serum 30ml', batch: 'B-25-0412', start: '06:30', end: '14:00', progress: 74, status: 'Running', tone: 'approved' as const },
  { line: 'L02', product: 'Velvet Matte Lipstick 04', batch: 'B-25-0414', start: '07:00', end: '15:30', progress: 54, status: 'Running', tone: 'approved' as const },
  { line: 'L03', product: 'Anti-Aging Cream 50ml', batch: 'B-25-0415', start: '08:15', end: '17:00', progress: 31, status: 'QC Hold', tone: 'warning' as const },
  { line: 'L04', product: 'Micellar Cleanser 200ml', batch: 'B-25-0416', start: '10:30', end: '18:00', progress: 12, status: 'Changeover', tone: 'info' as const },
]

function Dashboard({ navigate, notify }: { navigate:(p:string)=>void; notify:(s:string)=>void }) {
  return <>
    <PageHeader eyebrow="PHASE 1 · CONTROLLED CORE" title="Operations Command Center" subtitle="Monday, 14 April 2025 · Morning shift">
      <div className="live-pill"><span/> Live operations</div><Button variant="secondary" icon="download">Export shift report</Button>
    </PageHeader>
    <div className="metrics-grid">{metrics.map((m,i)=><div className="metric-card" key={m.label}><div className={`metric-icon ${m.tone}`}><Icon name={m.icon}/></div><div className="metric-top"><span>{m.label}</span><Icon name="more" size={17}/></div><strong className="metric-value">{m.value}</strong><div className="metric-bottom"><span>{m.detail}</span><b className={i === 0 ? 'positive' : i === 3 ? 'danger-text' : ''}>{m.delta}</b></div></div>)}</div>
    <div className="section-card quick-actions"><div className="section-heading"><div><h2>Quick actions</h2><p>Start a controlled operation</p></div></div><div className="quick-grid">
      <button onClick={()=>notify('New goods receipt started')}><span className="quick-icon"><Icon name="plus"/></span><span><strong>New goods receipt</strong><small>Receive supplier materials</small></span><Icon name="chevron" size={16}/></button>
      <button onClick={()=>navigate('dispensing')}><span className="quick-icon"><Icon name="scale"/></span><span><strong>Start batch dispensing</strong><small>Open weighing station</small></span><Icon name="chevron" size={16}/></button>
      <button onClick={()=>navigate('quality')}><span className="quick-icon"><Icon name="flask"/></span><span><strong>Log QC sample</strong><small>Record laboratory result</small></span><Icon name="chevron" size={16}/></button>
      <button onClick={()=>navigate('trace')} className="recall"><span className="quick-icon"><Icon name="trace"/></span><span><strong>Emergency recall trace</strong><small>Forward / backward trace</small></span><Icon name="chevron" size={16}/></button>
    </div></div>
    <div className="dashboard-lower">
      <div className="section-card production-card"><div className="section-heading"><div><h2>Today’s production lines</h2><p>Live MES status · Shift A</p></div><button className="text-button">View schedule <Icon name="arrow" size={15}/></button></div>
        <div className="timeline-head"><span>Line / Product</span><div><span>06:00</span><span>09:00</span><span>12:00</span><span>15:00</span><span>18:00</span></div><span>Status</span></div>
        {lineData.map((line)=><div className="timeline-row" key={line.line}><div className="line-name"><b>{line.line}</b><span><strong>{line.product}</strong><small>{line.batch}</small></span></div><div className="timeline-track"><span className="now-line"/><div className={`timeline-bar bar-${line.tone}`} style={{width:`${line.progress}%`}}><span>{line.start}</span><b>{line.progress}%</b><span>{line.end}</span></div></div><Status type={line.tone}>{line.status}</Status></div>)}
      </div>
      <div className="section-card attention-card"><div className="section-heading"><div><h2>Needs attention</h2><p>Prioritized by risk</p></div><span className="count-badge">6</span></div>
        {[['QC overdue','RM-RET-24018','2h overdue','danger'],['Expiry approaching','LOT-GLY-8821','18 days','warning'],['Scale calibration','WS-03','Due today','warning'],['QA signature','B-25-0398','Waiting 46m','info']].map((a)=><button key={a[1]} className="attention-item"><span className={`attention-mark ${a[3]}`}/><span><strong>{a[0]}</strong><small>{a[1]}</small></span><b>{a[2]}</b><Icon name="chevron" size={15}/></button>)}
      </div>
    </div>
  </>
}

const lots = [
  { code:'RM-00142', name:'Vegetable Glycerin 99.5%', lot:'GLY-8821-IT', expiry:'02 May 2025', location:'WH-A · A03 · S12', status:'Approved', qty:'1,240.00', unit:'kg', fefo:'18 days' },
  { code:'RM-00028', name:'Retinol 10% Encapsulated', lot:'RET-24018', expiry:'18 Jun 2025', location:'WH-Q · Q01 · S04', status:'Quarantine', qty:'18.50', unit:'kg', fefo:'65 days' },
  { code:'RM-00193', name:'Hyaluronic Acid LMW', lot:'HA-25-0031', expiry:'04 Mar 2026', location:'WH-A · A01 · S08', status:'Approved', qty:'42.75', unit:'kg', fefo:'' },
  { code:'PK-00414', name:'Glass Dropper Bottle 30ml', lot:'BOT-250322', expiry:'—', location:'WH-P · P07 · S02', status:'Approved', qty:'24,600', unit:'pcs', fefo:'' },
  { code:'RM-00201', name:'Natural Rose Fragrance', lot:'RF-11884', expiry:'28 Apr 2025', location:'WH-R · R02 · S10', status:'Rejected', qty:'8.25', unit:'kg', fefo:'14 days' },
  { code:'RM-00056', name:'Niacinamide USP', lot:'NIA-24091', expiry:'22 Dec 2026', location:'WH-A · A02 · S03', status:'Approved', qty:'320.00', unit:'kg', fefo:'' },
]

function Inventory() {
  const [selected, setSelected] = useState<typeof lots[0] | null>(null)
  return <>
    <PageHeader eyebrow="WAREHOUSE MANAGEMENT" title="Inventory & Lot Ledger" subtitle="Real-time stock across all controlled storage locations">
      <Button variant="secondary" icon="download">Export</Button><Button icon="plus">New goods receipt</Button>
    </PageHeader>
    <div className="summary-strip"><div><span>Total SKUs</span><strong>1,284</strong></div><div><span>Inventory value</span><strong>€2.84M</strong></div><div><span>Quarantined</span><strong className="warning-text">18 lots</strong></div><div><span>Expiring &lt; 30d</span><strong className="danger-text">3 lots</strong></div><div className="sync"><span className="live-dot"/>Synced 32s ago</div></div>
    <div className="section-card table-card"><div className="table-toolbar"><div className="search table-search"><Icon name="search" size={16}/><input placeholder="Search item, lot, location…"/></div><div className="table-buttons"><Button variant="secondary" icon="filter">Filters <span className="filter-count">2</span></Button><select aria-label="Warehouse"><option>All warehouses</option><option>Main warehouse</option><option>Quarantine</option></select></div></div>
      <div className="data-table-wrap"><table className="data-table"><thead><tr><th><input type="checkbox" aria-label="Select all"/></th><th>Item code</th><th>Product / Material</th><th>Lot number</th><th>Expiry date</th><th>Storage location</th><th>Status</th><th className="number">Available qty</th><th>Unit</th><th/></tr></thead><tbody>
        {lots.map((lot)=><tr key={lot.lot} onClick={()=>setSelected(lot)}><td><input type="checkbox" aria-label={`Select ${lot.lot}`} onClick={e=>e.stopPropagation()}/></td><td><code>{lot.code}</code></td><td><strong>{lot.name}</strong></td><td><button className="lot-link">{lot.lot}</button></td><td><div className={lot.fefo ? 'expiry-date' : ''}>{lot.expiry}{lot.fefo && <small><Icon name="alert" size={12}/>{lot.fefo} left</small>}</div></td><td><span className="location">{lot.location}</span></td><td><Status type={lot.status === 'Approved' ? 'approved' : lot.status === 'Quarantine' ? 'warning' : 'danger'}>{lot.status}</Status></td><td className="number"><strong>{lot.qty}</strong></td><td>{lot.unit}</td><td><Icon name="chevron" size={15}/></td></tr>)}
      </tbody></table></div><div className="table-footer"><span>Showing 1–6 of 1,284 lots</span><div><Button variant="secondary">Previous</Button><Button variant="secondary">Next</Button></div></div>
    </div>
    {selected && <><div className="drawer-overlay" onClick={()=>setSelected(null)}/><aside className="drawer"><div className="drawer-head"><div><span>LOT DETAILS</span><h2>{selected.lot}</h2><p>{selected.name}</p></div><button className="icon-button" onClick={()=>setSelected(null)}><Icon name="close"/></button></div><div className="drawer-status"><Status type={selected.status === 'Approved' ? 'approved' : selected.status === 'Quarantine' ? 'warning' : 'danger'}>{selected.status}</Status><span>Last updated 14 Apr, 09:42</span></div><div className="drawer-section"><h3>Lot information</h3><dl><div><dt>Item code</dt><dd>{selected.code}</dd></div><div><dt>Available quantity</dt><dd>{selected.qty} {selected.unit}</dd></div><div><dt>Location</dt><dd>{selected.location}</dd></div><div><dt>Expiry date</dt><dd>{selected.expiry}</dd></div><div><dt>Supplier</dt><dd>Cosmetica Materie S.p.A.</dd></div><div><dt>Source document</dt><dd className="link">GR-2025-00842</dd></div></dl></div><div className="drawer-section"><h3>Immutable movement ledger</h3><div className="ledger">{[['14 Apr · 09:42','QC status changed','Quarantine → Approved','Elena R.'],['12 Apr · 14:18','QC sample created','QC-S-004182','Luca B.'],['12 Apr · 08:31','Goods received','+ 1,250.00 kg','Marco D.'],['12 Apr · 08:29','Lot created','PO-2025-0189','System']].map((e,i)=><div className="ledger-entry" key={e[0]}><span className={i===0?'active':''}><Icon name={i===0?'check':'file'} size={13}/></span><div><small>{e[0]}</small><strong>{e[1]}</strong><p>{e[2]} · {e[3]}</p></div></div>)}</div></div><div className="drawer-foot"><Button variant="secondary" icon="download">Download ledger</Button><Button>Open full record</Button></div></aside></>}
  </>
}

const ingredients = [
  ['A','Aqua','Purified Water EP','Solvent','67.950','679.50'],
  ['A','Glycerin','Vegetable Glycerin 99.5%','Humectant','5.000','50.00'],
  ['A','Niacinamide','Niacinamide USP','Active','4.000','40.00'],
  ['B','Cetearyl Alcohol','Lanette O','Emulsifier','6.000','60.00'],
  ['B','Caprylic/Capric Triglyceride','MCT Oil','Emollient','8.000','80.00'],
  ['B','Butyrospermum Parkii Butter','Shea Butter Refined','Emollient','5.000','50.00'],
  ['C','Retinol','Encapsulated Retinol 10%','Active','1.000','10.00'],
  ['C','Phenoxyethanol, Ethylhexylglycerin','Euxyl PE 9010','Preservative','1.000','10.00'],
  ['C','Parfum','Signature Bloom 114','Fragrance','2.050','20.50'],
]

function Formulation() {
  return <>
    <PageHeader eyebrow="R&D · PRODUCT LIFECYCLE MANAGEMENT" title="Anti-Aging Renewal Cream" subtitle="Formula code: FRM-SC-0042 · Owner: Dr. Sofia Bianchi">
      <Button variant="secondary">Compare versions</Button><Button icon="file">Create new version</Button>
    </PageHeader>
    <div className="formula-banner"><div className="formula-version"><span>ACTIVE VERSION</span><button>v2.4 <Status type="approved">Approved</Status><Icon name="chevron" size={15}/></button></div><div><span>Target batch size</span><label><input value="1,000" readOnly/><b>kg</b></label></div><div><span>Effective from</span><strong>01 Apr 2025</strong></div><div><span>Last modified</span><strong>28 Mar 2025 · S. Bianchi</strong></div></div>
    <div className="formula-layout"><div className="section-card formula-table-card"><div className="section-heading"><div><h2>Formula composition</h2><p>Quantities recalculated for 1,000 kg batch size</p></div><Button variant="secondary" icon="plus">Add ingredient</Button></div>
      <div className="data-table-wrap"><table className="data-table formula-table"><thead><tr><th>Phase</th><th>INCI name</th><th>Technical name</th><th>Function</th><th className="number">% w/w</th><th className="number">Scale qty (kg)</th><th/></tr></thead><tbody>{ingredients.map((x,i)=><tr key={x[1]}><td><span className={`phase phase-${x[0].toLowerCase()}`}>{x[0]}</span></td><td><strong>{x[1]}</strong></td><td>{x[2]}</td><td><span className="function-tag">{x[3]}</span></td><td className="number"><input value={x[4]} readOnly/></td><td className="number"><strong>{x[5]}</strong></td><td><Icon name="more" size={16}/></td></tr>)}</tbody><tfoot><tr><td colSpan={4}><strong>Total formula</strong><span>Required total: 100.000%</span></td><td className="number total-valid"><Icon name="check" size={15}/><strong>100.000%</strong></td><td className="number"><strong>1,000.00</strong></td><td/></tr></tfoot></table></div>
    </div><aside className="formula-side"><div className="section-card workflow-card"><div className="section-heading"><div><h2>Approval workflow</h2><p>GMP signature chain</p></div><Status type="approved">Complete</Status></div>
      {[['Formulator','Dr. Sofia Bianchi','SB','26 Mar · 16:42'],['Lab Supervisor','Luca Bellini','LB','27 Mar · 11:08'],['QA Head','Elena Rossi','ER','28 Mar · 09:14']].map((s,i)=><div className="signature" key={s[0]}><div className="signature-line"><span><Icon name="check" size={13}/></span>{i<2&&<i/>}</div><div className="avatar small">{s[2]}</div><div><small>{s[0]}</small><strong>{s[1]}</strong><p>Electronically signed · {s[3]}</p></div></div>)}
    </div><div className="section-card formula-meta"><h2>Formula controls</h2><dl><div><dt>Regulatory region</dt><dd>EU / EEA</dd></div><div><dt>Product type</dt><dd>Leave-on face cream</dd></div><div><dt>Allergen review</dt><dd><Status type="approved">Passed</Status></dd></div><div><dt>IFRA compliance</dt><dd><Status type="approved">Compliant</Status></dd></div></dl><Button variant="secondary" icon="file" className="full-button">View specification</Button></div></aside></div>
  </>
}

function Dispensing({ notify }: { notify:(s:string)=>void }) {
  const [step, setStep] = useState(2)
  const [signed, setSigned] = useState(false)
  const [isManualMode, setIsManualMode] = useState(false)
  const [manualWeight, setManualWeight] = useState('50.042')
  const [manualReason, setManualReason] = useState('Physical scale reading verified manually (discrepancy override)')
  const [liveWeight, setLiveWeight] = useState(50.042)
  const [tareWeight, setTareWeight] = useState(0.0)

  const activeIng = ingredients[step - 1]
  const targetWeight = parseFloat(activeIng[5]) || 50.0
  const tolPercent = 0.50
  const minAllowed = targetWeight * (1 - tolPercent / 100)
  const maxAllowed = targetWeight * (1 + tolPercent / 100)

  const effectiveWeight = isManualMode 
    ? (parseFloat(manualWeight) || 0) 
    : Math.max(0, parseFloat((liveWeight - tareWeight).toFixed(3)))

  const delta = effectiveWeight - targetWeight
  const deltaPercent = targetWeight > 0 ? (delta / targetWeight) * 100 : 0
  const isInTolerance = effectiveWeight >= minAllowed && effectiveWeight <= maxAllowed && effectiveWeight > 0

  const handleSelectStep = (s: number) => {
    setStep(s)
    const t = parseFloat(ingredients[s - 1][5]) || 50.0
    setLiveWeight(t)
    setManualWeight(t.toFixed(3))
    setSigned(false)
  }

  const handleTare = () => {
    setTareWeight(liveWeight)
    notify(`Scale Tared: ${liveWeight.toFixed(3)} kg container weight deducted`)
  }

  return <div className="shopfloor">
    <PageHeader eyebrow="ELECTRONIC BATCH RECORD · WEIGHING STATION WS-02" title="Batch Dispensing (Tablet Mode)" subtitle="B-25-0415 · Anti-Aging Renewal Cream · 1,000 kg">
      <Status type="approved">Scale connected</Status><Button variant="secondary">Pause session</Button>
    </PageHeader>
    <div className="dispense-progress">
      <div><span>Progress</span><strong>{step} of 9 materials ({Math.round(((step - 1) / 9) * 100)}%)</strong></div>
      <div className="progress-track"><span style={{width:`${((step - 1) / 9) * 100}%`}}/></div>
      <b>{Math.round(((step - 1) / 9) * 100)}%</b>
    </div>
    
    <div className="dispense-layout">
      <aside className="step-list">
        <div className="step-list-head"><h2>Dispensing sequence</h2><span>Phase {activeIng[0]}</span></div>
        {ingredients.map((x,i)=><button key={x[1]} className={`${i===step-1?'active':''} ${i<step-1?'done':''}`} onClick={()=>handleSelectStep(i+1)}>
          <span>{i<step-1?<Icon name="check" size={14}/>:i+1}</span>
          <div><strong>{x[1]}</strong><small>Target {x[5]} kg (Phase {x[0]})</small></div>
          {i===step-1&&<i/>}
        </button>)}
      </aside>

      <div className="weigh-workspace">
        <div className="material-head">
          <div className="material-phase">{activeIng[0]}</div>
          <div>
            <span>MATERIAL {step} OF 9</span>
            <h2>{activeIng[1]}</h2>
            <p>{activeIng[2]} · RM-{step === 2 ? '00142' : '00'+step}</p>
          </div>
          <Status type="approved">Correct material</Status>
        </div>

        <div className="scan-panel">
          <label>Scan raw material lot barcode</label>
          <div>
            <Icon name="scan" size={25}/>
            <input value={step===2?'GLY-8821-IT':'PW-250415'} placeholder="Scan or enter lot barcode…" readOnly/>
            <span><Icon name="check" size={16}/> Verified</span>
          </div>
          <p>FEFO lot selected · Expires 02 May 2026 · Location WH-A / A03 / S12</p>
        </div>

        {/* Reading Mode Selector & Tare */}
        <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', background: '#f8fafc', padding: '10px 14px', borderRadius: '8px', border: '1px solid #e2e8f0', gap: '8px' }}>
          <div style={{ display: 'flex', alignItems: 'center', gap: '8px' }}>
            <span style={{ fontSize: '11px', fontWeight: 700, color: '#475569' }}>Mode:</span>
            <button 
              type="button" 
              style={{ padding: '4px 10px', fontSize: '11px', fontWeight: 600, borderRadius: '4px', border: '1px solid #cbd5e1', background: !isManualMode ? '#1e293b' : 'white', color: !isManualMode ? 'white' : '#64748b' }}
              onClick={()=>setIsManualMode(false)}
            >
              Scale Stream (Auto)
            </button>
            <button 
              type="button" 
              style={{ padding: '4px 10px', fontSize: '11px', fontWeight: 600, borderRadius: '4px', border: '1px solid #cbd5e1', background: isManualMode ? '#f59e0b' : 'white', color: isManualMode ? '#78350f' : '#64748b' }}
              onClick={()=>{ setIsManualMode(true); setManualWeight(effectiveWeight.toFixed(3)); }}
            >
              ✏️ Manual Override (تعديل يدوي)
            </button>
          </div>
          {!isManualMode && (
            <div style={{ display: 'flex', alignItems: 'center', gap: '6px', fontSize: '10px' }}>
              <span>Tare: <strong>{tareWeight.toFixed(3)} kg</strong></span>
              <button type="button" onClick={handleTare} style={{ padding: '3px 8px', fontSize: '10px', border: '1px solid #cbd5e1', borderRadius: '4px', background: 'white' }}>Tare Scale</button>
              {tareWeight > 0 && <button type="button" onClick={()=>setTareWeight(0)} style={{ padding: '3px 6px', fontSize: '10px', color: '#be123c', border: '1px solid #fecdd3', borderRadius: '4px', background: '#fff1f2' }}>Reset</button>}
            </div>
          )}
        </div>

        {/* Manual Override Inputs */}
        {isManualMode && (
          <div style={{ background: '#fffbeb', border: '1px solid #fde68a', borderRadius: '8px', padding: '12px 14px' }}>
            <div style={{ display: 'flex', alignItems: 'center', gap: '8px', marginBottom: '8px' }}>
              <Icon name="alert" size={16} />
              <strong style={{ fontSize: '12px', color: '#92400e' }}>Manual Weight Override Active (إدخال وتعديل يدوي)</strong>
            </div>
            <div style={{ display: 'grid', gridTemplateColumns: '1fr 2fr', gap: '10px' }}>
              <div>
                <label style={{ display: 'block', fontSize: '10px', fontWeight: 700, color: '#78350f', marginBottom: '4px' }}>Actual Weight (kg) *</label>
                <input 
                  type="number" 
                  step="0.001" 
                  style={{ width: '100%', height: '36px', fontSize: '15px', fontWeight: 'bold', padding: '0 8px', border: '2px solid #f59e0b', borderRadius: '6px' }}
                  value={manualWeight} 
                  onChange={e=>setManualWeight(e.target.value)}
                />
              </div>
              <div>
                <label style={{ display: 'block', fontSize: '10px', fontWeight: 700, color: '#78350f', marginBottom: '4px' }}>Justification *</label>
                <input 
                  style={{ width: '100%', height: '36px', fontSize: '11px', padding: '0 8px', border: '1px solid #d97706', borderRadius: '6px' }}
                  value={manualReason} 
                  onChange={e=>setManualReason(e.target.value)}
                />
              </div>
            </div>
          </div>
        )}

        <div className="weight-grid">
          <div>
            <span>Target weight</span>
            <strong>{targetWeight.toFixed(3)}<small> kg</small></strong>
            <p>Tolerance ±0.5% · ({minAllowed.toFixed(3)} – {maxAllowed.toFixed(3)} kg)</p>
          </div>
          <div className="actual-weight" style={{ background: isInTolerance ? '#ecfdf5' : '#fffbeb', borderColor: isInTolerance ? '#a7f3d0' : '#fde68a' }}>
            <span>{isManualMode ? 'Manual Weight' : 'Live scale weight'} <i>STABLE</i></span>
            <strong>{effectiveWeight.toFixed(3)}<small> kg</small></strong>
            <p style={{ color: isInTolerance ? '#047857' : '#b45309' }}>
              {isInTolerance ? <><Icon name="check" size={16}/> Within tolerance ({deltaPercent >= 0 ? '+' : ''}{deltaPercent.toFixed(3)}%)</> : `Outside tolerance (${deltaPercent.toFixed(2)}%)`}
            </p>
          </div>
        </div>

        <div className="operator-note">
          <Icon name="shield"/>
          <div>
            <strong>GMP verification {isInTolerance ? 'passed' : 'pending'}</strong>
            <p>{isManualMode ? 'Manual reading recorded with justification for audit log.' : 'Material identity, lot status, and live weight tolerance verified.'}</p>
          </div>
        </div>

        <div className="dispense-actions">
          <Button variant="secondary" onClick={()=>notify('Deviation logged')}>Report deviation</Button>
          <Button 
            icon="check" 
            className="confirm-dispense" 
            onClick={()=>{
              setSigned(true)
              notify(`Step ${step} signed (${effectiveWeight.toFixed(3)} kg - ${isManualMode ? 'Manual Override' : 'Scale'})`)
              if (step < 9) handleSelectStep(step + 1)
            }}
          >
            {signed ? 'Re-confirm & Update Weight' : (isManualMode ? 'Confirm Manual Weight' : 'Confirm & Sign Dispense')}
          </Button>
        </div>
      </div>

      <aside className="station-side">
        <div className="station-card">
          <span>WEIGHING STATION</span>
          <h3>WS-02</h3>
          <div className="scale-visual">
            <Icon name="scale" size={30}/>
            <strong>{effectiveWeight.toFixed(3)} kg</strong>
            <small>METTLER TOLEDO · {isManualMode ? 'Manual Override' : 'Live Stream'}</small>
          </div>
          <dl>
            <div><dt>Calibration</dt><dd className="positive">Valid</dd></div>
            <div><dt>Due date</dt><dd>30 Apr 2026</dd></div>
            <div><dt>Operator</dt><dd>G. Romano</dd></div>
          </dl>
        </div>

        {/* Quick Simulator Bar */}
        <div style={{ background: 'white', border: '1px solid #e2e8f0', borderRadius: '8px', padding: '12px' }}>
          <strong style={{ fontSize: '11px', display: 'block', marginBottom: '8px' }}>🛠️ Test Simulator:</strong>
          <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '6px' }}>
            <button type="button" onClick={()=>{ setLiveWeight(targetWeight); setManualWeight(targetWeight.toFixed(3)); }} style={{ padding: '4px', fontSize: '10px', border: '1px solid #cbd5e1', borderRadius: '4px', background: '#f8fafc' }}>Exact Target</button>
            <button type="button" onClick={()=>{ const v = parseFloat((targetWeight*0.95).toFixed(3)); setLiveWeight(v); setManualWeight(v.toFixed(3)); }} style={{ padding: '4px', fontSize: '10px', border: '1px solid #cbd5e1', borderRadius: '4px', background: '#f8fafc' }}>-5% Under</button>
            <button type="button" onClick={()=>{ const v = parseFloat((targetWeight*1.03).toFixed(3)); setLiveWeight(v); setManualWeight(v.toFixed(3)); }} style={{ padding: '4px', fontSize: '10px', border: '1px solid #cbd5e1', borderRadius: '4px', background: '#f8fafc' }}>+3% Over</button>
            <button type="button" onClick={()=>{ setLiveWeight(0); setManualWeight('0.000'); }} style={{ padding: '4px', fontSize: '10px', border: '1px solid #cbd5e1', borderRadius: '4px', background: '#f8fafc' }}>Zero</button>
          </div>
        </div>

        <div className="audit-note">
          <Icon name="lock" size={16}/>
          <span>All actions are time-stamped and recorded in the immutable eBMR audit trail.</span>
        </div>
        {signed&&<div className="signed-notice"><Icon name="check"/><div><strong>Step signed</strong><span>Giulia Romano · just now</span></div></div>}
      </aside>
    </div>
  </div>
}

const tests = [
  ['Appearance','Homogeneous cream','—','Homogeneous cream','Pass'],
  ['Color','White to ivory','—','Ivory','Pass'],
  ['Odor','Characteristic','—','Characteristic','Pass'],
  ['pH @ 25°C','5.50','6.50','5.86','Pass'],
  ['Viscosity @ 25°C','18,000 cP','24,000 cP','21,450 cP','Pass'],
  ['Microbiology','Within ISO 17516 limits','—','Conforms','Pass'],
]

function Quality({ notify }: { notify:(s:string)=>void }) {
  const [modal,setModal]=useState(false)
  return <>
    <PageHeader eyebrow="QUALITY CONTROL · SAMPLE QC-S-004182" title="Batch Quality Review" subtitle="B-25-0398 · HydraGlow Serum 30ml · Finished product">
      <Button variant="secondary" icon="file">View batch record</Button><Status type="warning">Pending QA release</Status>
    </PageHeader>
    <div className="qa-banner"><Icon name="shield"/><div><strong>Segregation of duties enforced</strong><span>QC testing completed by Luca Bellini. Final release requires an authorized QA role.</span></div><span>21 CFR Part 11 controls active</span></div>
    <div className="quality-layout"><div><div className="section-card test-card"><div className="section-heading"><div><h2>Physical & chemical testing</h2><p>Specification: SPEC-FG-0094 v3.1</p></div><Status type="approved">6 of 6 passed</Status></div>
      <div className="data-table-wrap"><table className="data-table tests-table"><thead><tr><th>Parameter</th><th>Minimum / Requirement</th><th>Maximum</th><th>Actual result</th><th>Result</th></tr></thead><tbody>{tests.map(t=><tr key={t[0]}><td><strong>{t[0]}</strong></td><td>{t[1]}</td><td>{t[2]}</td><td><input value={t[3]} readOnly/></td><td><Status type="approved">{t[4]}</Status></td></tr>)}</tbody></table></div><div className="tested-by"><div className="avatar small">LB</div><div><span>Tested and electronically signed by</span><strong>Luca Bellini · QC Analyst</strong></div><time>14 Apr 2025 · 10:24 CET</time></div></div>
      <div className="section-card micro-card"><div className="section-heading"><div><h2>Microbiology clearance</h2><p>7-day incubation · External laboratory</p></div><label className="toggle"><input type="checkbox" defaultChecked/><span/></label></div><div className="micro-grid"><div><span>Total aerobic count</span><strong>&lt; 10 CFU/g</strong><Status type="approved">Pass</Status></div><div><span>Yeast & mold</span><strong>&lt; 10 CFU/g</strong><Status type="approved">Pass</Status></div><div><span>Specified pathogens</span><strong>Absent</strong><Status type="approved">Pass</Status></div><button className="coa-upload"><Icon name="file"/><span><strong>COA-QC-004182.pdf</strong><small>412 KB · uploaded 14 Apr</small></span><Icon name="download" size={17}/></button></div></div>
    </div><aside className="release-panel"><div className="release-head"><span>FINAL QA DISPOSITION</span><Icon name="shield" size={24}/></div><div className="release-check"><span><Icon name="check" size={14}/></span><div><strong>Batch record complete</strong><p>48/48 process steps signed</p></div></div><div className="release-check"><span><Icon name="check" size={14}/></span><div><strong>QC specification passed</strong><p>6/6 parameters conform</p></div></div><div className="release-check"><span><Icon name="check" size={14}/></span><div><strong>Microbiology cleared</strong><p>COA attached and verified</p></div></div><div className="release-check"><span><Icon name="check" size={14}/></span><div><strong>Deviations reviewed</strong><p>No open critical deviations</p></div></div><div className="release-summary"><span>Quantity to release</span><strong>14,820 <small>units</small></strong><p>Inventory value · €86,420</p></div><Button className="full-button release-button" icon="lock" onClick={()=>setModal(true)}>Release batch</Button><button className="reject-link">Reject or place on hold</button><p className="release-warning"><Icon name="alert" size={14}/> This action is permanent and requires your electronic signature.</p></aside></div>
    {modal&&<div className="modal-overlay"><div className="modal"><div className="modal-icon"><Icon name="lock"/></div><h2>Electronic signature required</h2><p>You are releasing batch <strong>B-25-0398</strong>. This signature is legally binding and will be added to the permanent audit trail under 21 CFR Part 11 controls.</p><label>Signed in as<input value="Marco Conti · QA Manager" readOnly/></label><label>Password re-entry<input type="password" placeholder="Re-enter your password" autoFocus/></label><label>Approval reason<select defaultValue="commercial"><option value="commercial">Authorized for Commercial Distribution</option><option value="conditional">Conditional release per approved deviation</option></select></label><div className="signature-meaning"><input type="checkbox" defaultChecked/><span>I understand that this electronic signature is the legal equivalent of my handwritten signature.</span></div><div className="timestamp"><Icon name="clock" size={15}/>14 April 2025 · 11:46:22 CET · Europe/Rome</div><div className="modal-actions"><Button variant="secondary" onClick={()=>setModal(false)}>Cancel</Button><Button icon="lock" onClick={()=>{setModal(false);notify('Batch B-25-0398 released successfully')}}>Sign & release batch</Button></div></div></div>}
  </>
}

const traceNodes = [
  { col:0, y:1, type:'SUPPLIER PO', title:'PO-2025-0189', meta:'Cosmetica Materie S.p.A.', status:'Received' },
  { col:1, y:0, type:'RAW MATERIAL LOT', title:'GLY-8821-IT', meta:'Vegetable Glycerin · 1,250 kg', status:'Approved' },
  { col:1, y:2, type:'RAW MATERIAL LOT', title:'NIA-24091', meta:'Niacinamide USP · 320 kg', status:'Approved' },
  { col:2, y:1, type:'PRODUCTION ORDER', title:'MO-2025-0415', meta:'Anti-Aging Cream · 1,000 kg', status:'Complete' },
  { col:3, y:1, type:'BULK BATCH', title:'BULK-25-0415', meta:'Manufactured 12 Apr 2025', status:'Released' },
  { col:4, y:1, type:'FINISHED LOT', title:'FG-250415-A', meta:'18,400 units · 50ml', status:'Released' },
  { col:5, y:0, type:'CUSTOMER DELIVERY', title:'DN-2025-1184', meta:'Bellezza Retail · 8,000 units', status:'Delivered' },
  { col:5, y:2, type:'CUSTOMER DELIVERY', title:'DN-2025-1191', meta:'Milano Cosmesi · 6,400 units', status:'In transit' },
]

function Traceability() {
  const [mode,setMode]=useState('Forward')
  return <>
    <PageHeader eyebrow="GMP TRACEABILITY" title="Forward / Backward Trace" subtitle="End-to-end genealogy across suppliers, production, inventory, and customers">
      <Button variant="secondary" icon="download">Export trace report</Button><Button variant="danger" icon="alert">Initiate recall</Button>
    </PageHeader>
    <div className="trace-toolbar"><div className="segmented"><button className={mode==='Backward'?'active':''} onClick={()=>setMode('Backward')}>← Backward trace</button><button className={mode==='Forward'?'active':''} onClick={()=>setMode('Forward')}>Forward trace →</button></div><div className="search trace-search"><Icon name="search"/><input value="FG-250415-A" readOnly/><kbd>Finished goods lot</kbd></div><Button>Run trace</Button><span className="trace-speed"><Icon name="clock" size={14}/>Complete in 0.84s</span></div>
    <div className="trace-stats"><div><span>Materials traced</span><strong>9</strong></div><div><span>Production records</span><strong>1</strong></div><div><span>Finished units</span><strong>18,400</strong></div><div><span>Customers impacted</span><strong>2</strong></div><div><Status type="approved">Complete genealogy</Status></div></div>
    <div className="section-card trace-canvas"><div className="trace-legend"><span><i className="supplier"/>Supplier</span><span><i className="material"/>Material</span><span><i className="production"/>Production</span><span><i className="finished"/>Finished goods</span><span><i className="customer"/>Customer</span></div>
      <div className="trace-columns">{['SUPPLIER','RAW MATERIALS','PRODUCTION','BULK BATCH','FINISHED GOODS','DELIVERIES'].map((x,i)=><div key={x}><span>0{i+1}</span>{x}</div>)}</div>
      <div className="trace-map">
        <svg viewBox="0 0 1200 390" preserveAspectRatio="none" aria-hidden="true"><path d="M105 195 C150 195 150 75 220 75 M105 195 C150 195 150 315 220 315 M315 75 C355 75 360 195 420 195 M315 315 C355 315 360 195 420 195 M515 195H620 M715 195H820 M915 195 C970 195 965 75 1020 75 M915 195 C970 195 965 315 1020 315"/></svg>
        {traceNodes.map((n,i)=><button key={n.title} className={`trace-node node-col-${n.col} node-y-${n.y}`}><span className="node-icon"><Icon name={n.col===0?'file':n.col===1?'boxes':n.col===5?'arrow':n.col===2?'scale':'shield'} size={17}/></span><span><small>{n.type}</small><strong>{n.title}</strong><p>{n.meta}</p></span><Status type={n.status==='In transit'?'info':'approved'}>{n.status}</Status>{i===5&&<b className="selected-ring"/>}</button>)}
      </div>
      <div className="trace-footer"><span><Icon name="shield" size={16}/>Genealogy verified · No data gaps detected</span><span>Source: Immutable lot ledger · Snapshot 14 Apr 2025 11:44 CET</span></div>
    </div>
  </>
}

export default function App() {
  const [page, setPage] = useState('dashboard')
  const [role, setRoleState] = useState<Role>('Admin')
  const [collapsed, setCollapsed] = useState(false)
  const [mobileOpen, setMobileOpen] = useState(false)
  const [toast, setToast] = useState('')
  const notify = (message:string) => { setToast(message); window.setTimeout(()=>setToast(''), 2600) }
  const switchRole = (nextRole:Role) => {
    setRoleState(nextRole)
    setPage(roleProfiles[nextRole].landing)
    notify(`Workspace switched to ${nextRole}`)
  }
  const content: Record<string, ReactNode> = {
    dashboard: <Dashboard navigate={setPage} notify={notify}/>,
    inventory: <Inventory/>,
    formulation: <Formulation/>,
    dispensing: <Dispensing notify={notify}/>,
    quality: <Quality notify={notify}/>,
    trace: <Traceability/>,
  }
  return <div className="app-shell">
    <Sidebar page={page} setPage={setPage} collapsed={collapsed} setCollapsed={setCollapsed} mobileOpen={mobileOpen} setMobileOpen={setMobileOpen} role={role}/>
    <div className="main-shell"><Topbar setMobileOpen={setMobileOpen} role={role} setRole={switchRole}/><div className="role-context"><span><Icon name={role === 'Admin' ? 'grid' : role === 'Warehouse Operator' ? 'boxes' : role === 'R&D Chemist' ? 'flask' : role === 'Shopfloor Operator' ? 'scale' : 'shield'} size={14}/>{roleProfiles[role].label}</span><p>{roleProfiles[role].detail}</p><b>RBAC ACTIVE</b></div><main>{content[page]}</main></div>
    {mobileOpen&&<div className="mobile-overlay" onClick={()=>setMobileOpen(false)}/>}
    {toast&&<div className="toast"><span><Icon name="check" size={15}/></span>{toast}</div>}
  </div>
}
