#include <string>
#include <regex>

#ifndef CUSTOM_VALIDATE
#define CUSTOM_VALIDATE

using namespace std;

struct validate_info {	

	validate_info() : is_correct(false) {}

	bool is_correct;
};

bool validate(string expected, string user);

#endif