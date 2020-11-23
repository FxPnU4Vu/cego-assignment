FROM ubuntu:focal
ARG TZ="Europe/Copenhagen"
RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone
RUN echo 'debconf debconf/frontend select Noninteractive' | debconf-set-selections
RUN apt update && apt install -y -q php-cli php-mysql
RUN apt autoremove --purge
RUN mkdir /script
RUN mkdir /script/output
COPY script/cegoassignment.php /script/
RUN chmod +x /script/cegoassignment.php
RUN sed -i 's/127.0.0.1/cegomariadb/' /script/cegoassignment.php
ENTRYPOINT ["/script/cegoassignment.php"]
