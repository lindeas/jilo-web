# URL canonicalization and normalization guide

This document defines the standard flow for query-string canonicalization in page/controllers.
Use it for all new route work and when touching existing page logic.

## Why this exists

Canonical URLs make route behavior predictable and secure by:
- removing unknown query parameters,
- normalizing known parameters to expected types,
- preventing duplicate URL variants for the same page state,
- reducing controller-specific ad-hoc redirect logic.

## Shared helper

All route canonicalization must use:
- `app/helpers/url_canonicalizer.php`

Core functions:
- `app_url_build_query_from_policy(array $sourceQuery, array $policy): array`
- `app_url_redirect_to_canonical_query(string $appRoot, array $currentQuery, array $canonicalQuery): void`
- `app_url_build_internal(string $appRoot, array $query): string`
- `app_url_policy_value(string $targetKey, array $rule, array $sourceQuery)`

## Standard controller flow

For GET routes, follow this order:

1. Resolve request context (`app_root`, user/session state, etc.).
2. Resolve a defensive GET guard (`$isGetRequest`) from `$_SERVER['REQUEST_METHOD']`.
3. Define canonical policy rules for the route.
4. Build canonical query from `$_GET`.
5. Redirect if current query differs from canonical query.
6. Continue regular page logic (rendering, DB loading, etc.).

Reference pattern:

```php
require_once APP_PATH . 'helpers/url_canonicalizer.php';

$isGetRequest = strtoupper((string)($_SERVER['REQUEST_METHOD'] ?? 'GET')) === 'GET';
if ($isGetRequest) {
    $canonicalPolicy = [
        'page' => [
            'type' => 'literal',
            'value' => 'example',
        ],
    ];

    $canonicalQuery = app_url_build_query_from_policy($_GET, $canonicalPolicy);

    // Keep example URLs constrained to supported route state.
    app_url_redirect_to_canonical_query((string)$app_root, $_GET, $canonicalQuery);
}
```

## Policy rule types

Supported rule `type` values:
- `literal`: fixed value from policy (`value`)
- `string`: trimmed scalar string
- `int`: integer with optional bounds (`min`, `max`)
- `enum`: string limited to `allowed` values
- `bool_flag`: emits `value_true` for truthy request inputs
- `string_list`: normalized list values (optionally `unique`)

Useful options:
- `source`: map canonical key from another source key
- `default`: fallback value
- `include_if`: callable gate to include rule conditionally
- `omit_if`: drop key when value equals sentinel
- `transform`: callable value transformer
- `validator`: callable final validator

## Route design rules

When adding canonicalization:
- Always include `page` as `literal`.
- Keep allowed query set minimal.
- Use `enum` for fixed states (`tab`, `action`, `status`, etc.).
- Use `int` with bounds for IDs and pagination.
- Use `omit_if` to avoid noisy defaults in URLs (for example `p=1`).
- Preserve only query keys that materially represent page state.

## What not to canonicalize as page URLs

Do not force page-style canonicalization on non-page endpoints that intentionally behave as API/callback streams, for example:
- JSON suggestion endpoints,
- payment webhook/callback handlers,
- binary/document output handlers,
- static asset streaming handlers.

For these endpoints, keep strict input validation and explicit allowlists as currently implemented.

## Redirect behavior

`app_url_redirect_to_canonical_query` compares normalized current and canonical queries.
If different, it sends a `Location` header and exits.

Implications:
- Logic after the call runs only for canonical request URLs.
- Downstream code may continue reading `$_GET`; values are already canonicalized by redirect gate.
- If custom redirect URL construction is needed after POST actions, use `app_url_build_internal` with a policy-built query.

## Update checklist for new/edited routes

When changing a route:
1. Add/confirm `require_once` for `url_canonicalizer.php`.
2. Use the standardized defensive guard:
   `$isGetRequest = strtoupper((string)($_SERVER['REQUEST_METHOD'] ?? 'GET')) === 'GET';`
3. Add/adjust GET canonical policy near route entry.
4. Keep existing business logic unchanged unless explicitly requested.
5. Add concise inline comment for non-trivial policy/condition blocks.
6. Update deployment-facing route documentation used in your environment.
7. Run syntax checks and PHPUnit as part of validation cadence.

## Deployment notes

Coverage is deployment-scoped.

When auditing a specific environment:
- verify enabled route entry points use policy-based canonicalization,
- keep non-page API/callback/document/asset endpoints on strict allowlist
  validation,
- keep local operational/developer documentation updated according to the
  documentation set available in that installation.
