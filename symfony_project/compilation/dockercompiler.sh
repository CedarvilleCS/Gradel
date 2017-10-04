#!/bin/bash

if [ "$#" -ne 9 ]; then

	echo "$#"
	echo "usage: ./dockercompiler.sh"
	echo "(1)problem_id (2)team_id"
	echo "(3)submitted_file_path (4)submitted_file_name (5)submitted_file_type "
	echo "(6)is_zipped (7)time_limit (8)compiler_flags"
	echo "(9)output_folder_name"
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

output_folder_name="$9"

echo "Variable names..."

echo "The output_folder_name is $output_folder_name"
echo "The team_id is $team_id"
echo "The problem_id is $problem_id"

TEAM_DIRECTORY="$PWD/submissions/$team_id"
PROBLEM_DIRECTORY="$PWD/submissions/$team_id/$problem_id"
SUBMISSION_DIRECTORY="$PWD/submissions/$team_id/$problem_id/$output_folder_name"
CODE_DIRECTORY="$PWD/submissions/$team_id/$problem_id/$output_folder_name/code"
STUDENT_OUTPUT_DIRECTORY="$PWD/submissions/$team_id/$problem_id/$output_folder_name/output"
LOG_DIRECTORY="$PWD/submissions/$team_id/$problem_id/$output_folder_name/logs"

INPUT_DIRECTORY="problems/$problem_id/input"
EXPECTED_OUTPUT_DIRECTORY="problems/$problem_id/output"

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
if [ ! -d "$INPUT_DIRECTORY" ]; then

	echo "$INPUT_DIRECTORY does not exist"
	exit 1
	
else
	
	file_count=$(find $INPUT_DIRECTORY -maxdepth 1 -name "*.in" | wc -l)
	other_file_count=$(find $EXPECTED_OUTPUT_DIRECTORY -maxdepth 1 -name "*.out" | wc -l)
	
	if [ $file_count -lt 1 ]; then
		echo "this problem has no input test cases"
		exit 1
	elif [ $other_file_count -ne $file_count ]; then
		echo "this problem does not have the same number of input and output files"
		exit 1	
	else
		echo "$PWD/problems/$problem_id/input exists and has $file_count input cases"
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
echo "created output directory"

# copy the submitted file over into the mounted directory
if [ -f "$file_path/$file_name" ]; then
	cp "$file_path/$file_name" "$PWD/code_to_submit/$file_name"
fi	

# run the sandbox
echo ""
echo "Creating the docker sandbox to run student code..."

docker run --name=gradelone -d -v $SUBMISSION_DIRECTORY:/home/abc/submission -v $PWD/code_to_submit:/home/abc/code_to_submit -v $PWD/problems/$problem_id/input:/home/abc/input gradel /home/abc/compile_code.sh $file_type $is_zipped $file_name $linker_flags $compiler_flags

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

echo ""
echo "Comparing student output with expected output..."

STUDENT_OUTPUT_FILES=($STUDENT_OUTPUT_DIRECTORY/*.out)
EXPECTED_OUTPUT_FILES=($EXPECTED_OUTPUT_DIRECTORY/*.out)

student_file_count=$(find $INPUT_DIRECTORY -maxdepth 1 -name "*.in" | wc -l)
expect_file_count=$(find $EXPECTED_OUTPUT_DIRECTORY -maxdepth 1 -name "*.out" | wc -l)
	
if [ $student_file_count -ne $expect_file_count ]; then
	echo "student output does not have the same number of files"
	exit 1
fi

right=0
for ((i=0;i<${#STUDENT_OUTPUT_FILES[@]};++i)); do  
	echo "diff ${STUDENT_OUTPUT_FILES[i]} ${EXPECTED_OUTPUT_FILES[i]}"    
	cmp=$(diff ${STUDENT_OUTPUT_FILES[i]} ${EXPECTED_OUTPUT_FILES[i]})
  
	if [ "$cmp" != "" ]; then
		echo "$((i+1))) Wrong answer!"
	else
		echo "$((i+1))) Correct!"
		right=$((right+1))
	fi
done

echo "$right/$expect_file_count correct"