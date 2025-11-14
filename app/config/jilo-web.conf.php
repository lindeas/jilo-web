<?php

return [

    //*******************
    // edit to customize
    //*******************

    // domain for the web app
    'domain'			=> 'localhost',
    // subfolder for the web app, if any
    'folder'			=> '/jilo-web/',
    // site name used in emails and in the interface
    'site_name'			=> 'Jilo Web',

    // session configuration
    'session' => [
        // session name, if empty a random one will be generated
        'name' => 'jilo',
        // 2 hours (7200) default, when "remember me" is not checked
        'lifetime' => '7200',
        // 30 days (2592000) default, when "remember me" is checked
        'remember_me_lifetime' => '2592000',
    ],
    // set to false to disable new registrations
    'registration_enabled'	=> true,
    // will be displayed on login screen
    'login_message'		=> '',

    //*******************************************
    // edit only if needed for tests or debugging
    //*******************************************

    // database
    'db_type'			=> 'mariadb',

    'sqlite' => [
        'sqlite_file'		=> '../app/jilo-web.db',
    ],

    'sql' => [
        'sql_host'		=> 'localhost',
        'sql_port'		=> '3306',
        'sql_database'		=> 'jilo',
        'sql_username'		=> 'jilouser',
        'sql_password'		=> 'jilopass',
    ],

    // avatars path
    'avatars_path'		=> 'uploads/avatars/',
    // default avatar
    'default_avatar'		=> 'static/default_avatar.png',
    // system info
    'version'			=> '0.4.1',
    // development has verbose error messages, production has not
    'environment'		=> 'development',

];
