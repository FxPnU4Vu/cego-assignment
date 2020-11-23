FROM ubuntu:focal
ARG TZ="Europe/Copenhagen"
RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone
RUN echo 'debconf debconf/frontend select Noninteractive' | debconf-set-selections
RUN apt update && apt install -y -q php7.4-cli php7.4-mysql && apt autoremove --purge
RUN mkdir -p /script/output
COPY script/cegoassignment.php /script/
RUN chmod +x /script/cegoassignment.php
RUN sed -i 's/127.0.0.1/cegomariadb/' /script/cegoassignment.php
ENTRYPOINT ["/script/cegoassignment.php"]
