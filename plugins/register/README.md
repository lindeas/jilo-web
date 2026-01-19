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

Uses simple callable dispatcher pattern for single-action plugin:
```php
register_plugin_route_prefix('register', [
    'dispatcher' => function($context) {
        require_once PLUGIN_REGISTER_PATH . 'controllers/register.php';
    },
    'access' => 'public',
]);
```

## Dependencies

None - functions independently.
