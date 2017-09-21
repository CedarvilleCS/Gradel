#! /bin/bash
cd root
cd shareddir

gcc hello.c -o hello.out

./hello.out > output.log
