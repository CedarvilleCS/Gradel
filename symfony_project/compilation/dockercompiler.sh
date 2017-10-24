#!/bin/bash

# if [ "$#" -ne 11 ]; then

	# echo "$#"
	# echo "usage: ./dockercompiler.sh"
	# echo "(1)problem_id (2)team_id"
	# echo "(3)submitted_file_path (4)submitted_file_name (5)submitted_language_name "
	# echo "(6)is_zipped (7)time_limit (8)compiler_flags"
	# echo "(9)submission_id (10)main_class (11)package_name"
	# exit 1
# fi

# get the variables from the command arguments
problem_id="$1"
team_id="$2"

file_path="$3"
file_name="$4"
file_type="$5"
is_zipped="$6"
time_limit="$7"

compiler_flags="$8"

submission_id="$9"


if [ "$#" -ge 10 ]; then
	main_class="${10}"
fi

if [ "$#" -ge 11 ]; then
	package_name="${11}"
fi

echo "Variable names..."

echo "The submission_id is $submission_id"
echo "The team_id is $team_id"
echo "The problem_id is $problem_id"

SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"

SUBMISSION_DIRECTORY="$SCRIPT_DIR/submissions/$submission_id"
CODE_DIRECTORY="$SCRIPT_DIR/submissions/$submission_id/code"
STUDENT_OUTPUT_DIRECTORY="$SCRIPT_DIR/submissions/$submission_id/output"
RUNTIME_LOG_DIRECTORY="$SCRIPT_DIR/submissions/$submission_id/runtime_logs"
DIFF_LOG_DIRECTORY="$SCRIPT_DIR/submissions/$submission_id/diff_logs"
TIME_LOG_DIRECTORY="$SCRIPT_DIR/submissions/$submission_id/time_logs"

CODE_TO_SUBMIT_DIRECTORY="$SCRIPT_DIR/code_to_submit/$submission_id"

INPUT_DIRECTORY="$SCRIPT_DIR/temp/$submission_id/input"
EXPECTED_OUTPUT_DIRECTORY="$SCRIPT_DIR/temp/$submission_id/output"

echo "submitted directory: $CODE_DIRECTORY"
echo "output directory: $STUDENT_OUTPUT_DIRECTORY"
echo "runtime log directory: $RUNTIME_LOG_DIRECTORY"
echo "diff log directory: $DIFF_LOG_DIRECTORY"
echo "diff log directory: $TIME_LOG_DIRECTORY"

# check if the problem has input files
if [ ! -d "$INPUT_DIRECTORY" ] || [ ! -d "$EXPECTED_OUTPUT_DIRECTORY" ]; then

	echo "$INPUT_DIRECTORY or $EXPECTED_OUTPUT_DIRECTORY does not exist"
	exit 1
	
else
	
	file_count=$(find $INPUT_DIRECTORY -maxdepth 1 -name "*.in" | wc -l)
	other_file_count=$(find $EXPECTED_OUTPUT_DIRECTORY -maxdepth 1 -name "*.out" | wc -l)
	
	ls -l $INPUT_DIRECTORY;
	
	if [ $file_count -lt 1 ]; then
		echo "this problem has no input test cases"
		exit 1
	elif [ $other_file_count -ne $file_count ]; then
		echo "this problem does not have the same number of input and output files"
		exit 1	
	else
		echo "$INPUT_DIRECTORY exists and has $file_count input cases"
	fi

fi


# copy the submitted file over into the mounted directory
if [ -f "$file_path/$file_name" ]; then

	echo "Found submitted file $file_path/$file_name. Copying to submit directory..."
	cp "$file_path/$file_name" "$CODE_TO_SUBMIT_DIRECTORY/$file_name"

else
	echo "Cannot find submitted file $file_path/$file_name"
	exit 1
fi	

# run the sandbox
echo ""
echo "Creating the docker sandbox to run student code..."

submission_mount_option="-v $SUBMISSION_DIRECTORY:/home/abc/submission"
code_to_submit_mount_option="-v $CODE_TO_SUBMIT_DIRECTORY:/home/abc/code_to_submit:ro"
input_testcases_mount_option="-v $INPUT_DIRECTORY:/home/abc/input:ro"
output_testcases_mount_option="-v $EXPECTED_OUTPUT_DIRECTORY:/home/abc/output:ro"

group_mount_option="-v /etc/group:/etc/group:ro"
passwd_mount_option="-v /etc/passwd:/etc/passwd:ro"

user_option="-u $( id -u $USER ):$( id -g $USER )"

echo "FILETYPE IS: $file_type"

if [ $file_type == "Java" ]; then
	echo "$# is the number"
	if [ $# -ge 11 ]; then
		echo "HI"
		java_script_ext="-M $main_class -P $package_name"
	elif [ $# -ge 10 ]; then
		echo "HEY"
		java_script_ext="-M $main_class"
	else
		echo "YO"
		java_script_ext=""
	fi
	
else
	java_script_ext=""
fi

if [ $is_zipped ]; then
	zip_ext="-z"
else
	zip_ext="";
fi

script_command="/home/abc/compile_code -l ${file_type} -f ${file_name} $zip_ext -c '${compiler_flags}' $java_script_ext"

container_name="gd$submission_id"

echo "docker run --name=$container_name -d $group_mount_option $passwd_mount_option $submission_mount_option $code_to_submit_mount_option $input_testcases_mount_option $output_testcases_mount_option gradel $script_command"

echo $(docker run --name=$container_name -d $group_mount_option $passwd_mount_option $submission_mount_option $code_to_submit_mount_option $input_testcases_mount_option $output_testcases_mount_option gradel $script_command)

echo "timeout $time_limit docker wait gd$submission_id"

code=$(timeout $time_limit docker wait gd$submission_id 2>&1 || true)

echo $(docker kill $container_name 2>&1)
echo $(docker rm $container_name 2>&1)

echo -n 'status: '
if [ -z "$code" ]; then
    echo TIMEOUT
	touch $SUBMISSION_DIRECTORY/dockertimeout
else
    echo exited with $code
fi
