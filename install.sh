#!/usr/bin/env bash

echo 'Jilo-Web install script'

# FIXME
#enter domain
DOMAIN="localhost"

#enter folder
WEB_DIR="jilo-web"

INSTALL_DIR="/opt/jilo-web/public_html"
DOC_DIR="/opt/jilo-web/doc"
ETC_DIR="/opt/jilo-web/etc"

mkdir -p $INSTALL_DIR
cp -r ./public_html/* $INSTALL_DIR

mkdir -p $DOC_DIR
cp CHANGELOG.md $DOC_DIR
cp LICENSE $DOC_DIR
cp README.md $DOC_DIR
cp TODO.md $DOC_DIR
cp config.apache $DOC_DIR
cp config.nginx $DOC_DIR

mkdir -p $ETC_DIR
cp jilo-web.conf.php $ETC_DIR
cp jilo-web.schema $ETC_DIR

#FIXME
#mkdir -p "jilo-web-$VERSION/usr/share/man/man8"
#cp ../man-jilo-web.8 "jilo-web-$VERSION/usr/share/man/man8/jilo-web.8"

# we need a webserver, check for Apache and Nginx
WEB_SERVER=""

# install and enable apache vhost
if dpkg-query -W -f='${status}' apache2 2>/dev/null | grep -q "ok installed"; then
    WEB_SERVER="apache2"
    cp "${DOC_DIR}/config.apache" /etc/apache2/sites-available/jilo-web.conf
    sed -i -e "s/\$DOMAIN/$DOMAIN/g" /etc/apache2/sites-available/jilo-web.conf
    # there is '/' in INSTALL_DIR, we use '%'
    sed -i -e "s%\$INSTALL_DIR%$INSTALL_DIR%g" /etc/apache2/sites-available/jilo-web.conf
    a2ensite jilo-web.conf
    /etc/init.d/apache2 reload

# install and enable nginx vhost
elif dpkg-query -W -f='${status}' nginx 2>/dev/null | grep -q "ok installed"; then
    WEB_SERVER="nginx"
    cp "${DOC_DIR}/config.nginx" /etc/nginx/sites-available/jilo-web
    sed -i -e "s/\$DOMAIN/$DOMAIN/g" /etc/nginx/sites-available/jilo-web
    # there is '/' in INSTALL_DIR, we use '%'
    sed -i -e "s%\$INSTALL_DIR%$INSTALL_DIR%g" /etc/nginx/sites-available/jilo-web
    ln -s /etc/nginx/sites-available/jilo-web /etc/nginx/sites-enabled/
    /etc/init.d/nginx reload
else
    echo "Nginx or Apache is needed, please install one of them first."
    exit 1
fi

# permissions for web folder
chown -R www-data:www-data "$INSTALL_DIR"
chmod -R ug+rw "$INSTALL_DIR"

echo 'Install finished.'
