# Jilo Web

## overview

Jilo Web is a PHP web interface to **[Jilo](https://work.lindeas.com/redirect.php?url=jilo)** (JItsi Logs Observer).

To have a working installation, in addition to the code here you need a jilo database file, generated by Jilo.

The webpage for this project is https://lindeas.com/jilo. There you will find information about both Jilo and Jilo Web.

The main git repo of Jilo Web is:
- https://code.lindeas.com/lindeas/jilo-web

It is mirrored at:
- https://codeberg.org/lindeas/jilo-web
- https://github.com/lindeas/jilo-web
- https://gitlab.com/lindeas/jilo-web

You can use any of these git repos to get the program.

You are welcome to send feedback with issues, comments and pull requests to a git mirror you prefer.

## demo

To see a demo install, go to https://work.lindeas.com/jilo-web-demo/

## version

Current version: **0.1** released on **2024-07-08**

## requirements

- web server (deb: apache | nginx)
- php support in the web server (deb: php-fpm | libapache2-mod-php)
- pdo and pdo_sqlite support in php (deb: php-db, php-sqlite3) uncomment in php.ini: ;extension=pdo_sqlite

## installation

You can install it in the following ways:

- download the latest release from the **"Releases"** section here
- clone the **git repo**:
```bash
git clone https://github.com/lindeas/jilo-web.git
cd jilo
```
- DEB and RPM packages are planned, but still unavailable

## config

- edit index.php and set the system path to the jilo-web.conf.php
- edit jilo-web.conf.php and set all the variables correctly
- "database" is the sqlite db file for jilo-web itself, create it with `cat jilo-web.schema | sqlite3 jilo-web.db`
- "jilo_database" is the sqlite db file for jilo, with data from the Jitsi logs

## database

The database is an SQLite file. You also need the sqlite db from jilo (mysql/mariadb support is planned, but still unavailable).
