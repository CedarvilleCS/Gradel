#include <string.h>
#include <iostream>

#include "compiler.h"

using namespace std;

compile_info compile_code(string language, string compiler_options, string main_class, string package_name){
	
	// the actual file compiled to
	string compiled_filename = "";
	
	// the program that must be typed in to run the program
	string program_name = "";
	
	// compile code
	struct compile_info info;
	
	// switch based on language
	string compile_cmd = "";
	if(language == "C++"){
		
		compiled_filename = "compiled_code/a.out";
		program_name = compiled_filename;
		
		compile_cmd = "g++ -std=c++11 " + compiler_options + " student_code/*.cpp" + " -o " + compiled_filename + " 2>&1";		
		
	} else if(language == "C"){
		
		compiled_filename = "compiled_code/a.out";
		program_name = compiled_filename;
		
		compile_cmd = "gcc " + compiler_options + " student_code/*.c" + " -o " + compiled_filename + " 2>&1";
		
	} else if(language == "Java"){
		
		string java_file_name = (package_name.length() > 0) ? package_name+"/"+main_class : main_class;
		string java_file_path = "student_code/" + java_file_name + ".java";
		
		compiled_filename = "compiled_code/" + java_file_name + ".class";
		program_name = "java -cp compiled_code " + java_file_name;
		
		compile_cmd = "javac " + compiler_options + " -d compiled_code/ " + java_file_path + " 2>&1";
		
	} else {
		fprintf( stderr, "ERROR: Language provided does not match any implemented language.\n");
		return info;
	}
	
	string compile_result = "";
	char c = 0;
	FILE *code_compilation;
	code_compilation = (FILE*)popen(compile_cmd.c_str(), "r");
	
	// loop through the compilation output and store it in the compile_result
	while(fread(&c, sizeof c, 1, code_compilation)){
		compile_result += c;
	}
	int ret_val = pclose(code_compilation);
	
	info.is_error = (ret_val != 0);
		
	// make the file executable
	string chmod_cmd = "chmod 755 " + compiled_filename;
	system(chmod_cmd.c_str());	
		
	if(info.is_error){
		info.errors = compile_result;
	} else {
		info.warnings = compile_result;
		info.filename = compiled_filename;
		info.program_name = program_name;
	}
	
	return info;
}