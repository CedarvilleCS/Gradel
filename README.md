# Gradel Setup Instructions
_This guide assumes that you have a LAMP stack set up. If you don't, follow [these instructions](http://howtoubuntu.org/how-to-install-lamp-on-ubuntu). There's only about five steps that are needed._

You may also want to set the ServerName for use in some of the later steps. Adding `ServerName localhost` in `/etc/apache2/apache2.conf` will work.

### Installing and Updating Necessities
Get started by installing git and vim
```
sudo apt-get update
sudo apt-get install git vim -y
```

#### Installing Composer
Composer is a PHP-based installer that Symfony uses heavily.
Follow the instructions (here)[https://getcomposer.org/download/] to install Composer.

We will need a couple of additional PHP libraries.
```
sudo apt-get install php5-cli php5-curl acl libzmq3-dev php5-mysql phpmyadmin -y
sudo apt-get install php5-dev php5-gd php5-intl php5-xsl -y
```
If it prompts you during the phpmyadmin installation, choose `Apache` and `Yes`.


#### Installing ZeroMQ
ZMQ is a React-based library used in web sockets.
```
sudo apt-get install php-pear -y
sudo pecl install zmq-beta
```
It will prompt you for a value, just press enter to leave it blank for autodetecting.

After ZMQ is installed, we need to enable the extension in the PHP ini file. 
Add the following line to /etc/php5/cli/php.ini in the extensions section (look for other .so examples if you want to put it with them)
```
extension=zmq.so
```


#### Installing Docker
Docker is a lightweight virtual machine that allows us to run the student code in a safe environment.
Run the following commands to install it.
```
sudo apt-get install \
    apt-transport-https \
    ca-certificates \
    curl \
    software-properties-common -y

curl -fsSL https://download.docker.com/linux/ubuntu/gpg | sudo apt-key add -

sudo add-apt-repository \
   "deb [arch=amd64] https://download.docker.com/linux/ubuntu \
   $(lsb_release -cs) \
   stable"
   
sudo apt-get update   
sudo apt-get install docker-ce -y
```

### Configuring the O/S

We need to give some users some more permissions. The website and user need to be able to run Docker.
We are also using a group called 2018. Feel free to change this stuff as long as you remember that `www-data` needs to be able to access the Symfony files.
```
sudo getent group docker || sudo groupadd docker
sudo getent group 2018 || sudo groupadd 2018 -g 2018
sudo usermod -a -G docker www-data
sudo usermod -a -G 2018 www-data
sudo usermod -a -G docker $USER
sudo usermod -a -G 2018 $USER
sudo su -l $USER

sudo service apache2 restart
```

We need to open up an additional port in the firewall to run the web socket server on. You can change 8081 to be any port that you want. Just remember to change it later on as well.
```
sudo ufw allow 8081
```
#### Installing Symfony
Symfony is the PHP framework we are using for our website. It is the heart of our website.
```
sudo mkdir -p /usr/local/bin
sudo curl -LsS https://symfony.com/installer -o /usr/local/bin/symfony
sudo chmod a+x /usr/local/bin/symfony
```

### Downloading Gradel

Time to download the Git repository into the `/var/www` folder
We also rename Gradel to lowercase gradel for ease of use.
```
cd /var/www/
sudo git clone https://github.com/CedarvilleCS/Gradel.git
sudo mv /var/www/Gradel/ /var/www/gradel/
```

Change the owner and the group and set the chmod to make it so that only `www-data` can view it.
```
sudo chown -R $USER gradel/
sudo chgrp -R 2018 gradel/
sudo chmod -R 770 gradel/
```

### Installing Symfony Project

We need to install the Composer/Symfony bundles that our website uses. This sets up the entire site for us.
```
cd /var/www/gradel/symfony_project/
sudo php composer.phar install
```
You will be prompted to fill in a few parameters
`database_host`: localhost _(NOT 127.0.0.1)_
`database_port`: 3306
`database_name`: gradel
`database_user`: gradel
`database_password`: gradeldb251
`the rest`: you can leave the rest blank

You will probably get an error message while installing. If the error is about a socket_port variable, that's fine.
Add the following lines to `app/config/parameters.yml` after the secret, which you can change if you want to.
```
socket_port: 8081
socket_domain: "ws://<put_your_server_name_here>"
```

### Getting the Database Setup

We need to enable phpmyadmin if it isn't already.
Open `/etc/apache2/apache2.conf` and add the following line if it's not there

```
Include /etc/phpmyadmin/apache.conf
```

These changes need to be applied, so `sudo service apache2 restart`

** _Go to phpmyadmin and create a `gradel` database with a new priviliged gradel user using the credentials used above_ **

We need to install some vendor JavaScript with `sudo php bin/console assets:install`

Once the assets have been installed, we will do a check to make sure we can update the cache and prepare the ORM for creating the database schema and populating it for.
```
./update_cache
sudo php bin/console doctrine:schema:create 
sudo php bin/console doctrine:fixtures:load
```

### Setting Up the Virtual Host

We need to edit the virtual hosts so that Apache knows where to look for our Symfony project.
Edit the `/etc/apache2/sites-available/000-default.conf` or make a new conf file with these changes

1. Uncomment `ServerName www.example.com` and make it `ServerName <my_server_name>`
2. Set `DocumentRoot` to `/var/www/gradel/symfony_project/web`
3. Add the following code or modify the existing directory info
```
	<Directory "/var/www/gradel/symfony_project/web">
		Options -Indexes +FollowSymLinks
		AllowOverride All
		Order Allow,Deny
		Allow from All
	</Directory>
```

We need to enable URL rewriting to make the URLs pretty with `sudo a2enmod rewrite`

### Creating the Docker Container for Compilation

Now we can build the docker image that will run the compilation environment. Switch to the proper directory and build.
```
cd compilation/
./make_compiler.sh
sudo docker build --tag=gradel:latest ./.
cd ..
```

### Configure the Web Socket to Restart on Reboot

*Note: An unnamed developer destroyed joseph because he accidentally `chmod 770`'d the entire file structure recursively. Be careful with this step!!!*

We can add a script to the startup directory so that the socket will start on reboot. To do this:
`cd /etc/init.d`
Create a new file named socket_server.sh and add:
```
cd /var/www/gradel/symfony_project
./update_cache
cd /etc/init.d/
```
Then run:
```
sudo chmod +x socket_server.sh
sudo update-rc.d socket_server.sh defaults
```
This allows the script to be run as a script on reboot

### JS Caching

When adding new JS code, you must be careful of versioning. Chrome is aggressive about caching files client side. To assure that the user gets the most updated file, increment the `framework.assets.version` field.

### Cleanup

And we will update all of these apache changes with `sudo service apache2 restart`


When you login to Gradel, you may get a Google OAuth error. This means you need to add your URI to the allowed list. If you don't have access to this page, contact Timothy Smith, Emily Wolf, or Dr. Gallagher
[Google OAuth Redirect API](https://tinyurl.com/gradeloauth)

*Congrats!* You did it!

_Known Issue_ Some Linux Ubuntu installations do not have memory swap available, which is a procedure used by Docker to allow us to put limits on the number of files and processes run inside of the Docker container. You can run `docker info` to see if you get a swap warning on the last line. It works regardless on joseph and jabez, but it does not work on some virtual machines. A (relatively) unsafe version of the bash script that works is called `dockercompiler.sh.unsafe` in the compilation folder inside the project. You can swap out this file to allow Gradel to run on these systems. Try it out first. If your submit solution that gets no testcase results attached, it is a good indicator that there is a problem.
