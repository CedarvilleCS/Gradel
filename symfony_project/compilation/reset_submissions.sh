#!/bin/bash

if [ -d "submissions" ]; then
	rm -rf "submissions"
	mkdir "submissions"
    chown $USER submissions
	chmod 777 submissions
    echo "cleaned out submissions directory"

    touch submissions/.gitkeep
else
	echo "submissions directory does not exist"
fi
