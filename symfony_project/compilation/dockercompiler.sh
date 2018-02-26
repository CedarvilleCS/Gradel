#!/bin/bash

# get the variables from the command arguments
program_options="$1"
submission_id="$2"
time_limit="$3"

script_dir="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"

sub_dir="$script_dir/submissions/$submission_id"

student_code_dir="$sub_dir/student_code/"
compiled_code_dir="$sub_dir/compiled_code/"

flags_dir="$sub_dir/flags/"
custom_validator_dir="$sub_dir/custom_validator/"

run_log_dir="$sub_dir/run_logs/"
time_log_dir="$sub_dir/time_logs/"
diff_log_dir="$sub_dir/diff_logs/"

input_file_dir="$sub_dir/input_files/"
output_file_dir="$sub_dir/output_files/"
arg_file_dir="$sub_dir/arg_files/"

user_output_dir="$sub_dir/user_output/"	

echo "SCRIPT DIR: $script_dir"
echo "STUDENT CODE DIR: $student_code_dir"
echo "COMPILED CODE DIR: $compiled_code_dir"

# run the sandbox
echo ""
echo "Creating the docker sandbox to run student code..."

mount_arg="-v $arg_file_dir:/compilation/arg_files"
mount_cmp="-v $compiled_code_dir:/compilation/compiled_code"
mount_val="-v $custom_validator_dir:/compilation/custom_validator"
mount_dif="-v $diff_log_dir:/compilation/diff_logs"
mount_flg="-v $flags_dir:/compilation/flags"
mount_inp="-v $input_file_dir:/compilation/input_files"
mount_run="-v $run_log_dir:/compilation/run_logs"
mount_std="-v $student_code_dir:/compilation/student_code"
mount_tim="-v $time_log_dir:/compilation/time_logs"
mount_use="-v $user_output_dir:/compilation/user_output"
mount_out="-v $output_file_dir:/compilation/output_files"

mount_all="$mount_arg $mount_cmp $mount_val $mount_dif $mount_flg $mount_inp $mount_run $mount_std $mount_tim $mount_use $mount_out"

script_options="$program_options"

container_name="gd$submission_id"

echo "docker run --memory=4096m --ulimit nofile=128:128 --ulimit nproc=16:16 --name=$container_name -d \
	$mount_all \
	gradel \
	/compilation/compiler $script_options"
	
echo $(docker run --memory=4096m --ulimit nofile=128:128 --ulimit nproc=16:16 --name=$container_name -d \
	$mount_all \
	gradel \
	/compilation/compiler $script_options)

echo "timeout $time_limit docker wait $container_name"
code=$(timeout $time_limit docker wait $container_name 2>&1 || true)

echo $(docker kill $container_name 2>&1)
echo $(docker rm $container_name 2>&1)

echo -n 'status: '
if [ -z "$code" ]; then
	echo TIMEOUT	
else
    echo exited with $code
fi	

