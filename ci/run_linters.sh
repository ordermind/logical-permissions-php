#!/bin/bash

set -e

echo '*** PHPSTAN ***'
./vendor/bin/phpstan analyse

echo '*** PHPCS ***'
./vendor/bin/phpcbf || true
./vendor/bin/phpcs

echo '*** PHPMD ***'
./vendor/bin/phpmd src text phpmd.xml
./vendor/bin/phpmd tests/Fixtures text phpmd.xml
echo 'No errors'
