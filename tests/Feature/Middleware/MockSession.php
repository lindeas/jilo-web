<?php

namespace Tests\Feature\Middleware\Mock;

class Session {
    public static function startSession() {}

    public static function isValidSession() {
        return isset($_SESSION["user_id"]) && 
               isset($_SESSION["username"]) &&
               (!isset($_SESSION["LAST_ACTIVITY"]) || 
                $_SESSION["LAST_ACTIVITY"] > time() - 7200 ||
                isset($_SESSION["REMEMBER_ME"]));
    }

    public static function cleanup($config) {
        $_SESSION = [];
    }
}
