FROM debian:10
 
RUN apt-get update && \
    apt-get install --no-install-recommends -y ca-certificates supervisor unzip curl sudo vim tmux locales php php-intl php-curl php-json php-mysql php-xml php-mbstring && \
    sed -i -e 's/# en_US.UTF-8 UTF-8/en_US.UTF-8 UTF-8/' /etc/locale.gen && \
    locale-gen && \
    adduser --disabled-password --gecos "First Last,RoomNumber,WorkPhone,HomePhone" --uid 1000 trd && \
    usermod -a -G sudo trd && \
    echo '%sudo ALL=(ALL) NOPASSWD:ALL' | tee -a /etc/sudoers && \
    curl -O "https://getcomposer.org/installer" > "/opt/composer-setup.php" && \
    php /opt/composer-setup.php --install-dir="/usr/local/bin" --filename="composer" && \
    apt-get clean && \
    rm -rf "/var/lib/apt/lists/*"
 
WORKDIR /opt/trd
 
COPY supervisord.conf /etc/supervisor/supervisord.conf
 
USER trd
 
ENTRYPOINT [ "/usr/bin/supervisord", "-n" ]
