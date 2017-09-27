#! /bin/bash

php symfony_project/composer.phar install

chown -R $UID symfony_project/*
chmod -R 775 symfony_project/*
chgrp -R 2018 symfony_project/*
