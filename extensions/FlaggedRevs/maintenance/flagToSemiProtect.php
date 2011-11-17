<?php

if ( getenv( 'MW_INSTALL_PATH' ) ) {
    $IP = getenv( 'MW_INSTALL_PATH' );
} else {
    $IP = dirname(__FILE__).'/../../..';
}
require "$IP/maintenance/commandLine.inc";
require dirname(__FILE__) . '/flagToSemiProtect.inc';

if( isset( $options['help'] ) || empty( $args[0] ) ) {
	echo <<<TEXT
Usage:
    php flagToSemiProtect.php --help
    php flagToSemiProtect.php <username> [<reason>]

    --help               : This help message
    --<user>             : The name of the admin user to use as the "protector"

TEXT;
	exit(0);
}

error_reporting( E_ALL );

$wgUser = User::newFromName( $args[0] );
if ( !$wgUser || !$wgUser->getID() ) {
	echo( "Invalid user specified!" );
	exit(0);
}

echo "Protecter username: \"".$wgUser->getName()."\"\n";
echo "Running in 5 seconds...\n";
sleep( 5 );

if ( isset( $args[1] ) ) {
	$reason = $args[1];
} else {
	$reason = "Converting flagged protection settings to edit protection settings.";
}

$db = wfGetDB( DB_MASTER );
flag_to_semi_protect( $db, $reason );
