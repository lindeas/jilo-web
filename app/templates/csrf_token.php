<?php
$token = SecurityHelper::getInstance()->generateCsrfToken();
?>
<input type="hidden" name="csrf_token" value="<?= htmlspecialchars($token) ?>" />
