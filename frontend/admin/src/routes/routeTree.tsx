import {
    Link,
    Outlet,
    createRootRoute,
    createRoute,
} from '@tanstack/react-router';
import { ProductDemoList } from '../components/ProductDemoList';

function Shell() {
    return (
        <div className="frame">
            <header className="top">
                <div className="brand">Admin</div>
                <nav className="nav">
                    <a href="/">Public</a>
                    <Link to="/" activeProps={{ className: 'underline' }}>
                        Home
                    </Link>
                    <Link to="/products" activeProps={{ className: 'underline' }}>
                        Products
                    </Link>
                </nav>
            </header>
            <Outlet />
        </div>
    );
}

function Home() {
    return (
        <main className="panel">
            <p className="kicker">Private surface</p>
            <h1>Admin</h1>
            <p className="lede">
                This SPA talks to <code>/api/v1</code> only. No business rules in React.
            </p>
            <p className="note">
                Open <Link to="/products">Products</Link> for the Product API demo list.
            </p>
        </main>
    );
}

function ProductsPage() {
    return (
        <main className="panel max-w-2xl">
            <p className="kicker">Product module</p>
            <h1>Products</h1>
            <p className="lede">
                Dummy screen wired to <code>GET /api/v1/products</code>.
            </p>
            <ProductDemoList />
        </main>
    );
}

function NotFound() {
    return (
        <main className="panel">
            <h1>Not found</h1>
            <p className="lede">
                <Link to="/">Back to admin home</Link>
            </p>
        </main>
    );
}

const rootRoute = createRootRoute({
    component: Shell,
    notFoundComponent: NotFound,
});

const indexRoute = createRoute({
    getParentRoute: () => rootRoute,
    path: '/',
    component: Home,
});

const productsRoute = createRoute({
    getParentRoute: () => rootRoute,
    path: '/products',
    component: ProductsPage,
});

export const routeTree = rootRoute.addChildren([indexRoute, productsRoute]);
