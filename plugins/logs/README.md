# Logger plugin

## Overview
The Logger plugin (located in `plugins/logs/`) provides a modular, pluggable logging
system for the application. It records both user and system events in the `log`
table and exposes retrieval utilities plus a built-in UI at `?page=logs`.

The plugin uses the callable dispatcher pattern with `PluginRouteRegistry` for routing
and follows the App API pattern for service access.

## Features
1. **Log entry management**
   - PSR-3-style `log()` method with level + context payloads
   - Core helper `app_log()` for simplified access with NullLogger fallback
2. **Filtering & pagination**
   - Query by scope, user, time range, message text, or specific user IDs
   - Pagination-ready result sets with newest-first sorting
3. **User awareness**
   - Stores username via joins for auditing
   - Captures current user IP via plugin bootstrap
4. **Auto-migration**
   - `logs_ensure_tables()` function creates the `log` table on demand
   - Called automatically via `logger.system_init` hook
5. **UI integration**
   - Adds a "Logs" entry to the top menu
   - Provides list/detail views with tabs for user vs system scopes
   - Uses callable dispatcher for route handling

## Installation
1. Copy the `logs` folder into the project's `plugins/` directory.
2. Enable the plugin via the admin plugin management interface (stored in `settings` table).
3. The plugin bootstrap automatically:
   - Registers the `logs` route prefix with a callable dispatcher
   - Sets up the `logs_ensure_tables()` migration function
   - Initializes the logger via the `logger.system_init` hook

## Database Schema
The plugin defines the following table (auto-created):
```sql
CREATE TABLE IF NOT EXISTS `log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `scope` SET('user','system') NOT NULL,
  `message` VARCHAR(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `log_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
```

## Routing & Dispatcher
The plugin registers its route using `PluginRouteRegistry`:
```php
register_plugin_route_prefix('logs', [
    'dispatcher' => function($action, array $context = []) {
        require_once PLUGIN_LOGS_PATH . 'controllers/logs.php';
        if (function_exists('logs_plugin_handle')) {
            return logs_plugin_handle($action, $context);
        }
        return false;
    },
    'access' => 'private',
    'defaults' => ['action' => 'list'],
    'plugin' => 'logs',
]);
```

## Hook + Loader API
Core must fire the initialization hook after the database connection is ready:
```php
do_hook('logger.system_init', ['db' => $db]);
```
The plugin listener:
- calls `logs_ensure_tables()` to create the `log` table if needed
- resolves the current user IP
- exposes `$GLOBALS['logObject']` (`Log` instance) and `$GLOBALS['user_IP']`

When `$logObject` is not available, use `app_log($level, $message, $context)` which falls back to `NullLogger`.

## PHP API
`Log` lives in `plugins/logs/models/Log.php` and receives the database connector.

### Methods
```php
Log::log(string $level, string $message, array $context = []): void
Log::readLog(int $userId, string $scope, int $offset = 0, int $itemsPerPage = 0, array $filters = []): array
```

### Supported log levels
`emergency`, `alert`, `critical`, `error`, `warning`, `notice`, `info`, `debug`

### Supported filters
- `from_time`: `YYYY-MM-DD` lower bound (inclusive)
- `until_time`: `YYYY-MM-DD` upper bound (inclusive)
- `message`: substring match across message text
- `id`: explicit user ID (system scope only)

### Typical usage
```php
app_log('info', 'User updated profile', [
    'user_id' => $userId,
    'scope'   => 'user',
]);

$entries = $logObject->readLog(
    $userId,
    $scope,
    $offset,
    $itemsPerPage,
    ['message' => 'profile']
);
```

## Usage guidelines
1. **When to log**
   - User actions, authentication events, configuration changes
   - System events, background job outcomes, and security anomalies
2. **Message hygiene**
   - Keep messages concise, include essential metadata, avoid sensitive data
3. **Data integrity**
   - Validate user input before logging to avoid malformed queries
   - Wrap bulk insertions in transactions when necessary
4. **Performance**
   - Prefer pagination for large result sets
   - Index columns used by custom filters if extending the schema
5. **Retention**
   - Schedule archival/log rotation via cron if the table grows quickly

## File Structure
```
plugins/logs/
├─ bootstrap.php         # registers route, migration function, hooks & menu
├─ plugin.json           # plugin metadata
├─ README.md             # this documentation
├─ controllers/
│   └─ logs.php          # procedural handler functions for callable dispatcher
├─ models/
│   ├─ Log.php           # main Log class
│   └─ LoggerFactory.php # migration + factory
├─ helpers/
│   ├─ logs_view_helper.php
├─ helpers.php       # plugin helper wrapper
└─ migrations/
    └─ create_log_table.sql
```

## Controller Architecture
The controller uses procedural functions instead of classes:
- `logs_plugin_handle($action, $context)` - main dispatcher function
- `logs_plugin_render_list($logObject, $db, $userId, $validSession, $app_root)` - renders log list with filters and pagination

The callable dispatcher pattern provides:
- Clean separation of concerns
- Access to request context (user_id, db, app_root, valid_session)
- Consistent error handling and layout rendering

## Admin Plugin Check
The plugin provides `logs_ensure_tables()` for the admin plugin management interface:
- **Owned tables:** `log` (will be removed on purge)
- **Referenced tables:** `user` (dependency, not removed)

## Uninstall / Disable
To disable the plugin:
- Use the admin plugin management interface to disable it (updates the `settings` table), or
- Delete the `plugins/logs/` folder entirely

When disabled, the `app_log()` helper automatically falls back to `NullLogger`, so logging calls remain safe and won't cause errors. To remove plugin data, use the admin plugin management interface to purge the `log` table.
