#include <stdio.h>
#include <stdlib.h>
#include <string.h>

typedef int bool;
#define true 1
#define false 0

int main(int argc, char** argv){
	
	char* language = NULL;
	char* filename = NULL;
	char* compiler_flags = NULL;
	char* java_main_class = NULL;
	char* java_package_name = NULL;
		
	// optional variables
	bool zipped = false;
	
	/* INPUT VALIDATION */
	// loop through the command line arguments two at a time
	int i;	
	for(i=1; i<argc; i++){
	
		// switch on the options
		if(strcmp(argv[i], "-l") == 0 && i+1 < argc){
			
			language = (char*) malloc((strlen(argv[i+1])+1)*sizeof(char));
			strncpy(language, argv[i+1], strlen(argv[i+1]));
			
			i++;
		} else if(strcmp(argv[i], "-f") == 0 && i+1 < argc){
			
			filename = (char*) malloc((strlen(argv[i+1])+1)*sizeof(char));
			strncpy(filename, argv[i+1], strlen(argv[i+1]));
			
			i++;
		} else if(strcmp(argv[i], "-c") == 0 && i+1 < argc){
			
			compiler_flags = (char*) malloc((strlen(argv[i+1])+1)*sizeof(char));
			strncpy(compiler_flags, argv[i+1], strlen(argv[i+1]));
			
			i++;			
		} else if(strcmp(argv[i], "-M") == 0 && i+1 < argc){
			
			java_main_class = (char*) malloc((strlen(argv[i+1])+1)*sizeof(char));
			strncpy(java_main_class, argv[i+1], strlen(argv[i+1]));
			
			i++;
		} else if(strcmp(argv[i], "-P") == 0 && i+1 < argc){			
			java_package_name = (char*) malloc((strlen(argv[i+1])+1)*sizeof(char));
			strncpy(java_package_name, argv[i+1], strlen(argv[i+1]));
			
			i++;
		} else if(strcmp(argv[i], "-z") == 0){
			zipped = true;
			
		} else {			
			printf("ERROR! Unknown command line option %s\n", argv[i]);	
			return 1;
		}
		
	}
	
	// check to make sure the language was set
	if(!language || strlen(language) == 0){
		printf("language_id (-l) is required\n");
		return 1;
		
	}
	// make sure the main class and package name options were set if the language was Java
	else if(strcmp(language, "Java") == 0 && !(java_main_class && java_package_name)){
		printf("main class and package name both required with Java\n");
		return 1;
	}
	
	// check to make sure the input file was set
	if(!filename || strlen(filename) == 0){
		printf("filename (-f) is required\n");
		return 1;
	}
	
	// set the compiler_flags to "" if it wasn't set
	if(!compiler_flags || strlen(compiler_flags) == 0){
		//compiler_flags = (char*) malloc(1*sizeof(char));
		compiler_flags = "''";
	}
	
	
	/* MOVE CODE TO SUBMISISON DIRECTORY */
	char* cp_cmd = (char*) malloc((strlen("cp -r code_to_submit/") + strlen(filename) + strlen(" submission/code/.") + 1)*sizeof(char));
	strcat(cp_cmd,"cp -r code_to_submit/");
	strcat(cp_cmd,filename);
	strcat(cp_cmd," submission/code/.");
	
	//system(cp_cmd);
	
	/* UNZIP FILES IF NECESSARY */
	if(zipped){
		char* unzip_cmd = (char*) malloc((strlen("unzip submission/code/") + strlen(filename) + strlen(" -d submission/code") + 1)*sizeof(char));
		strcat(unzip_cmd,"unzip submission/code/");
		strcat(unzip_cmd,filename);
		strcat(unzip_cmd," -d submission/code");
		
		printf("system(%s)\n", unzip_cmd);
		//system(unzip_cmd);
	}
	
	if(strcmp(language, "C") == 0){

		char* compile_cmd = (char*) malloc((strlen("./compile_c.sh ") + strlen(compiler_flags) + 1)*sizeof(char));
		strcat(compile_cmd, "./compile_c.sh ");
		strcat(compile_cmd, compiler_flags);
		
		printf("system(%s)\n", compile_cmd);
		//system(compile_cmd);
	
	} else if(strcmp(language, "C++") == 0){

	} else if(strcmp(language, "Java") == 0){
		
	} else {
		// the language_id provided does not match any we can compile
	}
	
	return 0;
}