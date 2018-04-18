#include <string.h>
#include <iostream>
#include <unistd.h>

#include "compiler.h"

using namespace std;

compile_info compile_code(string language, string compiler_options, string filename){
	
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
		
		compile_cmd = "g++ -std=c++11 " + compiler_options + " student_code/" + filename + " -o " + compiled_filename + " 2>&1";		
		
	} else if(language == "C"){
		
		compiled_filename = "compiled_code/a.out";
		program_name = compiled_filename;
		
		compile_cmd = "gcc " + compiler_options + " student_code/" + filename + " -o " + compiled_filename + " 2>&1";
		
	} else if(language == "Java"){
		
		string java_file_name = filename;
		string java_file_path = java_file_name + ".java";
		
		compiled_filename = "compiled_code/" + java_file_name + ".class";
		program_name = "java -XX:+UnlockExperimentalVMOptions -XX:+UseCGroupMemoryLimitForHeap -XX:MaxRAMFraction=1 -cp compiled_code " + java_file_name;
		
		// move into the directory for java to handle packages
		compile_cmd = "cd student_code/ && javac " + compiler_options + " -d ../compiled_code/ " + java_file_path + " 2>&1 && cd ../";
		
	} else if(language == "Python2"){

		// Python does not get compiled
		// Just move the files into compiled code directory for the "compilation" step
		
		compiled_filename = "compiled_code/" + filename;
		program_name = "python compiled_code/" + filename;
		
		compile_cmd = "cp -r student_code/*.* compiled_code/";
	
	} else if(language == "Python3"){
		
		// Python does not get compiled
		// Just move the files into compiled code directory for the "compilation" step
		
		compiled_filename = "compiled_code/" + filename;
		program_name = "python3 compiled_code/" + filename;
				
		compile_cmd = "cp -r student_code/*.* compiled_code/";
		
	} else if(language == "PHP"){
	
		compiled_filename = "compiled_code/" + filename;
		program_name = "php compiled_code/" + filename;
		
		compile_cmd = "cp -r student_code/*.* compiled_code/";
	
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
	string chmod_cmd = "chmod -R 755 compiled_code/";
	system(chmod_cmd.c_str());	
	
	// empty out the student_code folder
	string stud_rm_cmd = "rm -rf student_code/*";
	system(stud_rm_cmd.c_str());
		
	if(info.is_error){
		info.errors = compile_result;
	} else {
		info.warnings = compile_result;
		info.filename = compiled_filename;
		info.program_name = program_name;
	}
	
	return info;
}