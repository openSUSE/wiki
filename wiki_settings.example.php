<?php
# Settings template for localhost development. Use SQLite and PHP internal
# server. No need to setup database account or apache configuration.
#
# Copy paste this file, and rename to wiki_settings.php
#
# You don't need to change anything. However, it is totally okay to modify it as
# your wish.

# Protect against web entry. Do NOT change this line.
if ( !defined( 'MEDIAWIKI' ) ) {
	exit;
}

## The protocol and server name to use in fully-qualified URLs. Do NOT change this line.
$wgServer = "http://localhost:8023";

## Database settings
$wgDBtype = "sqlite";
$wgDBserver = "";
$wgDBname = "wiki";
$wgDBuser = "";
$wgDBpassword = "";
$wgSQLiteDataDir = __DIR__ . "/data";

$wgLanguageCode = "en";

$wgSecretKey = "d7f485efc01a837be9c6d1fee3e04bf9257bb2473f3d6e0ea05ccf6b9010850a";
$wgUpgradeKey = "46716633f6e3d34a";
