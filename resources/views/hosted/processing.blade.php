<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="refresh" content="4">
    <title>جارٍ التجهيز — {{ $project->displayName() }}</title>
    <style>
        :root { color-scheme: dark; }
        * { box-sizing: border-box; }
        body { margin: 0; min-height: 100vh; display: grid; place-items: center;
               font-family: system-ui, "Segoe UI", Tahoma, sans-serif;
               background: radial-gradient(circle at 30% 20%, #1e293b, #0f172a); color: #e2e8f0; }
        .card { text-align: center; padding: 2.5rem 3rem; }
        .spinner { width: 56px; height: 56px; margin: 0 auto 1.5rem; border-radius: 50%;
                   border: 4px solid #334155; border-top-color: #38bdf8; animation: spin 1s linear infinite; }
        @keyframes spin { to { transform: rotate(360deg); } }
        h1 { font-size: 1.4rem; margin: 0 0 .5rem; }
        p { color: #94a3b8; margin: .25rem 0; }
        .bar { width: 280px; max-width: 80vw; height: 8px; background: #1e293b; border-radius: 99px; margin: 1.25rem auto 0; overflow: hidden; }
        .bar > span { display: block; height: 100%; background: linear-gradient(90deg, #38bdf8, #818cf8);
                      width: {{ (int) round(($project->processing_step / 7) * 100) }}%; transition: width .4s; }
        .step { margin-top: .75rem; font-size: .85rem; color: #64748b; }
    </style>
</head>
<body>
    <div class="card">
        <div class="spinner"></div>
        <h1>جارٍ تجهيز «{{ $project->displayName() }}»</h1>
        <p>يتم فحص المشروع ونشره الآن. ستُحدَّث هذه الصفحة تلقائيًا.</p>
        <div class="bar"><span></span></div>
        <p class="step">الخطوة {{ $project->processing_step }} من 7</p>
        @if ($project->processing_status === 'failed')
            <p style="color:#f87171; margin-top:1rem;">تعذّر إكمال النشر. يرجى مراجعة لوحة التحكم.</p>
        @endif
    </div>
</body>
</html>
