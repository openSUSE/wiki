<?php

function flag_to_semi_protect( $db, $reason ) {
	global $wgFlaggedRevsNamespaces, $wgFlaggedRevsProtection;
	echo "Semi-protecting all flag-protected pages...\n";
	if ( !$wgFlaggedRevsProtection ) {
		echo "\$wgFlaggedRevsProtection not enabled!\n";
	}
	$BATCH_SIZE = 500;
	$start = $db->selectField( 'flaggedpage_config', 'MIN(fpc_page_id)', false, __FUNCTION__ );
	$end = $db->selectField( 'flaggedpage_config', 'MAX(fpc_page_id)', false, __FUNCTION__ );
	if ( is_null( $start ) || is_null( $end ) ){
		echo "...flaggedpage_config table seems to be empty.\n";
		return;
	}
	# Do remaining chunk
	$end += $BATCH_SIZE - 1;
	$blockStart = $start;
	$blockEnd = $start + $BATCH_SIZE - 1;
	$count = 0;
	while ( $blockEnd <= $end ) {
		echo "...doing fpc_page_id from $blockStart to $blockEnd\n";
		$res = $db->select(
			array( 'flaggedpage_config', 'page' ),
			array( 'fpc_page_id', 'fpc_level', 'fpc_expiry' ), 
			array( "fpc_page_id BETWEEN $blockStart AND $blockEnd",
				'page_namespace' => $wgFlaggedRevsNamespaces,
				'page_id = fpc_page_id',
				"fpc_level != ''" ),
			__FUNCTION__
		);
		# Go through and protect each page...
		foreach ( $res as $row ) {
			$title = Title::newFromId( $row->fpc_page_id );
			if ( $title->isProtected( 'edit' ) ) {
				continue; // This page already has edit protection - skip it
			}
			$article = new Article( $title );
			# Flagged protection settings
			$frLimit = trim( $row->fpc_level );
			$frExpiry = ( $row->fpc_expiry === 'infinity' )
				? Block::infinity()
				: wfTimestamp( TS_MW, $row->fpc_expiry );
			# Build the new protection settings
			$cascade = 0;
			$limit = $expiry = array();
			$desc = array(); // for output
			foreach ( $title->getRestrictionTypes() as $type ) {
				# Get existing restrictions for this action
				$oldLimit = $title->getRestrictions( $type ); // array
				$oldExpiry = $title->getRestrictionExpiry( $type ); // MW_TS
				# Move or Edit rights - take highest of (flag,type) settings
				if ( $type == 'edit' || $type == 'move' ) {
					# Sysop flag-protect -> full protect
					if ( $frLimit == 'sysop' || in_array( 'sysop', $oldLimit ) ) {
						$newLimit = 'sysop';
					# Reviewer/autoconfirmed flag-protect -> semi-protect
					} else {
						$newLimit = 'autoconfirmed';
					}
					# Take highest expiry of (flag,type) settings
					$newExpiry = ( !$oldLimit || $frExpiry >= $oldExpiry )
						? $frExpiry // note: 'infinity' > '99999999999999'
						: $oldExpiry;
				# Otherwise - maintain original limits
				} else {
					$newLimit = $oldLimit;
					$newExpiry = $oldExpiry;
				}
				$limit[$type] = $newLimit;
				$expiry[$type] = $newExpiry;
				$desc[] = "{$type}={$newLimit}: {$newExpiry}";
			}
			
			$db->begin();
			$ok = $article->updateRestrictions( $limit, $reason, $cascade, $expiry );
			if ( $ok ) {
				#echo $title->getPrefixedText() . "\n\t[" . implode( ', ', $desc ) . "]\n";
				$count++;
			} else {
				echo "Could not protect: " . $title->getPrefixedText() . "\n";
			}
			$db->commit();
		}
		$db->freeResult( $res );
		$blockStart += $BATCH_SIZE - 1;
		$blockEnd += $BATCH_SIZE - 1;
		wfWaitForSlaves( 5 );
	}
	echo "Protection of all flag-protected pages complete ... {$count} pages\n";
}
