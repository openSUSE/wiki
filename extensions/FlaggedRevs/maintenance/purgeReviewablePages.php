<?php

if ( getenv( 'MW_INSTALL_PATH' ) ) {
    $IP = getenv( 'MW_INSTALL_PATH' );
} else {
    $IP = dirname(__FILE__).'/../../..';
}
require "$IP/maintenance/commandLine.inc";
require dirname(__FILE__) . '/purgeReviewablePages.inc';

$makeList = isset( $options['makelist'] );
$purgeList = isset( $options['purgelist'] );

if ( isset( $options['help'] ) || ( !$makeList && !$purgeList ) ) {
	echo <<<TEXT
Purpose:
	Use to purge squid/file cache for all reviewable pages
Usage:
    php purgeReviewablePages.php --help
	php purgeReviewablePages.php --makelist
	php purgeReviewablePages.php --purgelist

	--help		: This help message
	--makelist	: Build the list of reviewable pages to pagesToPurge.list
	--purgelist	: Purge the list of pages in pagesToPurge.list

TEXT;
	exit( 0 );
}

error_reporting( E_ALL );

$fileName = "pagesToPurge.list";

if ( $makeList ) {
	$db = wfGetDB( DB_MASTER );
	$fileHandle = fopen( $fileName, 'w+' );
	if ( !$fileHandle ) {
		echo "Can't open file to create purge list.\n";
		exit( -1 );
	}
	list_reviewable_pages( $db, $fileHandle );
	fclose( $fileHandle );
}

if ( $purgeList ) {
	$db = wfGetDB( DB_MASTER );
	$fileHandle = fopen( $fileName, 'r' );
	if ( !$fileHandle ) {
		echo "Can't open file to read purge list.\n";
		exit( -1 );
	}
	purge_reviewable_pages( $db, $fileHandle );
	fclose( $fileHandle );
}
