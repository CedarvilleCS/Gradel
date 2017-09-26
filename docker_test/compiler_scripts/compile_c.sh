#! /bin/bash

if [ "$#" -ne 3 ]; then
	echo "usage: ./compile_c.sh is_zipped input_name output_name"
	exit 1
fi

if [ "$1" = true ]; then
	echo "It's a zip file..."
	cp code_to_submit/$2 shareddir/code/
	unzip shareddir/code/$2 -d shareddir/code
	rm shareddir/code/$2
else
	cp code_to_submit/$2 shareddir/code/
fi

gcc shareddir/code/*.c -o a.out

rm -rf code_to_submit/*

./a.out > shareddir/output/$3.log