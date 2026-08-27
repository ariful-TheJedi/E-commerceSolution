# Host

A modular monolith you can copy for any product.

One Laravel app. One Postgres. Domain in `modules/`. UI in `frontend/`. The host stays empty of business rules.

---

## Start here

Read in this order. Each file answers one question.

1. [Architecture](doc/architecture-map.txt) — what the system looks like
2. [Folders](doc/folder-structure.txt) — where every file goes
3. [Modules](doc/module-guideline.txt) — how to add or change a module
4. [Workflow](doc/dev-workflow.txt) — how we build, one slice at a time

If those four disagree: architecture wins on shape, folders on paths, modules on procedure.

**When you need them**

- [Tech stack](doc/tech-stack.txt) — versions
- [Features](doc/Features-list.txt) — what *this* product does (you fill this in)
- [AGENTS.md](AGENTS.md) / [.cursorrules](.cursorrules) — rules for an AI assistant

Architecture, modules, and features are three separate conversations. Do not mix them.

---

## New project

1. Copy this repo
2. Set `APP_NAME`, the Composer `name`, `DB_*`, and `MEDIA_PREFIX`
3. Write the product in `doc/Features-list.txt`
4. Add modules one at a time — empty `modules/` is correct until then

---

## Run

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate

php artisan serve
npm run dev          # public site
npm run dev:admin    # admin, port 5174
```

`composer check` runs tests, PHPStan, and Deptrac.

Postgres: `docker-compose.test.yml` (optional until a module needs a database).

---

## Layout

```
app/                 host only — no business rules
modules/             all domain code
frontend/
  storefront/        public Blade + React islands
  admin/             private React SPA  (/admin)
packages/
  shared/            tiny kernel — ids, clock, money
  platform/          machinery — bus, media, no domain
media/               all uploads  (subfolder = MEDIA_PREFIX)
database/migrations/ stays empty — migrations live in modules
resources/           stays empty — views live in frontend/
```
