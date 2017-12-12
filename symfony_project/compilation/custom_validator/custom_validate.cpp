#include <string>
#include <iostream>
#include <fstream>

#include "custom_validate.h"

using namespace std;

validate_info validate(string expected, string user){
	
	validate_info info;
	
	info.is_correct = (expected == user);	
	
	return info;
}
