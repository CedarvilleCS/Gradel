sudo ./update_cache
php bin/console gos:websocket:server


# if [ -z "$1" ]; then
#   echo "Error! Must provide username for websocket starter";
#   exit
# fi
# sudo echo -e "Checking for Currently Running Servers..."
# pid=$(sudo ps aux | grep x'bin/console gos:websocket' | grep $1)
# sudo echo -e "Server running with pid $pid"
# ./update_cache
# php bin/console gos:websocket:server &

