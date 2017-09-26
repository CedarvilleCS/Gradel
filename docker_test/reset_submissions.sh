#!/bin/bash

if [ -d "submissions" ]; then
	rm -rf "submissions"
	mkdir "submissions"
	echo "cleaned out submissions directory"
else
	echo "submissions directory does not exist"
fi