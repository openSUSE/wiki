#!/usr/bin/env bash

# Installation Script for Local Development

if [ -e /etc/os-release ]; then
   . /etc/os-release
else
   . /usr/lib/os-release
fi
if [ "$ID" = "opensuse-leap" ]; then
    echo "Do something Leap $VERSION"
    sudo zypper addrepo https://download.opensuse.org/repositories/openSUSE:infrastructure:wiki/openSUSE_Leap_$VERSION/openSUSE:infrastructure:wiki.repo
elif [ "$ID" = "opensuse-tumbleweed" ]; then
    echo "Add wiki repository for openSUSE Tumbleweed"
    # TODO: use Tumbleweed repository
    sudo zypper addrepo https://download.opensuse.org/repositories/openSUSE:infrastructure:wiki/openSUSE_Leap_15.0/openSUSE:infrastructure:wiki.repo
fi

sudo zypper refresh

# Install RPM packages
sudo zypper install mediawiki_1_27-openSUSE nodejs8 npm8

# Install global NodeJS packages
sudo npm install -g gulp-cli

# Install project NodeJS packages
cd skins/Chameleon
npm install
cd ../..

# Link folders and files

function link() {
    rm ./$1
    ln -s /usr/share/mediawiki_1_27/$1 ./$1
}

link api.php
link autoload.php
link img_auth.php
link index.php
link load.php
link opensearch_desc.php
link thumb_handler.php
link thumb.php

link extensions/AbuseFilter
link extensions/Auth_remoteuser
link extensions/CategoryTree
link extensions/CirrusSearch
link extensions/Cite
link extensions/CiteThisPage
link extensions/ConfirmEdit
link extensions/Elastica
link extensions/Gadgets
link extensions/GitHub
link extensions/HitCounters
link extensions/ImageMap
link extensions/InputBox
link extensions/intersection
link extensions/Interwiki
link extensions/LocalisationUpdate
link extensions/Maps
link extensions/maps-vendor
link extensions/MultiBoilerplate
link extensions/Nuke
link extensions/ParamProcessor
link extensions/ParserFunctions
link extensions/PdfHandler
link extensions/Poem
link extensions/Renameuser
link extensions/ReplaceText
link extensions/RSS
link extensions/SpamBlacklist
link extensions/SyntaxHighlight_GeSHi
link extensions/TitleBlacklist
link extensions/UserMerge
link extensions/UserPageEditProtection
link extensions/Validator
link extensions/WikiEditor

link includes
link languages
link maintenance
link resources
link serialized
link vendor

# Copy development settings
cp wiki_settings.example.php wiki_settings.php

# Make directories
mkdir /tmp/wiki_sessions # PHP session save path
mkdir data # Save SQLite files

# Run installation script
rm -r data
mkdir data
mv LocalSettings.php _LocalSettings.php
php maintenance/install.php --dbuser="" --dbpass="" --dbname=wiki --dbpath=./data \
    --dbtype=sqlite --confpath=./ --scriptpath=/ --pass=evergreen openSUSE Geeko

rm LocalSettings.php
mv _LocalSettings.php LocalSettings.php
php maintenance/update.php
