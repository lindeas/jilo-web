# Logger plugin

## Overview
The Logger plugin provides a modular, pluggable logging system for the application.
It logs user and system events to a MySQL table named `log`.

## Installation
1. Copy the entire `logger` folder into your project's `plugins/` directory.
2. Ensure `"enabled": true` in `plugins/logger/plugin.json`.
3. On first initialization, the plugin will create the `log` table if it does not already exist.

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

## Hook API
Core must call:
```php
// After DB connect:
do_hook('logger.system_init', ['db' => $db]);
```
The plugin listens on `logger.system_init`, runs auto-migration, then sets:
```php
$GLOBALS['logObject']; // instance of Log
$GLOBALS['user_IP'];  // current user IP
```

Then in the code use:
```php
$logObject->insertLog($userId, 'Your message', 'user');
$data = $logObject->readLog($userId, 'user', $offset, $limit, $filters);
```

## File Structure
```
plugins/logger/
├─ bootstrap.php        # registers hook
├─ plugin.json          # metadata & enabled flag
├─ README.md            # this documentation
├─ models/
│   ├─ Log.php          # main Log class
│   └─ LoggerFactory.php# migration + factory
├─ helpers/
│   └─ logs.php         # user IP helper
└─ migrations/
    └─ create_log_table.sql
```

## Uninstall / Disable
- Set `"enabled": false` in `plugin.json` or delete the `plugins/logger/` folder.
- Core code will default to `NullLogger` and no logs will be written.
