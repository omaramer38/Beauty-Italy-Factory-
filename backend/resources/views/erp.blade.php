<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Beauty Italy Factory ERP · Phase 1 Controlled Core</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700;1,9..40,400&display=swap" rel="stylesheet">
  
  <!-- Main ERP Stylesheet (Clean Modular CSS) -->
  <link rel="stylesheet" href="/css/erp.css">

  <!-- React 18, Babel Standalone, QRCodeJS & jsQR Client-Side Decoder -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/react/18.2.0/umd/react.production.min.js" crossorigin="anonymous"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/react-dom/18.2.0/umd/react-dom.production.min.js" crossorigin="anonymous"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/babel-standalone/7.23.5/babel.min.js" crossorigin="anonymous"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js" crossorigin="anonymous"></script>
  <script src="/js/jsQR.min.js"></script>
  <script>
    if (!window.jsQR) {
      var s = document.createElement('script');
      s.src = 'https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.min.js';
      document.head.appendChild(s);
    }
  </script>
  <script src="/js/pdf.min.js"></script>
  <script>
    if (window.pdfjsLib) {
      window.pdfjsLib.GlobalWorkerOptions.workerSrc = '/js/pdf.worker.min.js';
    }
  </script>
</head>
<body>
  <div id="root">
    <div style="min-height: 100vh; display: grid; place-items: center; background: #0f172a; color: white; font-family: sans-serif;">
      <div style="text-align: center;">
        <div style="width: 44px; height: 44px; border-radius: 12px; background: #4f46e5; display: grid; place-items: center; margin: 0 auto 16px; font-weight: bold; font-size: 22px;">B</div>
        <h2 style="font-size: 20px; font-weight: 700;">Beauty Italy Factory ERP</h2>
        <p style="color: #94a3b8; font-size: 13px; margin-top: 6px;">Loading Phase 1 Controlled Core...</p>
      </div>
    </div>
  </div>

  <script>
    window.addEventListener('error', function(e) {
      console.error('Captured ERP error:', e);
      // Don't show red banner for generic cross-origin resource load warnings
      if (e.message === 'Script error.' && (!e.filename || e.filename.indexOf('http') === -1)) {
        return;
      }
      var errDiv = document.getElementById('erp-error-banner');
      if (!errDiv) {
        errDiv = document.createElement('div');
        errDiv.id = 'erp-error-banner';
        errDiv.style = 'position:fixed;top:10px;left:50%;transform:translateX(-50%);background:#ef4444;color:white;padding:12px 20px;border-radius:8px;font-size:12px;z-index:9999;box-shadow:0 10px 30px rgba(0,0,0,0.3);max-width:90%;font-family:sans-serif;white-space:pre-wrap;';
        document.body.appendChild(errDiv);
      }
      var msg = (e.error && (e.error.stack || e.error.message)) || e.message || 'Script Execution Error';
      errDiv.innerHTML = '<strong>Application Error:</strong> ' + msg;
    });
  </script>

  <script type="text/babel">
    const { useState, useEffect, useRef } = React;

    {{-- Common Shared Components --}}
    @include('components.common.icons')
    @include('components.common.ui-primitives')
    @include('components.common.omnibox')

    {{-- Business Logic Modules --}}
    @include('components.modules.dashboard')
    @include('components.modules.inventory')
    @include('components.modules.formulation')
    @include('components.modules.dispensing')
    @include('components.modules.quality')
    @include('components.modules.traceability')

    {{-- Root App Shell & Dispatcher --}}
    @include('components.shell.app-shell')
  </script>
</body>
</html>
