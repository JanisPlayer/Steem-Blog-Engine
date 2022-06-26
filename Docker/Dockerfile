FROM ubuntu:latest

LABEL maintainer="steem-blog-engine"

ENV USER www-data
ENV HOMEDIR "/home/${USER}"

ENV TZ=Europe/Berlin
RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone

RUN apt-get update -y && apt-get upgrade -y && apt-get install wget -y && apt-get install curl -y && apt-get install apache2 -y && apt-get install php8.1 -y

RUN mkdir /home/entrypoint/

ADD entrypoint.sh /home/
RUN chmod 755 /home/entrypoint/entrypoint.sh

VOLUME /var/www/html/

WORKDIR /home/entrypoint/

CMD ["bash"]

ENTRYPOINT ["/home/entrypoint/entrypoint.sh"]

# Expose ports
EXPOSE 8080/tcp