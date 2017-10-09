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
for f in input/*.in; do

	filename=$(basename $f .in)
		
	mytime="$((time ( ./a.out  < $f > submission/output/$filename.out 2> submission/output/$filename.log ) 2>&1 ) | grep user)"
	echo $mytime >> submission/testcase_exectime.log
	
	file_size="$(wc -c submission/output/$filename.log | awk '{print $1}')"
	if [ "$file_size" -eq 0 ]; then
		rm -f submission/output/$filename.log
	fi
done


# COMPARE AND OUTPUT RESULTS
echo ""
echo "Comparing student output with expected output..."

STUDENT_OUTPUT_FILES=(submission/output/*.out)
EXPECTED_OUTPUT_FILES=(output/*.out)

student_file_count=$(find submission/output/ -maxdepth 1 -name "*.out" | wc -l)
expect_file_count=$(find output/ -maxdepth 1 -name "*.out" | wc -l)
	
if [ $student_file_count -ne $expect_file_count ]; then
	echo "student output does not have the same number of files" >> submission/testcase_error.log
	echo $student_file_count - $expect_file_count >> submission/testcase_error.log
	exit 1
fi

num_correct=0
for ((i=0;i<${#STUDENT_OUTPUT_FILES[@]};++i)); do

	cmp=$(diff ${STUDENT_OUTPUT_FILES[i]} ${EXPECTED_OUTPUT_FILES[i]})
  
	if [ "$cmp" != "" ]; then
		echo "NO"  >> submission/testcase_diff.log
	else
		echo "YES"  >> submission/testcase_diff.log
		num_correct=$((num_correct+1))
	fi
done

#echo "$num_correct/$expect_file_count correct"  >> submission/testcase_diff.log


exit 0
