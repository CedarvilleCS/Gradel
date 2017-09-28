#! /bin/bash

if [ "$#" -ne 4 ]; then
	echo "usage: ./compile_code.sh language_id is_zipped input_name output_name"
	exit 1
fi

language_id="$1"
is_zipped="$2"
input_name="$3"
output_name="$4"

./unzip_file.sh $input_name 

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

./$compile_script.sh $is_zipped $input_name $output_name