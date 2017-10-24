#include <string>
#include <cstdlib>
#include <iostream>
#include <stdio.h>
#include <stdlib.h>

#include "language_scripts.h"

using namespace std;

string codefolder = "submission/code/";
string compilerlogfile = "submission/compiler.log";

int compile_c(string compiler_flags){	
	
	int compile_res = 0;
	
	if(compiler_flags.length() > 0 && compiler_flags != "''"){
		compiler_flags = compiler_flags + " ";
	}
	
	string compile_cmd = "gcc " + compiler_flags + codefolder + "*.c" + " -o a.out 2> " + compilerlogfile;		
	cout << compile_cmd << endl;
	compile_res = system(compile_cmd.c_str());
	
	if(compile_res != 0){
				
		cout << "Error with compiling!\n";		
				
		string touch_cmd = "touch submission/compileerror";
		system(touch_cmd.c_str());
		
		return 1;
	}
	
	string run_cmd = "./run_c.sh";
	cout << run_cmd << endl;
	system(run_cmd.c_str());
	
	return 0;
}

int compile_cpp(string compiler_flags){	
	
	int compile_res = 0;
	
	if(compiler_flags.length() > 0 && compiler_flags != "''"){
		compiler_flags = compiler_flags + " ";
	}
	
	string compile_cmd = "g++ " + compiler_flags + codefolder + "*.cpp" + " -o a.out 2> " + compilerlogfile;		
	cout << compile_cmd << endl;
	compile_res = system(compile_cmd.c_str());
	
	if(compile_res != 0){
				
		cout << "Error with compiling!\n";		
				
		string touch_cmd = "touch submission/compileerror";
		system(touch_cmd.c_str());
		
		return 1;
	}
	
	string run_cmd = "./run_c.sh";
	cout << run_cmd << endl;
	system(run_cmd.c_str());
	
	return 0;
}

int compile_java(string compiler_flags, string main_class, string package_name){
	
	int compile_res = 0;
	
	if(compiler_flags.length() > 0 && compiler_flags != "''"){
		compiler_flags = compiler_flags + " ";
	}
	
	if(package_name != ""){
		main_class = package_name+"/"+main_class;
	}
	
	string compile_cmd = "javac " + compiler_flags + "-d . " + codefolder + main_class + ".java" + " 2> " + compilerlogfile;
	cout << compile_cmd << endl;
	compile_res = system(compile_cmd.c_str());
	
	if(compile_res != 0){
				
		cout << "Error with compiling!\n";		
				
		string touch_cmd = "touch submission/compileerror";
		system(touch_cmd.c_str());
		
		return 1;
	}
	
	string run_cmd = "./run_java.sh " + main_class;		
	cout << run_cmd << endl;
	system(run_cmd.c_str());
	
	return 0;
}

