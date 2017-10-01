#! /bin/bash

if [ "$#" -ne 1 ] && [ "$#" -ne 0]; then
	echo "usage: ./compile_c.sh <(1)compiler_flags>"
	exit 55
fi

if [ "$#" -eq 1 ]; then
	compiler_flags="$1"
else 
	compiler_flags=""
fi

touch submission/compiler.log
chmod 775 submission/compiler.log
gcc submission/code/*.c $compiler_flags -o a.out > submission/compiler.log

if [ $? -ne 0 ]; then
	echo "error with compiling"
	exit $?
fi

touch submssion/script_running.log
chmod 775 submission/script_running.log

for f in input/*.in; do

	filename=$(basename $f .in)

	touch submission/output/$filename.out
	chmod 775 submission/output/$filename.out
	./a.out < $f > submission/output/$filename.out
	
done

exit 0
