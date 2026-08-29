<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name') }}</title>
    @vite(['frontend/storefront/css/app.css', 'frontend/storefront/islands/main.tsx'])
</head>
<body>
    <div class="shell">
        <header class="top">
            <div class="brand">{{ config('app.name') }}</div>
            <nav class="nav">
                <a href="/">Public</a>
                <a href="/admin">Admin</a>
                <a href="/admin/products">Admin products</a>
            </nav>
        </header>

        <section class="hero">
            <p class="kicker">Modular monolith</p>
            <h1>Empty host. Domain stays out.</h1>
            <p class="lede">
                One Laravel app, one Postgres, modules you add later.
                Copy this repo for any product. Do not put business rules in the host.
            </p>
        </section>

        <div class="grid">
            <article class="card">
                <h2>Public site</h2>
                <p>Blade here. React only as islands. Controllers call contracts, never tables.</p>
            </article>
            <article class="card">
                <h2>Admin</h2>
                <p>React SPA under <code>/admin</code>. Speaks HTTP to <code>/api/v1</code> only.</p>
            </article>
            <article class="card">
                <h2>Modules</h2>
                <p>All domain code in <code>modules/</code>. One schema each. Host stays thin.</p>
            </article>
        </div>

        {{-- Blade → ProductApi (in-process). Visible without the React island. --}}
        <section class="mt-8 rounded-lg border border-[var(--line)] bg-[var(--bg-raised)] p-4">
            <p class="mb-1 text-xs uppercase tracking-[0.18em] text-[var(--accent)]">Product API (Blade)</p>
            <h2 class="mb-2 text-base font-semibold">Factory demo products</h2>
            <p class="mb-4 text-sm text-[var(--muted)]">
                Rendered by Blade via <code>ProductApi::listActiveSummaries()</code> — not from the database yet.
            </p>
            <ul class="m-0 flex list-none flex-col gap-3 p-0">
                @forelse ($products as $product)
                    <li class="rounded-md border border-[var(--line)] px-3 py-3">
                        <div class="flex items-baseline justify-between gap-3">
                            <span class="font-medium">{{ $product->title }}</span>
                            <span class="shrink-0 text-sm text-[var(--accent)]">
                                {{ number_format($product->priceMinor / 100, 2) }} {{ $product->currency }}
                            </span>
                        </div>
                        <p class="mt-1 text-sm text-[var(--muted)]">{{ $product->description }}</p>
                        <p class="mt-1 text-xs text-[var(--muted)]">{{ $product->slug }}</p>
                    </li>
                @empty
                    <li class="text-sm text-[var(--muted)]">No active products.</li>
                @endforelse
            </ul>
        </section>

        <div data-island="product-demo-list" class="mt-4"></div>

        <ul class="rules">
            <li><strong>One owner</strong><span>One module writes and migrates each table.</span></li>
            <li><strong>Published surface</strong><span>Call <code>Contracts/</code>. Never internals, tables, or HTTP.</span></li>
            <li><strong>One direction</strong><span>No cycles between modules.</span></li>
            <li><strong>Outside behind a port</strong><span>Adapters implement interfaces the module defines.</span></li>
            <li><strong>Additive first</strong><span>Never rename a published thing in place.</span></li>
        </ul>

        <p class="foot">Shape: <code>doc/architecture-map.txt</code>. Paths: <code>doc/folder-structure.txt</code>.</p>
    </div>
</body>
</html>
