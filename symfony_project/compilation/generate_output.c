#include <stdio.h>

int main(){

    char line[250];

    while(1){

        scanf("%250s", line);

        if(strcmp(line, "<QUIT>") == 0){
            break;
        }

        printf("%s\n", line);
    }

    return 0;
}
