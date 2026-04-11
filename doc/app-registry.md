# App Registry Helper

The `App\App` class provides a service locator to expose core services to
plugins without relying on globals or relative `require_once` paths.

## Goals

1. Provide a stable API surface for plugins and core modules.
2. Allow gradual refactors away from `$GLOBALS`.
3. Keep legacy code working by falling back to existing globals when no service
   has been registered yet.

## Usage

```php
use App\App;

// Register services during bootstrap
App::set('config', $config);
App::set('db', $dbConnection);
App::set('logger', $logger);

// Use services anywhere later
$db = App::db();
$config = App::config();
$logger = App::get('logger');
```

### Convenience Helpers

The helper exposes shortcuts for the most common services:

- `App::db()` – database connection
- `App::config()` – configuration array
- `App::user()` – authenticated user object (if any)

All helper calls fall back to their legacy `$GLOBALS` equivalents so older code
can be migrated incrementally.

### Resetting (Tests)

Unit tests can call `App::reset()` (optionally with a service key) to clear the
registry and avoid old state bleed between test cases.

## Bootstrap Integration

`public_html/index.php` now registers runtime services as they are created:

```php
App::set('config', $config);
App::set('config_path', $configFile);
App::set('app_root', $appRoot);
App::set('db', $db);
App::set('logger', $logger);
```

Plugins should prefer `App` over accessing globals directly. This ensures future
moves (like relocating call logic into `plugins/calls/`) do not require path rewrites
or global variables.
