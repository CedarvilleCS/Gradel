#! /bin/bash

if [ "$#" -ne 3 ]; then
	echo "usage: ./compile_c.sh is_zipped input_name output_name"
	exit 1
fi

gcc submission/code/*.c -o a.out

rm -rf code_to_submit/*

count=1
for f in input/*.in; do

	touch submission/output/$count.out
	chmod 775 submission/output/$count.out
	./a.out < $f > submission/output/$count.out
	
	count=$((count+1))
	
done
