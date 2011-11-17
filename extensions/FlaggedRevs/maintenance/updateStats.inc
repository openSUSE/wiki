<?php

if ( php_sapi_name() != 'cli' ) {
	print( 'Invalid entry' );
	die( 1 );
}

function update_flaggedrevs_stats() {
	global $wgFlaggedRevsNamespaces, $wgFlaggedRevsStatsAge;
	print( "ValidationStatistics..." );
	if ( empty( $wgFlaggedRevsNamespaces ) ) {
		print( "nothing to update.");
		return; // no SQL errors please :)
	}

	// Set key to limit duplicate updates...
	$dbCache = wfGetCache( CACHE_DB );
	$keySQL = wfMemcKey( 'flaggedrevs', 'statsUpdating' );
	$dbCache->set( $keySQL, '1', $wgFlaggedRevsStatsAge );

	$ns_total = array();
	$ns_reviewed = array();
	$ns_synced = array();

	$dbr = wfGetDB( DB_SLAVE );
	// Get total, reviewed, and synced page count for each namespace
	$ret = $dbr->select( array( 'page', 'flaggedpages' ),
		array( 'page_namespace',
			'COUNT(*) AS total',
			'COUNT(fp_page_id) AS reviewed',
			'COUNT(fp_pending_since) AS pending' ),
		array( 'page_is_redirect' => 0, 'page_namespace' => $wgFlaggedRevsNamespaces ),
		__METHOD__,
		array( 'GROUP BY' => 'page_namespace' ),
		array( 'flaggedpages' => array( 'LEFT JOIN', 'fp_page_id = page_id' ) )
	);
	while ( $row = $dbr->fetchObject( $ret ) ) {
		$ns_total[$row->page_namespace] = (int)$row->total;
		$ns_reviewed[$row->page_namespace] = (int)$row->reviewed;
		$ns_synced[$row->page_namespace] = (int)$row->reviewed - (int)$row->pending;
	}
	// Getting mean pending edit time
	// @TODO: percentiles
	$nowUnix = wfTimestamp( TS_UNIX ); // current time in UNIX TS
	$avePET = (int)$dbr->selectField( array('flaggedpages','page'),
		"AVG( $nowUnix - UNIX_TIMESTAMP(fp_pending_since) )",
		array( 'fp_pending_since IS NOT NULL', 
			'fp_page_id = page_id',
			'page_namespace' => $wgFlaggedRevsNamespaces // Sanity check the NS
		),
		__METHOD__,
		array( 'USE INDEX' => array('flaggedpages' => 'fp_pending_since') )
	);
	# Get wait times for anon edits...
	list( $aveRT, $medianRT, $rPerTable ) = getAveReviewTimesAnons( $dbr, $dbCache );

	// Save the data...
	// @TODO: new permanent schema
	#echo "\nAve=".($aveRT/3600)." Med=".($medianRT/3600)."\n";
	$dbw = wfGetDB( DB_MASTER );
	// Make sure the master has flaggedrevs_stats and _stats2
	if ( !$dbw->tableExists( 'flaggedrevs_stats' ) ||
		!$dbw->tableExists( 'flaggedrevs_stats2' ) )
	{
		print( "Need to run update.php to create flaggedrevs_stats and flaggedrevs_stats2. Stopping." );
		die( 1 );
	}
	$dbw->begin();
	// Save/cache percentiles
	$key = wfMemcKey( 'flaggedrevs', 'reviewPercentiles' );
	$dbCache->set( $key, $rPerTable, 30*24*3600 );
	// Per-namespace stats...
	foreach( $wgFlaggedRevsNamespaces as $namespace ) {
		$dbw->replace( 'flaggedrevs_stats',
			array( 'namespace' ),
			array( 'namespace' => intval($namespace),
				'total'    => isset($ns_total[$namespace]) ? $ns_total[$namespace] : 0,
				'reviewed' => isset($ns_reviewed[$namespace]) ? $ns_reviewed[$namespace] : 0,
				'synced'   => isset($ns_synced[$namespace]) ? $ns_synced[$namespace] : 0
			),
			__METHOD__
		);
	}
	// Overall stats...
	$dbw->replace( 'flaggedrevs_stats2',
		array( 'stat_id' ),
		array( 'stat_id' => 1,
			'ave_review_time' => $aveRT,
			'med_review_time' => $medianRT,
			'ave_pending_time' => $avePET
		),
		__METHOD__
	);
	// Update timestamp
	$dbw->replace( 'querycache_info', array('qci_type'),
		array('qci_type' => 'validationstats', 'qci_timestamp' => $dbw->timestamp()),
		__METHOD__
	);
	$dbw->commit();
	// Stats are now up to date!
	$key = wfMemcKey( 'flaggedrevs', 'statsUpdated' );
	$dbCache->set( $key, '1', $wgFlaggedRevsStatsAge );
	$dbCache->delete( $keySQL );
	print( "done updating!\n" );
}

function getAveReviewTimesAnons( $dbr, $dbCache ) {
	global $wgFlaggedRevsNamespaces;
	$aveRT = $medianRT = 0;
	$rPerTable = array(); // review wait percentiles
	if ( FlaggedRevs::useOnlyIfProtected() ) {
		return array($aveRT,$medianRT,$rPerTable); // disabled
	}
	$nowUnix = wfTimestamp( TS_UNIX ); // current time in UNIX TS
	# Only go so far back...otherwise we will get garbage values due to 
	# the fact that FlaggedRevs wasn't enabled until after a while.
	$installedUnix = (int)$dbr->selectField( 'logging',
		'UNIX_TIMESTAMP( MIN(log_timestamp) )',
		array('log_type' => 'review')
	);
	if ( !$installedUnix ) {
		$installedUnix = wfTimestamp( TS_UNIX ); // now
	}
	$encInstalled = $dbr->addQuotes( $dbr->timestamp( $installedUnix ) );
	# Skip the most recent recent revs as they are likely to just 
	# be WHERE condition misses. This also gives us more data to use.
	# Lastly, we want to avoid bias that would make the time too low
	# since new revisions could not have "took a long time to sight".
	$worstLagTS = $dbr->timestamp(); // now
	$last = '0';
	while ( true ) { // should almost always be ~1 pass
		# Get the page with the worst pending lag...
		$row = $dbr->selectRow( array( 'flaggedpage_pending', 'flaggedrevs' ),
			array( 'fpp_page_id', 'fpp_rev_id', 'fpp_pending_since', 'fr_timestamp' ),
			array(
				'fpp_quality' => 0, // "checked"
				'fpp_pending_since > '.$encInstalled, // needs actual display lag
				'fr_page_id = fpp_page_id AND fr_rev_id = fpp_rev_id',
				'fpp_pending_since > '.$dbr->addQuotes($last), // skip failed rows
			),
			__METHOD__,
			array( 'ORDER BY' => 'fpp_pending_since ASC',
				'USE INDEX' => array( 'flaggedpage_pending' => 'fpp_quality_pending' ) )
		);
		if ( !$row ) break;
		# Find the newest revision at the time the page was reviewed,
		# this is the one that *should* have been reviewed.
		$idealRev = (int)$dbr->selectField( 'revision', 'rev_id', 
			array( 'rev_page' => $row->fpp_page_id, 
				'rev_timestamp < '.$dbr->addQuotes( $row->fr_timestamp ) ),
			__METHOD__,
			array( 'ORDER BY' => 'rev_timestamp DESC', 'LIMIT' => 1 )
		);
		# Fudge factor to prevent deliberate reviewing of non-current revisions
		# from squeezing the range. Shouldn't effect anything otherwise.
		if ( $row->fpp_rev_id >= $idealRev ) {
			$worstLagTS = $row->fpp_pending_since;
			break;
		} else {
			$last = $row->fpp_pending_since; // next iteration
		}
	}
	# Avoid having to censor data
	# Note: if no edits pending, $worstLagTS is the cur time just before we checked
	# for the worst lag. Thus, new edits *right* after the check are properly excluded.
	$maxTSUnix = wfTimestamp(TS_UNIX,$worstLagTS) - 1; // all edits later reviewed
	$encMaxTS = $dbr->addQuotes( $dbr->timestamp( $maxTSUnix ) );
	# Use a one week time range
	$days = 7;
	$minTSUnix = $maxTSUnix - $days*86400;
	$encMinTS = $dbr->addQuotes( $dbr->timestamp( $minTSUnix ) );
	# Approximate the number rows to scan
	$rows = $dbr->estimateRowCount( 'revision', '1',
		"rev_user=0 AND rev_timestamp BETWEEN $encMinTS AND $encMaxTS" );
	# If the range doesn't have many rows (like on small wikis), use 30 days
	if ( $rows < 500 ) {
		$days = 30;
		$minTSUnix = $maxTSUnix - $days*86400;
		$encMinTS = $dbr->addQuotes( $dbr->timestamp( $minTSUnix ) );
		# Approximate rows to scan
		$rows = $dbr->estimateRowCount( 'revision', '1',
			"rev_user=0 AND rev_timestamp BETWEEN $encMinTS AND $encMaxTS" );
		# If the range doesn't have many rows (like on really tiny wikis), use 90 days
		if ( $rows < 500 ) {
			$days = 90;
			$minTSUnix = $maxTSUnix - $days*86400;
		}
	}
	$sampleSize = 1500; // sample size
	# Sanity check the starting timestamp
	$minTSUnix = max($minTSUnix,$installedUnix);
	$encMinTS = $dbr->addQuotes( $dbr->timestamp( $minTSUnix ) );
	# Get timestamp boundaries
	$timeCondition = "rev_timestamp BETWEEN $encMinTS AND $encMaxTS";
	# Get mod for edit spread
	$ecKey = wfMemcKey( 'flaggedrevs', 'anonEditCount', $days );
	$edits = (int)$dbCache->get( $ecKey );
	if ( !$edits ) {
		$edits = (int)$dbr->selectField( array('page','revision'),
			'COUNT(*)',
			array(
				'rev_user = 0', // IP edits (should start off unreviewed)
				$timeCondition, // in time range
				'page_id = rev_page',
				'page_namespace' => $wgFlaggedRevsNamespaces // reviewable NS
			)
		);
		$dbCache->set( $ecKey, $edits, 14*24*3600 ); // cache for 2 weeks
	}
	$mod = max( floor( $edits/$sampleSize ), 1 ); # $mod >= 1
	# For edits that started off pending, how long do they take to get reviewed?
	# Edits started off pending if they were made when a flagged rev of the page already existed.
	# Get the *first* reviewed rev *after* each edit and get the time difference.
	list($revision,$page,$flaggedrevs) = $dbr->tableNamesN('revision','page','flaggedrevs');
	$reviewableNS = $dbr->makeList( $wgFlaggedRevsNamespaces );
	$sql = "
		SELECT
			MIN(rev_timestamp) AS rt, MAX(p.fr_timestamp) AS pft, MIN(n.fr_timestamp) AS nft
		FROM $revision
		INNER JOIN $page FORCE INDEX (PRIMARY) ON
			(page_id = rev_page AND page_namespace IN ($reviewableNS))
		INNER JOIN $flaggedrevs AS p FORCE INDEX (PRIMARY) ON
			(p.fr_page_id = page_id AND p.fr_rev_id < rev_id AND p.fr_timestamp < rev_timestamp)
		LEFT JOIN $flaggedrevs AS n FORCE INDEX (PRIMARY) ON
			(n.fr_page_id = page_id AND n.fr_rev_id >= rev_id AND n.fr_timestamp >= rev_timestamp)
		WHERE
			((rev_user = 0) AND ($timeCondition) AND ((rev_id % $mod) = 0))
		GROUP BY rev_id";
	# Actually run the query...
	$res = $dbr->doQuery( $sql, __METHOD__ );
	$secondsR = 0; // total wait seconds for edits later reviewed
	$secondsP = 0; // total wait seconds for edits still pending
	$aveRT = $medianRT = 0;
	if ( $dbr->numRows($res) ) {
		$times = array();
		# Get the sum of elapsed times
		while ( $row = $dbr->fetchObject($res) ) {
			# Reviewed: just subtract the times (flagged - edit made)
			if ( $row->nft != null ) {
				$time = wfTimestamp(TS_UNIX,$row->nft) - wfTimestamp(TS_UNIX,$row->rt);
				$time = max( $time, 0 ); // sanity
				$secondsR += $time;
				$times[] = $time;
			# Not reviewed yet: censored data (now - edit made)
			} else {
				$time = $nowUnix - wfTimestamp(TS_UNIX,$row->rt);
				$time = max( $time, 0 ); // sanity
				$secondsP += $time;
			}
			#echo( "\nedit:".$row->rt . "\twait:" . $time );
		}
		$sampleSize = count($times);
		$aveRT = ($secondsR + $secondsP)/$sampleSize; // sample mean
		sort($times); // order smallest -> largest
		// Sample median
		$rank = round( count($times)/2 + .5 ) - 1;
		$medianRT = $times[$rank];
		// Make percentile tabulation data
		$percentile = 35; // start at 35th
		while ( $percentile < 100 ) {
			$rank = round( $percentile*count($times)/100 + .5 ) - 1;
			$rPerTable[$percentile] = $times[$rank];
			$percentile += 10;
		}
		echo "(sampled ".count($times)." edits)...";
	}
	return array($aveRT,$medianRT,$rPerTable);
}
