<?php

return [

    //*******************
    // edit to customize
    //*******************

    // domain for the web app
    'domain'			=> 'localhost',
    // subfolder for the web app, if any
    'folder'			=> '/jilo-web/',
    // set to false to disable new registrations
    'registration_enabled'	=> '1',
    // will be displayed on login screen
    'login_message'		=> '',

    //*******************************************
    // edit only if needed for tests or debugging
    //*******************************************

    // database
    'db' => [
        // DB type for the web app, currently only "sqlite" is used
        'db_type'		=> 'sqlite',
        // default is ../app/jilo-web.db
        'sqlite_file'		=> '../app/jilo-web.db',
    ],
    // avatars path
    'avatars_path'		=> 'uploads/avatars/',
    // default avatar
    'default_avatar'		=> 'static/default_avatar.png',
    // system info
    'version'			=> '0.3',
    // development has verbose error messages, production has not
    'environment'		=> 'development',

];
