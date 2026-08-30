Schema: `product` on database `ecommercesolution`.

Postgres stores the name in lowercase (unquoted CREATE DATABASE).
Source of columns: `database-design.txt` in this folder.
SQL: `../src/Infrastructure/Persistence/Bootstrap/schema.sql`

Roles: `product_owner` (DDL) · `product_app` (DML). No grants on other schemas.

Applied 2026-08-31: platform + reporting + product (22 tables).
