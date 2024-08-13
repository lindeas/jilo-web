<?php

return [
    // edit to customize
    'domain'			=> 'localhost',			// domain for the web app
    'folder'			=> '/jilo-web/',		// subfolder for the web app, if any
    'jilo_database'		=> '../../jilo/jilo.db',	// database with logs from Jilo

    'registration_enabled'	=> true,			// set to false to disable new registrations
    'login_message'		=> '',				// will be displayed on login screen

    // edit only if needed for tests or debugging
    'db' => [
        'db_type'		=> 'sqlite',			// DB type for the web app, currently only "sqlite" is used
        'sqlite_file'		=> '../app/jilo-web.db',	// default is ../app/jilo-web.db
    ],
    'version'			=> '0.1.1',			// system info
];

?>
