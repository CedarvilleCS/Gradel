#!/bin/bash
sudo rm -rf compiled_code/*
sudo chmod 700 compiled_code/

sudo rm -rf time_logs/*
sudo chmod 700 time_logs/

sudo rm -rf run_logs/*
sudo chmod 700 run_logs/

sudo rm -rf user_output/*
sudo chmod 700 user_output/

sudo rm -rf diff_logs/*
sudo chmod 700 diff_logs/

sudo rm -rf flags/*
sudo chmod 700 flags/

sudo rm -rf student_code/*
sudo chmod 700 student_code/

sudo chmod -R 700 arg_files/
sudo chmod -R 700 input_files/
sudo chmod -R 700 custom_validator/
sudo chmod -R 700 output_files/
