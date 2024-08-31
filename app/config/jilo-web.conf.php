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
    'registration_enabled'	=> true,
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
    // system info
    'version'			=> '0.2',
    // development has verbose error messages, production has not
    'environment'		=> 'development',

    // *************************************
    // Maintained by the app, edit with care
    // *************************************

    'platforms' => [
        '0' => [
            'name' => 'lindeas',
            'jitsi_url' => 'https://meet.lindeas.com',
            'jilo_database' => '../../jilo/jilo-meet.lindeas.db',
        ],
        '1' => [
            'name' => 'meet.example.com',
            'jitsi_url' => 'https://meet.example.com',
            'jilo_database' => '../../jilo/jilo.db',
        ],
        '2' => [
            'name' => 'test3',
            'jitsi_url' => 'https://test3.example.com',
            'jilo_database' => '../../jilo/jilo2.db',
        ],
    ],
];

?>
