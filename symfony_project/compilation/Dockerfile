FROM ubuntu:14.04
MAINTAINER timothyglensmith@cedarville.edu


RUN apt-get update

RUN apt-get install -y gcc
RUN apt-get install -y g++

RUN apt-get install -y ruby
RUN apt-get install -y python
RUN apt-get install -y python3
RUN apt-get install -y default-jre
RUN apt-get install -y default-jdk
RUN apt-get install -y python-software-properties
RUN apt-get install -y software-properties-common

RUN apt-get install -y php5

# RUN apt-get install -y gobjc
# RUN apt-get install -y scala
# RUN apt-get install -y curl

RUN yes '' | add-apt-repository ppa:ubuntu-toolchain-r/test

RUN apt-get update

RUN apt-get install -y gcc-5 g++-5

RUN update-alternatives --install /usr/bin/gcc gcc /usr/bin/gcc-5 60 --slave /usr/bin/g++ g++ /usr/bin/g++-5
RUN update-alternatives --install /usr/bin/gcc gcc /usr/bin/gcc-5 1

RUN ln -s /usr/bin/g++-5 /usr/bin/g++

RUN apt-get install -y zip
RUN apt-get install -y sudo
RUN apt-get install -y vim
# RUN apt-get install -y bc
# RUN apt-get install -y time

# Set up a new user
RUN useradd -ms /bin/bash student

# parent directory where compilation will take place
USER root

RUN mkdir /compilation
WORKDIR /compilation

ADD compiler_source/compiler /compilation/
RUN chmod 700 compiler
RUN chmod a+x compiler
RUN chmod u+s compiler

# Run the docker as that user who has almost no privileges
USER student
CMD /bin/bash