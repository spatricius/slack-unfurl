#! /bin/bash

set -x
set -e

cd /var/www

echo '-------------------------------------------------------------------'
echo 'Installing assets'
echo '-------------------------------------------------------------------'

# install assets
php /bin/composer install --no-interaction --no-scripts

echo '-------------------------------------------------------------------'
echo 'Assets finished'
echo '-------------------------------------------------------------------'

# fpm
php-fpm
