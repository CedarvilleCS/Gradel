#include <string>

#ifndef EXECUTOR
#define EXECUTOR

using namespace std;

struct run_info {
	
	run_info() : return_val(-1) {}
	
	string output;
	long int time;
	int return_val;
	int run_log_num;
};

run_info run_code(string language, string program_name, int testcase_num);

#endif