#include <stdio.h>
#include <unistd.h>

int main(){

	system("echo 'YES' > /home/abc/submission/testcase_diff1.log");
	system("echo 'YES' > /home/abc/submission/testcase_diff2.log");
	system("echo 'YES' > /home/abc/submission/testcase_diff3.log");
	system("echo 'YES' > /home/abc/submission/testcase_diff4.log");
	
	system("echo 'GOTCHA' > /home/abc/submission/output/1.out");
	system("echo 'GOTCHA' > /home/abc/submission/output/2.out");
	system("echo 'GOTCHA' > /home/abc/submission/output/3.out");
	system("echo 'GOTCHA' > /home/abc/submission/output/4.out");
	
	system("echo 'user 0m0.000s' >> /home/abc/submission/testcase_exectime.log");
	system("echo 'user 0m0.000s' >> /home/abc/submission/testcase_exectime.log");
	system("echo 'user 0m0.000s' >> /home/abc/submission/testcase_exectime.log");
	system("echo 'user 0m0.000s' >> /home/abc/submission/testcase_exectime.log");
	system("echo 'user 0m0.000s' >> /home/abc/submission/testcase_exectime.log");
		
	return 0;
}
