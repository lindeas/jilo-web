# Jilo Web

This is still not operational. Goals for v.0.1 - browsing of basic info about Jilo config and about Jitsi Meet conferences.

## demo

To see a demo install, go to https://work.lindeas.com/jilo-web-demo/

## requirements

- web server (deb: apache | nginx)

- php support in the web server (deb: php-fpm | libapache2-mod-php)

- pdo and pdo_sqlite support in php (deb: php-db, php-sqlite3) uncomment in php.ini: ;extension=pdo_sqlite

## config

- edit jilo-web.conf.php and set all the variables correctly

- "database" is the sqlite db file for jilo-web itself, create it with `cat jilo-web.schema | sqlite3 jilo-web.db`

- "jilo_database" is the sqlite db file for jilo, with data from the Jitsi logs
