import { useQuery } from '@tanstack/react-query';

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

async function fetchActiveProducts(): Promise<ProductSummary[]> {
    const res = await fetch('/api/v1/products');
    if (!res.ok) {
        throw new Error(`HTTP ${res.status}`);
    }
    const body = (await res.json()) as ListResponse;
    return body.data;
}

function formatMoney(minor: number, currency: string): string {
    return new Intl.NumberFormat(undefined, {
        style: 'currency',
        currency,
    }).format(minor / 100);
}

/**
 * Demo only — talks to /api/v1/products. No business rules in React.
 */
export function ProductDemoList() {
    const { data, error, isPending } = useQuery({
        queryKey: ['products', 'active'],
        queryFn: fetchActiveProducts,
    });

    return (
        <section className="mt-6 rounded-lg border border-[var(--line)] bg-[var(--bg-raised)] p-4">
            <p className="mb-1 text-xs uppercase tracking-[0.18em] text-[var(--accent)]">
                Demo
            </p>
            <h2 className="mb-2 text-base font-semibold text-[var(--text)]">
                Products via <code className="text-sm">/api/v1/products</code>
            </h2>
            <p className="mb-4 text-sm text-[var(--muted)]">
                Factory-built stub — replace with real persistence in F1.
            </p>

            {isPending && <p className="text-sm text-[var(--muted)]">Loading…</p>}
            {error && (
                <p className="text-sm text-red-400" role="alert">
                    {error instanceof Error ? error.message : 'Request failed'}
                </p>
            )}
            {data && (
                <ul className="m-0 flex list-none flex-col gap-3 p-0">
                    {data.map((p) => (
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
                    {data.length === 0 && (
                        <li className="text-sm text-[var(--muted)]">No active products.</li>
                    )}
                </ul>
            )}
        </section>
    );
}
