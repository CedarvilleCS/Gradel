#ifndef LANGUAGE_SCRIPTS
#define LANGUAGE_SCRIPTS

#include <string>

using namespace std;

int compile_c(string compiler_flags);
int compile_cpp(string compiler_flags);
int compile_java(string compiler_flags, string main_class, string package_name);

#endif