FROM wordpress:5.5.1

ADD .devcontainer/install-composer.sh /var/www/html
RUN chmod +x /var/www/html/install-composer.sh 
RUN apt-get update 
RUN apt-get install wget -y 
RUN ./install-composer.sh 