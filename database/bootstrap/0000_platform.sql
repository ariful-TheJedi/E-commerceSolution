-- Host schemas only. Module schemas are created by the owning module.
CREATE SCHEMA IF NOT EXISTS platform;
CREATE SCHEMA IF NOT EXISTS reporting;

REVOKE CREATE ON SCHEMA public FROM PUBLIC;
