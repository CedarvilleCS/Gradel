#! /bin/bash

sudo getent group docker || sudo groupadd docker
sudo getent group 2019gradel || sudo groupadd 2019gradel -g 2019gradel
sudo usermod -a -G docker www-data
sudo usermod -a -G 2019gradel www-data
sudo usermod -a -G docker $USER
sudo usermod -a -G 2019gradel $USER

sudo service apache2 restart

sudo ufw allow 8081
