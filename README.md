# openSUSE Wiki

## Development

### Setup

1.  Of course, you are using openSUSE 🦎.

    ```bash
    sudo zypper install git php7 php7-fileinfo php7-json php7-opcache php7-sqlite php7-mbstring php-composer
    ```

2.  Optional, if you want to build skin.

    ```bash
    sudo zypper install nodejs npm
    sudo npm install -g gulp
    ```

3.  Fork this project and clone your forked repository to your machine.

    ```bash
    git clone <your-git-url>
    cd wiki
    git submodule update --init
    ```

4.  Install MediaWiki 1.27 LTS release (a little bit old...)

    ```bash
    git clone --branch REL1_27 --depth 1 https://gerrit.wikimedia.org/r/p/mediawiki/core.git
    cd core
    rm -rf .git
    mv -n * ..
    cd ..
    rm -rf core
    ```

5.  Install Composer packages

    ```bash
    composer install
    ```

6.  Start PHP internal web server

    ```bash
    php -S localhost:8023 server.php
    ```

7.  Visit http://localhost:8023/mw-config/ and install the wiki

### Upgrade MediaWiki

Replace REL1_27 to the release branch you want.

```
rm -r docs includes languages maintenance mw-config resources tests
git clone --branch REL1_27 --depth 1 https://gerrit.wikimedia.org/r/p/mediawiki/core.git
cd core
rm -rf .git
mv -n * ..
cd ..
rm -rf core
php maintenance/update.php
```

## Production Deployment

### PHP Configuration

Modify `/etc/php7/apache2/php.ini`:

Increase memory and upload limits

```ini
memory_limit=64M

; Maximum allowed size for uploaded files.
upload_max_filesize=8M

; Must be greater than or equal to upload_max_filesize
post_max_size=8M
```

Enable OPCache

```ini
; Determines if Zend OPCache is enabled
opcache.enable=1

; Determines if Zend OPCache is enabled for the CLI version of PHP
opcache.enable_cli=1

; The OPcache shared memory storage size.
opcache.memory_consumption=64

; The amount of memory for interned strings in Mbytes.
opcache.interned_strings_buffer=8

; The maximum number of keys (scripts) in the OPcache hash table.
; Only numbers between 200 and 100000 are allowed.
opcache.max_accelerated_files=8000

; The maximum percentage of "wasted" memory until a restart is scheduled.
opcache.max_wasted_percentage=5

; When disabled, you must reset the OPcache manually or restart the
; webserver for changes to the filesystem to take effect.
opcache.validate_timestamps=1

; How often (in seconds) to check file timestamps for changes to the shared
; memory storage allocation. ("1" means validate once per second, but only
; once per request. "0" means always validate)
opcache.revalidate_freq=60

; If enabled, a fast shutdown sequence is used for the accelerated code
opcache.fast_shutdown=1
```
