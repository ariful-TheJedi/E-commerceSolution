# Identity API

The Identity API is not implemented yet. Routes will be owned by this module
and mounted by the host under `/api/v1`.

Authentication and authorization middleware are host integration concerns.
Identity will provide published contracts, immutable DTOs, and authorization
policies; it will not expose Eloquent models or database tables.
