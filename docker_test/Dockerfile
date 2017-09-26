FROM ubuntu
MAINTAINER timothyglensmith@cedarville.edu

RUN apt-get update

RUN apt-get install -y gcc
RUN apt-get install -y g++
RUN apt-get install -y ruby
RUN apt-get install -y python
RUN apt-get install -y gobjc
RUN apt-get install -y scala
RUN apt-get install -y curl
RUN apt-get install -y default-jre
RUN apt-get install -y default-jdk
RUN apt-get install -y python-software-properties
RUN apt-get install -y software-properties-common

RUN apt-get install -y sudo
RUN apt-get install -y bc

RUN export uid=1000 gid=1000 && \
	mkdir -p /home/abc && \
	echo "abc:x:${uid}:${gid}:Abc,,,:/home/abc:/bin/bash" >> /etc/passwd && \
	echo "abc:x:${uid}:" >> /etc/group && \
	echo "abc ALL=(ALL) NOPASSWD: ALL" > /etc/sudoers.d/abc && \
	chmod 0440 /etc/sudoers.d/abc
	
COPY compiler_scripts/*.sh /home/abc/

RUN chown root.root /home/abc/*.sh && \
	chmod 4755 /home/abc/*.sh

USER abc
ENV HOME /home/abc
WORKDIR $HOME
CMD /bin/bash	

CMD echo "Gradel docker image created"


