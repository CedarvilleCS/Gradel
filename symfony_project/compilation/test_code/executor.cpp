// for getting the CPU time
#include <sys/times.h>
#include <sys/types.h>
#include <string>
#include <pwd.h>
#include <iostream>
#include <unistd.h>

#include <cstdlib> // random
#include <ctime> // time

#include <fstream> // ifstream

#include "compilation.h"
#include "executor.h"

using namespace std;


run_info run_code(string language, string program_name, int testcase_num){
	
	// run_info stores the information necessary to grade the run
	run_info info;
	
	/*************** ROOT USER **************/
	// nmake sure that youre a root user
	if(seteuid(0) != 0){
		fprintf( stderr, "Could not become a root user!\n");
		return info;
	}
		
	// runtime log file
	// randomly generate the name so the user cannot guess it
	srand(time(NULL));
	
	int rand_num;
	string run_output_file;
	
	do{		
		rand_num = rand();
		run_output_file = "run_logs/" + to_string(rand_num) + "_run.log";
		
	} while(exists(run_output_file));
	
	// create the log file and give it the proper permissions
	string create_log_file_cmd = "touch " + run_output_file;
	string chmod_log_file_cmd = "chmod 777 " + run_output_file;
	
	system(create_log_file_cmd.c_str());
	system(chmod_log_file_cmd.c_str());			
	
	// input and arg files
	string input_file = "input_files/" + to_string(testcase_num) + ".in";
	string arg_file = "arg_files/" + to_string(testcase_num) + ".args";
	
	// input file
	bool has_inputfile = exists(input_file);
	// set the chmod for the input file
	if(has_inputfile){
		string chmod_input = "chmod 755 " + input_file;
		system(chmod_input.c_str());
	}
	
	bool has_commandline = exists(arg_file);

	// command line arguments (only the first line)	
	string command_line_args = "";
	if(has_commandline){
		ifstream argfile(arg_file, ifstream::in);
		getline(argfile, command_line_args, '\n');
		argfile.close();
	}
	
	// the command to run
	string cmd;
	if(has_inputfile && has_commandline){
		cmd = program_name + " " + command_line_args + " < " + input_file + " 2> " + run_output_file;
	} else if(has_commandline){
		cmd = program_name + " " + command_line_args + " 2> " + run_output_file;
	} else if(has_inputfile){
		cmd = program_name + " < " + input_file + " 2> " + run_output_file;
	} else {
		fprintf( stderr, "No files to run program against!\n");
		return info;
	}
	
	/************* NORMAL USER **************/
	// set the current user to be a normal one
	passwd* asdf = getpwnam("student");
	seteuid(asdf->pw_uid);
		
	// run the cmd and get the output stored in student_output
	char c = 0;		
	string student_output = "";
	
	// get the time the program ran
	struct tms beg_struct;	
	times(&beg_struct);	
	long int beg_time = beg_struct.tms_cutime*10;
	
	FILE *program_file;
	program_file = (FILE*)popen(cmd.c_str(), "r");
	
	// loop through the output and store it in the student_output
	while(fread(&c, sizeof c, 1, program_file)){
		//printf("%c", c);
		student_output += c;
	}
	int ret_val = pclose(program_file);
		
		
	/************* ROOT USER **************/
	// go back to being a root user
	seteuid(0);
	
	// unset the chmod for the input file
	if(has_inputfile){
		string chmod_input = "chmod 700 " + input_file;
		system(chmod_input.c_str());
	}
	
	// unset the chmod for the runtime file
	string chmod_log_file_cmd2 = "chmod 700 " + run_output_file;
	system(chmod_log_file_cmd2.c_str());
	
	string mv_log_file_cmd = "mv " + run_output_file + " run_logs/" + to_string(testcase_num) + ".log";
	system(mv_log_file_cmd.c_str());
		
	// get the time the program ran
	struct tms end_struct;	
	times(&end_struct);	
	long int end_time = end_struct.tms_cutime*10;
	
	long int runtime = end_time - beg_time;
	
	// save time of execution
	string timefile = "time_logs/" + to_string(testcase_num) + ".log";
	ofstream time_output_file(timefile);
	time_output_file << runtime << endl;
	time_output_file.close();
	
	string chmod_time_cmd = "chmod 700 " + timefile;
	system(chmod_time_cmd.c_str());	
			
	// save user output
	string userfile = "user_output/" + to_string(testcase_num) + ".out";
	ofstream user_output_file(userfile);
	user_output_file << student_output;
	user_output_file.close();
			
	string chmod_user_cmd = "chmod 700 " + userfile;
	system(chmod_user_cmd.c_str());
	
	// save values to the info struct
	info.output = student_output;
	info.return_val = ret_val;
	info.time = runtime;
	info.run_log_num = rand_num;
   
	return info;
}