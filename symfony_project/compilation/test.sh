#!/bin/bash


while true
do 
	count=0
	containers=$(docker ps | awk 'BEGIN {count=0} /gd/ {count++} END{print count}')
	echo $containers

	if [ $containers -gt 4 ];
	then
		echo "Waiting... $containers containers running"		
	else
		echo "Ready!"
		break

	fi

	sleep 5
done