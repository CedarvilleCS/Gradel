#! /bin/bash

if [ "$#" -ne 4 ] && [ "$#" -ne 3 ]; then
	echo "usage: ./compile_code.sh (1)language_id (2)is_zipped"
	echo "(3)file_name <(4)compiler_flags>"
	exit 55
fi

# get variables from the command line
language_id="$1"
is_zipped="$2"
file_name="$3"

if [ "$#" -eq 4 ]; then
	compiler_flags="$4"
else
	compiler_flags=""
fi

./unzip_file.sh $file_name 

if [ $? -ne 0 ]; then
	echo "error with ./unzip_file.sh"
	exit $?
fi

# decide which script to run
if [ "$language_id" == "c" ]; then
	compile_script="compile_c"
elif [ "$language_id" == "java" ]; then
	compile_script="compile_java"
elif [ "$language_id" == "cpp" ]; then
	compile_script="compile_cpp"
else
	echo "language_id is unknown"
	exit 1
fi

./$compile_script.sh $linker_flags $compiler_flags

# delete the code to submit
rm -rf code_to_submit/*

exit $?

