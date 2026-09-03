-- Platform schemas. Lives in packages/platform Infrastructure.
-- Module schemas live in that module's Infrastructure/Persistence/Bootstrap/.
CREATE SCHEMA IF NOT EXISTS platform;
CREATE SCHEMA IF NOT EXISTS reporting;

REVOKE CREATE ON SCHEMA public FROM PUBLIC;

CREATE TABLE IF NOT EXISTS platform.outbox (
    id            UUID PRIMARY KEY,
    type          TEXT NOT NULL,
    payload       JSONB NOT NULL,
    occurred_at   TIMESTAMPTZ NOT NULL,
    published_at  TIMESTAMPTZ NULL
);

-- Module app roles insert here (adapter). Platform owns DDL.
-- product_app is created by the product bootstrap; grant if that role exists.
DO $outbox_grant$
BEGIN
    IF EXISTS (SELECT 1 FROM pg_roles WHERE rolname = 'product_app') THEN
        GRANT INSERT, SELECT ON platform.outbox TO product_app;
    END IF;
END
$outbox_grant$;
