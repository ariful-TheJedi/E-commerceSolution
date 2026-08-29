<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin — {{ config('app.name') }}</title>
    <style>
        html, body { margin: 0; min-height: 100%; background: #0e1014; color: #8d95a1; font-family: "Segoe UI", system-ui, sans-serif; }
        .boot { padding: 2rem 1.25rem; font-size: 0.9rem; max-width: 40rem; }
        .boot a { color: #c9a36a; }
        .boot code { font-size: 0.86em; }
    </style>
</head>
<body>
    <div id="root">
        <div class="boot">
            <p>Loading admin…</p>
        </div>
    </div>
    @php
        // Prefer built assets so /admin works with only `php artisan serve`.
        // Opt into Vite with ADMIN_VITE=1 and `npm run dev:admin` (writes public/hot-admin).
        $wantVite = filter_var(env('ADMIN_VITE', false), FILTER_VALIDATE_BOOLEAN)
            && file_exists(public_path('hot-admin'));
        $manifestPath = public_path('build/admin/.vite/manifest.json');
        $manifest = file_exists($manifestPath)
            ? json_decode((string) file_get_contents($manifestPath), true)
            : [];
        $entry = is_array($manifest) ? ($manifest['src/main.tsx'] ?? null) : null;
        $adminViteBase = 'http://127.0.0.1:5174/build/admin';
        $viteClient = $adminViteBase.'/@vite/client';
        $viteMain = $adminViteBase.'/src/main.tsx';
    @endphp
    @if ($wantVite)
        <script type="module" src="{{ $viteClient }}"></script>
        <script type="module" src="{{ $viteMain }}"></script>
    @elseif ($entry)
        @foreach ($entry['css'] ?? [] as $css)
            <link rel="stylesheet" href="{{ asset('build/admin/'.$css) }}">
        @endforeach
        <script type="module" src="{{ asset('build/admin/'.$entry['file']) }}"></script>
    @else
        <p class="boot">
            Admin assets missing. Run <code>npm run build:admin</code>
            (or set <code>ADMIN_VITE=true</code> and run <code>npm run dev:admin</code>).
        </p>
    @endif
</body>
</html>
