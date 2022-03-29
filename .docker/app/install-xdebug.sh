#!/usr/bin/env bash

if [[ "${WITHOUT_XDEBUG}" == "1" ]]
then
    echo '-------------------------------------------------------------------'
    echo 'Installing WITHOUT XDEBUG'
    echo '-------------------------------------------------------------------'
else
    echo '-------------------------------------------------------------------'
    echo "Installing WITH XDebug"
    echo '-------------------------------------------------------------------'
    pecl install xdebug && docker-php-ext-enable xdebug
    cat /tmp/xdebug.ini >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini
    rm /tmp/xdebug.ini
fi

echo '-------------------------------------------------------------------'
echo 'XDebug finished'
echo '-------------------------------------------------------------------'
