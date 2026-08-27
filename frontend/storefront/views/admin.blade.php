<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin — {{ config('app.name') }}</title>
    <style>
        html, body { margin: 0; min-height: 100%; background: #0e1014; color: #8d95a1; font-family: "Segoe UI", system-ui, sans-serif; }
        .boot { padding: 2rem 1.25rem; font-size: 0.9rem; }
    </style>
</head>
<body>
    <div id="root"><p class="boot">Loading admin…</p></div>
    @if (file_exists(public_path('hot-admin')))
        <script type="module" src="http://localhost:5174/@vite/client"></script>
        <script type="module" src="http://localhost:5174/src/main.tsx"></script>
    @else
        @php
            $manifestPath = public_path('build/admin/.vite/manifest.json');
            $manifest = file_exists($manifestPath)
                ? json_decode(file_get_contents($manifestPath), true)
                : [];
            $entry = $manifest['src/main.tsx'] ?? null;
        @endphp
        @if ($entry)
            @foreach ($entry['css'] ?? [] as $css)
                <link rel="stylesheet" href="{{ asset('build/admin/'.$css) }}">
            @endforeach
            <script type="module" src="{{ asset('build/admin/'.$entry['file']) }}"></script>
        @endif
    @endif
</body>
</html>
