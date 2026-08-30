-- Platform schemas. Lives in packages/platform Infrastructure.
-- Module schemas live in that module's Infrastructure/Persistence/Bootstrap/.
CREATE SCHEMA IF NOT EXISTS platform;
CREATE SCHEMA IF NOT EXISTS reporting;

REVOKE CREATE ON SCHEMA public FROM PUBLIC;
