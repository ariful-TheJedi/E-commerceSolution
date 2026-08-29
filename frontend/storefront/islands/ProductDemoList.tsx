import { createRoot } from 'react-dom/client';
import { useEffect, useState } from 'react';

type ProductSummary = {
    id: string;
    title: string;
    slug: string;
    status: string;
    description: string;
    price_minor: number;
    currency: string;
};

type ListResponse = {
    data: ProductSummary[];
};

function formatMoney(minor: number, currency: string): string {
    return new Intl.NumberFormat(undefined, {
        style: 'currency',
        currency,
    }).format(minor / 100);
}

function ProductDemoList() {
    const [items, setItems] = useState<ProductSummary[]>([]);
    const [error, setError] = useState<string | null>(null);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        let cancelled = false;

        fetch('/api/v1/products')
            .then(async (res) => {
                if (!res.ok) {
                    throw new Error(`HTTP ${res.status}`);
                }
                return (await res.json()) as ListResponse;
            })
            .then((body) => {
                if (!cancelled) {
                    setItems(body.data);
                    setLoading(false);
                }
            })
            .catch((err: unknown) => {
                if (!cancelled) {
                    setError(err instanceof Error ? err.message : 'Request failed');
                    setLoading(false);
                }
            });

        return () => {
            cancelled = true;
        };
    }, []);

    return (
        <section className="mt-8 rounded-lg border border-[var(--line)] bg-[var(--bg-raised)] p-4">
            <p className="mb-1 text-xs uppercase tracking-[0.18em] text-[var(--accent)]">
                Demo island
            </p>
            <h2 className="mb-2 text-base font-semibold text-[var(--text)]">
                Products via <code className="text-sm">/api/v1/products</code>
            </h2>
            <p className="mb-4 text-sm text-[var(--muted)]">
                Factory-built stub data from <code>ProductFactory</code> — not persisted (F1 later).
            </p>

            {loading && <p className="text-sm text-[var(--muted)]">Loading…</p>}
            {error && (
                <p className="text-sm text-red-400" role="alert">
                    {error}
                </p>
            )}
            {!loading && !error && (
                <ul className="m-0 flex list-none flex-col gap-3 p-0">
                    {items.map((p) => (
                        <li
                            key={p.id}
                            className="rounded-md border border-[var(--line)] px-3 py-3"
                        >
                            <div className="flex items-baseline justify-between gap-3">
                                <span className="font-medium text-[var(--text)]">{p.title}</span>
                                <span className="shrink-0 text-sm text-[var(--accent)]">
                                    {formatMoney(p.price_minor, p.currency)}
                                </span>
                            </div>
                            <p className="mt-1 text-sm text-[var(--muted)]">{p.description}</p>
                            <p className="mt-1 text-xs text-[var(--muted)]">{p.slug}</p>
                        </li>
                    ))}
                    {items.length === 0 && (
                        <li className="text-sm text-[var(--muted)]">No active products.</li>
                    )}
                </ul>
            )}
        </section>
    );
}

export function mountProductDemoList(el: HTMLElement): void {
    createRoot(el).render(<ProductDemoList />);
}
