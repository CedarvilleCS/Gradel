#include <string>

#ifndef CUSTOM_VALIDATE
#define CUSTOM_VALIDATE

using namespace std;

struct validate_info {	

	validate_info() : is_correct(false) {}

	bool is_correct;
};

validate_info validate(string expected, string user);

#endif