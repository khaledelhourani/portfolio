<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>تحت الصيانة — {{ $project->displayName() }}</title>
    <style>
        :root { color-scheme: dark; }
        body { margin: 0; min-height: 100vh; display: grid; place-items: center;
               font-family: system-ui, "Segoe UI", Tahoma, sans-serif;
               background: radial-gradient(circle at 70% 20%, #422006, #0f172a); color: #e2e8f0; }
        .card { text-align: center; padding: 2.5rem 3rem; max-width: 28rem; }
        .icon { font-size: 3rem; margin-bottom: 1rem; }
        h1 { font-size: 1.4rem; margin: 0 0 .5rem; }
        p { color: #94a3b8; }
    </style>
</head>
<body>
    <div class="card">
        <div class="icon">🛠️</div>
        <h1>«{{ $project->displayName() }}» تحت الصيانة</h1>
        <p>هذا المشروع متوقف مؤقتًا لإجراء تحديثات. يرجى العودة لاحقًا.</p>
    </div>
</body>
</html>
