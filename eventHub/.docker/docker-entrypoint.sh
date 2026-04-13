#!/bin/bash

/usr/bin/supervisord -nc /etc/supervisord.conf &

php-fpm
