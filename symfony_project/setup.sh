#! /bin/bash

./composer_install.sh && \
./zero_mq_install.sh && \
./docker_install.sh && \
./system_configure.sh && \
./symfony_install.sh && \
./install_symfony_deps.sh
