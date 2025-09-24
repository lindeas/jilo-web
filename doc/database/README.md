# Database migrations

This app ships with a lightweight SQL migration system to safely upgrade a running site when code changes require database changes.

## Concepts

- Migrations live in `doc/database/migrations/` and are plain `.sql` files.
- They are named in a sortable order, e.g. `YYYYMMDD_HHMMSS_description.sql`.
- Applied migrations are tracked in a DB table called `migrations`.
- Use the CLI script `scripts/migrate.php` to inspect and apply migrations.

## Usage

1. Show current status

```bash
php scripts/migrate.php status
```

2. Apply all pending migrations

```bash
php scripts/migrate.php up
```

3. Typical deployment steps

- Pull new code from git.
- Put the site in maintenance mode (recommended): `php scripts/maintenance.php on "Upgrading database"`.
- Run `php scripts/migrate.php status`.
- If there are pending migrations, run `php scripts/migrate.php up`.
- Disable maintenance mode: `php scripts/maintenance.php off`.
- Clear opcache if applicable and resume traffic.

## Maintenance mode

Enable maintenance mode to temporarily block non-admin traffic during upgrades. Superusers (user ID 1 or with `superuser` right) can still access the site.

Commands:

```bash
# Turn on with optional message
php scripts/maintenance.php on "Upgrading database"

# Turn off
php scripts/maintenance.php off

# Status
php scripts/maintenance.php status
```

## Authoring new migrations

1. Create a new SQL file in `doc/database/migrations/`, e.g.:

```
doc/database/migrations/20250924_170001_add_user_meta_theme.sql
```

2. Write forward-only SQL. Avoid destructive changes unless absolutely necessary.

3. Prefer idempotent SQL. For MySQL 8.0+ you can use `ADD COLUMN IF NOT EXISTS`. For older MySQL/MariaDB versions, either:

- Check existence in PHP and conditionally run DDL, or
- Write migrations that are safe to run once and tracked by the `migrations` table.

## Notes

- The application checks for pending migrations at runtime and shows a warning banner but will not auto-apply changes.
- The `migrations` table is created automatically by the runner if missing.
- The runner executes each migration inside a single transaction (when supported by the storage engine for the statements used). If any statement fails, the migration batch is rolled back and no migration is marked as applied.

## Example migration

This repo includes an example migration that adds a per-user theme column:

```
20250924_170001_add_user_meta_theme.sql
```

It adds `user_meta.theme` used to store each user's preferred theme.
