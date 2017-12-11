#include <string>
#include <iostream>
#include <fstream>

#include "custom_validate.h"

using namespace std;

validate_info validate(string expected, string user){
	
	validate_info info;
	
	int user_out, exp_out;
	
	if(sscanf(expected.c_str(), "%d\n", &exp_out) != 1 || sscanf(user.c_str(), "%d\n", &user_out) != 1){
		return info;
	}	
	
	info.is_correct = (exp_out == user_out);	
	
	return info;
}