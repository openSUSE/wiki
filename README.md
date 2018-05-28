# openSUSE Wiki

## Development

### Setup

Of course, you are using openSUSE 🦎.

1.  Fork this project on Github if you don't have direct push access.
2.  Clone it to your computer: `git clone <your-git-url>`
3.  Change to project directory: `cd <your-git-dir>`
4.  Update Git submodules: `git submodule update --init`
5.  Run installation script: `./install_devel.sh`
6.  Start PHP built-in web server: `./start_devel.sh`
7.  Visit http://localhost:8023/mw-config/ and install the wiki

Everytime you want to code, just do:

1.  Start PHP built-in web server: `./start_devel.sh`
2.  Visit http://localhost:8023/

### PHP Configuration (Optional)

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
