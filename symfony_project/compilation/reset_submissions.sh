#!/bin/bash

if [ -d "submissions" ]; then
	rm -rf "submissions"
	mkdir "submissions"
    chmod -R 775 submissions
	echo "cleaned out submissions directory"

    touch submissions/.gitkeep
else
	echo "submissions directory does not exist"
fi
