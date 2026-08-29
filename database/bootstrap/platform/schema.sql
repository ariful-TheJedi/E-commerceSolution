-- Host schemas only. Module schemas live in modules/<name>/database/bootstrap/.
CREATE SCHEMA IF NOT EXISTS platform;
CREATE SCHEMA IF NOT EXISTS reporting;

REVOKE CREATE ON SCHEMA public FROM PUBLIC;
