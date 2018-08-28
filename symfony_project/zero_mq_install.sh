#! /bin/bash

sudo apt-get install php-pear -y
sudo pecl install zmq-beta

echo 'Remember to add `extension=zmq.so` to the extensions section of /etc/php5/cli/php.ini'
echo 'If you still have issues, uninstall zmq-beta with pecl and then run the command on its own for the install.'
