#!/bin/bash

if [ "$#" -ne 11 ]; then

	echo "$#"
	echo "usage: ./dockercompiler.sh"
	echo "(1)problem_id (2)team_id"
	echo "(3)submitted_file_path (4)submitted_file_name (5)submitted_file_type "
	echo "(6)is_zipped (7)time_limit (8)linker_flags (9) compiler_flags"
	echo "(10)output_name (11)timestamp"
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

linker_flags="$8"
compiler_flags="$9"

output_name="${10}"

timestamp="${11}"

echo "The output_name is $output_name"
echo "The team_id is $team_id"
echo "The problem_id is $problem_id"

TEAM_DIRECTORY="$PWD/submissions/$team_id"
PROBLEM_DIRECTORY="$PWD/submissions/$team_id/$problem_id"
SUBMISSION_DIRECTORY="$PWD/submissions/$team_id/$problem_id/$output_name"
CODE_DIRECTORY="$PWD/submissions/$team_id/$problem_id/$output_name/code"
OUTPUT_DIRECTORY="$PWD/submissions/$team_id/$problem_id/$output_name/output"
LOG_DIRECTORY="$PWD/submissions/$team_id/$problem_id/$output_name/logs"

echo "team_id directory: $TEAM_DIRECTORY"
echo "problem_id directory: $PROBLEM_DIRECTORY"
echo "submitted directory: $CODE_DIRECTORY"
echo "output directory: $OUTPUT_DIRECTORY"
echo "log directory: $LOG_DIRECTORY"

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

# check if the timestamp folder exists yet
if [ ! -d "$SUBMISSION_DIRECTORY" ]; then
	mkdir "$SUBMISSION_DIRECTORY"
	chmod 775 "$SUBMISSION_DIRECTORY"
	echo "created submitted directory"
else
	echo "directory already exists for this submission"	
	exit 1;
fi

# check if the problem has input files
if [ ! -d "$PWD/problems/$problem_id/input" ]; then

	echo "$PWD/problems/$problem_id/input does not exist"
	exit 1;
	
else
	
	file_count=$(find problems/$problem_id/input/ -maxdepth 1 -name "*.in" | wc -l)
	
	echo $file_count
	
	if [ $file_count -lt 1 ]; then
		echo "this problem has no input test cases"
		exit 1;
	else 
		echo "$PWD/problems/$problem_id/input exists and has $file_count input cases"
	fi

fi

# make the submission directory
mkdir "$CODE_DIRECTORY"
chmod 775 "$CODE_DIRECTORY"
echo "created submitted directory"

# make the output directory
mkdir "$OUTPUT_DIRECTORY"
chmod 775 "$OUTPUT_DIRECTORY"
echo "created output directory"

# make the log directory
mkdir "$LOG_DIRECTORY"
chmod 775 "$LOG_DIRECTORY"
echo "created output directory"



# copy the submitted file over into the mounted directory
if [ -f "$file_path/$file_name" ]; then
	cp "$file_path/$file_name" "$PWD/code_to_submit/$file_name"
fi	

# run the sandbox
docker run --name=gradelone -d -v $SUBMISSION_DIRECTORY:/home/abc/submission -v $PWD/code_to_submit:/home/abc/code_to_submit -v $PWD/problems/$problem_id/input:/home/abc/input gradel /home/abc/compile_code.sh $file_type $is_zipped $file_name $output_name

echo "timeout $time_limit docker wait gradelone"
code=$(timeout "$time_limit"s docker wait gradelone || true)

docker kill gradelone
docker rm gradelone

echo -n 'status: '
if [ -z "$code" ]; then
    echo TIMEOUT	
else
    echo exited with $code
fi


# diff through the outputs to see how they compare



