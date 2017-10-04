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

COPY compiler_scripts/*.sh /home/abc/

ENV HOME /home/abc
WORKDIR $HOME
CMD /bin/bash


