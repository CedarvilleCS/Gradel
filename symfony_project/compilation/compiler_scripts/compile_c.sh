#! /bin/bash

# check command line params
if [ "$#" -ne 1 ] && [ "$#" -ne 0 ]; then
	echo "usage: ./compile_c.sh <(1)compiler_flags>"
	exit 55
fi

if [ "$#" -eq 1 ]; then
	compiler_flags="$1"
else 
	compiler_flags=""
fi

# COMPILATION
# compile the code and check for compiler error
gcc submission/code/*.c $compiler_flags -o a.out 2> submission/compiler_errors.log

# if there was an error, exit
# otherwise, we know the output file is really just warnings
if [ $? -ne 0 ]; then
	echo "Error with compiling!"
	exit 1
else
	mv submission/compiler_errors.log submission/compiler_warnings.log
fi


# TESTCASES
# run all of the test cases
INPUT_FILES=(input/*.in)
EXPECTED_OUTPUT_FILES=(output/*.out)

input_file_count=$(find input/ -maxdepth 1 -name "*.in" | wc -l)
expect_file_count=$(find output/ -maxdepth 1 -name "*.out" | wc -l)
	
if [ $input_file_count -ne $expect_file_count ]; then
	echo "student output does not have the same number of files" >> submission/testcase_error.log
	echo $input_file_count - $expect_file_count >> submission/testcase_error.log
	exit 1
fi

num_correct=0

for ((i=0;i<${#INPUT_FILES[@]};++i)); do

	seq_num=$(basename ${INPUT_FILES[i]} .in)
	
	diff_log_name=submission/testcase_diff$seq_num.log
	
	echo "TIME LIMIT" > $diff_log_name
	
	mytime="$((time ( sh -c 'trap "" 11; ./a.out'  < ${INPUT_FILES[i]} 1> submission/output/$seq_num.out 2> submission/logs/$seq_num.log ) 2>&1 ) | grep user)"
	echo $mytime >> submission/testcase_exectime.log
	
	# get the runtime errors stored
#	file_size="$(wc -c submission/output/$seq_num.log | awk '{print $1}')"
#	if [ "$file_size" -eq 0 ]; then
#		rm -f submission/output/$seq_num.log
#	fi	
	
	# COMPARE THE RESULTS
	cmp=$(diff submission/output/$seq_num.out ${EXPECTED_OUTPUT_FILES[i]})
  
	if [ "$cmp" != "" ]; then
		echo "NO"  > $diff_log_name
	else
		echo "YES"  > $diff_log_name
		num_correct=$((num_correct+1))
	fi
done

echo "$num_correct/$expect_file_count correct"  >> submission/testcase_diff.log


exit 0
