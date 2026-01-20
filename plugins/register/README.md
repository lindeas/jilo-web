# Register Plugin

Provides user registration functionality.

## API Endpoints

### POST /register?action=register
Register a new user account.

**Request:**
- `username` (string, 3-20 chars, required)
- `password` (string, 8-255 chars, required)
- `confirm_password` (string, required)
- `csrf_token` (string, required)
- `terms` (boolean, required)

**Response:**
```json
{
    "success": true,
    "data": {
        "message": "Registration successful. You can log in now.",
        "redirect_to": "?page=login"
    }
}
```

### GET /register?action=status
Check if registration is enabled.

## Configuration

Enable/disable registration in `totalmeet.conf.php`:
```php
'registration_enabled' => true,
```

## Security Features

- CSRF protection
- Rate limiting
- Password hashing
- Input sanitization

## Implementation

Uses callable dispatcher pattern with procedural handler functions:
```php
register_plugin_route_prefix('register', [
    'dispatcher' => function($action, array $context = []) {
        require_once PLUGIN_REGISTER_PATH . 'controllers/register.php';
        if (function_exists('register_plugin_handle_register')) {
            return register_plugin_handle_register($action, $context);
        }
        return false;
    },
    'access' => 'public',
    'defaults' => ['action' => 'register'],
    'plugin' => 'register',
]);
```

## Controller Architecture

The controller uses procedural functions:
- `register_plugin_handle_register($action, $context)` - main handler
- `register_plugin_handle_submission(...)` - processes form submission
- `register_plugin_render_form(...)` - renders registration form with layout
- `register_plugin_log_success(...)` - logs successful registration

## Database Tables

No plugin-specific tables. Uses core `user` and `user_meta` tables.

## Enable/Disable

The plugin is managed via the admin plugin management interface (stored in `settings` table).
When disabled, the registration route becomes unavailable.

## File Structure

```
plugins/register/
├── bootstrap.php         # registers route with callable dispatcher
├── plugin.json           # plugin metadata
├── README.md             # this documentation
├── controllers/
│   └── register.php      # procedural handler functions
├── models/
│   └── register.php      # registration logic and validation
├── helpers.php           # plugin helper wrapper
└── views/
    └── form-register.php # registration form template
```

## Dependencies

None - functions independently.
