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

# copy over the code to submit
cp code_to_submit/*.* submission/code/.

# unzip the zip files
if [ "$is_zipped" = true ]; then
	echo "It's a zip file..."
	unzip submission/code/$file_name -d submission/code
	rm submission/code/$file_name
fi

# decide which script to run
if [ "$language_id" == "C" ]; then
	compile_script="compile_c"
elif [ "$language_id" == "Java" ]; then
	compile_script="compile_java"
elif [ "$language_id" == "C++" ]; then
	compile_script="compile_cpp"
else
	echo "language_id is unknown"
	exit 54
fi

./$compile_script.sh $linker_flags $compiler_flags

# delete the code to submit
rm code_to_submit/*

exit 0

