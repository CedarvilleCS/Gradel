#include <string>
#include "custom_validate.h"

using namespace std;

bool validate(string expected, string user){
	
	if(expected != user){
		
		if(expected.back() == '\n'){
			expected.erase(expected.size()-1);		
		}
		
		return expected == user;
		
	} else {
		return true;
	}
}
