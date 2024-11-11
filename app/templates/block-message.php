<?php

// Display and clean up session messages
foreach (['error', 'notice'] as $type) {
    if (isset($_SESSION[$type])) {
        renderMessage($_SESSION[$type], $type, true);
    }
}

// Display standalone messages
if (isset($error)) {
    renderMessage($error, 'error', true);
}

if (isset($notice)) {
    renderMessage($notice, 'notice', true);
}
?>
