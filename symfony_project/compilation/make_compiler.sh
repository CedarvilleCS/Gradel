#!/bin/bash

sudo g++ -std=c++11 compiler_source/*.cpp -o compiler_source/compiler
sudo chown root compiler_source/compiler
sudo chgrp root compiler_source/compiler
sudo chmod 700 compiler_source/compiler
sudo chmod a+x compiler_source/compiler
sudo chmod u+s compiler_source/compiler
