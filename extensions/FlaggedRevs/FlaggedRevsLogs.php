<?php

class FlaggedRevsLogs {
	/**
	* $action is a valid review log action
	* @returns bool
	*/
	public static function isReviewAction( $action ) {
		return preg_match( '/^(approve2?(-i|-a|-ia)?|unapprove2?)$/', $action );
	}

	/**
	* $action is a valid stability log action
	* @returns bool
	*/
	public static function isStabilityAction( $action ) {
		return preg_match( '/^(config|modify|reset)$/', $action );
	}

	/**
	* $action is a valid review log deprecate action
	* @returns bool
	*/
	public static function isReviewDeapproval( $action ) {
		return ( $action == 'unapprove' || $action == 'unapprove2' );
	}

	/**	
	* Add setting change description to log line
	* @returns string
	*/
	public static function stabilityLogText(
		$type, $action, $title = null, $skin = null, $params = array()
	) {
		if ( !$title ) {
			return ''; // sanity check
		}
		if ( $skin ) {
			$titleLink = $skin->link( $title, $title->getPrefixedText() );
			$text = wfMsgHtml( "stable-logentry-{$action}", $titleLink );
		} else { // for content (e.g. IRC...)
			$text = wfMsgExt( "stable-logentry-{$action}",
				array( 'parsemag', 'escape', 'replaceafter', 'content' ),
				$title->getPrefixedText() );
		}
		$pars = self::expandParams( $params ); // list -> assoc array
		$details = self::stabilitySettings( $pars, !$skin ); // list of setting values
		$text .= " $details";
		return $text;
	}

	/**
	* Add history page link to log line
	*
	* @param Title $title
	* @param string $timestamp
	* @param array $params
	* @returns string
	*/
	public static function stabilityLogLinks( $title, $timestamp, $params ) {
		global $wgUser;
		# Add history link showing edits right before the config change
		$hist = $wgUser->getSkin()->link(
			$title,
			wfMsgHtml( 'hist' ),
			array(),
			array( 'action' => 'history', 'offset' => $timestamp )
		);
		$hist = wfMsgHtml( 'parentheses', $hist );
		return $hist;
	}

	/**
	* Make a list of stability settings for display
	*
	* @param array $pars assoc array
	* @param bool $forContent
	* @returns string
	*/
	public static function stabilitySettings( Array $pars, $forContent ) {
		global $wgLang, $wgContLang;
		$set = array();
		$settings = '';
		$wfMsg = $forContent ? 'wfMsgForContent' : 'wfMsg';
		$langObj = $forContent ? $wgContLang : $wgLang;
		// Protection-based configs (precedence never changed)...
		if ( !isset( $pars['precedence'] ) ) {
			if ( isset( $pars['autoreview'] ) && strlen( $pars['autoreview'] ) ) {
				$set[] = $wfMsg( 'stable-log-restriction', $pars['autoreview'] );
			}
		// General case...
		} else {
			// Default version shown on page view
			if ( isset( $pars['override'] ) ) {
				$set[] = $wfMsg( 'stabilization-def-short' ) .
					$wfMsg( 'colon-separator' ) .
					$wfMsg( 'stabilization-def-short-' . $pars['override'] );
			}
			// Autoreview restriction
			if ( isset( $pars['autoreview'] ) && strlen( $pars['autoreview'] ) ) {
				$set[] = 'autoreview=' . $pars['autoreview'];
			}
		}
		if ( $set ) {
			$settings = '[' . $langObj->commaList( $set ) . ']';
		}
		# Expiry is a MW timestamp or 'infinity'
		if ( isset( $pars['expiry'] ) && $pars['expiry'] != 'infinity' ) {
			$expiry_description = $wfMsg( 'stabilize-expiring',
				$langObj->timeanddate( $pars['expiry'], false, false ) ,
				$langObj->date( $pars['expiry'], false, false ) ,
				$langObj->time( $pars['expiry'], false, false )
			);
			if ( $settings != '' ) $settings .= ' ';
			$settings .= $wfMsg( 'parentheses', $expiry_description );
		}
		return htmlspecialchars( $settings );
	}

	/**
	* Create revision, diff, and history links for log line entry
	*/
	public static function reviewLogLinks( $action, $title, $params ) {
		global $wgUser, $wgLang;
		$links = '';
		# Show link to page with oldid=x as well as the diff to the former stable rev.
		# Param format is <rev id, last stable id, rev timestamp>.
		if ( isset( $params[0] ) ) {
			$revId = (int)$params[0]; // the revision reviewed
			$oldStable = isset( $params[1] ) ? (int)$params[1] : 0;
			# Show diff to changes since the prior stable version
			if ( $oldStable && $revId > $oldStable ) {
				$msg = self::isReviewDeapproval( $action )
					? 'review-logentry-diff2' // unreviewed
					: 'review-logentry-diff'; // reviewed
				$links .= '(';
				$links .= $wgUser->getSkin()->makeKnownLinkObj(
					$title,
					wfMsgHtml( $msg ),
					"oldid={$oldStable}&diff={$revId}"
				);
				$links .= ')';
			}
			# Show a diff link to this revision
			$ts = empty( $params[2] )
				? Revision::getTimestampFromId( $title, $revId )
				: $params[2];
			$time = $wgLang->timeanddate( $ts, true );
			$links .= ' (';
			$links .= $wgUser->getSkin()->makeKnownLinkObj(
				$title,
				wfMsgHtml( 'review-logentry-id', $revId, $time ),
				"oldid={$revId}&diff=prev&diffonly=0"
			);
			$links .= ')';
		}
		return $links;
	}

	/**
	 * Record a log entry on the action
	 * @param Title $title
	 * @param array $dims
	 * @param array $oldDims
	 * @param string $comment
	 * @param int $revId, revision ID
	 * @param int $stableId, prior stable revision ID
	 * @param bool $approve, approved? (otherwise unapproved)
	 * @param bool $auto
	 */
	public static function updateLog( $title, $dims, $oldDims, $comment,
		$revId, $stableId, $approve, $auto = false )
	{
		$log = new LogPage( 'review',
			false /* $rc */,
			$auto ? "skipUDP" : "UDP" // UDP logging
		);
		# Tag rating list (e.g. accuracy=x, depth=y, style=z)
		$ratings = array();
		# Skip rating list if flagging is just an 0/1 feature...
		if ( !FlaggedRevs::binaryFlagging() ) {
			foreach ( $dims as $quality => $level ) {
				$ratings[] = wfMsgForContent( "revreview-$quality" ) .
					wfMsgForContent( 'colon-separator' ) .
					wfMsgForContent( "revreview-$quality-$level" );
			}
		}
		$isAuto = ( $auto && !FlaggedRevs::isQuality( $dims ) ); // Paranoid check
		// Approved revisions
		if ( $approve ) {
			if ( $isAuto ) {
				$comment = wfMsgForContent( 'revreview-auto' ); // override this
			}
			# Make comma-separated list of ratings
			$rating = !empty( $ratings )
				? '[' . implode( ', ', $ratings ) . ']'
				: '';
			# Append comment with ratings
			if ( $rating != '' ) {
				$comment .= $comment ? " $rating" : $rating;
			}
			# Sort into the proper action (useful for filtering)
			$action = ( FlaggedRevs::isQuality( $dims ) || FlaggedRevs::isQuality( $oldDims ) ) ?
				'approve2' : 'approve';
			if ( !$stableId ) { // first time
				$action .= $isAuto ? "-ia" : "-i";
			} elseif ( $isAuto ) { // automatic
				$action .= "-a";
			}
		// De-approved revisions
		} else {
			$action = FlaggedRevs::isQuality( $oldDims ) ?
				'unapprove2' : 'unapprove';
		}
		$ts = Revision::getTimestampFromId( $title, $revId );
		# Param format is <rev id, old stable id, rev timestamp>
		$log->addEntry( $action, $title, $comment, array( $revId, $stableId, $ts ) );
	}

	/**
	 * Collapse an associate array into a string
	 * @param array $pars
	 * @returns string
	 */
	public static function collapseParams( Array $pars ) {
		$res = array();
		foreach ( $pars as $param => $value ) {
			// Sanity check...
			if ( strpos( $param, '=' ) !== false || strpos( $value, '=' ) !== false ) {
				throw new MWException( "collapseParams() - cannot use equal sign" );
			} elseif ( strpos( $param, "\n" ) !== false || strpos( $value, "\n" ) !== false ) {
				throw new MWException( "collapseParams() - cannot use newline" );
			}
			$res[] = "{$param}={$value}";
		}
		return implode( "\n", $res );
	}

	/**
	 * Expand a list of log params into an associative array
	 * @params array $pars
	 * @returns array (associative)
	 */
	public static function expandParams( Array $pars ) {
		$res = array();
		foreach ( $pars as $paramAndValue ) {
			list( $param, $value ) = explode( '=', $paramAndValue, 2 );
			$res[$param] = $value;
		}
		return $res;
	}
}
