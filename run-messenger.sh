#! /bin/bash

set -x
set -e

cd /var/www

function waitForSymfony(){
    while ! php bin/console -q; do sleep 5; echo "Symfony not ready"; done
    echo 'Symfony ready'
}
waitForSymfony

echo '-------------------------------------------------------------------'
echo 'Starting messenger'
echo '-------------------------------------------------------------------'

php bin/console messenger:consume -v

echo '-------------------------------------------------------------------'
echo 'Messenger closed'
echo '-------------------------------------------------------------------'
