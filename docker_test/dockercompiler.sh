#!/bin/bash

if [ "$#" -ne 7 ]; then
	echo "usage: ./dockercompiler.sh problem_id team_id submitted_file_path submitted_file_name submitted_file_type is_zip time_limit"
	exit 1
fi

problem_id="$1"
team_id="$2"

file_path="$3"
file_name="$4"
file_extension="$5"

time_limit="$7"

echo "The team_id is $team_id"
echo "The problem_id is $problem_id"

timestamp=$(date +%s%3N)

TEAM_DIRECTORY="$PWD/submissions/$team_id"
PROBLEM_DIRECTORY="$PWD/submissions/$team_id/$problem_id"
SUBMISSION_DIRECTORY="$PWD/submissions/$team_id/$problem_id/$timestamp"
CODE_DIRECTORY="$PWD/submissions/$team_id/$problem_id/$timestamp/code"
OUTPUT_DIRECTORY="$PWD/submissions/$team_id/$problem_id/$timestamp/output"

echo "team_id directory: $TEAM_DIRECTORY"
echo "problem_id directory: $PROBLEM_DIRECTORY"
echo "submitted directory: $CODE_DIRECTORY"
echo "output directory: $OUTPUT_DIRECTORY"

# check if the team_id folder exists yet
if [ ! -d "$TEAM_DIRECTORY" ]; then
	mkdir "$TEAM_DIRECTORY"
	echo "created team directory"
else
	echo "team directory already exists"
fi

# check if the problem_id folder exists yet
if [ ! -d "$PROBLEM_DIRECTORY" ]; then
	mkdir "$PROBLEM_DIRECTORY"
	echo "created problem directory"
else
	echo "problem directory already exists"
fi

# check if the timestamp folder exists yet
if [ ! -d "$SUBMISSION_DIRECTORY" ]; then
	mkdir "$SUBMISSION_DIRECTORY"
	echo "created submitted directory"
else
	echo "submitted directory already exists"
fi

# check if the submitted folder exists yet
if [ ! -d "$CODE_DIRECTORY" ]; then
	mkdir "$CODE_DIRECTORY"
	echo "created submitted directory"
else
	echo "submitted directory already exists"
fi

# check if the output folder exists yet
if [ ! -d "$OUTPUT_DIRECTORY" ]; then
	mkdir "$OUTPUT_DIRECTORY"
	echo "created output directory"
else
	echo "output directory already exists"
fi

# copy the submitted file over into the mounted directory
if [ -f "$3/$4" ]; then
	cp "$3/$4" "$PWD/code_to_submit/$4"
fi

# decide which script to run
if [ "$5" == "c" ]; then
	echo "running c script"
	compile_script="compile_c"
elif [ "$5" == "java" ]; then
	echo "running java script"
	compile_script="compile_java"
elif [ "$5" == "cpp" ]; then
	echo "running cpp script"
	compile_script="compile_cpp"
else
	echo "illegal file type"
	exit 1
fi
	

# run the sandbox
docker run -u abc --name=gradelone --rm -v $SUBMISSION_DIRECTORY:/home/abc/shareddir -v $PWD/code_to_submit:/home/abc/code_to_submit gradel sudo /home/abc/$compile_script.sh $6 $file_name $timestamp

echo "submitted code: "
ls -l $CODE_DIRECTORY

# display the output
echo "output: "
cat $OUTPUT_DIRECTORY/*.log