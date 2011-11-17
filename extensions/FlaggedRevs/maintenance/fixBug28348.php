<?php

if ( getenv( 'MW_INSTALL_PATH' ) ) {
    $IP = getenv( 'MW_INSTALL_PATH' );
} else {
    $IP = dirname(__FILE__).'/../../..';
}

$options = array( 'help', 'startrev' );
require "$IP/maintenance/commandLine.inc";
require dirname(__FILE__) . '/fixBug28348.inc';

if ( isset($options['help']) ) {
	echo <<<TEXT
Purpose:
	Correct bad fi_img_timestamp rows due to bug 28348
Usage:
    php updateLinks.php --help
    php updateLinks.php [--startrev <ID>]

    --help             : This help message
    --<ID>             : The ID of the starting rev
    --<CALL>           : One of (revs)

TEXT;
	exit(0);
}

error_reporting( E_ALL );

$startRev = isset( $options['startrev'] ) ?
	(int)$options['startrev'] : null;

update_images_bug_28348( $startRev );
