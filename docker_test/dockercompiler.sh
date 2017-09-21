#!/bin/sh

docker run -u abc --name=gradelone --rm -v /var/www/gradel_dev/tgsmith/Docker/shareddir:/root/shareddir gradel /root/shareddir/compile.sh
