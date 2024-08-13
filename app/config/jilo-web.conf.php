<?php

return [

    //*******************
    // edit to customize
    //*******************

    // domain for the web app
    'domain'			=> 'localhost',
    // subfolder for the web app, if any
    'folder'			=> '/jilo-web/',
    // database with logs from Jilo
    'jilo_database'		=> '../../jilo/jilo.db',
    // set to false to disable new registrations
    'registration_enabled'	=> true,
    // will be displayed on login screen
    'login_message'		=> '',

    //*******************************************
    // edit only if needed for tests or debugging
    //*******************************************

    'db' => [
        // DB type for the web app, currently only "sqlite" is used
        'db_type'		=> 'sqlite',
        // default is ../app/jilo-web.db
        'sqlite_file'		=> '../app/jilo-web.db',
    ],
    // system info
    'version'			=> '0.1.1',
];

?>
