<?php

/**
 * Shared URL canonicalization utilities.
 *
 * Provides query normalization/compare helpers plus a policy-based builder so
 * controllers can define canonical query contracts declaratively.
 */

/**
 * Normalize query payload recursively for stable comparisons.
 *
 * - Sort associative keys.
 * - Re-index list arrays.
 * - Drop null values.
 * - Convert scalar values to strings so GET payloads and canonical payloads
 *   compare by value rather than PHP type.
 *
 * @param array<string,mixed> $query
 * @return array<string,mixed>
 */
function app_url_normalize_query(array $query): array
{
    $normalized = [];

    foreach ($query as $key => $value) {
        if ($value === null) {
            continue;
        }

        if (is_array($value)) {
            $child = app_url_normalize_query($value);
            $normalized[$key] = array_is_list($value)
                ? array_values($child)
                : $child;
            continue;
        }

        if (is_bool($value)) {
            $normalized[$key] = $value ? '1' : '0';
            continue;
        }

        $normalized[$key] = (string)$value;
    }

    if (!array_is_list($normalized)) {
        ksort($normalized);
    }

    return $normalized;
}

/**
 * Compare two query arrays after canonical normalization.
 *
 * @param array<string,mixed> $left
 * @param array<string,mixed> $right
 */
function app_url_queries_match(array $left, array $right): bool
{
    return app_url_normalize_query($left) === app_url_normalize_query($right);
}

/**
 * Build an internal app URL from a canonical query payload.
 *
 * @param array<string,mixed> $query
 */
function app_url_build_internal(string $appRoot, array $query): string
{
    return rtrim($appRoot !== '' ? $appRoot : '/', '/?&') . '/?' . http_build_query($query);
}

/**
 * Redirect to canonical query URL when current and canonical payloads differ.
 *
 * @param array<string,mixed> $currentQuery
 * @param array<string,mixed> $canonicalQuery
 */
function app_url_redirect_to_canonical_query(string $appRoot, array $currentQuery, array $canonicalQuery): void
{
    if (!app_url_queries_match($currentQuery, $canonicalQuery)) {
        header('Location: ' . app_url_build_internal($appRoot, $canonicalQuery));
        exit;
    }
}

/**
 * Resolve one canonical value from a policy rule.
 *
 * Supported rule types:
 * - literal: fixed `value`
 * - string: trimmed scalar string
 * - int: validated integer with optional `min` / `max`
 * - enum: trimmed scalar string constrained by `allowed`
 * - bool_flag: includes `value_true` only when request value is truthy
 * - string_list: trims and filters array items
 *
 * @param string $targetKey
 * @param array<string,mixed> $rule
 * @param array<string,mixed> $sourceQuery
 * @return mixed|null
 */
function app_url_policy_value(string $targetKey, array $rule, array $sourceQuery)
{
    $type = (string)($rule['type'] ?? 'string');
    $sourceKey = (string)($rule['source'] ?? $targetKey);

    if ($type === 'literal') {
        return $rule['value'] ?? null;
    }

    $hasSourceValue = array_key_exists($sourceKey, $sourceQuery);
    $rawValue = $hasSourceValue ? $sourceQuery[$sourceKey] : null;

    if (!$hasSourceValue) {
        return $rule['default'] ?? null;
    }

    if ($type === 'string') {
        if (is_array($rawValue)) {
            return $rule['default'] ?? null;
        }
        $value = (string)$rawValue;
        if (($rule['trim'] ?? true) === true) {
            $value = trim($value);
        }
        if ($value === '' && !($rule['allow_empty'] ?? false)) {
            return $rule['default'] ?? null;
        }
        return $value;
    }

    if ($type === 'int') {
        if (is_array($rawValue)) {
            return $rule['default'] ?? null;
        }
        $candidate = trim((string)$rawValue);
        if ($candidate === '' || filter_var($candidate, FILTER_VALIDATE_INT) === false) {
            return $rule['default'] ?? null;
        }
        $value = (int)$candidate;
        if (isset($rule['min']) && $value < (int)$rule['min']) {
            return $rule['default'] ?? null;
        }
        if (isset($rule['max']) && $value > (int)$rule['max']) {
            return $rule['default'] ?? null;
        }
        return $value;
    }

    if ($type === 'enum') {
        if (is_array($rawValue)) {
            return $rule['default'] ?? null;
        }
        $value = trim((string)$rawValue);
        if ($value === '') {
            return $rule['default'] ?? null;
        }
        $allowed = is_array($rule['allowed'] ?? null) ? $rule['allowed'] : [];
        if (!in_array($value, $allowed, true)) {
            return $rule['default'] ?? null;
        }
        return $value;
    }

    if ($type === 'bool_flag') {
        if (is_array($rawValue)) {
            return $rule['default'] ?? null;
        }
        $truthyValues = is_array($rule['truthy_values'] ?? null)
            ? $rule['truthy_values']
            : ['1', 'true', 'yes', 'on'];
        $candidate = strtolower(trim((string)$rawValue));
        if (in_array($candidate, $truthyValues, true)) {
            return $rule['value_true'] ?? '1';
        }
        return $rule['default'] ?? null;
    }

    if ($type === 'string_list') {
        if (!is_array($rawValue)) {
            return $rule['default'] ?? null;
        }

        // Normalize list payloads into deterministic arrays for canonical URLs.
        $items = array_map(static function ($item): string {
            return trim((string)$item);
        }, $rawValue);
        $items = array_values(array_filter($items, static function ($item): bool {
            return $item !== '';
        }));

        if (!empty($rule['unique'])) {
            $items = array_values(array_unique($items));
        }

        if (empty($items) && !($rule['allow_empty'] ?? false)) {
            return $rule['default'] ?? null;
        }
        return $items;
    }

    return $rule['default'] ?? null;
}

/**
 * Build canonical query payload from declarative policy rules.
 *
 * @param array<string,mixed> $sourceQuery
 * @param array<string,array<string,mixed>> $policy
 * @return array<string,mixed>
 */
function app_url_build_query_from_policy(array $sourceQuery, array $policy): array
{
    $canonical = [];

    foreach ($policy as $targetKey => $rule) {
        if (!is_array($rule)) {
            continue;
        }

        if (isset($rule['include_if']) && is_callable($rule['include_if']) && !$rule['include_if']($sourceQuery, $canonical)) {
            continue;
        }

        $value = app_url_policy_value((string)$targetKey, $rule, $sourceQuery);
        if ($value === null) {
            continue;
        }

        if (isset($rule['transform']) && is_callable($rule['transform'])) {
            $value = $rule['transform']($value, $sourceQuery, $canonical);
            if ($value === null) {
                continue;
            }
        }

        if (isset($rule['validator']) && is_callable($rule['validator']) && !$rule['validator']($value, $sourceQuery, $canonical)) {
            continue;
        }

        if (array_key_exists('omit_if', $rule) && $value === $rule['omit_if']) {
            continue;
        }

        $canonical[(string)$targetKey] = $value;
    }

    return $canonical;
}
