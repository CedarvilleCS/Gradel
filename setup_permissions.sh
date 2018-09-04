#! /bin/bash

chown -R $UID .
chmod -R 775 .
chgrp -R 2019gradel .

chmod -R 777 symfony_project/var/
