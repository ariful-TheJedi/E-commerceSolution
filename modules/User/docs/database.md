# Identity database

Schema: `identity`.

The schema will own users, credential hashes, verification and recovery
secrets, sessions, addresses, roles, permissions, account status, privacy
requests, and security audit records.

No table in Identity may contain an order, cart, promotion, review, Product,
or inventory record. Cross-module references use bare IDs or immutable
snapshots as defined by the architecture; no cross-schema foreign keys.
