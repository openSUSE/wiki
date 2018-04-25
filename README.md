# openSUSE Wiki

## Development

### Installation

1.  Of course, you are using openSUSE 🦎.

    ```
    sudo zypper install git php7 php7-fileinfo php7-json php7-sqlite php7-mbstring php-composer
    ```

2.  Optional, if you want to build skin.

    ```
    sudo zypper install nodejs npm
    sudo npm install -g gulp
    ```

3.  Fork this project and clone your forked repository to your machine.

    ```
    git clone <your-git-url>
    cd wiki
    git submodule update --init
    ```

4.  Install MediaWiki 1.29 Release (a little bit old...)

    ```
    git clone --branch REL1_29 --depth 1 https://gerrit.wikimedia.org/r/p/mediawiki/core.git
    rm -rf core/.git
    mv -n core/* .
    rm -rf core
    ```

5.  Install Composer packages

    ```
    composer install
    ```

6.  Start PHP internal web server

    ```
    php -S localhost:8023 server.php
    ```

7.  Visit
