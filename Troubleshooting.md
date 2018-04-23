The purpose of this document is to help with developer issues that may arise when adding new features

# Socket Server
## Restarting the Server
### Easy Method
For the most part, the provided `server_stop.sh` and `server_start.sh` scripts should be sufficient to restart the server.
However, we have found that sometimes this approach does not work. In this case, use the hard method.

### Hard Method
At any given time, there may be multiple instances of the socket server running for development branches and the production branch.
To view current branches, execute:

`sudo ps aux | grep gos:websocket`

At this point, the server will display a list of server instances in the following format:

`USERNAME PID  3.5  0.8 309180 33840 pts/7    S    13:03   0:00 php bin/console gos:websocket:server`

The number `PID` is the id of the process, and it may be used to kill the server using:

`sudo kill -9 PID`

At this point, the `./server_start.sh` command should work again.
