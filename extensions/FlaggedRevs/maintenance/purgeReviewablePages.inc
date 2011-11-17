<?php

function list_reviewable_pages( $db, $fileHandle ) {
	global $wgFlaggedRevsNamespaces, $wgUseSquid, $wgUseFileCache;
	echo "Building list of all reviewable pages to purge ...\n";
	if ( !$wgUseSquid && !$wgUseFileCache ) {
		echo "Squid/file cache not enabled ... nothing to purge.\n";
		return;
	} elseif ( empty( $wgFlaggedRevsNamespaces ) ) {
		echo "There are no reviewable namespaces ... nothing to purge.\n";
		return;
	}
	$BATCH_SIZE = 500;
	$start = $db->selectField( 'page', 'MIN(page_id)', false, __FUNCTION__ );
	$end = $db->selectField( 'page', 'MAX(page_id)', false, __FUNCTION__ );
	if ( is_null( $start ) || is_null( $end ) ) {
		echo "... page table seems to be empty.\n";
		return;
	}
	# Do remaining chunk
	$end += $BATCH_SIZE - 1;
	$blockStart = $start;
	$blockEnd = $start + $BATCH_SIZE - 1;
	$count = 0;
	while ( $blockEnd <= $end ) {
		echo "... doing page_id from $blockStart to $blockEnd\n";
		$res = $db->select( 'page', '*', 
			array(
				"page_id BETWEEN $blockStart AND $blockEnd",
				'page_namespace' => $wgFlaggedRevsNamespaces ),
			__FUNCTION__
		);
		# Go through and protect each page...
		foreach ( $res as $row ) {
			$title = Title::newFromRow( $row );
			$fa = FlaggedArticle::getTitleInstance( $title );
			if ( $fa->isReviewable( FR_MASTER ) ) {
				# Need to purge this page - add to list
				fwrite( $fileHandle, $title->getPrefixedDBKey() . "\n" );
				$count++;
			}
		}
		$db->freeResult( $res );
		$blockStart += $BATCH_SIZE - 1;
		$blockEnd += $BATCH_SIZE - 1;
		wfWaitForSlaves( 5 ); // not really needed
	}
	echo "List of reviewable pages to purge complete ... {$count} pages\n";
}

function purge_reviewable_pages( $db, $fileHandle ) {
	global $wgUseSquid, $wgUseFileCache;
	echo "Purging squid cache for list of pages to purge ...\n";
	if ( !$wgUseSquid && !$wgUseFileCache ) {
		echo "Squid/file cache not enabled ... nothing to purge.\n";
		return;
	}
	$BATCH_SIZE = 500;
	$count = 0;
	while ( !feof( $fileHandle ) ) {
		$dbKey = trim( fgets( $fileHandle ) );
		if ( $dbKey == '' ) continue; // last line?
		$title = Title::newFromDBkey( $dbKey );
		if ( $title ) {
			$title->purgeSquid(); // send PURGE
			HTMLFileCache::clearFileCache( $title ); // purge poor-mans's squid
			$count++;
			echo "... $dbKey\n";
			if ( $count % $BATCH_SIZE ) {
				wfWaitForSlaves( 5 ); // not really needed
			}
		} else {
			echo "Invalid title - cannot purge: $dbKey\n";
		}
	}
	echo "Squid/file cache purge of page list complete ... {$count} pages\n";
}
