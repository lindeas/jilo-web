<?php

// 'error' for errors
if (isset($_SESSION['error'])) {
    echo "\t\t" . '<div class="error">' . $_SESSION['error'] . '</div>';
}

// 'notice' for all non-critical messages
if (isset($_SESSION['notice'])) {
    echo "\t\t" . '<div class="notice">' . $_SESSION['notice'] . '</div>';
}

// 'error' for errors
if (isset($error)) {
    echo "\t\t" . '<div class="error">' . $error . '</div>';
}

// 'notice' for all non-critical messages
if (isset($_SESSION['notice'])) {
    echo "\t\t" . '<div class="notice">' . $_SESSION['notice'] . '</div>';
}


?>
