#include <string>
#include <stdio.h>

#ifndef COMPILATION
#define COMPILATION

using namespace std;

inline bool exists(const string& name) {
    if (FILE *file = fopen(name.c_str(), "r")) {
        fclose(file);
        return true;
    } else {
        return false;
    }   
}

#endif