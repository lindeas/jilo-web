<?php

session_start();
session_unset();
session_destroy();
unset($error);

echo "You logged out.";

?>