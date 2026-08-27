import { createRootRoute, createRoute, Outlet } from '@tanstack/react-router';

function Shell() {
    return (
        <div className="frame">
            <header className="top">
                <div className="brand">Admin</div>
                <nav className="nav">
                    <a href="/">Public</a>
                    <a href="/admin">Admin</a>
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
            <h1>No screens yet</h1>
            <p className="lede">
                This SPA is the locked-down UI. It talks to <code>/api/v1</code> only.
                Add routes here when a module publishes an endpoint worth a screen.
            </p>
            <p className="note">
                Do not put business rules in React. A rule enforced only in the
                frontend is not enforced.
            </p>
        </main>
    );
}

const rootRoute = createRootRoute({
    component: Shell,
});

const indexRoute = createRoute({
    getParentRoute: () => rootRoute,
    path: '/',
    component: Home,
});

export const routeTree = rootRoute.addChildren([indexRoute]);
