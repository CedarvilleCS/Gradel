#include <iostream>
#include <cstdlib>
#include <string>

#include "language_scripts.h"
using namespace std;

int main(int argc, char** argv){
	
	string language;
	string filename;
	string compiler_flags;
	string java_main_class;
	string java_package_name;
			
	// optional variables
	bool zipped = false;
	
	
	/* INPUT VALIDATION */
	// loop through the command line arguments two at a time
	int i;	
	for(i=1; i<argc; i++){
	
		// switch on the options
		string argv_i = argv[i];
		
		if(argv_i == "-l" && i+1 < argc){
			
			language = argv[i+1];			
			i++;
			
		} else if(argv_i == "-f" && i+1 < argc){
			
			filename = argv[i+1];
			i++;
			
		} else if(argv_i == "-c" && i+1 < argc){
			
			compiler_flags = argv[i+1];
			i++;
			
		} else if(argv_i == "-M" && i+1 < argc){
			
			java_main_class = argv[i+1];
			i++;
			
		} else if(argv_i == "-P" && i+1 < argc){			
			
			java_package_name = argv[i+1];
			i++;
			
		} else if(argv_i == "-z"){
			
			zipped = true;
			
		} else {			
			cout << "ERROR! Unknown command line option " << argv_i << "\n";	
			return 1;
		}
		
	}
	
	cout << language << endl;
	
	// check to make sure the language was set
	if(language.length() == 0){
		cout << "language_id (-l) is required\n";
		return 1;
		
	}
	// make sure the main class and package name options were set if the language was Java
	else if(language  == "Java" && (java_main_class.length() == 0 || java_package_name.length() == 0)){
		cout << "main class and package name both required with Java\n";
		return 1;
	}
	
	// check to make sure the input file was set
	if(filename.length() == 0){
		cout << "filename (-f) is required\n";
		return 1;
	}
	
	// set the compiler_flags to "" if it wasn't set
	/*if(compiler_flags.length() == 0){
		compiler_flags = "''";
	}*/	
	
	/* MOVE CODE TO SUBMISISON DIRECTORY */
	string cp_cmd = "cp code_to_submit/" + filename + " submission/code/.";
	cout << cp_cmd << endl;
	system(cp_cmd.c_str());
	
	/* UNZIP FILES IF NECESSARY */
	if(zipped){
		
		string unzip_cmd = "unzip submission/code/" + filename + " -d submission/code";
		cout << unzip_cmd << endl;
		system(unzip_cmd.c_str());
	}
	
	if(language == "C"){

		return compile_c(compiler_flags);
	
	} else if(language == "C++"){
		
		return compile_cpp(compiler_flags);
	
	} else if(language == "Java"){
		
		return compile_java(compiler_flags, java_main_class, java_package_name);
		
	} else {
		// the language_id provided does not match any we can compile
		;
	}
	
	return 0;
}