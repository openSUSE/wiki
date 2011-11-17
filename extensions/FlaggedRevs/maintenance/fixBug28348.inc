<?php

function update_images_bug_28348( $start = null ) {
	echo "Correcting fi_img_timestamp column in flaggedimages\n";
	
	$BATCH_SIZE = 1000;
	
	$db = wfGetDB( DB_MASTER );
	
	if( $start === null ) {
		$start = $db->selectField( 'flaggedimages', 'MIN(fi_rev_id)', false, __FUNCTION__ );
	}
	$end = $db->selectField( 'flaggedimages', 'MAX(fi_rev_id)', false, __FUNCTION__ );
	if( is_null( $start ) || is_null( $end ) ) {
		echo "...flaggedimages table seems to be empty.\n";
		return;
	}
	# Do remaining chunk
	$end += $BATCH_SIZE - 1;
	$blockStart = $start;
	$blockEnd = $start + $BATCH_SIZE - 1;
	
	$count = $changed = 0;
	while( $blockEnd <= $end ) {
		echo "...doing fi_rev_id from $blockStart to $blockEnd\n";
		$cond = "fi_rev_id BETWEEN $blockStart AND $blockEnd";
		$res = $db->select( 'flaggedimages', '*', $cond, __FUNCTION__ );
		
		$db->begin();
		# Go through and clean up missing items, as well as correct fr_quality...
		foreach( $res as $row ) {
			$count++;
			$fi_img_timestamp = trim( $row->fi_img_timestamp ); // clear pad garbage
			if ( !$fi_img_timestamp ) {
				continue; // nothing to check
			}
			$time = wfTimestamp( TS_MW, $fi_img_timestamp );
			$sha1 = $row->fi_img_sha1;
			# Check if the specified file exists...
			$file = RepoGroup::singleton()->findFileFromKey( $sha1, array( 'time' => $time ) );
			if ( !$file ) { // doesn't exist?
				$time = wfTimestamp( TS_MW, wfTimestamp( TS_UNIX, $time ) + 1 );
				# Check if the fi_img_timestamp value is off by 1 second...
				$file = RepoGroup::singleton()->findFileFromKey( $sha1, array( 'time' => $time ) );
				if ( $file ) {
					echo "fixed file {$row->fi_name} reference in rev ID {$row->fi_rev_id}\n";
					# Fix the fi_img_timestamp value...
					$dbw->update( 'flaggedimages',
						array( 'fi_img_timestamp' => $dbw->timestamp( $time ) ),
						array( 'fi_rev_id' => $row->fi_rev_id, 'fi_name' => $row->fi_name ),
						__METHOD__
					);
					$changed++;
				}
			}
		}
		$db->commit();
		$db->freeResult( $res );
		$blockStart += $BATCH_SIZE;
		$blockEnd += $BATCH_SIZE;
		wfWaitForSlaves( 5 );
	}
	echo "fi_img_timestamp column fixes complete ... {$count} rows [{$changed} changed]\n";
}
