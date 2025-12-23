<?php

/**
 * Email Template Helper
 *
 * Provides functions to render email templates with variable substitution
 */

/**
 * Render email template with variables
 *
 * @param string $templateName Template filename (without extension)
 * @param array $variables Variables to substitute
 * @param array $options Additional options
 * @return string Rendered template content
 */
function renderEmailTemplate($templateName, $variables = [], array $options = []) {
    $searchPaths = [];

    // Explicit plugin template path takes priority
    if (!empty($options['plugin_template'])) {
        $searchPaths[] = rtrim((string)$options['plugin_template'], DIRECTORY_SEPARATOR);
    }

    // Plugin name maps to its templates directory (if registered)
    if (!empty($options['plugin'])) {
        $pluginKey = (string)$options['plugin'];
        $pluginInfo = $GLOBALS['enabled_plugins'][$pluginKey] ?? null;
        if (!empty($pluginInfo['path'])) {
            $pluginBase = rtrim($pluginInfo['path'], DIRECTORY_SEPARATOR);

            // We search for email templates in the following locations:
            // we can add more locations if needed, but "views/emails" is the standard location
            $searchPaths[] = $pluginBase . '/views/emails';
            $searchPaths[] = $pluginBase . '/views/email';
        }
    }

    // Fallback to core app templates
    $searchPaths[] = __DIR__ . '/../templates/emails';

    $templateFile = null;
    foreach ($searchPaths as $basePath) {
        $candidate = rtrim($basePath, DIRECTORY_SEPARATOR) . '/' . $templateName . '.txt';
        if (is_file($candidate)) {
            $templateFile = $candidate;
            break;
        }
    }

    if ($templateFile === null) {
        throw new RuntimeException("Email template '$templateName' not found in any configured template paths");
    }

    $content = file_get_contents($templateFile);

    // Replace {{variable}} placeholders
    foreach ($variables as $key => $value) {
        $content = str_replace('{{' . $key . '}}', $value, $content);
    }

    return $content;
}

/**
 * Send email using template
 *
 * @param string $to Recipient email
 * @param string $subject Email subject
 * @param string $templateName Template name
 * @param array $variables Template variables
 * @param array $config Application config
 * @param array $additionalHeaders Additional email headers
 * @param array $options Additional options
 * @return bool Success status
 */
function sendTemplateEmail($to, $subject, $templateName, $variables, $config, $additionalHeaders = [], array $options = []) {
    try {
        $message = renderEmailTemplate($templateName, $variables, $options);

        $fromDomain = $config['domain'] ?? ($_SERVER['HTTP_HOST'] ?? 'totalmeet.local');
        $headers = array_merge([
            'From: noreply@' . $fromDomain,
            'X-Mailer: PHP/' . phpversion(),
            'Content-Type: text/plain; charset=UTF-8'
        ], $additionalHeaders);

        return mail($to, $subject, $message, implode("\r\n", $headers));
    } catch (Exception $e) {
        error_log("Failed to send template email: " . $e->getMessage());
        return false;
    }
}
