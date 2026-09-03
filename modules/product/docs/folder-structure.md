# Product module — folders

`src/` follows `doc/folder-structure.txt` (module alpha shape).

```
modules/product/
├── composer.json
├── src/
│   ├── ProductServiceProvider.php
│   ├── Contracts/Events/     # ProductUpdated
│   ├── Api/
│   │   ├── Routes/api.php
│   │   ├── Controllers/      # thin; id bound, load via repository
│   │   ├── Requests/         # HTTP body shape only
│   │   └── Resources/        # JSON for React
│   ├── Application/
│   │   ├── Ports/            # ProductRepository, MediaRepository, Outbox
│   │   ├── Exceptions/       # ProductNotFound
│   │   ├── CreateProduct/
│   │   ├── UpdateProduct/
│   │   ├── DraftProduct/
│   │   ├── PublishProduct/
│   │   ├── ArchiveProduct/
│   │   ├── Media/            # image gallery and digital-file use cases
│   │   ├── Taxonomy/          # categories, tags, and collection use cases
│   │   ├── Relations/         # manual product relationship use cases
│   │   └── Operations/        # admin list, duplicate, bulk, CSV use cases
│   ├── Domain/               # Product, status, visibility, exceptions
│   └── Infrastructure/
│       ├── Adapters/         # Clock, Ids, PlatformOutbox
│       └── Persistence/
│           ├── Models/       # ProductModel (Eloquent, here only)
│           ├── Repositories/ # product, media, taxonomy, and relation repositories
│           └── Bootstrap/    # schema.sql
└── tests/                    # see below
```

Tests follow `doc/testing-workflow.txt`. This module's tree:

```
modules/product/tests/
├── use-cases.txt         # Living Use Case Cards (features 1–12). Write and update here.
├── Unit/
│   ├── Domain/           # Pure PHP rules (no framework, no DB)
│   └── Application/      # Handlers. In-memory repo + fake outbox.
├── Feature/
│   ├── Api/              # Front door HTTP (JSON formatting)
│   ├── Infrastructure/   # Database persistence (SQLite mapping)
│   └── Listeners/        # Cross-module event reactions (outbox delivery)
└── Architecture/         # Boundary checks (layers do not mix)
```
