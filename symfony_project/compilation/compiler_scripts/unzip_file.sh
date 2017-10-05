#! /bin/bash

if [ "$#" -ne 2 ]; then
	echo "usage: ./unzip_file.sh input_name is_zipped"
	exit 1
fi

input_name="$1"
is_zipped="$2"

if [ "$is_zipped" = true ]; then
	echo "It's a zip file..."
	cp code_to_submit/$input_name submission/code/
	unzip submission/code/$input_name -d submission/code
	rm submission/code/$input_name
else
	cp code_to_submit/$input_name submission/code/
fi

exit 0;