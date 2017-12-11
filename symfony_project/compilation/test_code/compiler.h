#include <string>

#ifndef COMPILER
#define COMPILER

using namespace std;

struct compile_info {
	
	compile_info() : is_error(true) {}
	
	string warnings;
	string errors;
	string filename;
	string program_name;
	bool is_error;
};


compile_info compile_code(string language, string compiler_options, string main_class, string package_name);

#endif