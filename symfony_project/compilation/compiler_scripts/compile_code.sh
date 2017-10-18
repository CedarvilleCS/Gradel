#! /bin/bash

if [ "$#" -ne 6 ]; then
	echo "usage: ./compile_code.sh (1)language_id (2)is_zipped"
	echo "(3)file_name (4)compiler_flags"
	echo "(5)main class (6)package name"
	exit 55
fi

# get variables from the command line
language_id="$1"
is_zipped="$2"
file_name="$3"
compiler_flags="$4"
main_class="$5"
package_name="$6"

if [ "$compiler_flags" == "''" ]; then
	compiler_flags=""
fi

# copy over the code to submit
cp -r code_to_submit/*.* submission/code/.

# unzip the zip files
if [ "$is_zipped" = true ]; then
	echo "It's a zip file..."
	unzip submission/code/$file_name -d submission/code
	rm submission/code/$file_name
fi

# decide which script to run
if [ "$language_id" == "C" ]; then
	compile_script="compile_c"	
	./$compile_script.sh "$compiler_flags"
elif [ "$language_id" == "Java" ]; then
	compile_script="compile_java"
	./$compile_script.sh "$compiler_flags" "$main_class" "$package_name"
elif [ "$language_id" == "C++" ]; then
	compile_script="compile_cpp"
	./$compile_script.sh "$compiler_flags"
else
	echo "language_id $langueage_id is unknown"
	exit 54
fi

exit 0

