#!/bin/bash

sudo g++ -std=c++11 *.cpp -o compiler
sudo chown root compiler
sudo chgrp root compiler
sudo chmod 700 compiler
sudo chmod a+x compiler
sudo chmod u+s compiler

sudo rm -rf compiled_code/*
