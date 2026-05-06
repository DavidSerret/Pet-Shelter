#!/usr/bin/env bash

/usr/bin/php /var/www/html/artisan migrate --force --no-ansi -q
/usr/bin/php /var/www/html/artisan db:seed --force --no-ansi -q
/usr/bin/php /var/www/html/artisan storage:link --force --no-ansi -q