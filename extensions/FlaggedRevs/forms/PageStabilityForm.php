<?php
# (c) Aaron Schulz 2010 GPL
if ( !defined( 'MEDIAWIKI' ) ) {
	echo "FlaggedRevs extension\n";
	exit( 1 );
}
/**
 * Class containing stability settings form business logic
 * Note: edit tokens are the responsibility of caller
 * Usage: (a) set ALL form params before doing anything else
 *		  (b) call ready() when all params are set
 *		  (c) call preloadSettings() or submit() as needed
 */
abstract class PageStabilityForm
{
	/* Form parameters which can be user given */
	protected $page = false; # Target page obj
	protected $watchThis = null; # Watch checkbox
	protected $reviewThis = null; # Auto-review option...
	protected $reason = ''; # Custom/extra reason
	protected $reasonSelection = ''; # Reason dropdown key
	protected $expiry = ''; # Custom expiry
	protected $expirySelection = ''; # Expiry dropdown key
	protected $override = -1; # Default version
	protected $autoreview = ''; # Autoreview restrictions...

	protected $oldConfig = array(); # Old page config
	protected $oldExpiry = ''; # Old page config expiry (GMT)
	protected $inputLock = 0; # Disallow bad submissions

	protected $user = null;
	protected $skin = null;

	public function __construct( $user ) {
		$this->user = $user;
		$this->skin = $user->getSkin();
	}

	public function getPage() {
		return $this->page;
	}

	public function setPage( Title $value ) {
		$this->trySet( $this->page, $value );
	}

	public function getWatchThis() {
		return $this->watchThis;
	}

	public function setWatchThis( $value ) {
		$this->trySet( $this->watchThis, $value );
	}

	public function getReason() {
		return $this->reason;
	}

	public function setReason( $value ) {
		$this->trySet( $this->reason, $value );
	}

	public function getReasonSelection() {
		return $this->reasonSelection;
	}

	public function setReasonSelection( $value ) {
		$this->trySet( $this->reasonSelection, $value );
	}

	public function getExpiry() {
		return $this->expiry;
	}

	public function setExpiry( $value ) {
		$this->trySet( $this->expiry, $value );
	}

	public function getExpirySelection() {
		return $this->expirySelection;
	}

	public function setExpirySelection( $value ) {
		$this->trySet( $this->expirySelection, $value );
	}

	public function getAutoreview() {
		return $this->autoreview;
	}	

	public function setAutoreview( $value ) {
		$this->trySet( $this->autoreview, $value );
	}

	/**
	* Set a member field to a value if the fields are unlocked
	*/
	protected function trySet( &$field, $value ) {
		if ( $this->inputLock ) {
			throw new MWException( __CLASS__ . " fields cannot be set anymore.\n");
		} else {
			$field = $value; // still allowing input
		} 
	}

	/**
	* Signal that inputs are starting
	*/
	public function start() {
		$this->inputLock = 0;
	}

	/**
	* Signal that inputs are done and load old config
	* @return mixed (true on success, error string on target failure)
	*/
	public function ready() {
		$this->inputLock = 1;
		$status = $this->checkTarget();
		if ( $status !== true ) {
			return $status; // bad target
		}
		$this->loadOldConfig(); // current settings from DB
		return $status;
	}

	/*
	* Preload existing page settings (e.g. from GET request).
	* @return mixed (true on success, error string on failure)
	*/
	public function preloadSettings() {
		if ( !$this->inputLock ) {
			throw new MWException( __CLASS__ . " input fields not set yet.\n");
		}
		$status = $this->checkTarget();
		if ( $status !== true ) {
			return $status; // bad target
		}
		return $this->reallyPreloadSettings(); // load the params...
	}

	/*
	* @return mixed (true on success, error string on failure)
	*/	
	protected function reallyPreloadSettings() {
		return true;
	}

	/*
	* Verify and clean up parameters (e.g. from POST request).
	* @return mixed (true on success, error string on failure)
	*/
	protected function checkSettings() {
		$status = $this->checkTarget();
		if ( $status !== true ) {
			return $status; // bad target
		}
		$status = $this->reallyCheckSettings(); // check other params...
		return $status;
	}

	/*
	* @return mixed (true on success, error string on failure)
	*/
	protected function reallyCheckSettings() {
		return true;
	}

	/*
	* Check that the target page is valid
	* @return mixed (true on success, error string on failure)
	*/
	protected function checkTarget() {
		if ( is_null( $this->page ) ) {
			return 'stabilize_page_invalid';
		} elseif ( !$this->page->exists() ) {
			return 'stabilize_page_notexists';
		} elseif ( !FlaggedRevs::inReviewNamespace( $this->page ) ) {
			return 'stabilize_page_unreviewable';
		}
		return true;
	}

	protected function loadOldConfig() {
		# Get the current page config and GMT expiry
		$this->oldConfig = FlaggedRevs::getPageVisibilitySettings( $this->page, FR_MASTER );
		$this->oldExpiry = $this->oldConfig['expiry'] === 'infinity'
			? 'infinite'
			: wfTimestamp( TS_RFC2822, $this->oldConfig['expiry'] );
	}

	/*
	* Gets the current config expiry in GMT (or 'infinite')
	* @return string
	*/
	public function getOldExpiryGMT() {
		if ( !$this->inputLock ) {
			throw new MWException( __CLASS__ . " input fields not set yet.\n");
		}
		return $this->oldExpiry;
	}

	/*
	* Can the user change the settings for this page?
	* Note: if the current autoreview restriction is too high for this user
	*		then this will return false. Useful for form selectors.
	* @return bool
	*/
	public function isAllowed() {
		# Users who cannot edit or review the page cannot set this
		return ( $this->page
			&& $this->page->userCan( 'stablesettings' )
			&& $this->page->userCan( 'edit' )
			&& $this->page->userCan( 'review' )
		);
	}

	/**
	* Submit the form parameters for the page config to the DB.
	* 
	* @return mixed (true on success, error string on failure)
	*/
	public function submit() {
		if ( !$this->inputLock ) {
			throw new MWException( __CLASS__ . " input fields not set yet.\n");
		}
		$status = $this->checkSettings();
		if ( $status !== true ) {
			return $status; // cannot submit - broken params
		}
		# Double-check permissions
		if ( !$this->isAllowed() ) {
			return 'stablize_denied';
		}
		# Are we are going back to site defaults?
		$reset = $this->newConfigIsReset();
		# Parse and cleanup the expiry time given...
		if ( $reset || $this->expiry == 'infinite' || $this->expiry == 'indefinite' ) {
			$this->expiry = Block::infinity(); // normalize to 'infinity'
		} else {
			# Convert GNU-style date, on error returns -1 for PHP <5.1 and false for PHP >=5.1
			$this->expiry = strtotime( $this->expiry );
			if ( $this->expiry < 0 || $this->expiry === false ) {
				return 'stabilize_expiry_invalid';
			}
			# Convert date to MW timestamp format
			$this->expiry = wfTimestamp( TS_MW, $this->expiry );
			if ( $this->expiry < wfTimestampNow() ) {
				return 'stabilize_expiry_old';
			}
		}
		# Update the DB row with the new config...
		$changed = $this->updateConfigRow( $reset );
		# Log if this actually changed anything...
		if ( $changed ) {
			# Update logs and make a null edit
			$nullRev = $this->updateLogsAndHistory( $reset );
			if ( $this->reviewThis ) {
				# Null edit may have been auto-reviewed already
				$frev = FlaggedRevision::newFromTitle(
					$this->page, $nullRev->getId(), FR_MASTER );
				# Check if this null edit is to be reviewed...
				if ( !$frev ) {
					$flags = null;
					$article = new Article( $this->page );
					# Review this revision of the page...
					$ok = FlaggedRevs::autoReviewEdit(
						$article, $this->user, $nullRev, $flags, true );
					if ( $ok ) {
						FlaggedRevs::markRevisionPatrolled( $nullRev ); // reviewed -> patrolled
					}
				}
			}
			# Update page and tracking tables and clear cache
			FlaggedRevs::stableVersionUpdates( $this->page );
		}
		# Apply watchlist checkbox value (may be NULL)
		$this->updateWatchlist();
		# Take this opportunity to purge out expired configurations
		FlaggedRevs::purgeExpiredConfigurations();
		return true;
	}

	/*
	* Do history & log updates:
	* (a) Add a new stability log entry
	* (b) Add a null edit like the log entry
	* @return Revision
	*/
	protected function updateLogsAndHistory( $reset ) {
		global $wgContLang;
		$article = new Article( $this->page );
		$latest = $this->page->getLatestRevID( Title::GAID_FOR_UPDATE );
		# Config may have changed to allow stable versions.
		# Refresh tracking to account for any hidden reviewed versions...
		$frev = FlaggedRevision::newFromStable( $this->page, FR_MASTER );
		if ( $frev ) {
			FlaggedRevs::updateStableVersion( $article, $frev->getRevision(), $latest );
		} else {
			FlaggedRevs::clearTrackingRows( $article->getId() );
		}
		# Insert stability log entry...
		$log = new LogPage( 'stable' );
		if ( $reset ) {
			$log->addEntry( 'reset', $this->page, $this->reason );
			$type = "stable-logentry-reset";
			$settings = ''; // no level, expiry info
		} else {
			$params = $this->getLogParams();
			$action = ( $this->oldConfig === FlaggedRevs::getDefaultVisibilitySettings() )
				? 'config' // set a custom configuration
				: 'modify'; // modified an existing custom configuration
			$log->addEntry( $action, $this->page, $this->reason,
				FlaggedRevsLogs::collapseParams( $params ) );
			$type = "stable-logentry-config";
			// Settings message in text form (e.g. [x=a,y=b,z])
			$settings = FlaggedRevsLogs::stabilitySettings( $params, true /*content*/ );
		}
		# Build null-edit comment...<action: reason [settings] (expiry)>
		$comment = $wgContLang->ucfirst(
			wfMsgForContent( $type, $this->page->getPrefixedText() ) ); // action
		if ( $this->reason != '' ) {
			$comment .= wfMsgForContent( 'colon-separator' ) . $this->reason; // add reason
		}
		if ( $settings != '' ) {
			$comment .= " {$settings}"; // add settings
		}
		# Insert a null revision...
		$dbw = wfGetDB( DB_MASTER );
		$nullRev = Revision::newNullRevision( $dbw, $article->getId(), $comment, true );
		$nullRev->insertOn( $dbw );
		# Update page record and touch page
		$article->updateRevisionOn( $dbw, $nullRev, $latest );
		wfRunHooks( 'NewRevisionFromEditComplete', array( $article, $nullRev, $latest ) );
		# Return null Revision object for autoreview check
		return $nullRev;
	}

	/*
	* Checks if new config is the same as the site default
	* @return bool
	*/
	protected function newConfigIsReset() {
		return false;
	}

	/*
	* Get assoc. array of log params
	* @return array
	*/
	protected function getLogParams() {
		return array();
	}

	/*
	* (a) Watch page if $watchThis is true
	* (b) Unwatch if $watchThis is false
	*/
	protected function updateWatchlist() {
		# Apply watchlist checkbox value (may be NULL)
		if ( $this->watchThis === true ) {
			$this->user->addWatch( $this->page );
		} elseif ( $this->watchThis === false ) {
			$this->user->removeWatch( $this->page );
		}
	}

	protected function loadExpiry() {
		# Custom expiry replaces dropdown
		if ( $this->expiry == '' ) {
			$this->expiry = $this->expirySelection;
			if ( $this->expiry == 'existing' ) {
				$this->expiry = $this->oldExpiry;
			}
		}
	}

	protected function loadReason() {
		# Custom reason replaces dropdown
		if ( $this->reasonSelection != 'other' ) {
			$comment = $this->reasonSelection; // start with dropdown reason
			if ( $this->reason != '' ) {
				# Append custom reason
				$comment .= wfMsgForContent( 'colon-separator' ) . $this->reason;
			}
		} else {
			$comment = $this->reason; // just use custom reason
		}
		$this->reason = $comment;
	}

	// Same JS used for expiry for either $wgFlaggedRevsProtection case
	public static function addProtectionJS() {
		global $wgOut;
		$wgOut->addScript(
			"<script type=\"text/javascript\">
				function onFRChangeExpiryDropdown() {
					document.getElementById('mwStabilizeExpiryOther').value = '';
				}
				function onFRChangeExpiryField() {
					document.getElementById('mwStabilizeExpirySelection').value = 'othertime';
				}
			</script>"
		);
	}
}

// Assumes $wgFlaggedRevsProtection is off
class PageStabilityGeneralForm extends PageStabilityForm {
	public function getReviewThis() {
		return $this->reviewThis;
	}

	public function setReviewThis( $value ) {
		$this->trySet( $this->reviewThis, $value );
	}

	public function getOverride() {
		return $this->override;
	}

	public function setOverride( $value ) {
		$this->trySet( $this->override, $value );
	}

	protected function reallyPreloadSettings() {
		$this->override = $this->oldConfig['override'];
		$this->autoreview = $this->oldConfig['autoreview'];
		$this->expiry = $this->oldExpiry;
		$this->expirySelection = 'existing';
		$this->watchThis = $this->page->userIsWatching();
		return true;
	}

	protected function reallyCheckSettings() {
		$this->loadReason();
		$this->loadExpiry();
		$this->override = $this->override ? 1 : 0; // default version settings is 0 or 1
		// Check autoreview restriction setting
		if ( $this->autoreview != '' // restriction other than 'none'
			&& !in_array( $this->autoreview, FlaggedRevs::getRestrictionLevels() ) )
		{
			return 'stabilize_invalid_autoreview'; // invalid value
		}
		if ( !FlaggedRevs::userCanSetAutoreviewLevel( $this->user, $this->autoreview ) ) {
			return 'stabilize_denied'; // invalid value
		}
		return true;
	}

	protected function getLogParams() {
		return array(
			'override'   => $this->override,
			'autoreview' => $this->autoreview,
			'expiry'     => $this->expiry, // TS_MW/infinity
			'precedence' => 1 // here for log hook b/c
		);
	}

	// Return current config array
	public function getOldConfig() {
		if ( !$this->inputLock ) {
			throw new MWException( __CLASS__ . " input fields not set yet.\n");
		}
		return $this->oldConfig;
	}

	// returns whether row changed
	protected function updateConfigRow( $reset ) {
		$changed = false;
		$dbw = wfGetDB( DB_MASTER );
		# If setting to site default values and there is a row then erase it
		if ( $reset ) {
			$dbw->delete( 'flaggedpage_config',
				array( 'fpc_page_id' => $this->page->getArticleID() ),
				__METHOD__
			);
			$changed = ( $dbw->affectedRows() != 0 ); // did this do anything?
		# Otherwise, add/replace row if we are not just setting it to the site default
		} elseif ( !$reset ) {
			$dbExpiry = Block::encodeExpiry( $this->expiry, $dbw );
			# Get current config...
			$oldRow = $dbw->selectRow( 'flaggedpage_config',
				array( 'fpc_select', 'fpc_override', 'fpc_level', 'fpc_expiry' ),
				array( 'fpc_page_id' => $this->page->getArticleID() ),
				__METHOD__,
				'FOR UPDATE'
			);
			# Check if this is not the same config as the existing row (if any)
			$changed = $this->configIsDifferent( $oldRow,
				$this->select, $this->override, $this->autoreview, $dbExpiry );
			# If the new config is different, replace the old row...
			if ( $changed ) {
				$dbw->replace( 'flaggedpage_config',
					array( 'PRIMARY' ),
					array(
						'fpc_page_id'  => $this->page->getArticleID(),
						'fpc_select'   => 1, // unused
						'fpc_override' => (int)$this->override,
						'fpc_level'    => $this->autoreview,
						'fpc_expiry'   => $dbExpiry
					),
					__METHOD__
				);
			}
		}
		return $changed;
	}

	protected function newConfigIsReset() {
		return ( $this->override == FlaggedRevs::isStableShownByDefault()
			&& $this->autoreview == '' );
	}

	// Checks if new config is different than the existing row
	protected function configIsDifferent( $oldRow, $override, $autoreview, $dbExpiry ) {
		if( !$oldRow ) {
			return true; // no previous config
		}
		return ( $oldRow->fpc_override != $override // ...override changed, or...
			|| $oldRow->fpc_level != $autoreview // ...autoreview level changed, or...
			|| $oldRow->fpc_expiry != $dbExpiry // ...expiry changed
		);
	}
}

// Assumes $wgFlaggedRevsProtection is on
class PageStabilityProtectForm extends PageStabilityForm {
	protected function reallyPreloadSettings() {
		$this->autoreview = $this->oldConfig['autoreview']; // protect level
		$this->expiry = $this->oldExpiry;
		$this->expirySelection = 'existing';
		$this->watchThis = $this->page->userIsWatching();
		return true;
	}

	protected function reallyCheckSettings() {
		# WMF temp hack...protection limit quota
		global $wgFlaggedRevsProtectQuota;
		if ( isset( $wgFlaggedRevsProtectQuota ) // quota exists
			&& $this->autoreview != '' // and we are protecting
			&& FlaggedRevs::getProtectionLevel( $this->oldConfig ) == 'none' ) // and page is unprotected
		{
			$dbw = wfGetDB( DB_MASTER );
			$count = $dbw->selectField( 'flaggedpage_config', 'COUNT(*)', '', __METHOD__ );
			if ( $count >= $wgFlaggedRevsProtectQuota ) {
				return 'stabilize_protect_quota';
			}
		}
		$this->loadReason();
		$this->loadExpiry();
		# Autoreview only when protecting currently unprotected pages
		$this->reviewThis = ( FlaggedRevs::getProtectionLevel( $this->oldConfig ) == 'none' );
		# Autoreview restriction => use stable
		# No autoreview restriction => site default
		$this->override = ( $this->autoreview != '' )
			? 1 // edits require review before being published
			: (int)FlaggedRevs::isStableShownByDefault(); // site default
		# Check that settings are a valid protection level...
		$newConfig = array(
			'override'   => $this->override,
			'autoreview' => $this->autoreview
		);
		if ( FlaggedRevs::getProtectionLevel( $newConfig ) == 'invalid' ) {
			return 'stabilize_invalid_level'; // double-check configuration
		}
		# Check autoreview restriction setting
		if ( !FlaggedRevs::userCanSetAutoreviewLevel( $this->user, $this->autoreview ) ) {
			return 'stabilize_denied'; // invalid value
		}
		return true;
	}

	// Doesn't and shouldn't include 'precedence'; checked in FlaggedRevsLogs
	protected function getLogParams() {
		return array(
			'override'   => $this->override, // in case of site changes
			'autoreview' => $this->autoreview,
			'expiry'     => $this->expiry // TS_MW/infinity
		);
	}

	protected function updateConfigRow( $reset ) {
		$changed = false;
		$dbw = wfGetDB( DB_MASTER );
		# If setting to site default values and there is a row then erase it
		if ( $reset ) {
			$dbw->delete( 'flaggedpage_config',
				array( 'fpc_page_id' => $this->page->getArticleID() ),
				__METHOD__
			);
			$changed = ( $dbw->affectedRows() != 0 ); // did this do anything?
		# Otherwise, add/replace row if we are not just setting it to the site default
		} elseif ( !$reset ) {
			$dbExpiry = Block::encodeExpiry( $this->expiry, $dbw );
			# Get current config...
			$oldRow = $dbw->selectRow( 'flaggedpage_config',
				array( 'fpc_override', 'fpc_level', 'fpc_expiry' ),
				array( 'fpc_page_id' => $this->page->getArticleID() ),
				__METHOD__,
				'FOR UPDATE'
			);
			# Check if this is not the same config as the existing row (if any)
			$changed = $this->configIsDifferent( $oldRow,
				$this->override, $this->autoreview, $dbExpiry );
			# If the new config is different, replace the old row...
			if ( $changed ) {
				$dbw->replace( 'flaggedpage_config',
					array( 'PRIMARY' ),
					array(
						'fpc_page_id'  => $this->page->getArticleID(),
						'fpc_select'   => -1, // ignored
						'fpc_override' => (int)$this->override,
						'fpc_level'    => $this->autoreview,
						'fpc_expiry'   => $dbExpiry
					),
					__METHOD__
				);
			}
		}
		return $changed;
	}

	protected function newConfigIsReset() {
		# For protection config, just ignore the fpc_select column
		return ( $this->autoreview == '' );
	}

	// Checks if new config is different than the existing row
	protected function configIsDifferent( $oldRow, $override, $autoreview, $dbExpiry ) {
		if ( !$oldRow ) {
			return true; // no previous config
		}
		# For protection config, just ignore the fpc_select column
		return ( $oldRow->fpc_override != $override // ...override changed, or...
			|| $oldRow->fpc_level != $autoreview // ...autoreview level changed, or...
			|| $oldRow->fpc_expiry != $dbExpiry // ...expiry changed
		);
	}
}