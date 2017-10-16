#!/bin/bash

if [ "$#" -ne 9 ]; then

	echo "$#"
	echo "usage: ./dockercompiler.sh"
	echo "(1)problem_id (2)team_id"
	echo "(3)submitted_file_path (4)submitted_file_name (5)submitted_language_name "
	echo "(6)is_zipped (7)time_limit (8)compiler_flags"
	echo "(9)submission_id"
	exit 1
fi

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

echo "Variable names..."

echo "The submission_id is $submission_id"
echo "The team_id is $team_id"
echo "The problem_id is $problem_id"

SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"

TEAM_DIRECTORY="$SCRIPT_DIR/submissions/$team_id"
PROBLEM_DIRECTORY="$SCRIPT_DIR/submissions/$team_id/$problem_id"
SUBMISSION_DIRECTORY="$SCRIPT_DIR/submissions/$team_id/$problem_id/$submission_id"
CODE_DIRECTORY="$SCRIPT_DIR/submissions/$team_id/$problem_id/$submission_id/code"
STUDENT_OUTPUT_DIRECTORY="$SCRIPT_DIR/submissions/$team_id/$problem_id/$submission_id/output"
LOG_DIRECTORY="$SCRIPT_DIR/submissions/$team_id/$problem_id/$submission_id/logs"

CODE_TO_SUBMIT_DIRECTORY="$SCRIPT_DIR/code_to_submit/$submission_id/"

INPUT_DIRECTORY="$SCRIPT_DIR/temp/$submission_id/input"
EXPECTED_OUTPUT_DIRECTORY="$SCRIPT_DIR/temp/$submission_id/output"

echo "team_id directory: $TEAM_DIRECTORY"
echo "problem_id directory: $PROBLEM_DIRECTORY"
echo "submitted directory: $CODE_DIRECTORY"
echo "output directory: $STUDENT_OUTPUT_DIRECTORY"
echo "log directory: $LOG_DIRECTORY"

# Folder creation
echo ""
echo "Creating the directory structure for temporary file storage..."

# check if the team_id folder exists yet
if [ ! -d "$TEAM_DIRECTORY" ]; then
	mkdir "$TEAM_DIRECTORY"
	chmod 775 "$TEAM_DIRECTORY"
	echo "created team directory"
else
	echo "team directory already exists"
fi

# check if the problem_id folder exists yet
if [ ! -d "$PROBLEM_DIRECTORY" ]; then
	mkdir "$PROBLEM_DIRECTORY"
	chmod 775 "$PROBLEM_DIRECTORY"
	echo "created problem directory"
else
	echo "problem directory already exists"
fi

# check if the submission folder exists yet
if [ ! -d "$SUBMISSION_DIRECTORY" ]; then
	mkdir "$SUBMISSION_DIRECTORY"
	chmod 775 "$SUBMISSION_DIRECTORY"
	echo "created submitted directory"
else
	echo "directory already exists for this submission"	
	exit 1
fi

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

# make the submission directory
mkdir "$CODE_DIRECTORY"
chmod 775 "$CODE_DIRECTORY"
echo "created submitted directory"

# make the output directory
mkdir "$STUDENT_OUTPUT_DIRECTORY"
chmod 775 "$STUDENT_OUTPUT_DIRECTORY"
echo "created output directory"

# make the log directory
mkdir "$LOG_DIRECTORY"
chmod 775 "$LOG_DIRECTORY"
echo "created log directory"

# copy the submitted file over into the mounted directory
if [ -f "$file_path/$file_name" ]; then

	mkdir -p $CODE_TO_SUBMIT_DIRECTORY
	echo "Found submitted file $file_path/$file_name. Copying to submit directory..."
	cp "$file_path/$file_name" "$CODE_TO_SUBMIT_DIRECTORY$file_name"

else
	echo "Cannot find submitted file $file_path/$file_name"
	exit 1
fi	


# change permissions on shared directories

# run the sandbox
echo ""
echo "Creating the docker sandbox to run student code..."

submission_mount_option="-v $SUBMISSION_DIRECTORY:/home/abc/submission"
code_to_submit_mount_option="-v $CODE_TO_SUBMIT_DIRECTORY:/home/abc/code_to_submit"
input_testcases_mount_option="-v $INPUT_DIRECTORY:/home/abc/input"
output_testcases_mount_option="-v $EXPECTED_OUTPUT_DIRECTORY:/home/abc/output"

script_command="/home/abc/compile_code.sh $file_type $is_zipped $file_name $linker_flags $compiler_flags"

container_name="gd$submission_id"

echo "docker run --name=gd$submission_id -d $submission_mount_option $code_to_submit_mount_option $input_testcases_mount_option $output_testcases_mount_option gradel $script_command"

return

echo $(docker run --name=$container_name -d $submission_mount_option $code_to_submit_mount_option $input_testcases_mount_option $output_testcases_mount_option gradel $script_command)

echo "timeout $time_limit docker wait gd$submission_id"

code=$(timeout $time_limit docker wait gd$submission_id 2>&1 || true)

echo $(docker kill $container_name 2>&1)
echo $(docker rm $container_name 2>&1)

echo -n 'status: '
if [ -z "$code" ]; then
    echo TIMEOUT	
else
    echo exited with $code
fi

rm -rf $CODE_TO_SUBMIT_DIRECTORY
