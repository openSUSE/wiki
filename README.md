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
7.  Visit <http://localhost:8023/>

Everytime you want to code, just do:

1.  Start PHP built-in web server: `./start_devel.sh`
2.  Visit <http://localhost:8023/>

### Login

Default site admin user is Geeko, password is evergreen. Note default login modal
won't work on local development environment. You need to visit
<http://localhost:8023/Special:UserLogin>
