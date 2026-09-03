# Identity module

Identity owns credentials, sessions, profile data, addresses, account status,
privacy requests, roles, permissions, and security audit events.

Other modules use the public contracts in `src/Contracts/`. They never read
schema `identity` directly. Orders, Promotions, Customer Reviews, Product, and
Inventory retain ownership of their own data.

Requirements are listed in `features-list.txt` and are authoritative in
`doc/Features-list.txt`.
