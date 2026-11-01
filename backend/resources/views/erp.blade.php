<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Beauty Italy Factory ERP · Phase 1 Controlled Core</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700;1,9..40,400&display=swap" rel="stylesheet">
  
  <!-- React 18 & Babel Standalone -->
  <script src="https://unpkg.com/react@18/umd/react.production.min.js" crossorigin></script>
  <script src="https://unpkg.com/react-dom@18/umd/react-dom.production.min.js" crossorigin></script>
  <script src="https://unpkg.com/@babel/standalone/babel.min.js"></script>

  <style>
    :root {
      --navy-950:#0f172a; --navy-900:#172033; --navy-800:#1e293b; --navy-700:#334155;
      --slate-600:#475569; --slate-500:#64748b; --slate-400:#94a3b8; --slate-300:#cbd5e1;
      --slate-200:#e2e8f0; --slate-150:#e9edf2; --slate-100:#f1f5f9; --slate-50:#f8fafc;
      --white:#fff; --indigo:#4f46e5; --indigo-light:#eef2ff;
      --green:#10b981; --green-dark:#047857; --green-light:#ecfdf5;
      --amber:#f59e0b; --amber-dark:#b45309; --amber-light:#fffbeb;
      --rose:#ef4444; --rose-dark:#be123c; --rose-light:#fff1f2; --blue:#2563eb; --blue-light:#eff6ff;
      --border:#e4e8ee; --shadow:0 1px 2px rgba(15,23,42,.04),0 1px 3px rgba(15,23,42,.04);
      --font-sans: 'DM Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    }
    * { box-sizing: border-box; margin: 0; padding: 0; scrollbar-width: thin; scrollbar-color: #cbd5e1 transparent; }
    body { min-width: 320px; min-height: 100vh; background: #f6f7f9; color: #172033; font-family: var(--font-sans); }
    button, input, select { font-family: inherit; outline: none; }
    button { cursor: pointer; }

    .app-shell { min-height:100vh; display:flex; }
    .sidebar { position:fixed; inset:0 auto 0 0; width:240px; background:var(--navy-950); color:var(--slate-300); display:flex; flex-direction:column; padding:0 12px 12px; z-index:40; transition:width .2s ease, transform .25s ease; }
    .sidebar.collapsed { width:72px; }
    .brand { height:68px; display:flex; align-items:center; gap:11px; padding:0 10px; border-bottom:1px solid rgba(255,255,255,.08); }
    .brand-mark { width:33px; height:33px; flex:0 0 33px; display:grid; place-items:center; border-radius:9px; background:linear-gradient(145deg,#818cf8,#4f46e5); color:white; font-weight:700; font-size:17px; box-shadow:0 0 0 1px rgba(255,255,255,.15) inset; }
    .brand div:nth-child(2) { display:flex; flex-direction:column; line-height:1.2; white-space:nowrap; }
    .brand strong { font-size:14px; color:white; letter-spacing:-.1px; }
    .brand span { font-size:10px; color:var(--slate-400); text-transform:uppercase; letter-spacing:1px; margin-top:3px; }
    .nav-section-label { height:49px; display:flex; align-items:flex-end; padding:0 11px 10px; font-size:9px; font-weight:700; letter-spacing:1.4px; color:#64748b; white-space:nowrap; }
    nav { display:flex; flex-direction:column; gap:3px; }
    .nav-item { height:40px; width:100%; border:0; background:transparent; color:#94a3b8; border-radius:7px; display:flex; align-items:center; gap:11px; padding:0 11px; font-size:13px; font-weight:500; transition:.15s; white-space:nowrap; }
    .nav-item svg { flex:0 0 auto; }
    .nav-item:hover { color:#e2e8f0; background:rgba(255,255,255,.055); }
    .nav-item.active { background:rgba(99,102,241,.18); color:#c7d2fe; box-shadow:inset 2px 0 #818cf8; }
    .nav-item span { flex:1; text-align:left; }
    .nav-item b { background:rgba(245,158,11,.18); color:#fbbf24; font-size:10px; padding:2px 6px; border-radius:10px; }
    .sidebar-spacer { flex:1; }
    .compliance-card { margin:8px 2px 12px; padding:11px 9px; border:1px solid rgba(255,255,255,.08); background:rgba(255,255,255,.035); border-radius:8px; display:grid; grid-template-columns:28px 1fr; align-items:center; gap:8px; }
    .compliance-icon { width:28px; height:28px; border-radius:6px; color:#34d399; background:rgba(16,185,129,.12); display:grid; place-items:center; }
    .compliance-card>div:nth-child(2) { display:flex; flex-direction:column; }
    .compliance-card strong { color:#e2e8f0; font-size:11px; }
    .compliance-card>div span { font-size:9px; margin-top:2px; }
    .compliance-card>.status { grid-column:1/-1; justify-self:start; }
    .collapse-button { border:0; background:transparent; color:#64748b; display:flex; align-items:center; height:36px; gap:10px; padding:0 12px; font-size:11px; white-space:nowrap; }
    .collapse-button:hover { color:#cbd5e1; }
    .collapsed .collapse-button svg { transform:rotate(180deg); }
    .mobile-close { display:none; }
    
    .main-shell { margin-left:240px; width:calc(100% - 240px); min-width:0; transition:margin .2s,width .2s; }
    .collapsed+.main-shell { margin-left:72px; width:calc(100% - 72px); }
    .topbar { height:60px; background:rgba(255,255,255,.96); border-bottom:1px solid var(--border); display:flex; align-items:center; padding:0 28px; position:sticky; top:0; z-index:30; backdrop-filter:blur(10px); }
    .search { height:34px; display:flex; align-items:center; gap:9px; color:var(--slate-400); background:var(--slate-50); border:1px solid var(--slate-200); border-radius:7px; padding:0 10px; width:340px; }
    .search input { border:0; background:transparent; flex:1; min-width:0; color:var(--navy-900); font-size:12px; }
    .search input::placeholder { color:var(--slate-400); }
    kbd { font-family:inherit; font-size:9px; color:var(--slate-500); border:1px solid var(--slate-200); border-radius:4px; padding:2px 5px; background:white; white-space:nowrap; }
    .topbar-right { margin-left:auto; display:flex; align-items:center; height:100%; gap:12px; }
    .warehouse { height:34px; display:flex; align-items:center; gap:7px; border:1px solid var(--slate-200); background:white; border-radius:7px; padding:0 10px; color:var(--slate-600); font-size:11px; font-weight:600; }
    .warehouse svg { transform:rotate(90deg); color:var(--slate-400); }
    .warehouse-dot { width:6px; height:6px; border-radius:50%; background:var(--green); }
    .icon-button { width:34px; height:34px; border:1px solid var(--slate-200); background:white; color:var(--slate-600); border-radius:7px; display:grid; place-items:center; }
    .notification { position:relative; }
    .notification>span { position:absolute; width:6px; height:6px; background:var(--rose); border:2px solid white; box-sizing:content-box; border-radius:50%; right:5px; top:5px; }
    .role-switcher { position:relative; height:100%; display:flex; align-items:center; }
    .user-profile { height:38px; border:0; border-left:1px solid var(--slate-200); padding:0 0 0 14px; background:white; display:flex; align-items:center; gap:8px; text-align:left; }
    .avatar { width:31px; height:31px; border-radius:50%; background:#e0e7ff; color:#4338ca; display:grid; place-items:center; font-size:10px; font-weight:700; }
    .user-profile>div:nth-child(2) { display:flex; flex-direction:column; min-width:125px; }
    .user-profile strong { font-size:11px; color:var(--navy-800); }
    .user-profile span { font-size:9px; color:var(--slate-500); margin-top:1px; }
    .user-profile span b { color:#4338ca; }
    .user-profile>svg { transform:rotate(90deg); color:var(--slate-400); }
    .menu-button { display:none; }
    .role-menu { position:absolute; z-index:70; width:310px; right:0; top:55px; background:white; border:1px solid var(--border); border-radius:9px; box-shadow:0 18px 45px rgba(15,23,42,.18); overflow:hidden; padding:6px; }
    .role-menu-head { padding:9px 10px 10px; display:flex; flex-direction:column; border-bottom:1px solid var(--slate-100); margin-bottom:4px; }
    .role-menu-head span { font-size:8px; letter-spacing:.9px; color:var(--indigo); font-weight:700; }
    .role-menu-head small { font-size:8px; color:var(--slate-500); margin-top:3px; }
    .role-menu>button { width:100%; min-height:49px; border:0; background:white; border-radius:6px; display:flex; align-items:center; gap:9px; padding:6px 8px; color:var(--navy-800); text-align:left; }
    .role-menu>button:hover { background:var(--slate-50); }
    .role-menu>button.active { background:var(--indigo-light); color:#3730a3; }
    .role-avatar { width:27px; height:27px; flex:0 0 27px; display:grid; place-items:center; border-radius:50%; background:var(--slate-100); color:var(--slate-600); font-size:8px; font-weight:700; }
    .role-menu>button.active .role-avatar { background:#c7d2fe; color:#3730a3; }
    .role-menu>button>span:nth-child(2) { display:flex; flex-direction:column; flex:1; }
    .role-menu>button strong { font-size:9px; }
    .role-menu>button small { font-size:7px; color:var(--slate-500); margin-top:2px; }
    .role-menu-foot { margin:5px -6px -6px; padding:9px 13px; background:var(--slate-50); border-top:1px solid var(--border); color:var(--slate-500); font-size:8px; display:flex; align-items:center; gap:6px; }
    .role-context { min-height:34px; padding:0 30px; display:flex; align-items:center; gap:10px; background:var(--indigo-light); color:#3730a3; border-bottom:1px solid #dfe3ff; }
    .role-context>span { display:flex; align-items:center; gap:6px; font-size:9px; font-weight:700; white-space:nowrap; }
    .role-context p { font-size:8px; color:#5b5aa5; }
    .role-context b { margin-left:auto; font-size:7px; letter-spacing:.7px; background:white; border:1px solid #c7d2fe; padding:3px 6px; border-radius:4px; white-space:nowrap; }
    
    main { padding:27px 30px 40px; max-width:1700px; margin:0 auto; }
    .page-header { display:flex; align-items:flex-end; justify-content:space-between; gap:24px; margin-bottom:22px; }
    .eyebrow { color:#6366f1; font-size:9px; font-weight:700; letter-spacing:1.25px; margin-bottom:6px; min-height:12px; }
    .page-header h1 { font-size:23px; line-height:1.25; letter-spacing:-.5px; font-weight:700; color:var(--navy-950); }
    .page-header p { font-size:11px; color:var(--slate-500); margin-top:5px; }
    .header-actions { display:flex; align-items:center; justify-content:flex-end; gap:8px; flex-wrap:wrap; }
    .btn { height:34px; border-radius:6px; border:1px solid transparent; padding:0 12px; display:inline-flex; align-items:center; justify-content:center; gap:7px; font-size:11px; font-weight:600; white-space:nowrap; transition:.15s; }
    .btn-primary { background:var(--navy-800); color:white; box-shadow:0 1px 2px rgba(15,23,42,.15); }
    .btn-primary:hover { background:var(--navy-950); }
    .btn-secondary { background:white; border-color:var(--slate-200); color:var(--navy-700); }
    .btn-secondary:hover { background:var(--slate-50); }
    .btn-danger { background:var(--rose); color:white; }
    .btn-danger:hover { background:#dc2626; }
    
    .status { display:inline-flex; align-items:center; gap:5px; width:max-content; border-radius:999px; padding:3px 7px; font-size:9px; font-weight:700; line-height:1.2; white-space:nowrap; }
    .status-dot { width:5px; height:5px; border-radius:50%; background:currentColor; }
    .status-approved { color:var(--green-dark); background:var(--green-light); }
    .status-warning { color:var(--amber-dark); background:var(--amber-light); }
    .status-danger { color:var(--rose-dark); background:var(--rose-light); }
    .status-info { color:#1d4ed8; background:var(--blue-light); }
    .status-neutral { color:var(--slate-600); background:var(--slate-100); }
    
    .live-pill { display:flex; gap:6px; align-items:center; font-size:10px; font-weight:600; color:var(--green-dark); background:var(--green-light); padding:7px 10px; border-radius:6px; }
    .live-pill span,.live-dot { width:6px;height:6px;border-radius:50%;background:var(--green);box-shadow:0 0 0 3px rgba(16,185,129,.12); }
    .metrics-grid { display:grid; grid-template-columns:repeat(4,1fr); gap:12px; margin-bottom:14px; }
    .metric-card,.section-card { background:white; border:1px solid var(--border); border-radius:9px; box-shadow:var(--shadow); }
    .metric-card { padding:15px 16px; display:grid; grid-template-columns:36px 1fr; grid-template-rows:auto auto auto; column-gap:11px; }
    .metric-icon { grid-row:1/3; width:34px; height:34px; border-radius:7px; display:grid; place-items:center; }
    .metric-icon.blue { color:var(--blue);background:var(--blue-light); }
    .metric-icon.amber { color:var(--amber-dark);background:var(--amber-light); }
    .metric-icon.rose { color:var(--rose);background:var(--rose-light); }
    .metric-icon.green { color:var(--green-dark);background:var(--green-light); }
    .metric-top { display:flex; align-items:center; justify-content:space-between; color:var(--slate-500); font-size:10px; font-weight:500; }
    .metric-value { font-size:25px; color:var(--navy-950); line-height:1.2; margin-top:3px; }
    .metric-bottom { grid-column:1/-1; border-top:1px solid var(--slate-100); margin-top:12px; padding-top:10px; display:flex; justify-content:space-between; font-size:9px; color:var(--slate-500); }
    .positive { color:var(--green-dark)!important; }
    .danger-text { color:var(--rose-dark)!important; }
    .warning-text { color:var(--amber-dark)!important; }
    
    .section-heading { min-height:58px; padding:0 16px; display:flex; align-items:center; justify-content:space-between; gap:12px; border-bottom:1px solid var(--border); }
    .section-heading h2 { font-size:13px; font-weight:700; color:var(--navy-900); }
    .section-heading p { color:var(--slate-500); font-size:9px; margin-top:3px; }
    .quick-actions { margin-bottom:14px; }
    .quick-grid { display:grid; grid-template-columns:repeat(4,1fr); }
    .quick-grid>button { height:70px; border:0; border-right:1px solid var(--border); background:white; display:flex; align-items:center; text-align:left; gap:10px; padding:0 15px; color:var(--navy-700); }
    .quick-grid>button:last-child { border:0; }
    .quick-grid>button:hover { background:var(--slate-50); }
    .quick-icon { width:31px;height:31px;border:1px solid var(--slate-200);border-radius:6px;display:grid;place-items:center;color:var(--indigo); }
    .quick-grid button>span:nth-child(2) { display:flex; flex-direction:column; flex:1; }
    .quick-grid strong { font-size:10px; }
    .quick-grid small { font-size:9px;color:var(--slate-500);margin-top:3px; }
    .dashboard-lower { display:grid; grid-template-columns:minmax(0,2.15fr) minmax(260px,.85fr); gap:14px; }
    .timeline-head { display:grid;grid-template-columns:210px 1fr 85px;gap:12px;background:var(--slate-50);padding:9px 16px;color:var(--slate-500);font-size:8px;font-weight:700;text-transform:uppercase;letter-spacing:.6px; }
    .timeline-head>div { display:flex;justify-content:space-between; }
    .timeline-row { min-height:67px; display:grid;grid-template-columns:210px 1fr 85px;gap:12px;align-items:center;padding:0 16px;border-top:1px solid var(--slate-100); }
    .timeline-row:first-of-type { border-top:0; }
    .line-name { display:flex;align-items:center;gap:10px; }
    .line-name>b { width:31px;height:31px;border-radius:6px;background:var(--slate-100);display:grid;place-items:center;font-size:10px;color:var(--slate-600); }
    .line-name>span { display:flex;flex-direction:column;min-width:0; }
    .line-name strong { font-size:10px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap; }
    .line-name small { font-size:8px;color:var(--slate-500);margin-top:3px; }
    .timeline-track { height:26px;position:relative;background:repeating-linear-gradient(90deg,var(--slate-100) 0,var(--slate-100) 1px,transparent 1px,transparent 25%); }
    .timeline-bar { position:absolute;top:4px;left:0;height:18px;border-radius:4px;display:flex;align-items:center;justify-content:space-between;padding:0 7px;font-size:7px;min-width:90px; }
    .timeline-bar b { font-size:8px; }
    .bar-approved { background:#d1fae5;color:#047857; }
    .bar-warning { background:#fef3c7;color:#b45309; }
    .bar-info { background:#dbeafe;color:#1d4ed8; }
    .now-line { position:absolute;height:34px;border-left:1px dashed var(--rose);left:62%;top:-4px;z-index:2; }
    .attention-card { overflow:hidden; }
    .count-badge { width:20px;height:20px;border-radius:50%;background:var(--rose-light);color:var(--rose-dark);font-size:9px;display:grid;place-items:center;font-weight:700; }
    .attention-item { width:100%;height:62px;border:0;border-top:1px solid var(--slate-100);background:white;display:flex;align-items:center;gap:10px;padding:0 14px;text-align:left; }
    .attention-item:hover { background:var(--slate-50); }
    .attention-mark { width:7px;height:30px;border-radius:5px;background:var(--slate-300); }
    .attention-mark.danger { background:var(--rose); }
    .attention-mark.warning { background:var(--amber); }
    .attention-mark.info { background:var(--blue); }
    .attention-item>span:nth-child(2) { display:flex;flex-direction:column;flex:1; }
    .attention-item strong { font-size:10px; }
    .attention-item small { font-size:8px;color:var(--slate-500);margin-top:3px; }
    .attention-item>b { font-size:8px;color:var(--slate-500); }

    .summary-strip { min-height:65px;background:white;border:1px solid var(--border);border-radius:9px;display:flex;align-items:center;margin-bottom:14px;box-shadow:var(--shadow); }
    .summary-strip>div { min-width:150px;padding:0 20px;border-right:1px solid var(--border);display:flex;flex-direction:column; }
    .summary-strip span { font-size:9px;color:var(--slate-500); }
    .summary-strip strong { font-size:15px;margin-top:3px; }
    .summary-strip .sync { margin-left:auto;min-width:0;border:0;flex-direction:row;align-items:center;gap:7px;color:var(--slate-500);font-size:9px; }
    .table-card { overflow:hidden; }
    .table-toolbar { height:58px;display:flex;align-items:center;justify-content:space-between;padding:0 14px;border-bottom:1px solid var(--border); }
    .table-search { width:280px; }
    .data-table-wrap { overflow-x:auto; }
    .data-table { width:100%;border-collapse:collapse;font-size:10px;white-space:nowrap; }
    .data-table th { height:36px;background:var(--slate-50);color:var(--slate-500);font-size:8px;text-transform:uppercase;letter-spacing:.55px;text-align:left;padding:0 11px;border-bottom:1px solid var(--border); }
    .data-table td { height:52px;padding:0 11px;border-bottom:1px solid var(--slate-100);color:var(--slate-600); }
    .data-table tbody tr { cursor:pointer; }
    .data-table tbody tr:hover { background:#fafbfc; }
    .data-table td strong { color:var(--navy-800); }
    .data-table .number { text-align:right; }
    .data-table code { font-family:inherit;color:var(--slate-500);font-size:9px; }
    .lot-link { border:0;background:transparent;color:#4f46e5;text-decoration:underline;text-decoration-color:#c7d2fe;text-underline-offset:2px;font-size:10px;font-weight:600; }
    .location { padding:4px 6px;background:var(--slate-100);border-radius:4px;font-size:9px; }
    .expiry-date { color:var(--rose-dark);font-weight:600;display:flex;flex-direction:column; }
    .expiry-date small { font-size:8px;color:var(--rose);display:flex;align-items:center;gap:3px;margin-top:2px; }
    .table-footer { height:52px;display:flex;align-items:center;justify-content:space-between;padding:0 14px;color:var(--slate-500);font-size:9px; }
    
    .drawer-overlay { position:fixed;inset:0;background:rgba(15,23,42,.28);z-index:60;backdrop-filter:blur(1px); }
    .drawer { position:fixed;inset:0 0 0 auto;width:440px;max-width:95vw;background:white;z-index:61;box-shadow:-12px 0 35px rgba(15,23,42,.15);overflow-y:auto;display:flex;flex-direction:column; }
    .drawer-head { padding:23px 22px 17px;display:flex;justify-content:space-between;border-bottom:1px solid var(--border); }
    .drawer-head>div>span { font-size:8px;letter-spacing:1px;color:var(--indigo);font-weight:700; }
    .drawer-head h2 { font-size:19px;margin-top:5px; }
    .drawer-head p { font-size:10px;color:var(--slate-500);margin-top:3px; }
    .drawer-status { height:50px;padding:0 22px;background:var(--slate-50);display:flex;align-items:center;justify-content:space-between;border-bottom:1px solid var(--border); }
    .drawer-status>span:last-child { font-size:8px;color:var(--slate-500); }
    .drawer-section { padding:18px 22px;border-bottom:1px solid var(--border); }
    .drawer-section h3 { font-size:11px;margin-bottom:14px; }
    .drawer-section dl { display:grid;grid-template-columns:1fr 1fr;gap:15px; }
    .drawer-section dl div { display:flex;flex-direction:column; }
    .drawer-section dt { color:var(--slate-500);font-size:8px; }
    .drawer-section dd { font-size:10px;font-weight:600;margin-top:3px; }
    .ledger-entry { display:grid;grid-template-columns:24px 1fr;position:relative;min-height:62px; }
    .ledger-entry>span { width:20px;height:20px;border-radius:50%;background:var(--slate-100);color:var(--slate-500);display:grid;place-items:center;z-index:1; }
    .ledger-entry>span.active { background:var(--green-light);color:var(--green-dark); }
    .ledger-entry:not(:last-child)::before { content:"";position:absolute;left:9px;top:20px;height:42px;border-left:1px solid var(--slate-200); }
    .ledger-entry>div { display:flex;flex-direction:column; }
    .ledger-entry small { font-size:8px;color:var(--slate-400); }
    .ledger-entry strong { font-size:10px;margin-top:2px; }
    .ledger-entry p { font-size:8px;color:var(--slate-500);margin-top:2px; }
    .drawer-foot { margin-top:auto;padding:14px 22px;display:flex;justify-content:flex-end;gap:8px;position:sticky;bottom:0;background:white;border-top:1px solid var(--border); }

    .formula-banner { min-height:66px;background:var(--navy-950);color:white;border-radius:9px;margin-bottom:14px;display:flex;align-items:center;padding:0 18px;box-shadow:var(--shadow); }
    .formula-banner>div { min-width:180px;padding:0 22px;border-right:1px solid rgba(255,255,255,.12);display:flex;flex-direction:column; }
    .formula-banner>div:first-child { padding-left:0; }
    .formula-banner>div:last-child { border:0; }
    .formula-banner span { font-size:8px;color:#94a3b8;letter-spacing:.5px;text-transform:uppercase; }
    .formula-banner strong { font-size:10px;margin-top:5px; }
    .formula-version button { border:0;background:transparent;color:white;display:flex;align-items:center;gap:7px;padding:4px 0 0;font-size:14px;font-weight:700; }
    .formula-layout { display:grid;grid-template-columns:minmax(0,1fr) 300px;gap:14px; }
    .phase { width:23px;height:23px;display:grid;place-items:center;border-radius:5px;font-weight:700; }
    .phase-a { background:#dbeafe;color:#1d4ed8; }
    .phase-b { background:#f3e8ff;color:#7e22ce; }
    .phase-c { background:#fef3c7;color:#b45309; }
    .function-tag { background:var(--slate-100);padding:3px 6px;border-radius:4px;font-size:8px; }
    .signature { min-height:76px;display:grid;grid-template-columns:20px 29px 1fr;align-items:center;padding:0 14px;position:relative; }
    .signature-line { align-self:stretch;display:flex;align-items:center;position:relative; }
    .signature-line>span { width:18px;height:18px;background:var(--green);color:white;border-radius:50%;display:grid;place-items:center;z-index:1; }
    .signature-line i { position:absolute;left:8px;top:47px;height:47px;border-left:1px solid var(--slate-200); }
    .avatar.small { width:26px;height:26px;font-size:8px; }
    .formula-meta { padding:16px; }
    .formula-meta dl>div { height:34px;display:flex;align-items:center;justify-content:space-between;border-bottom:1px solid var(--slate-100); }

    .dispense-progress { height:55px;background:white;border:1px solid var(--border);border-radius:8px;display:grid;grid-template-columns:150px 1fr 40px;align-items:center;gap:15px;padding:0 17px;margin-bottom:14px; }
    .progress-track { height:7px;background:var(--slate-100);border-radius:5px;overflow:hidden; }
    .progress-track span { display:block;height:100%;background:var(--indigo);border-radius:5px; }
    .dispense-layout { display:grid;grid-template-columns:230px minmax(420px,1fr) 220px;gap:14px;align-items:start; }
    .step-list,.weigh-workspace,.station-card { background:white;border:1px solid var(--border);border-radius:9px;box-shadow:var(--shadow);overflow:hidden; }
    .step-list>button { width:100%;height:52px;border:0;border-bottom:1px solid var(--slate-100);background:white;display:flex;align-items:center;gap:9px;padding:0 13px;text-align:left;position:relative; }
    .step-list>button.active { background:var(--indigo-light); }
    .step-list>button.active>span { background:var(--indigo);border-color:var(--indigo);color:white; }
    .step-list>button.done>span { color:var(--green-dark);background:var(--green-light);border-color:#a7f3d0; }
    .material-head { min-height:85px;display:flex;align-items:center;gap:12px;padding:0 18px;border-bottom:1px solid var(--border); }
    .material-phase { width:39px;height:39px;border-radius:7px;background:var(--blue-light);color:#1d4ed8;display:grid;place-items:center;font-weight:700;font-size:16px; }
    .scan-panel { margin:17px 18px 0;padding:14px;background:var(--slate-50);border:1px solid var(--slate-200);border-radius:7px; }
    .scan-panel>div { height:54px;border:2px solid #818cf8;background:white;border-radius:7px;display:flex;align-items:center;padding:0 13px;color:var(--indigo);box-shadow:0 0 0 3px rgba(99,102,241,.08); }
    .scan-panel input { border:0;flex:1;padding:0 12px;font-size:15px;font-weight:700;min-width:0; }
    .weight-grid { display:grid;grid-template-columns:1fr 1.3fr;gap:12px;margin:14px 18px; }
    .weight-grid>div { height:116px;border:1px solid var(--slate-200);border-radius:8px;padding:13px;display:flex;flex-direction:column;justify-content:center; }
    .weight-grid .actual-weight { border:2px solid #6ee7b7;background:var(--green-light); }
    .confirm-dispense { min-width:190px; height:43px; background:var(--green); font-size:12px; }
    .confirm-dispense:hover { background:#059669; }
    .scale-visual { height:115px;background:var(--navy-950);border-radius:7px;margin:12px 0;display:flex;flex-direction:column;align-items:center;justify-content:center;color:#a7f3d0; }

    .qa-banner { min-height:51px;background:#eef2ff;border:1px solid #c7d2fe;color:#3730a3;border-radius:8px;display:flex;align-items:center;gap:10px;padding:0 14px;margin-bottom:14px; }
    .quality-layout { display:grid;grid-template-columns:minmax(0,1fr) 300px;gap:14px;align-items:start; }
    .release-panel { background:var(--navy-950);color:white;border-radius:9px;overflow:hidden;padding-bottom:14px; }
    .release-head { height:59px;background:rgba(255,255,255,.04);border-bottom:1px solid rgba(255,255,255,.1);padding:0 16px;display:flex;align-items:center;justify-content:space-between;color:#a7f3d0; }
    .release-check { min-height:58px;display:flex;align-items:center;gap:9px;padding:0 16px; }
    .release-button { margin:0 14px;width:calc(100% - 28px);background:var(--green); }
    .modal-overlay { position:fixed;inset:0;background:rgba(15,23,42,.55);z-index:80;display:grid;place-items:center;backdrop-filter:blur(3px);padding:18px; }
    .modal { width:430px;max-width:100%;background:white;border-radius:11px;padding:23px;box-shadow:0 20px 50px rgba(15,23,42,.25); }

    .trace-toolbar { background:white;border:1px solid var(--border);border-radius:9px;min-height:59px;display:flex;align-items:center;gap:10px;padding:0 13px;margin-bottom:10px; }
    .trace-map { height:390px;position:relative;background:linear-gradient(90deg,transparent calc(16.66% - 1px),var(--slate-100) 16.66%,transparent calc(16.66% + 1px),transparent calc(33.33% - 1px),var(--slate-100) 33.33%,transparent calc(33.33% + 1px),transparent calc(50% - 1px),var(--slate-100) 50%,transparent calc(50% + 1px),transparent calc(66.66% - 1px),var(--slate-100) 66.66%,transparent calc(66.66% + 1px),transparent calc(83.33% - 1px),var(--slate-100) 83.33%,transparent calc(83.33% + 1px)); }
    .trace-map>svg { position:absolute;inset:0;width:100%;height:100%; }
    .trace-map>svg path { fill:none;stroke:#cbd5e1;stroke-width:2;vector-effect:non-scaling-stroke; }
    .trace-node { position:absolute;width:15%;height:70px;transform:translate(-50%,-50%);background:white;border:1px solid var(--slate-200);border-radius:7px;box-shadow:0 2px 5px rgba(15,23,42,.07);display:grid;grid-template-columns:27px 1fr;align-items:center;text-align:left;padding:8px;gap:7px;color:var(--navy-800);z-index:2; }
    .node-col-0{left:8.33%}.node-col-1{left:25%}.node-col-2{left:41.66%}.node-col-3{left:58.33%}.node-col-4{left:75%}.node-col-5{left:91.66%}.node-y-0{top:19%}.node-y-1{top:50%}.node-y-2{top:81%}
    .toast { position:fixed;right:22px;bottom:22px;z-index:100;background:var(--navy-950);color:white;border-radius:8px;padding:11px 15px 11px 10px;display:flex;align-items:center;gap:9px;font-size:10px;box-shadow:0 10px 30px rgba(15,23,42,.25); }
  </style>
</head>
<body>
  <div id="root"></div>

  <script type="text/babel">
    @verbatim
    const { useState, useEffect } = React;

    const paths = {
      grid: <><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></>,
      boxes: <><path d="m21 8-9 5-9-5 9-5 9 5Z"/><path d="m3 8 9 5 9-5v8l-9 5-9-5V8Z"/><path d="M12 13v8"/></>,
      flask: <><path d="M9 3h6M10 3v6l-5 9a2 2 0 0 0 2 3h10a2 2 0 0 0 2-3l-5-9V3"/><path d="M7.5 15h9"/></>,
      scale: <><path d="M6 20h12M12 4v16M4 7h16M4 7l-2 6h4L4 7ZM20 7l-2 6h4l-2-6Z"/></>,
      shield: <><path d="M12 22s8-3.5 8-10V5l-8-3-8 3v7c0 6.5 8 10 8 10Z"/><path d="m9 12 2 2 4-4"/></>,
      trace: <><circle cx="5" cy="5" r="2"/><circle cx="19" cy="12" r="2"/><circle cx="5" cy="19" r="2"/><path d="M7 5h4a4 4 0 0 1 4 4v0a3 3 0 0 0 3 3M7 19h4a4 4 0 0 0 4-4v0a3 3 0 0 1 3-3"/></>,
      search: <><circle cx="11" cy="11" r="7"/><path d="m20 20-4-4"/></>,
      bell: <><path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9ZM10 21h4"/></>,
      chevron: <path d="m9 18 6-6-6-6"/>,
      plus: <><path d="M12 5v14M5 12h14"/></>,
      scan: <><path d="M4 7V4h3M17 4h3v3M20 17v3h-3M7 20H4v-3M7 9v6M10 9v6M14 9v6M17 9v6"/></>,
      check: <path d="m5 12 4 4L19 6"/>,
      clock: <><circle cx="12" cy="12" r="9"/><path d="M12 7v6l4 2"/></>,
      alert: <><path d="M12 3 2.5 20h19L12 3Z"/><path d="M12 9v5M12 17h.01"/></>,
      arrow: <><path d="M5 12h14M14 7l5 5-5 5"/></>,
      file: <><path d="M6 2h8l4 4v16H6V2Z"/><path d="M14 2v5h5M9 12h6M9 16h6"/></>,
      close: <><path d="m6 6 12 12M18 6 6 18"/></>,
      lock: <><rect x="4" y="10" width="16" height="11" rx="2"/><path d="M8 10V7a4 4 0 0 1 8 0v3"/></>,
    };

    function Icon({ name, size = 18 }) {
      return <svg width={size} height={size} viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round">{paths[name] || null}</svg>;
    }

    function Button({ children, variant = 'primary', icon, onClick, className = '' }) {
      return <button type="button" onClick={onClick} className={`btn btn-${variant} ${className}`}>{icon && <Icon name={icon} size={16}/>}<span>{children}</span></button>;
    }

    function Status({ type, children }) {
      return <span className={`status status-${type}`}><span className="status-dot"/>{children}</span>;
    }

    const nav = [
      { id: 'dashboard', label: 'Command Center', icon: 'grid' },
      { id: 'inventory', label: 'Inventory & Lots', icon: 'boxes' },
      { id: 'formulation', label: 'Formulation Studio', icon: 'flask' },
      { id: 'dispensing', label: 'Dispensing & eBMR', icon: 'scale' },
      { id: 'quality', label: 'Quality & Release', icon: 'shield', count: 1 },
      { id: 'trace', label: 'Traceability', icon: 'trace' },
    ];

    const roles = ['Admin', 'Warehouse Operator', 'R&D Chemist', 'Shopfloor Operator', 'QC Lab Analyst', 'QA Manager'];
    const roleProfiles = {
      Admin: { landing: 'dashboard', label: 'Full system oversight', detail: 'All controlled modules and administrative permissions are active on MySQL.', initials: 'MC' },
      'Warehouse Operator': { landing: 'inventory', label: 'Warehouse workspace', detail: 'Receive materials, manage locations, and review FEFO and quarantine alerts.', initials: 'GR' },
      'R&D Chemist': { landing: 'formulation', label: 'R&D controlled workspace', detail: 'Create formula revisions and route specifications through approval.', initials: 'SB' },
      'Shopfloor Operator': { landing: 'dispensing', label: 'Shopfloor tablet mode', detail: 'Large touch controls and the active dispensing sequence are prioritized.', initials: 'GR' },
      'QC Lab Analyst': { landing: 'quality', label: 'Laboratory testing workspace', detail: 'Record controlled results and submit completed samples to QA.', initials: 'LB' },
      'QA Manager': { landing: 'quality', label: 'QA disposition workspace', detail: 'Review completed records and apply legally binding release signatures.', initials: 'ER' },
    };

    function Dashboard({ navigate, notify }) {
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
              <div className="live-pill"><span/> MySQL Active</div>
              <Button variant="secondary" icon="file">Shift Report</Button>
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
                  <b className={i === 0 ? 'positive' : i === 3 ? 'danger-text' : ''}>{m.delta}</b>
                </div>
              </div>
            ))}
          </div>

          <div className="section-card quick-actions">
            <div className="section-heading"><div><h2>Quick actions</h2><p>Start a controlled GMP operation</p></div></div>
            <div className="quick-grid">
              <button onClick={() => navigate('inventory')}><span className="quick-icon"><Icon name="plus"/></span><span><strong>New goods receipt</strong><small>Receive supplier materials</small></span></button>
              <button onClick={() => navigate('dispensing')}><span className="quick-icon"><Icon name="scale"/></span><span><strong>Start batch dispensing</strong><small>Open weighing station</small></span></button>
              <button onClick={() => navigate('quality')}><span className="quick-icon"><Icon name="flask"/></span><span><strong>Log QC sample</strong><small>Record laboratory result</small></span></button>
              <button onClick={() => navigate('trace')}><span className="quick-icon"><Icon name="trace"/></span><span><strong>Emergency recall trace</strong><small>Forward / backward trace</small></span></button>
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

    function Inventory() {
      const [lots, setLots] = useState([]);
      const [summary, setSummary] = useState({});
      const [selected, setSelected] = useState(null);
      const [ledger, setLedger] = useState([]);

      useEffect(() => {
        fetch('/api/v1/lots')
          .then(res => res.json())
          .then(data => { setLots(data.lots); setSummary(data.summary); });
      }, []);

      const openLot = (lot) => {
        setSelected(lot);
        fetch(`/api/v1/lots/${lot.lot}/ledger`)
          .then(res => res.json())
          .then(data => setLedger(data.ledger || []));
      };

      return (
        <div>
          <div className="page-header">
            <div>
              <div className="eyebrow">WAREHOUSE MANAGEMENT</div>
              <h1>Inventory & Lot Ledger</h1>
              <p>Real-time stock ledger backed by MySQL</p>
            </div>
          </div>

          <div className="summary-strip">
            <div><span>Total SKUs</span><strong>{summary.totalSkus || '1,284'}</strong></div>
            <div><span>Inventory value</span><strong>{summary.inventoryValue || '€2.84M'}</strong></div>
            <div><span>Quarantined</span><strong className="warning-text">{summary.quarantined || '1 lot'}</strong></div>
            <div className="sync"><span className="live-dot"/>Live MySQL Sync</div>
          </div>

          <div className="section-card table-card">
            <div className="data-table-wrap">
              <table className="data-table">
                <thead>
                  <tr>
                    <th>Item code</th><th>Product / Material</th><th>Lot number</th><th>Expiry date</th><th>Storage location</th><th>Status</th><th className="number">Available qty</th><th>Unit</th>
                  </tr>
                </thead>
                <tbody>
                  {lots.map(lot => (
                    <tr key={lot.lot} onClick={() => openLot(lot)}>
                      <td><code>{lot.code}</code></td>
                      <td><strong>{lot.name}</strong></td>
                      <td><button className="lot-link">{lot.lot}</button></td>
                      <td>{lot.expiry} {lot.fefo && <small style={{color: '#be123c'}}>({lot.fefo})</small>}</td>
                      <td><span className="location">{lot.location}</span></td>
                      <td><Status type={lot.status === 'Approved' ? 'approved' : lot.status === 'Quarantine' ? 'warning' : 'danger'}>{lot.status}</Status></td>
                      <td className="number"><strong>{lot.qty}</strong></td>
                      <td>{lot.unit}</td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          </div>

          {selected && (
            <>
              <div className="drawer-overlay" onClick={() => setSelected(null)}/>
              <aside className="drawer">
                <div className="drawer-head">
                  <div><span>LOT DETAILS</span><h2>{selected.lot}</h2><p>{selected.name}</p></div>
                  <button className="icon-button" onClick={() => setSelected(null)}><Icon name="close"/></button>
                </div>
                <div className="drawer-section">
                  <h3>Lot information</h3>
                  <dl>
                    <div><dt>Item code</dt><dd>{selected.code}</dd></div>
                    <div><dt>Available quantity</dt><dd>{selected.qty} {selected.unit}</dd></div>
                    <div><dt>Location</dt><dd>{selected.location}</dd></div>
                    <div><dt>Expiry</dt><dd>{selected.expiry}</dd></div>
                    <div><dt>Supplier</dt><dd>{selected.supplier}</dd></div>
                    <div><dt>Source doc</dt><dd>{selected.sourceDocument}</dd></div>
                  </dl>
                </div>
                <div className="drawer-section">
                  <h3>Immutable movement ledger (GMP)</h3>
                  <div className="ledger">
                    {ledger.map((e, idx) => (
                      <div className="ledger-entry" key={idx}>
                        <span className={idx === 0 ? 'active' : ''}><Icon name="check" size={13}/></span>
                        <div><small>{e.date}</small><strong>{e.action}</strong><p>{e.detail} · {e.user}</p></div>
                      </div>
                    ))}
                  </div>
                </div>
              </aside>
            </>
          )}
        </div>
      );
    }

    function Formulation() {
      const [form, setForm] = useState(null);

      useEffect(() => {
        fetch('/api/v1/formulations/FRM-SC-0042')
          .then(res => res.json())
          .then(data => setForm(data));
      }, []);

      if (!form) return <div style={{padding: '40px', textAlign: 'center'}}>Loading formulation from MySQL...</div>;

      return (
        <div>
          <div className="page-header">
            <div>
              <div className="eyebrow">R&D · PRODUCT LIFECYCLE MANAGEMENT</div>
              <h1>{form.productName}</h1>
              <p>Formula code: {form.code} · Author: {form.author}</p>
            </div>
          </div>

          <div className="formula-banner">
            <div><span>ACTIVE VERSION</span><button>{form.version} <Status type="approved">{form.status}</Status></button></div>
            <div><span>Target batch size</span><strong>{form.targetBatchSize} kg</strong></div>
            <div><span>Effective from</span><strong>{form.effectiveDate}</strong></div>
          </div>

          <div className="formula-layout">
            <div className="section-card formula-table-card">
              <div className="section-heading"><div><h2>Formula composition</h2><p>Recalculated for 1,000 kg batch</p></div></div>
              <div className="data-table-wrap">
                <table className="data-table">
                  <thead>
                    <tr><th>Phase</th><th>INCI name</th><th>Technical name</th><th>Function</th><th className="number">% w/w</th><th className="number">Scale qty (kg)</th></tr>
                  </thead>
                  <tbody>
                    {form.lines.map((l, i) => (
                      <tr key={i}>
                        <td><span className={`phase phase-${l.phase.toLowerCase()}`}>{l.phase}</span></td>
                        <td><strong>{l.inci || l.tech}</strong></td>
                        <td>{l.tech}</td>
                        <td><span className="function-tag">{l.function}</span></td>
                        <td className="number">{parseFloat(l.percentage).toFixed(3)}%</td>
                        <td className="number"><strong>{parseFloat(l.scale_qty).toFixed(2)}</strong></td>
                      </tr>
                    ))}
                  </tbody>
                </table>
              </div>
            </div>

            <aside className="formula-side">
              <div className="section-card workflow-card" style={{padding: '16px'}}>
                <h2 style={{fontSize: '12px', marginBottom: '12px'}}>Approval workflow</h2>
                {form.approvals.map((s, i) => (
                  <div className="signature" key={i}>
                    <div className="signature-line"><span><Icon name="check" size={13}/></span></div>
                    <div className="avatar small">{s.initials}</div>
                    <div><small>{s.role}</small><strong>{s.name}</strong><p>Signed · {s.signedAt}</p></div>
                  </div>
                ))}
              </div>
            </aside>
          </div>
        </div>
      );
    }

    function Dispensing({ notify }) {
      const [signed, setSigned] = useState(false);

      const handleSign = () => {
        fetch('/api/v1/dispensing/sign', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ stepId: 2, actualWeight: 50.042, operator: 'Giulia Romano' })
        })
        .then(res => res.json())
        .then(data => {
          setSigned(true);
          notify(data.message);
        });
      };

      return (
        <div className="shopfloor">
          <div className="page-header">
            <div>
              <div className="eyebrow">ELECTRONIC BATCH RECORD · WEIGHING STATION WS-02</div>
              <h1>Batch Dispensing (Tablet Mode)</h1>
              <p>Batch: B-25-0415 · Anti-Aging Renewal Cream · 1,000 kg</p>
            </div>
            <Status type="approved">Mettler Toledo Connected</Status>
          </div>

          <div className="dispense-layout">
            <aside className="step-list">
              <div className="step-list-head" style={{padding: '12px', borderBottom: '1px solid #e2e8f0'}}><h2>Dispensing sequence</h2></div>
              <button className="done"><span><Icon name="check" size={14}/></span><div><strong>Aqua (Purified)</strong><small>679.50 kg</small></div></button>
              <button className="active"><span>2</span><div><strong>Vegetable Glycerin</strong><small>50.00 kg</small></div></button>
              <button><span>3</span><div><strong>Niacinamide USP</strong><small>40.00 kg</small></div></button>
            </aside>

            <div className="weigh-workspace">
              <div className="material-head">
                <div className="material-phase">A</div>
                <div><span>MATERIAL 2 OF 9</span><h2>Vegetable Glycerin 99.5%</h2><p>RM-00142</p></div>
                <Status type="approved">Verified</Status>
              </div>

              <div className="scan-panel">
                <label>Scan raw material lot barcode</label>
                <div>
                  <Icon name="scan" size={24}/>
                  <input value="GLY-8821-IT" readOnly/>
                  <span style={{color: '#047857', background: '#ecfdf5', padding: '4px 8px', borderRadius: '4px'}}>Verified</span>
                </div>
              </div>

              <div className="weight-grid">
                <div><span>Target weight</span><strong>50.00 <small>kg</small></strong><p>Tolerance ±0.5%</p></div>
                <div className="actual-weight"><span>Live scale weight (STABLE)</span><strong>50.042 <small>kg</small></strong><p>Within tolerance (+0.084%)</p></div>
              </div>

              <div className="dispense-actions" style={{padding: '16px', display: 'flex', justifyContent: 'flex-end', borderTop: '1px solid #e2e8f0'}}>
                <Button icon="check" className="confirm-dispense" onClick={handleSign}>
                  {signed ? 'Step Electronically Signed' : 'Confirm & Sign Dispense'}
                </Button>
              </div>
            </div>

            <aside className="station-side">
              <div className="station-card" style={{padding: '16px'}}>
                <h3>WS-02 Station</h3>
                <div className="scale-visual"><strong>50.042 kg</strong><small>Mettler Toledo</small></div>
                <p style={{fontSize: '10px', color: '#64748b'}}>Operator: Giulia Romano</p>
              </div>
            </aside>
          </div>
        </div>
      );
    }

    function Quality({ notify }) {
      const [modal, setModal] = useState(false);
      const [released, setReleased] = useState(false);

      const handleRelease = () => {
        fetch('/api/v1/quality/release', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ batchNumber: 'B-25-0398', reason: 'Authorized for Commercial Distribution' })
        })
        .then(res => res.json())
        .then(data => {
          setModal(false);
          setReleased(true);
          notify(data.message);
        });
      };

      return (
        <div>
          <div className="page-header">
            <div>
              <div className="eyebrow">QUALITY CONTROL · SAMPLE QC-S-004182</div>
              <h1>Batch Quality Review</h1>
              <p>Batch B-25-0398 · HydraGlow Serum 30ml</p>
            </div>
            <Status type={released ? 'approved' : 'warning'}>{released ? 'Released for Sale' : 'Pending QA Release'}</Status>
          </div>

          <div className="qa-banner">
            <Icon name="shield"/>
            <div><strong>Segregation of Duties Enforced</strong><span>Tested by Luca Bellini. Release requires QA Role signature.</span></div>
          </div>

          <div className="quality-layout">
            <div className="section-card" style={{padding: '16px'}}>
              <h2>Laboratory Physical & Chemical Parameters</h2>
              <table className="data-table" style={{marginTop: '12px'}}>
                <thead>
                  <tr><th>Parameter</th><th>Specification</th><th>Actual Result</th><th>Status</th></tr>
                </thead>
                <tbody>
                  <tr><td>pH @ 25°C</td><td>5.50 – 6.50</td><td>5.86</td><td><Status type="approved">Pass</Status></td></tr>
                  <tr><td>Viscosity @ 25°C</td><td>18,000 – 24,000 cP</td><td>21,450 cP</td><td><Status type="approved">Pass</Status></td></tr>
                  <tr><td>Microbiology</td><td>ISO 17516 compliant</td><td>Conforms</td><td><Status type="approved">Pass</Status></td></tr>
                </tbody>
              </table>
            </div>

            <aside className="release-panel" style={{padding: '16px'}}>
              <h2 style={{color: '#a7f3d0', fontSize: '13px', marginBottom: '14px'}}>FINAL QA DISPOSITION</h2>
              <div style={{marginBottom: '14px', fontSize: '10px'}}>
                <p>✓ All 48 process steps signed</p>
                <p>✓ 6/6 QC specifications conform</p>
                <p>✓ COA verified and uploaded</p>
              </div>
              <Button className="release-button" icon="lock" onClick={() => setModal(true)}>
                {released ? 'Batch Released' : 'Release Batch'}
              </Button>
            </aside>
          </div>

          {modal && (
            <div className="modal-overlay">
              <div className="modal">
                <div className="modal-icon"><Icon name="lock"/></div>
                <h2>21 CFR Part 11 Electronic Signature</h2>
                <p>Releasing batch B-25-0398 to commercial finished goods in MySQL.</p>
                <label>QA Manager Password<input type="password" placeholder="••••••••" autoFocus/></label>
                <label>Reason for release<select><option>Authorized for Commercial Distribution</option></select></label>
                <div style={{display: 'flex', justifyContent: 'flex-end', gap: '8px', marginTop: '16px'}}>
                  <Button variant="secondary" onClick={() => setModal(false)}>Cancel</Button>
                  <Button icon="lock" onClick={handleRelease}>Sign & Release</Button>
                </div>
              </div>
            </div>
          )}
        </div>
      );
    }

    function Traceability() {
      return (
        <div>
          <div className="page-header">
            <div>
              <div className="eyebrow">GMP TRACEABILITY MATRIX</div>
              <h1>Forward & Backward Genealogy</h1>
              <p>Tracing finished lot FG-250415-A across suppliers, lots, batch records, and customers</p>
            </div>
          </div>

          <div className="section-card trace-canvas">
            <div className="trace-map">
              <svg viewBox="0 0 1200 390" preserveAspectRatio="none"><path d="M105 195 C150 195 150 75 220 75 M105 195 C150 195 150 315 220 315 M315 75 C355 75 360 195 420 195 M315 315 C355 315 360 195 420 195 M515 195H620 M715 195H820 M915 195 C970 195 965 75 1020 75 M915 195 C970 195 965 315 1020 315"/></svg>
              <div className="trace-node node-col-0 node-y-1"><Icon name="file"/><div><small>SUPPLIER PO</small><strong>PO-2025-0189</strong><p>Cosmetica Materie</p></div></div>
              <div className="trace-node node-col-1 node-y-0"><Icon name="boxes"/><div><small>RAW LOT</small><strong>GLY-8821-IT</strong><p>Glycerin 1,250 kg</p></div></div>
              <div className="trace-node node-col-1 node-y-2"><Icon name="boxes"/><div><small>RAW LOT</small><strong>NIA-24091</strong><p>Niacinamide 320 kg</p></div></div>
              <div className="trace-node node-col-2 node-y-1"><Icon name="scale"/><div><small>PRODUCTION</small><strong>MO-2025-0415</strong><p>1,000 kg Batch</p></div></div>
              <div className="trace-node node-col-3 node-y-1"><Icon name="shield"/><div><small>BULK BATCH</small><strong>BULK-25-0415</strong><p>Approved</p></div></div>
              <div className="trace-node node-col-4 node-y-1"><Icon name="shield"/><div><small>FINISHED LOT</small><strong>FG-250415-A</strong><p>18,400 units</p></div></div>
              <div className="trace-node node-col-5 node-y-0"><Icon name="arrow"/><div><small>DELIVERY</small><strong>DN-2025-1184</strong><p>Bellezza Retail</p></div></div>
              <div className="trace-node node-col-5 node-y-2"><Icon name="arrow"/><div><small>DELIVERY</small><strong>DN-2025-1191</strong><p>Milano Cosmesi</p></div></div>
            </div>
          </div>
        </div>
      );
    }

    function App() {
      const [page, setPage] = useState('dashboard');
      const [role, setRole] = useState('Admin');
      const [roleOpen, setRoleOpen] = useState(false);
      const [toast, setToast] = useState('');

      const notify = (msg) => { setToast(msg); setTimeout(() => setToast(''), 3000); };

      const content = {
        dashboard: <Dashboard navigate={setPage} notify={notify}/>,
        inventory: <Inventory/>,
        formulation: <Formulation/>,
        dispensing: <Dispensing notify={notify}/>,
        quality: <Quality notify={notify}/>,
        trace: <Traceability/>,
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
              <div className="search"><Icon name="search" size={17}/><input placeholder="Search lots, batches, formulations in MySQL..."/><kbd>⌘ K</kbd></div>
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

          {toast && <div className="toast"><span><Icon name="check" size={15}/></span>{toast}</div>}
        </div>
      );
    }

    ReactDOM.createRoot(document.getElementById('root')).render(<App />);
    @endverbatim
  </script>
</body>
</html>
