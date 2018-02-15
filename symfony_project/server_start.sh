./update_cache
nohup php bin/console gos:websocket:server > socket.log 2>&1 &
echo $! > save_pid.txt
