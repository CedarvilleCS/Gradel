The purpose of this document is to help with developer issues that may arise when adding new features or trying to figure out why stuff isn't working.

# Table of Contents
- [Fixing Bugs](#fixing-bugs)
    - [Error Messages](#error-messages)
        - [Alert Boxes](#alert-boxes)
        - [500 Errors](#500-errors)
        - [Compilation Errors](#compilation-errors)
    - [Commiting Bug Fixes](#commiting-bug-fixes)
- [Docker Issues](#docker-issues)
    - [Shutting Down Docker Containers](#shutting-down-docker-containers)
    - [Editing Time Limits and Memory](#editing-time-limits-and-memory)
    - [Editing the C++ Delegator](#editing-the-c-delegator)
- [Socket Server](#socket-server)
    - [Restarting the Server](#restarting-the-server)
        - [Easy Method](#easy-method)
        - [Hard Method](#hard-method)


# Fixing Bugs
## Error Messages

### Alert Boxes
When an error occurs, it is important to figure out quickly where it is coming from. Most of the time, Gradel will show an alert box with a little information that can help you. You can run `grep` in the `symfony_project` directory in order to find whatever error message is returned and what file the bug is probably in.

### 500 Errors
If you get an ugly 500 error, the best way to find what is going wrong is to view the log file by running `cat symfony_project/var/logs/prod.log` If it's a query error or something database related, check the controller associated with whatever page it is occurring on. Most of the time, 500 errors are very easy to fix with this log file. 

### Compilation Errors
If a compilation bug is reported, it should have an associated submission id. Most of the time, the submission folder is deleted after the Docker container is done running, but if an error occurs, the submission folder will still exist in `symfony_project\compilation\submissions\<ID#>`

You may have to chown the directory in order to get inside it.

In this directory are other directories used to run the code. The most helpful is the `flags` directory which contains output from inside the docker container. `docker_log` has output from the bash script on the server. `main_logs` has output from the C++ delegator inside the Docker container. That file is where most errors will be logged. Error codes in the docker_log may be helpful as well.

## Commiting Bug Fixes

If a bug is fixed, it should be commited to the repository and then the cache should be updated and socket server restarted with the `update_cache` script. Make sure to also change the version number to avoid javascript caching by incrementing the `framework.assets.version` field in `app/config.yml` before updating.

# Docker Issues

## Shutting Down Docker Containers

If a Docker container goes rogue or the server seems sluggish you may want to run a `sudo top` and see if there is a `docker`, `dockerd`, `compiler`, or `java` file that is taking up a lot of processing power. If that is the case, you can shut it down by running 
```
sudo docker ps -a  #see the container names
sudo docker rm <container_name> --force
```
The container_name will be `gd` followed by the submission id (e.g. `gd618`)

In rare cases, the Docker container will not stop, and we are pretty sure this is an issue with Docker itself. We have no sure-fire way to get rid of these ones. Sometimes you need to remove a zombie process, restart the entire server (`sudo reboot`), or find out how to restart the Docker daemon. Issues like this shouldn't happen anymore with the precautions we have taken.

## Editing Time Limits and Memory

Most memory and time limit settings are either in `src/AppBundle/Controller/CompilationController.php` or `compilation/dockercompiler.sh`

Look at the [Docker documentation](https://docs.docker.com/engine/reference/commandline/run/) to see what available settings there are.

## Editing the C++ Delegator

**_Note: Be very careful changing the compiler source. If you are editing this file, you should probably know what you are doing._**

The C++ executable that runs is located in `symfony_project/compilation/compiler_source`. Changes can be made to this file. Then, you need to run

```
./make_compiler.sh
sudo docker build --tag=gradel:latest ./.
```

from inside the `compilation` directory in order to update the Docker image that creates the containers.


# Socket Server
## Restarting the Server

### Easy Method
Most of the time, you just need to run `update_cache.sh` from inside the `symfony_project` folder. This will remove all caches files server-side (thus recreated entities and utilities) and restart the web socket server using the `server_stop.sh` and `server_start.sh` scripts.

Check to see if the server is running with `cat socket.log`

### Hard Method
If the easy method does not work, you need to manually kill the web socket (this shouldn't happen if you aren't actively developing anything).

At any given time, there may be multiple instances of the socket server running for development branches and the production branch.

To view current branches, execute:

`sudo ps aux | grep gos:websocket`

At this point, the server will display a list of server instances in the following format:

`USERNAME PID  3.5  0.8 309180 33840 pts/7    S    13:03   0:00 php bin/console gos:websocket:server`

The number `PID` is the id of the process, and it may be used to kill the server using:

`sudo kill -9 PID`

At this point, the easy method should work again.


