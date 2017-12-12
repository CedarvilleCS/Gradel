#include <string.h>
#include <iostream>
#include <fstream>

#include "compilation.h"
#include "executor.h"
#include "compiler.h"

using namespace std;

// the main program
int main(int argc, char** argv){
	
	/* COMMAND-LINE ARGS PARSING */	
	// language 
	// -l "language name"
	string language = "";
	// num_testcases
	// -n int
	int num_testcases = 0;
	// filename
	// -f "filename"
	string filename = "";
	// (is_zipped)
	// -z -!z
	bool is_zipped = false;
	// (main_class)
	// -M
	string main_class = "";
	// (package_name)
	// -P "package name"
	string package_name = "";
	// (first_fail)
	// -q -!q
	bool fail_on_first = false;
	// (compiler_options)
	// -c "compilation options"
	string compiler_options = "";
	// (is_graded) 
	// -g / -!g
	bool is_graded = true;
	
	// TAYLOR IS AWESOME <3	
	int count = 1;
	while(count < argc){
		
		string flag = argv[count++];
		
		if(flag == "-l" && count < argc){
			language = argv[count++];
		} 
		else if(flag == "-n" && count < argc){
			num_testcases = atoi(argv[count++]);
		}
		else if(flag == "-f" && count < argc){
			filename = argv[count++];
		}
		else if(flag == "-z"){
			is_zipped = true;
		}
		else if(flag == "-nz"){
			is_zipped = false;
		}
		else if(flag == "-M" && count < argc){
			main_class = argv[count++];
		}
		else if(flag == "-P" && count < argc){
			package_name = argv[count++];
		}
		else if(flag == "-q"){
			fail_on_first = true;
		}
		else if(flag == "-nq"){
			fail_on_first = false;
		}
		else if(flag == "-c" && count < argc){
			compiler_options = argv[count++];
		}
		else if(flag == "-g"){
			is_graded = true;
		}
		else if(flag == "-ng"){
			is_graded = false;
		}
		else {
			fprintf( stderr, "ERROR: unknown or invalid flag \"%s\"\n", flag.c_str());
			return 1;
		}
	}
	
	// make sure that the necessary files were set
	if(language.length() < 1){
		fprintf( stderr, "ERROR: language (-l) must be set\n");
		return 1;
	}
	
	if(filename.length() < 1){
		fprintf( stderr, "ERROR: filename (-f) must be set\n");
		return 1;
	}
	
	if(num_testcases < 1){
		fprintf( stderr, "ERROR: num_testcases (-n) must be set to be greater than 0\n");
		return 1;
	}
	
	if(language == "Java" && main_class.length() < 1){
		fprintf( stderr, "ERROR: language was Java but no main class (-M) was set\n");
		return 1;
	} else if(language != "Java" && (main_class.length() > 1 || package_name.length() > 1)){
		fprintf( stderr, "ERROR: language was not Java, but some Java fields were provided\n");
		return 1;
	}
	
	// default everything in the directory to be 700
	system("chown -R root ./");
	system("chmod -R 700 ./");
	
	cout << "Checking if zipped..." << endl;
	/* UNZIP IF NECESSARY */		
	// if zipped, we need to unzip to the directory first
	if(is_zipped){
		
		// unzip the file
		string unzip_cmd = "unzip student_code/" + filename + " -d student_code/";
		int unzip_val = system(unzip_cmd.c_str());
		
		if(unzip_val != 0){
			fprintf( stderr, "ERROR: file could not be unzipped\n");
			return 1;
		}
		
		// remove the unzipped file
		string rm_filename = "rm -f student_code/" + filename;
		int rm_val = system(rm_filename.c_str());
		
		if(rm_val != 0){
			fprintf( stderr, "ERROR: could not remove the zip file\n");
			return 1;
		}
		
		
	}
	
	if(language == "Java"){
		filename = (package_name.length() > 0) ? package_name + "/" + main_class : main_class;		
	}
		
	// set a reverse flag for timeout
	system("touch flags/time_limit");
		
	cout << "Let's compile the student's code..." << endl;	
	/* STUDENT CODE COMPILATION */
	//string compiler_options = "-pedantic -Wall -Wextra -Wcast-align -Wcast-qual -Wctor-dtor-privacy -Wdisabled-optimization -Wformat=2 -Winit-self -Wlogical-op -Wmissing-declarations -Wmissing-include-dirs -Wnoexcept -Wold-style-cast -Woverloaded-virtual -Wredundant-decls -Wshadow -Wsign-conversion -Wsign-promo -Wstrict-null-sentinel -Wstrict-overflow=5 -Wswitch-default -Wundef -Wno-unused";
	compile_info comp_info = compile_code(language, compiler_options, filename);
	
	// check for compilation error
	if(comp_info.is_error){
		fprintf( stderr, "ERROR: could not compile the student code\n");
		fprintf( stderr, "COMPILER OUTPUT: %s\n", comp_info.errors.c_str());
		
		system("touch flags/compile_error");
		
		return 0;
	}	
	
	// debug output	
	if(comp_info.warnings.length() > 0){
		cout << "COMPILER WARNINGS: " << comp_info.warnings;
	}
	 
	// program to run
	string compiled_program = comp_info.program_name;

	// compile the validator
	cout << "Let's compile the validator..." << endl;	
	system("chmod 755 -R custom_validator/");
	if(system("g++ -std=c++11 custom_validator/*.cpp -o custom_validator/validator") != 0){
		fprintf(stderr, "ERROR: Could not compile the custom_validator\n");
		return 1;
	}	
	system("chmod 700 -R custom_validator");
	
	/* STUDENT CODE EXECUTION */
	// loop over testcases
	for(int i=1; i<=num_testcases; i++){
		
		cout << "Let's run the student's code against testcase " + to_string(i) << endl;
	
		system("chmod 775 run_logs");
		system("chmod 775 input_files");
		cout << "Actually running..." << endl;
		run_info run_info = run_code(language, compiled_program, i);
		system("chmod 700 run_logs");
		system("chmod 700 input_files");
		
		cout << "Testcase #" << i << ") "; 
		// check for runtime error
		if(run_info.return_val != 0) {
			cout << "Runtime Error!" << endl;
			
			system("touch flags/runtime_error");
			
			continue;
		}
					
		// expected output
		string outputfile = "output_files/" + to_string(i) + ".out";
		string userfile = "user_output/" + to_string(i) + ".out";
				
		// run the validator
		string validate_cmd = "custom_validator/validator " + outputfile + " " + userfile;
		
		FILE *validate_file;
		validate_file = (FILE*)popen(validate_cmd.c_str(), "r");
		char c = 0;
		string testcase_output = "";
		
		// loop through the validator output
		while(fread(&c, sizeof c, 1, validate_file)){
			testcase_output += c;
		}		
		int ret_val = pclose(validate_file);
		
		if(ret_val != 0){
			cout << "Internal Error!" << endl;
			continue;
		}
		
		if(is_graded){
			
			// save the validator output as either yes or no
			string diff_output = (testcase_output == "true") ? "YES" : "NO";
			
			string difffile = "diff_logs/" + to_string(i) + ".log";
			ofstream diff_testcase_file(difffile);
			diff_testcase_file << diff_output << endl;
			diff_testcase_file.close();
					
			string chmod_diff_cmd = "chmod 700 " + difffile;
			system(chmod_diff_cmd.c_str());
			
			cout << testcase_output << endl;
			
			if(fail_on_first && testcase_output != "true"){
				break;
			}
		}
	}
	
	
	// remove the reverse flag for timeout
	system("rm flags/time_limit");
	
	return 0;
}