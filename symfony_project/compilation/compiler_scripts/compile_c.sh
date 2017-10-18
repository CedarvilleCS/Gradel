#! /bin/bash

# check command line params
if [ "$#" -ne 1 ]; then
	echo "usage: ./compile_c.sh (1)compiler_flags"
	exit 100
fi

compiler_flags="$1"

# COMPILATION
# compile the code and check for compiler error
gcc submission/code/*.c $compiler_flags -o a.out 2> submission/compiler.log

# if there was an error, exit and touch a flag file
if [ $? -ne 0 ]; then
	echo "Error with compiling!"
	touch submission/compileerror
	exit 1
fi


# TESTCASES
# run all of the test cases
INPUT_FILES=(input/*.in)
EXPECTED_OUTPUT_FILES=(output/*.out)

input_file_count=$(find input/ -maxdepth 1 -name "*.in" | wc -l)
expect_file_count=$(find output/ -maxdepth 1 -name "*.out" | wc -l)
	
if [ $input_file_count -ne $expect_file_count ]; then
	echo "student output does not have the same number of files"
	echo $input_file_count - $expect_file_count
	exit 1
fi

num_correct=0

for ((i=0;i<${#INPUT_FILES[@]};++i)); do

	seq_num=$(basename ${INPUT_FILES[i]} .in)
	
	diff_log_name=submission/diff_logs/$seq_num.log
	
	mytime="$((time ( sh -c 'trap "" 11; ./a.out'  < ${INPUT_FILES[i]} 1> submission/output/$seq_num.out 2> submission/runtime_logs/$seq_num.log ) 2>&1 ) | grep user)"
	echo $mytime > submission/time_logs/$seq_num.log

	# COMPARE THE RESULTS
	cmp=$(diff submission/output/$seq_num.out ${EXPECTED_OUTPUT_FILES[i]})
  
	if [ "$cmp" != "" ]; then
		echo "NO"  > $diff_log_name
	else
		echo "YES"  > $diff_log_name
		num_correct=$((num_correct+1))
	fi
done

echo "$num_correct/$expect_file_count correct"


exit 0
