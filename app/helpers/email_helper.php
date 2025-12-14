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
 * @return string Rendered template content
 */
function renderEmailTemplate($templateName, $variables = []) {
    $templateFile = __DIR__ . '/../templates/' . $templateName . '.txt';

    if (!file_exists($templateFile)) {
        throw new RuntimeException("Email template '$templateName' not found");
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
 * @return bool Success status
 */
function sendTemplateEmail($to, $subject, $templateName, $variables, $config, $additionalHeaders = []) {
    try {
        $message = renderEmailTemplate($templateName, $variables);

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
