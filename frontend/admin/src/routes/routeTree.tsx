import {
    Link,
    Outlet,
    createRootRoute,
    createRoute,
} from '@tanstack/react-router';

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
                This SPA talks to <code>/api/v1</code> only. Add routes when a
                module publishes an endpoint.
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
