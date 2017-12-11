#include <string.h>
#include <iostream>
#include <fstream>

#include "custom_validate.h"

using namespace std;

inline bool exists(const string& name) {
    if (FILE *file = fopen(name.c_str(), "r")) {
        fclose(file);
        return true;
    } else {
        return false;
    }   
}

/* this validator tests the user input and expected output against each other */
int main(int argc, char** argv){
	
	if(argc != 3){
		fprintf(stderr, "USAGE: ./validator expected_outputfile user_outputfile\n");
		return 1;
	}
	
	string expected_file = argv[1];
	string user_file = argv[2];
	
	char c;
	
	// get the expected output
	if(!exists(expected_file)){
		fprintf(stderr, "ERROR: Cannot open the expected output file\n");		
		return 1;
	}
	
	ifstream exp_output_file(expected_file);
	string exp_output = "";
	while (exp_output_file.get(c)){
		exp_output += c;
	}
	exp_output_file.close();  
	
	
	// get the user output
	if(!exists(user_file)){
		fprintf(stderr, "ERROR: Cannot open the user output file\n");		
		return 1;
	}
	
	ifstream user_output_file(user_file);
	string user_output = "";
	while (user_output_file.get(c)){
		user_output += c;
	}
	user_output_file.close();
	
	// call the custom function
	validate_info info = validate(exp_output, user_output);
	
	cout << ((info.is_correct) ? "true" : "false");
	
	return 0;
}