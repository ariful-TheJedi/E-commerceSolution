-- Product module schema, roles, grants.
-- product_owner: migrations / DDL
-- product_app:    runtime reads and writes; no DDL; no other schemas

CREATE SCHEMA IF NOT EXISTS product;

DO $$
BEGIN
    CREATE ROLE product_owner LOGIN PASSWORD 'secret';
EXCEPTION
    WHEN duplicate_object THEN NULL;
END
$$;

DO $$
BEGIN
    CREATE ROLE product_app LOGIN PASSWORD 'secret';
EXCEPTION
    WHEN duplicate_object THEN NULL;
END
$$;

GRANT USAGE, CREATE ON SCHEMA product TO product_owner;
ALTER SCHEMA product OWNER TO product_owner;

GRANT USAGE ON SCHEMA product TO product_app;

ALTER DEFAULT PRIVILEGES FOR ROLE product_owner IN SCHEMA product
    GRANT SELECT, INSERT, UPDATE, DELETE ON TABLES TO product_app;

ALTER DEFAULT PRIVILEGES FOR ROLE product_owner IN SCHEMA product
    GRANT USAGE, SELECT ON SEQUENCES TO product_app;

-- Seal the wall: product_app must not touch other schemas
REVOKE ALL ON SCHEMA platform FROM product_app;
REVOKE ALL ON SCHEMA reporting FROM product_app;
REVOKE ALL ON SCHEMA public FROM product_app;

REVOKE ALL ON SCHEMA platform FROM product_owner;
REVOKE ALL ON SCHEMA reporting FROM product_owner;
