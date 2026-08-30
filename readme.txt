// personal rough — this is how I talk to the agent.
// Agent: read this file first. Add answers here when I ask. Do not tidy the official doc/ files unless I say so.
// Agent session log: agent-log.txt — do not append dated work notes here.
// Do not update AGENTS.md each turn. Update it once when the Product module is complete.

A module is one capability. Same five parts every time.
Picture: architecture-map.txt lines 36–41.


TOP LEVEL  (repo root only — not inside a module)
------------------------------------------------
    /
    ├── app/                 host. no business rules
    ├── bootstrap/           Laravel start-up
    ├── config/              host settings
    ├── routes/              mounts /api/v1 and /admin
    ├── frontend/            ALL UI
    ├── modules/             ALL domain (one subfolder per module)
    ├── packages/            shared kernel + platform machinery
    ├── database/            EMPTY
    ├── public/              web root
    ├── media/               uploads
    ├── resources/           EMPTY
    ├── storage/             Laravel logs/cache
    ├── tests/               host tests
    ├── doc/                 architecture docs
    ├── composer.json
    └── package.json


ROUTES
------
JSON API for a capability  →  lives IN the module
    modules/<name>/src/Api/Routes/api.php
    loaded by the module's ServiceProvider
    example: GET /api/v1/products  (Product)

Root routes/ does NOT define product/order/etc endpoints.
It only mounts:

    routes/api.php     empty /api/v1 group. host mount. modules fill it
    routes/web.php     storefront require + /admin catch-all + /media

UI pages are not in the module either:

    frontend/storefront/routes/web.php     public pages (/)
    /admin/{any?}                          admin SPA shell (host)

Module names never appear in URLs.  /api/v1/products  not  /api/v1/product/products



MODULE STRUCTURE
----------------

    modules/<name>/
    ├── composer.json
    ├── docs/
    ├── src/
    │   ├── <Name>ServiceProvider.php
    │   ├── Contracts/          public door
    │   ├── Api/                HTTP
    │   ├── Application/        one folder per use case
    │   ├── Domain/             rules
    │   └── Infrastructure/     database + adapters
    │       └── Persistence/    ALL database files
    └── tests/


WHAT EACH LAYER IS FOR  (architecture-map.txt 36–41)
----------------------------------------------------

    api            HTTP only. Read the request, hand it to application,
                   format the JSON. No business rules. No Blade.

    application    One use case at a time. Orders the steps:
                   validate → load → call domain → save → publish.
                   Does not decide the rules itself.

    domain         The rules and the words of the business. Pure PHP.
                   No HTTP, no database, no Laravel.

    infra          How we talk to the outside: Postgres, files, mail,
                   payments. Eloquent and SQL live here only.

    contracts      The published surface. Other modules may call this
                   and nothing else. Interfaces, DTOs, events.
                   The line under infra in the diagram is the wall:
                   everything above it is private.

    Flow:

        api ──► application ──► domain
                      │
                      └── ports (interfaces)
                                ▲
                         infra implements them


HOW MODULES TALK
----------------

    Need the answer now?  →  call the other module's contracts
    Just announcing a fact? →  event (past tense), via the bus



The Core Backend Modules
===========================
1. Identity (User) Module [ ]
Responsibility: Acts as the security checkpoint for the REST API. Manages authentication, secure session tokens, profile data, and Role-Based Access Control (RBAC).
Boundary: Differentiates a standard customer from an admin, protecting the routes of all other modules without owning any order or cart data itself.

2. Product Module[ ]
Responsibility: Manages the catalog lifecycle, including creating, drafting, publishing, and archiving core product records. Handles multi-option variants, pricing tiers, media galleries, and hierarchical categories.
Boundary: Completely isolated from physical stock counts, cart discounts, and user reviews.

3. Inventory Module[ ]
Responsibility: The private back room for physical stock. Tracks warehouse quantities, handles stock reservations during checkout, and manages availability statuses.
Boundary: Operates purely on SKUs and quantities; it relies on the Product module's public contract to resolve item details if needed, rather than querying the product database directly.

4. Orders Module[ ]
Responsibility: Orchestrates the shopping cart, checkout calculations, payment processing, and the overall lifecycle of a customer order.
Boundary: Cannot calculate its own base prices or check database stock directly. It must query the Product and Inventory modules via their public interfaces (contracts) before safely completing a transaction.

5. Promotions Module [ ]
Responsibility: Manages dynamic cart-level logic, promotional campaigns, and coupon codes.
Boundary: Calculates temporary checkout discounts but does not permanently alter the base retail pricing stored securely in the Product module.