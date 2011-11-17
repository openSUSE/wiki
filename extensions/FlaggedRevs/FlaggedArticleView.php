<?php
/**
 * Class representing a web view of a MediaWiki page
 */
class FlaggedArticleView {
	protected $article = null;

	protected $diffRevs = null;
	protected $isReviewableDiff = false;
	protected $isDiffFromStable = false;
	protected $isMultiPageDiff = false;
	protected $reviewNotice = '';
	protected $reviewNotes = '';
	protected $diffNoticeBox = '';
	protected $reviewFormRev = false;

	protected $loaded = false;

	protected static $instance = null;

	/*
	* Get the FlaggedArticleView for this request
	*/
	public static function singleton() {
		if ( self::$instance == null ) {
			self::$instance = new self();
		}
		return self::$instance;
	}
	protected function __construct() { }
	protected function __clone() { }

	/*
	* Load the global FlaggedArticle instance
	*/
	protected function load() {
		if ( !$this->loaded ) {
			$this->loaded = true;
			$this->article = self::globalArticleInstance();
			if ( $this->article == null ) {
				throw new MWException( 'FlaggedArticleView has no context article!' );
			}
		}
	}

	/**
	 * Get the FlaggedArticle instance associated with $wgArticle/$wgTitle,
	 * or false if there isn't such a title
	 */
	public static function globalArticleInstance() {
		global $wgTitle;
		if ( !empty( $wgTitle ) ) {
			return FlaggedArticle::getTitleInstance( $wgTitle );
		}
		return null;
	}

	/**
	 * Do the config and current URL params allow
	 * for content overriding by the stable version?
	 * @returns bool
	 */
	public function pageOverride() {
		global $wgUser, $wgRequest;
		$this->load();
		# This only applies to viewing content pages
		$action = $wgRequest->getVal( 'action', 'view' );
		if ( !self::isViewAction( $action ) || !$this->article->isReviewable() ) {
			return false;
		}
		# Does not apply to diffs/old revision...
		if ( $wgRequest->getVal( 'oldid' ) || $wgRequest->getVal( 'diff' ) ) {
			return false;
		}
		# Explicit requests  for a certain stable version handled elsewhere...
		if ( $wgRequest->getVal( 'stableid' ) ) {
			return false;
		}
		# Check user preferences
		if ( $wgUser->getOption( 'flaggedrevsstable' ) ) {
			return !( $wgRequest->getIntOrNull( 'stable' ) === 0 );
		}
		# Get page configuration
		$config = $this->article->getVisibilitySettings();
		# Does the stable version override the current one?
		if ( $config['override'] ) {
			if ( $this->showDraftByDefault() ) {
				return ( $wgRequest->getIntOrNull( 'stable' ) === 1 );
			}
			# Viewer sees stable by default
			return !( $wgRequest->getIntOrNull( 'stable' ) === 0 );
		# We are explicity requesting the stable version?
		} elseif ( $wgRequest->getIntOrNull( 'stable' ) === 1 ) {
			return true;
		}
		return false;
	}

	/**
	 * Should this be using a simple icon-based UI?
	 * Check the user's preferences first, using the site settings as the default.
	 * @returns bool
	 */
	public function useSimpleUI() {
		global $wgUser, $wgSimpleFlaggedRevsUI;
		return $wgUser->getOption( 'flaggedrevssimpleui', intval( $wgSimpleFlaggedRevsUI ) );
	}

	/**
	 * Should this user see the current revision by default?
	 * Note: intended for users that probably edit
	 * @returns bool
	 */
	public function showDraftByDefault() {
		global $wgFlaggedRevsExceptions, $wgUser;
		# Viewer sees current by default (editors, insiders, ect...) ?
		foreach ( $wgFlaggedRevsExceptions as $group ) {
			if ( $group == 'user' ) {
				if ( $wgUser->getId() ) {
					return true;
				}
			} elseif ( in_array( $group, $wgUser->getGroups() ) ) {
				return true;
			}
		}
		return false;
	}

	 /**
	 * Is this user shown the stable version by default for this page?
	 * @returns bool
	 */
	public function isStableShownByDefaultUser() {
		$this->load();
		if ( $this->article->isReviewable() ) {
			$config = $this->article->getVisibilitySettings(); // page configuration
			return ( $config['override'] && !$this->showDraftByDefault() );
		}
		return false; // no stable
	}
	
	 /**
	 * Is this user shown the diff-to-stable on edit for this page?
	 * @returns bool
	 */
	public function isDiffShownOnEdit() {
		global $wgUser;
		$this->load();
		return ( $wgUser->isAllowed( 'review' ) || $this->isStableShownByDefaultUser() );
	}

	 /**
	 * Is this a view page action?
	 * @param $action string
	 * @returns bool
	 */
	protected static function isViewAction( $action ) {
		return ( $action == 'view' || $action == 'purge' || $action == 'render'
			|| $action == 'historysubmit' );
	}

	 /**
	 * Output review notice
	 */
	public function displayTag() {
		global $wgOut;
		$this->load();	
		// Sanity check that this is a reviewable page
		if ( $this->article->isReviewable() ) {		
			$wgOut->appendSubtitle( $this->reviewNotice );
		}
		return true;
	}

	 /**
	 * Add a stable link when viewing old versions of an article that
	 * have been reviewed. (e.g. for &oldid=x urls)
	 */
	public function addStableLink() {
		global $wgRequest, $wgOut, $wgLang;
		$this->load();
		if ( !$this->article->isReviewable() || !$wgRequest->getVal( 'oldid' ) ) {
			return true;
		}
		# We may have nav links like "direction=prev&oldid=x"
		$revID = $this->article->getOldIDFromRequest();
		$frev = FlaggedRevision::newFromTitle( $this->article->getTitle(), $revID );
		# Give a notice if this rev ID corresponds to a reviewed version...
		if ( $frev ) {
			$time = $wgLang->date( $frev->getTimestamp(), true );
			$flags = $frev->getTags();
			$quality = FlaggedRevs::isQuality( $flags );
			$msg = $quality ? 'revreview-quality-source' : 'revreview-basic-source';
			$tag = wfMsgExt( $msg, array( 'parseinline' ), $frev->getRevId(), $time );
			# Hide clutter
			if ( !$this->useSimpleUI() && !empty( $flags ) ) {
				$tag .= FlaggedRevsXML::ratingToggle() .
					"<div id='mw-fr-revisiondetails' style='display:block;'>" .
					wfMsgHtml( 'revreview-oldrating' ) .
					FlaggedRevsXML::addTagRatings( $flags ) . '</div>';
			}
			$css = 'flaggedrevs_notice plainlinks noprint';
			$tag = "<div id='mw-fr-revisiontag-old' class='$css'>$tag</div>";
			$wgOut->addHTML( $tag );
		}
		return true;
	}
	
	/**
	* @returns mixed int/false/null
	*/
	protected function getRequestedStableId() {
		global $wgRequest;
		$reqId = $wgRequest->getVal( 'stableid' );
		if ( $reqId === "best" ) {
			$reqId = FlaggedRevs::getPrimeFlaggedRevId( $this->article );
		}
		return $reqId;
	}

	 /**
	 * Replaces a page with the last stable version if possible
	 * Adds stable version status/info tags and notes
	 * Adds a quick review form on the bottom if needed
	 */
	public function setPageContent( &$outputDone, &$useParserCache ) {
		global $wgRequest, $wgOut, $wgContLang;
		$this->load();
		# Only trigger on article view for content pages, not for protect/delete/hist...
		$action = $wgRequest->getVal( 'action', 'view' );
		if ( !self::isViewAction( $action ) || !$this->article->exists() )
			return true;
		# Do not clutter up diffs any further and leave archived versions alone...
		if ( $wgRequest->getVal( 'diff' ) || $wgRequest->getVal( 'oldid' ) ) {
			return true;
		}
		# Only trigger for reviewable pages
		if ( !$this->article->isReviewable() ) {
			return true;
		}
		$simpleTag = $old = $stable = false;
		$tag = '';
		# Check the newest stable version.
		$srev = $this->article->getStableRev();
		$stableId = $srev ? $srev->getRevId() : 0;
		$frev = $srev; // $frev is the revision we are looking at
		# Check for any explicitly requested old stable version...
		$reqId = $this->getRequestedStableId();
		if ( $reqId ) {
			if ( !$stableId ) {
				$reqId = false; // must be invalid
			# Treat requesting the stable version by ID as &stable=1
			} else if ( $reqId != $stableId ) {
				$old = true; // old reviewed version requested by ID
				$frev = FlaggedRevision::newFromTitle( $this->article->getTitle(),
					$reqId, FR_TEXT );
				if ( !$frev ) {
					$reqId = false; // invalid ID given
				}
			} else {
				$stable = true; // stable version requested by ID
			}
		}
		// $reqId is null if nothing requested, false if invalid
		if ( $reqId === false ) {
			$wgOut->addWikiText( wfMsg( 'revreview-invalid' ) );
			$wgOut->returnToMain( false, $this->article->getTitle() );
			# Tell MW that parser output is done
			$outputDone = true;
			$useParserCache = false;
			return true;
		}
		// Is the page config altered?
		$prot = FlaggedRevsXML::lockStatusIcon( $this->article );
		// Is there no stable version?
		if ( !$frev ) {
			# Add "no reviewed version" tag, but not for printable output
			$this->showUnreviewedPage( $tag, $prot );
			return true;
		}
		# Get flags and date
		$flags = $frev->getTags();
		# Get quality level
		$quality = FlaggedRevs::isQuality( $flags );
		$pristine = FlaggedRevs::isPristine( $flags );
		// Looking at some specific old stable revision ("&stableid=x")
		// set to override given the relevant conditions. If the user is
		// requesting the stable revision ("&stableid=x"), defer to override
		// behavior below, since it is the same as ("&stable=1").
		if ( $old ) {
			$this->showOldReviewedVersion( $srev, $frev, $tag, $prot );
			$outputDone = true; # Tell MW that parser output is done
			$useParserCache = false;
		// Stable version requested by ID or relevant conditions met to
		// to override page view.
		} else if ( $stable || $this->pageOverride() ) {
			$this->showStableVersion( $srev, $tag, $prot );
			$outputDone = true; # Tell MW that parser output is done
			$useParserCache = false;
		// Looking at some specific old revision (&oldid=x) or if FlaggedRevs is not
		// set to override given the relevant conditions (like &stable=0) or there
		// is no stable version.
		} else {
	   		$this->showDraftVersion( $srev, $tag, $prot );
		}
		$encJS = ''; // JS events to use
		# Some checks for which tag CSS to use
		if ( $this->useSimpleUI() ) {
			$tagClass = 'flaggedrevs_short';
			# Collapse the box details on mouseOut
			$encJS .= ' onMouseOut="FlaggedRevs.onBoxMouseOut(event)"';
		} elseif ( $simpleTag ) {
			$tagClass = 'flaggedrevs_notice';
		} elseif ( $pristine ) {
			$tagClass = 'flaggedrevs_pristine';
		} elseif ( $quality ) {
			$tagClass = 'flaggedrevs_quality';
		} else {
			$tagClass = 'flaggedrevs_basic';
		}
		# Wrap tag contents in a div
		if ( $tag != '' ) {
			$rtl = $wgContLang->isRTL() ? " rtl" : ""; // RTL langauges
			$css = "{$tagClass}{$rtl} plainlinks noprint";
			$notice = "<div id=\"mw-fr-revisiontag\" class=\"{$css}\"{$encJS}>{$tag}</div>\n";
			$this->reviewNotice .= $notice;
		}
		return true;
	}

	// For pages that have a stable version, index only that version
	public function setRobotPolicy() {
		global $wgOut;
		if ( !$this->article->isReviewable() || !$this->article->getStableRev() ) {
			return true; // page has no stable version
		}
		if ( !$this->pageOverride() && $this->article->isStableShownByDefault() ) {
			# Index the stable version only if it is the default
			$wgOut->setRobotPolicy( 'noindex,nofollow' );
		}
		return true;
	}

	/**
	* @param $tag review box/bar info
	* @param $prot protection notice
	* Tag output function must be called by caller
	*/
	protected function showUnreviewedPage( $tag, $prot ) {
		global $wgOut, $wgContLang;
		if ( $wgOut->isPrintable() ) {
			return;
		}
		$icon = FlaggedRevsXML::draftStatusIcon();
		// Simple icon-based UI
		if ( $this->useSimpleUI() ) {
			// RTL langauges
			$rtl = $wgContLang->isRTL() ? " rtl" : "";
			$tag .= $prot . $icon . wfMsgExt( 'revreview-quick-none', array( 'parseinline' ) );
			$css = "flaggedrevs_short{$rtl} plainlinks noprint";
			$this->reviewNotice .= "<div id='mw-fr-revisiontag' class='$css'>$tag</div>";
		// Standard UI
		} else {
			$css = 'flaggedrevs_notice plainlinks noprint';
			$tag = "<div id='mw-fr-revisiontag' class='$css'>" .
				$prot . $icon . wfMsgExt( 'revreview-noflagged', array( 'parseinline' ) ) .
				"</div>";
			$this->reviewNotice .= $tag;
		}
	}
	
	/**
	* @param $srev stable version
	* @param $tag review box/bar info
	* @param $prot protection notice icon
	* Tag output function must be called by caller
	* Parser cache control deferred to caller
	*/
	protected function showDraftVersion( FlaggedRevision $srev, &$tag, $prot ) {
		global $wgUser, $wgOut, $wgLang, $wgRequest;
		$this->load();
		$flags = $srev->getTags();
		$time = $wgLang->date( $srev->getTimestamp(), true );
		# Get quality level
		$quality = FlaggedRevs::isQuality( $flags );
		# Get stable version sync status
		$synced = $this->article->stableVersionIsSynced();
		if ( $synced ) {
			$this->setReviewNotes( $srev ); // Still the same
		} else {
			# Make sure there is always a notice bar when viewing the draft
			if ( $this->useSimpleUI() ) { // already one for detailed UI
				$this->setPendingNotice( $srev );
			}
			$this->maybeShowTopDiff( $srev, $quality ); // user may want diff (via prefs)
		}
		# If they are synced, do special styling
		# Give notice to newer users if an unreviewed edit was completed...
		if ( $wgRequest->getVal( 'shownotice' )
			&& $this->article->getUserText() == $wgUser->getName() // FIXME: rawUserText?
			&& $this->article->revsArePending()
			&& !$wgUser->isAllowed( 'review' ) )
		{
			$revsSince = $this->article->getPendingRevCount();
			$pending = $prot;
			if ( $this->showRatingIcon() ) {
				$pending .= FlaggedRevsXML::draftStatusIcon();
			}
			$pending .= wfMsgExt( 'revreview-edited',
				array( 'parseinline' ), $srev->getRevId(), $revsSince );
			$anchor = $wgRequest->getVal( 'fromsection' );
			if ( $anchor != null ) {
				$section = str_replace( '_', ' ', $anchor ); // prettify
				$pending .= wfMsgExt( 'revreview-edited-section', 'parse', $anchor, $section );
			}
			# Notice should always use subtitle
			$this->reviewNotice = "<div id='mw-fr-reviewnotice' " .
				"class='flaggedrevs_preview plainlinks'>$pending</div>";
		# Construct some tagging for non-printable outputs. Note that the pending
		# notice has all this info already, so don't do this if we added that already.
		# Also, if low profile UI is enabled and the page is synced, skip the tag.
		} else if ( !$wgOut->isPrintable() && !( $this->article->lowProfileUI() && $synced ) ) {
			$revsSince = $this->article->getPendingRevCount();
			// Simple icon-based UI
			if ( $this->useSimpleUI() ) {
				if ( !$wgUser->getId() ) {
					$msgHTML = ''; // Anons just see simple icons
				} else if ( $synced ) {
					$msg = $quality
						? 'revreview-quick-quality-same'
						: 'revreview-quick-basic-same';
					$msgHTML = wfMsgExt( $msg, array( 'parseinline' ),
						$srev->getRevId(), $revsSince );
				} else {
					$msg = $quality
						? 'revreview-quick-see-quality'
						: 'revreview-quick-see-basic';
					$msgHTML = wfMsgExt( $msg, array( 'parseinline' ),
						$srev->getRevId(), $revsSince );
				}
				$icon = '';
				# For protection based configs, show lock only if it's not redundant.
				if ( $this->showRatingIcon() ) {
					$icon = $synced
						? FlaggedRevsXML::stableStatusIcon( $quality )
						: FlaggedRevsXML::draftStatusIcon();
				}
				$msgHTML = $prot . $icon . $msgHTML;
				$tag .= FlaggedRevsXML::prettyRatingBox( $srev, $msgHTML,
					$revsSince, 'draft', $synced, false );
			// Standard UI
			} else {
				if ( $synced ) {
					if ( $quality ) {
						$msg = 'revreview-quality-same';
					} else {
						$msg = 'revreview-basic-same';
					}
					$msgHTML = wfMsgExt( $msg, array( 'parseinline' ),
						$srev->getRevId(), $time, $revsSince );
				} else {
					$msg = $quality
						? 'revreview-newest-quality'
						: 'revreview-newest-basic';
					$msg .= ( $revsSince == 0 ) ? '-i' : '';
					$msgHTML = wfMsgExt( $msg, array( 'parseinline' ),
						$srev->getRevId(), $time, $revsSince );
				}
				$icon = $synced
					? FlaggedRevsXML::stableStatusIcon( $quality )
					: FlaggedRevsXML::draftStatusIcon();
				$tag .= $prot . $icon . $msgHTML;
			}
		}
	}
	
	/**
	* @param $srev stable version
	* @param $frev selected flagged revision
	* @param $tag review box/bar info
	* @param $prot protection notice icon
	* Tag output function must be called by caller
	* Parser cache control deferred to caller
	*/
	protected function showOldReviewedVersion(
		FlaggedRevision $srev, FlaggedRevision $frev, &$tag, $prot
	) {
		global $wgUser, $wgOut, $wgLang;
		$this->load();
		$flags = $frev->getTags();
		$time = $wgLang->date( $frev->getTimestamp(), true );
		# Set display revision ID
		$wgOut->setRevisionId( $frev->getRevId() );
		# Get quality level
		$quality = FlaggedRevs::isQuality( $flags );

		# Construct some tagging for non-printable outputs. Note that the pending
		# notice has all this info already, so don't do this if we added that already.
		if ( !$wgOut->isPrintable() ) {
			// Simple icon-based UI
			if ( $this->useSimpleUI() ) {
				$icon = '';
				# For protection based configs, show lock only if it's not redundant.
				if ( $this->showRatingIcon() ) {
					$icon = FlaggedRevsXML::stableStatusIcon( $quality );
				}
				$revsSince = $this->article->getPendingRevCount();
				if ( !$wgUser->getId() ) {
					$msgHTML = ''; // Anons just see simple icons
				} else {
					$msg = $quality
						? 'revreview-quick-quality-old'
						: 'revreview-quick-basic-old';
					$msgHTML = wfMsgExt( $msg, array( 'parseinline' ), $frev->getRevId(), $revsSince );
				}
				$msgHTML = $prot . $icon . $msgHTML;
				$tag = FlaggedRevsXML::prettyRatingBox( $frev, $msgHTML,
					$revsSince, 'oldstable', false /*synced*/ );
			// Standard UI
			} else {
				$icon = FlaggedRevsXML::stableStatusIcon( $quality );
				$msg = $quality
					? 'revreview-quality-old'
					: 'revreview-basic-old';
				$tag = $prot . $icon;
				$tag .= wfMsgExt( $msg, 'parseinline', $frev->getRevId(), $time );
				# Hide clutter
				if ( !empty( $flags ) ) {
					$tag .= FlaggedRevsXML::ratingToggle();
					$tag .= "<div id='mw-fr-revisiondetails' style='display:block;'>" .
						wfMsgHtml( 'revreview-oldrating' ) .
						FlaggedRevsXML::addTagRatings( $flags ) . '</div>';
				}
			}
		}
		# Load the review notes which will be shown by onSkinAfterContent
		$this->setReviewNotes( $frev );

		# Check if this is a redirect...
		$text = $frev->getRevText();
		$redirHtml = $this->getRedirectHtml( $text );

		# Parse and output HTML
		if ( $redirHtml == '' ) {
			$parserOptions = FlaggedRevs::makeParserOptions();
			$parserOut = FlaggedRevs::parseStableText(
				$this->article->getTitle(), $text, $frev->getRevId(), $parserOptions );
			$this->addParserOutput( $parserOut );
		} else {
			$wgOut->addHtml( $redirHtml );
		}
	}

	/**
	* @param $srev stable version
	* @param $tag review box/bar info
	* @param $prot protection notice
	* Tag output function must be called by caller
	* Parser cache control deferred to caller
	*/
	protected function showStableVersion( FlaggedRevision $srev, &$tag, $prot ) {
		global $wgOut, $wgLang, $wgUser;
		$this->load();
		$flags = $srev->getTags();
		$time = $wgLang->date( $srev->getTimestamp(), true );
		# Set display revision ID
		$wgOut->setRevisionId( $srev->getRevId() );
		# Get quality level
		$quality = FlaggedRevs::isQuality( $flags );

		$synced = $this->article->stableVersionIsSynced();
		# Construct some tagging
		if ( !$wgOut->isPrintable() && !( $this->article->lowProfileUI() && $synced ) ) {
			$revsSince = $this->article->getPendingRevCount();
			// Simple icon-based UI
			if ( $this->useSimpleUI() ) {
				$icon = '';
				# For protection based configs, show lock only if it's not redundant.
				if ( $this->showRatingIcon() ) {
					$icon = FlaggedRevsXML::stableStatusIcon( $quality );
				}
				if ( !$wgUser->getId() ) {
					$msgHTML = ''; // Anons just see simple icons
				} else {
					$msg = $quality
						? 'revreview-quick-quality'
						: 'revreview-quick-basic';
					# Uses messages 'revreview-quick-quality-same', 'revreview-quick-basic-same'
					$msg = $synced ? "{$msg}-same" : $msg;
					$msgHTML = wfMsgExt( $msg, array( 'parseinline' ),
						$srev->getRevId(), $revsSince );
				}
				$msgHTML = $prot . $icon . $msgHTML;
				$tag = FlaggedRevsXML::prettyRatingBox( $srev, $msgHTML,
					$revsSince, 'stable', $synced );
			// Standard UI
			} else {
				$icon = FlaggedRevsXML::stableStatusIcon( $quality );
				$msg = $quality ? 'revreview-quality' : 'revreview-basic';
				if ( $synced ) {
					# uses messages 'revreview-quality-same', 'revreview-basic-same'
					$msg .= '-same';
				} elseif ( $revsSince == 0 ) {
					# uses messages 'revreview-quality-i', 'revreview-basic-i'
					$msg .= '-i';
				}
				$tag = $prot . $icon;
				$tag .= wfMsgExt( $msg, 'parseinline', $srev->getRevId(), $time, $revsSince );
				if ( !empty( $flags ) ) {
					$tag .= FlaggedRevsXML::ratingToggle();
					$tag .= "<div id='mw-fr-revisiondetails' style='display:block;'>" .
						FlaggedRevsXML::addTagRatings( $flags ) . '</div>';
				}
			}
		}

		# Load the review notes which will be shown by onSkinAfterContent
		$this->setReviewNotes( $srev );

		# Get parsed stable version and output HTML
		$parserOut = FlaggedRevs::getPageCache( $this->article, $wgUser );
		if ( $parserOut ) {
			$this->addParserOutput( $parserOut );
		} else {
			$text = $srev->getRevText();
			# Check if this is a redirect...
			$redirHtml = $this->getRedirectHtml( $text );
			# Don't parse redirects, use separate handling...
			if ( $redirHtml == '' ) {
				# Get the new stable output
				$parserOptions = FlaggedRevs::makeParserOptions();
				$parserOut = FlaggedRevs::parseStableText(
					$this->article->getTitle(), $text, $srev->getRevId(), $parserOptions );
				# Update the stable version cache & dependancies
				FlaggedRevs::updatePageCache( $this->article, $parserOptions, $parserOut );
				FlaggedRevs::updateCacheTracking( $this->article, $parserOut );

				$this->addParserOutput( $parserOut );
			} else {
				$wgOut->addHtml( $redirHtml );
			}
		}
	}

	// Add parser output and update title
	// @TODO: refactor MW core to move this back
	protected function addParserOutput( ParserOutput $parserOut ) {
		global $wgOut;
		$wgOut->addParserOutput( $parserOut );
		# Adjust the title if it was set by displaytitle, -{T|}- or language conversion
		$titleText = $parserOut->getTitleText();
		if ( strval( $titleText ) !== '' ) {
			$wgOut->setPageTitle( $titleText );
		}
	}

	// Get fancy redirect arrow and link HTML
	protected function getRedirectHtml( $text ) {
		$rTargets = Title::newFromRedirectArray( $text );
		if ( $rTargets ) {
			return $this->article->viewRedirect( $rTargets );
		}
		return '';
	}

	// Show icons for draft/stable/old reviewed versions
	protected function showRatingIcon() {
		if ( FlaggedRevs::useOnlyIfProtected() ) {
			// If there is only one quality level and we have tabs to know
			// which version we are looking at, then just use the lock icon...
			return FlaggedRevs::qualityVersions();
		}
		return true;
	}

	/**
	* Add diff-to-stable to top of page views as needed
	* @param FlaggedRevision $srev, stable version
	* @param bool $quality, revision is quality
	* @returns bool, diff added to output
	*/
	protected function maybeShowTopDiff( FlaggedRevision $srev, $quality ) {
		global $wgUser;
		$this->load();
		if ( !$wgUser->getBoolOption( 'flaggedrevsviewdiffs' ) ) {
			return false; // nothing to do here
		}
		# Diff should only show for the draft
		$oldid = $this->article->getOldIDFromRequest();
		$latest = $this->article->getLatest();
		if ( $oldid && $oldid != $latest ) {
			return false; // not viewing the draft
		}
		$revsSince = $this->article->getPendingRevCount();
		if ( !$revsSince ) {
			return false; // no pending changes
		}
		$title = $this->article->getTitle(); // convenience
		# Review status of left diff revision...
		$leftNote = $quality
			? 'revreview-hist-quality'
			: 'revreview-hist-basic';
		$lClass = FlaggedRevsXML::getQualityColor( (int)$quality );
		$leftNote = "<span class='$lClass'>[" . wfMsgHtml( $leftNote ) . "]</span>";
		# Review status of right diff revision...
		$rClass = FlaggedRevsXML::getQualityColor( false );
		$rightNote = "<span class='$rClass'>[" .
			wfMsgHtml( 'revreview-hist-pending' ) . "]</span>";
		# Get the actual body of the diff...
		$diffEngine = new DifferenceEngine( $title, $srev->getRevId(), $latest );
		$diffBody = $diffEngine->getDiffBody();
		if ( strlen( $diffBody ) > 0 ) {
			$nEdits = $revsSince - 1; // full diff-to-stable, no need for query
			if ( $nEdits ) {
				$nUsers = $title->countAuthorsBetween( $srev->getRevId(), $latest, 101 );
				$multiNotice = DifferenceEngine::intermediateEditsMsg( $nEdits, $nUsers, 100 );
			} else {
				$multiNotice = '';
			}
			$items = array();
			$diffHtml =
				FlaggedRevsXML::pendingEditNotice( $this->article, $srev, $revsSince ) .
				' ' . FlaggedRevsXML::diffToggle() .
				"<div id='mw-fr-stablediff'>" .
				self::getFormattedDiff( $diffBody, $multiNotice, $leftNote, $rightNote ) .
				"</div>\n";
			$items[] = $diffHtml;
			$html = "<table class='flaggedrevs_viewnotice plainlinks'>";
			foreach ( $items as $item ) {
				$html .= '<tr><td>' . $item . '</td></tr>';
			}
			$html .= '</table>';
			$this->reviewNotice .= $html;
			$diffEngine->showDiffStyle(); // add CSS
			$this->isDiffFromStable = true; // alter default review form tags
			return true;
		}
		return false;
	}

	// $n number of in-between revs
	protected static function getFormattedDiff(
		$diffBody, $multiNotice, $leftStatus, $rightStatus
	) {
		if ( $multiNotice != '' ) {
			$multiNotice = "<tr><td colspan='4' align='center' class='diff-multi'>" .
				$multiNotice . "</td></tr>";
		}
		return
			"<table border='0' width='98%' cellpadding='0' cellspacing='4' class='diff'>" .
				"<col class='diff-marker' />" .
				"<col class='diff-content' />" .
				"<col class='diff-marker' />" .
				"<col class='diff-content' />" .
				"<tr>" .
					"<td colspan='2' width='50%' align='center' class='diff-otitle'><b>" .
						$leftStatus . "</b></td>" .
					"<td colspan='2' width='50%' align='center' class='diff-ntitle'><b>" .
						$rightStatus . "</b></td>" .
				"</tr>" .
				$multiNotice .
				$diffBody .
			"</table>";
	}

	/**
	 * Get the normal and display files for the underlying ImagePage.
	 * If the a stable version needs to be displayed, this will set $normalFile
	 * to the current version, and $displayFile to the desired version.
	 *
	 * If no stable version is required, the reference parameters will not be set
	 *
	 * Depends on $wgRequest
	 */
	public function imagePageFindFile( &$normalFile, &$displayFile ) {
		global $wgRequest, $wgArticle;
		$this->load();
		# Determine timestamp. A reviewed version may have explicitly been requested...
		$frev = null;
		$time = false;
		$reqId = $wgRequest->getVal( 'stableid' );
		if ( $reqId ) {
			$frev = FlaggedRevision::newFromTitle( $this->article->getTitle(), $reqId );
		} elseif ( $this->pageOverride() ) {
			$frev = $this->article->getStableRev();
		}
		if ( $frev ) {
			$time = $frev->getFileTimestamp();
			// B/C, may be stored in associated image version metadata table
			// @TODO: remove, updateTracking.php does this
			if ( !$time ) {
				$dbr = wfGetDB( DB_SLAVE );
				$time = $dbr->selectField( 'flaggedimages',
					'fi_img_timestamp',
					array( 'fi_rev_id' => $frev->getRevId(),
						'fi_name' => $this->article->getTitle()->getDBkey() ),
					__METHOD__
				);
				$time = trim( $time ); // remove garbage
				$time = $time ? wfTimestamp( TS_MW, $time ) : false;
			}
			# NOTE: if not found, this will use the current
			$wgArticle = new ImagePage( $this->article->getTitle(), $time );
		}
		if ( !$time ) {
			# Try request parameter
			$time = $wgRequest->getVal( 'filetimestamp', false );
		}

		if ( !$time ) {
			return; // Use the default behaviour
		}

		$title = $this->article->getTitle();
		$displayFile = wfFindFile( $title, array( 'time' => $time ) );
		# If none found, try current
		if ( !$displayFile ) {
			wfDebug( __METHOD__ . ": {$title->getPrefixedDBkey()}: $time not found, using current\n" );
			$displayFile = wfFindFile( $title );
			# If none found, use a valid local placeholder
			if ( !$displayFile ) {
				$displayFile = wfLocalFile( $title ); // fallback to current
			}
			$normalFile = $displayFile;
		# If found, set $normalFile
		} else {
			wfDebug( __METHOD__ . ": {$title->getPrefixedDBkey()}: using timestamp $time\n" );
			$normalFile = wfFindFile( $title );
		}
	}

	/**
	 * Adds stable version tags to page when viewing history
	 */
	public function addToHistView() {
		global $wgOut;
		$this->load();
		# Must be reviewable. UI may be limited to unobtrusive patrolling system.
		if ( !$this->article->isReviewable() ) {
			return true;
		}
		# Add a notice if there are pending edits...
		$srev = $this->article->getStableRev();
		if ( $srev && $this->article->revsArePending() ) {
			$revsSince = $this->article->getPendingRevCount();
			$tag = "<div id='mw-fr-revisiontag-edit' class='flaggedrevs_notice plainlinks'>" .
				FlaggedRevsXML::lockStatusIcon( $this->article ) . # flag protection icon as needed
				FlaggedRevsXML::pendingEditNotice( $this->article, $srev, $revsSince ) . "</div>";
			$wgOut->addHTML( $tag );
		}
		return true;
	}

	/**
	 * Adds stable version tags to page when editing
	 */
	public function addToEditView( EditPage $editPage ) {
		global $wgOut, $wgUser;
		$this->load();
		# Must be reviewable. UI may be limited to unobtrusive patrolling system.
		if ( !$this->article->isReviewable() ) {
			return true;
		}
		$items = array();
		# Show stabilization log
		$log = $this->stabilityLogNotice();
		if ( $log ) $items[] = $log;
		# Check the newest stable version
		$frev = $this->article->getStableRev();
		if ( $frev ) {
			$quality = $frev->getQuality();
			# Find out revision id of base version
			$latestId = $this->article->getLatest();
			$revId = $editPage->oldid ? $editPage->oldid : $latestId;
			# Let new users know about review procedure a tag.
			# If the log excerpt was shown this is redundant.
			if ( !$log && !$wgUser->getId() && $this->article->isStableShownByDefault() ) {
				$items[] = wfMsgExt( 'revreview-editnotice', array( 'parseinline' ) );
			}
			# Add a notice if there are pending edits...
			if ( $this->article->revsArePending() ) {
				$revsSince = $this->article->getPendingRevCount();
				$items[] = FlaggedRevsXML::pendingEditNotice( $this->article, $frev, $revsSince );
			}
			# Show diff to stable, to make things less confusing.
			# This can be disabled via user preferences and other conditions...
			if ( $frev->getRevId() < $latestId // changes were made
				&& $this->isDiffShownOnEdit() // stable default and user cannot review
				&& $wgUser->getBoolOption( 'flaggedrevseditdiffs' ) // not disable via prefs
				&& $revId == $latestId // only for current rev
				&& $editPage->section != 'new' // not for new sections
				&& $editPage->formtype != 'diff' // not "show changes"
			) {
				# Left diff side...
				$leftNote = $quality
					? 'revreview-hist-quality'
					: 'revreview-hist-basic';
				$lClass = FlaggedRevsXML::getQualityColor( (int)$quality );
				$leftNote = "<span class='$lClass'>[" .
					wfMsgHtml( $leftNote ) . "]</span>";
				# Right diff side...
				$rClass = FlaggedRevsXML::getQualityColor( false );
				$rightNote = "<span class='$rClass'>[" .
					wfMsgHtml( 'revreview-hist-pending' ) . "]</span>";
				# Get the stable version source
				$text = $frev->getRevText();
				# Are we editing a section?
				$section = ( $editPage->section == "" ) ?
					false : intval( $editPage->section );
				if ( $section !== false ) {
					$text = $this->article->getSection( $text, $section );
				}
				if ( $text !== false && strcmp( $text, $editPage->textbox1 ) !== 0 ) {
					$diffEngine = new DifferenceEngine( $this->article->getTitle() );
					$diffBody = $diffEngine->generateDiffBody( $text, $editPage->textbox1 );
					$diffHtml =
						wfMsgExt( 'review-edit-diff', 'parseinline' ) . ' ' .
						FlaggedRevsXML::diffToggle() .
						"<div id='mw-fr-stablediff'>" .
						self::getFormattedDiff( $diffBody, '', $leftNote, $rightNote ) .
						"</div>\n";
					$items[] = $diffHtml;
					$diffEngine->showDiffStyle(); // add CSS
				}
			}
			# Output items
			if ( count( $items ) ) {
				$html = "<table class='flaggedrevs_editnotice plainlinks'>";
				foreach ( $items as $item ) {
					$html .= '<tr><td>' . $item . '</td></tr>';
				}
				$html .= '</table>';
				$wgOut->addHTML( $html );
			}
		}
		return true;
	}
	
	protected function stabilityLogNotice() {
		$this->load();
		$s = '';
		# Only for pages manually made to be stable...
		if ( $this->article->isPageLocked() ) {
			$s = wfMsgExt( 'revreview-locked', 'parseinline' );
			$s .= ' ' . FlaggedRevsXML::logDetailsToggle();
			$s .= FlaggedRevsXML::stabilityLogExcerpt( $this->article );
		# ...or unstable
		} elseif ( $this->article->isPageUnlocked() ) {
			$s = wfMsgExt( 'revreview-unlocked', 'parseinline' );
			$s .= ' ' . FlaggedRevsXML::logDetailsToggle();
			$s .= FlaggedRevsXML::stabilityLogExcerpt( $this->article );
		}
		return $s;
	}
	
	public function addToNoSuchSection( EditPage $editPage, &$s ) {
		$this->load();
		if ( !$this->article->isReviewable() ) {
			return true; // nothing to do
		}
		$srev = $this->article->getStableRev();
		if ( $srev && $this->article->revsArePending() ) {
			$revsSince = $this->article->getPendingRevCount();
			if ( $revsSince ) {
				$s .= "<div class='flaggedrevs_editnotice plainlinks'>" .
					wfMsgExt( 'revreview-pending-nosection', array( 'parseinline' ),
						$srev->getRevId(), $revsSince ) . "</div>";
			}
		}
		return true;
	}

	/**
	 * Add unreviewed pages links
	 */
	public function addToCategoryView() {
		global $wgOut, $wgUser;
		$this->load();
		if ( !$wgUser->isAllowed( 'review' ) ) {
			return true;
		}
		if ( !FlaggedRevs::useOnlyIfProtected() ) {
			# Add links to lists of unreviewed pages and pending changes in this category
			$category = $this->article->getTitle()->getText();
			$wgOut->appendSubtitle(
				Html::rawElement(
					'span',
					array( 'class' => 'plainlinks', 'id' => 'mw-fr-category-oldreviewed' ), 
					wfMsgExt( 'flaggedrevs-categoryview', 'parseinline', urlencode( $category ) )
				)
			);
		}
		return true;
	}

	 /**
	 * Add review form to pages when necessary
	 * on a regular page view (action=view)
	 */
	public function addReviewForm( &$data ) {
		global $wgRequest, $wgUser, $wgOut;
		$this->load();
		if ( $wgOut->isPrintable() ) {
			return false; // Must be on non-printable output 
		}
		# User must have review rights
		if ( !$wgUser->isAllowed( 'review' ) ) {
			return true;
		}
		# Page must exist and be reviewable
		if ( !$this->article->exists() || !$this->article->isReviewable() ) {
			return true;
		}
		# Check action and if page is protected
		$action = $wgRequest->getVal( 'action', 'view' );
		# Must be view action...diffs handled elsewhere
		if ( !self::isViewAction( $action ) ) {
			return true;
		}
		# Get the revision being displayed
		$rev = false;
		if ( $this->reviewFormRev ) {
			$rev = $this->reviewFormRev; // $newRev for diffs stored here
		} elseif ( $wgOut->getRevisionId() ) {
			$rev = Revision::newFromId( $wgOut->getRevisionId() );
		}
		# Build the review form as needed
		if ( $rev && ( !$this->diffRevs || $this->isReviewableDiff ) ) {
			# $wgOut may not already have the inclusion IDs, such as for diffonly=1.
			# RevisionReviewForm will fetch them as needed however.
			$templateIDs = $fileSHA1Keys = null;
			if ( $wgOut->getRevisionId() == $rev->getId()
				&& isset( $wgOut->mTemplateIds )
				&& isset( $wgOut->fr_fileSHA1Keys ) )
			{
				$templateIDs = $wgOut->mTemplateIds;
				$fileSHA1Keys = $wgOut->fr_fileSHA1Keys;
			}
			# Review notice box goes in top of form
			$form = RevisionReviewForm::buildQuickReview(
				$wgUser, $this->article, $rev, $this->diffRevs['old'],
				$this->diffNoticeBox, $templateIDs, $fileSHA1Keys
			);
			# Diff action: place the form at the top of the page
			if ( $this->diffRevs ) {
				$wgOut->prependHTML( $form );
			# View action: place the form at the bottom of the page
			} else {
				$data .= $form;
			}
		}
		return true;
	}

	 /**
	 * Add link to stable version setting to protection form
	 */
	public function addVisibilityLink( &$data ) {
		global $wgRequest, $wgOut;
		$this->load();
		if ( FlaggedRevs::useProtectionLevels() ) {
			return true; // simple custom levels set for action=protect
		}
		# Check only if the title is reviewable
		if ( !FlaggedRevs::inReviewNamespace( $this->article->getTitle() ) ) {
			return true;
		}
		$action = $wgRequest->getVal( 'action', 'view' );
		if ( $action == 'protect' || $action == 'unprotect' ) {
			$title = SpecialPage::getTitleFor( 'Stabilization' );
			# Give a link to the page to configure the stable version
			$frev = $this->article->getStableRev();
			if ( $frev && $frev->getRevId() == $this->article->getLatest() ) {
				$wgOut->prependHTML( "<span class='plainlinks'>" .
					wfMsgExt( 'revreview-visibility', array( 'parseinline' ),
						$title->getPrefixedText() ) . "</span>" );
			} elseif ( $frev ) {
				$wgOut->prependHTML( "<span class='plainlinks'>" .
					wfMsgExt( 'revreview-visibility2', array( 'parseinline' ),
						$title->getPrefixedText() ) . "</span>" );
			} else {
				$wgOut->prependHTML( "<span class='plainlinks'>" .
					wfMsgExt( 'revreview-visibility3', array( 'parseinline' ),
						$title->getPrefixedText() ) . "</span>" );
			}
		}
		return true;
	}

	/**
	 * Modify an array of action links, as used by SkinTemplateNavigation and
	 * SkinTemplateTabs, to inlude flagged revs UI elements
	 */
	public function setActionTabs( $skin, array &$actions ) {
		global $wgUser;
		$this->load();
		if ( FlaggedRevs::useProtectionLevels() ) {
			return true; // simple custom levels set for action=protect
		}
		$title = $this->article->getTitle()->getSubjectPage();
		if ( !FlaggedRevs::inReviewNamespace( $title ) ) {
			return true; // Only reviewable pages need these tabs
		}
		// Check if we should show a stabilization tab
		if (
			!$skin->mTitle->isTalkPage() &&
			is_array( $actions ) &&
			!isset( $actions['protect'] ) &&
			!isset( $actions['unprotect'] ) &&
			$wgUser->isAllowed( 'stablesettings' ) &&
			$title->exists() )
		{
			$stableTitle = SpecialPage::getTitleFor( 'Stabilization' );
			// Add the tab
			$actions['default'] = array(
				'class' => false,
				'text' => wfMsg( 'stabilization-tab' ),
				'href' => $stableTitle->getLocalUrl(
					'page=' . $title->getPrefixedUrl()
				)
			);
		}
		return true;
	}

	/**
	 * Modify an array of tab links to include flagged revs UI elements
	 * @param string $type ('flat' for SkinTemplateTabs, 'nav' for SkinTemplateNavigation)
	 */
	public function setViewTabs( Skin $skin, array &$views, $type ) {
		global $wgRequest;
		$this->load();
		if ( $skin->mTitle->isTalkPage() ) {
			return true; // leave talk pages alone
		}
		// Get the type of action requested
		$action = $wgRequest->getVal( 'action', 'view' );
		if ( !$this->article->isReviewable() ) {
			return true; // Not a reviewable page or the UI is hidden
		}
		// XXX: shouldn't the session slave position check handle this?
		$flags = ( $action == 'rollback' ) ? FR_MASTER : 0;
		$srev = $this->article->getStableRev( $flags );
	   	if ( !$srev ) {
			return true; // No stable revision exists
		}
		$synced = $this->article->stableVersionIsSynced();
		$pendingEdits = !$synced && $this->article->isStableShownByDefault();
		// Set the edit tab names as needed...
	   	if ( $pendingEdits ) {
	   		if ( isset( $views['edit'] ) ) {
				$views['edit']['text'] = wfMsg( 'revreview-edit' );
	   		}
	   		if ( isset( $views['viewsource'] ) ) {
				$views['viewsource']['text'] = wfMsg( 'revreview-source' );
			}
	   	}
		# Add "pending changes" tab if the page is not synced
		if ( !$synced ) {
			$this->addDraftTab( $views, $srev, $action, $type );
		}
		return true;
	}

	// Add "pending changes" tab and set tab selection CSS
	protected function addDraftTab(
		array &$views, FlaggedRevision $srev, $action, $type
	) {
		global $wgRequest, $wgOut;
		$title = $this->article->getTitle(); // convenience
	 	$tabs = array(
	 		'read' => array( // view stable
				'text'  => '', // unused
				'href'  => $title->getLocalUrl( 'stable=1' ),
	 			'class' => ''
	 		),
	 		'draft' => array( // view draft
				'text'  => wfMsg( 'revreview-current' ),
				'href'  => $title->getLocalUrl( 'stable=0&redirect=no' ),
	 			'class' => 'collapsible'
	 		),
	 	);
		// Set tab selection CSS
		if ( $this->pageOverride() || $wgRequest->getVal( 'stableid' ) ) {
			// We are looking a the stable version or an old reviewed one
			$tabs['read']['class'] = 'selected';
		} elseif ( self::isViewAction( $action ) ) {
			$ts = null;
			if ( $wgOut->getRevisionId() ) { // @TODO: avoid same query in Skin.php
				$ts = ( $wgOut->getRevisionId() == $this->article->getLatest() )
					? $this->article->getTimestamp() // skip query
					: Revision::getTimestampFromId( $title, $wgOut->getRevisionId() );
			}
			// Are we looking at a pending revision?
			if ( $ts > $srev->getRevTimestamp() ) { // bug 15515
				$tabs['draft']['class'] .= ' selected';
			// Are there *just* pending template/file changes.
			} elseif ( $this->article->onlyTemplatesOrFilesPending()
				&& $wgOut->getRevisionId() == $this->article->getStable() )
			{
				$tabs['draft']['class'] .= ' selected';
			// Otherwise, fallback to regular tab behavior
			} else {
				$tabs['read']['class'] = 'selected';
			}
		}
		$newViews = array();
		// Rebuild tabs array. Deals with Monobook vs Vector differences.
		if ( $type == 'nav' ) { // Vector et al
			foreach ( $views as $tabAction => $data ) {
				// The 'view' tab. Make it go to the stable version...
				if ( $tabAction == 'view' ) {
					// 'view' for content page; make it go to the stable version
					$newViews[$tabAction]['text'] = $data['text']; // keep tab name
					$newViews[$tabAction]['href'] = $tabs['read']['href'];
					$newViews[$tabAction]['class'] = $tabs['read']['class'];
				// All other tabs...
				} else {
					// Add 'draft' tab to content page to the left of 'edit'...
					if ( $tabAction == 'edit' || $tabAction == 'viewsource' ) {
						$newViews['current'] = $tabs['draft'];
					}
					$newViews[$tabAction] = $data;
				}
			}
		} elseif ( $type == 'flat' ) { // MonoBook et al
			$first = true;
			foreach ( $views as $tabAction => $data ) {
				// The first tab ('page'). Make it go to the stable version...
				if ( $first ) {
					$first = false;
					$newViews[$tabAction]['text'] = $data['text']; // keep tab name
					$newViews[$tabAction]['href'] = $tabs['read']['href'];
					$newViews[$tabAction]['class'] = $data['class']; // keep tab class
				// All other tabs...
				} else {
					// Add 'draft' tab to content page to the left of 'edit'...
					if ( $tabAction == 'edit' || $tabAction == 'viewsource' ) {
						$newViews['current'] = $tabs['draft'];
					}
					$newViews[$tabAction] = $data;
				}
			}
		}
	   	// Replaces old tabs with new tabs
	   	$views = $newViews;
	}

	/**
	 * Adds notes by the reviewer to the bottom of the page
	 * @param FlaggedRevision $frev
	 * @return void
	 */
	public function setReviewNotes( FlaggedRevision $frev ) {
		global $wgUser;
		$this->load();
		if ( FlaggedRevs::allowComments() && $frev->getComment() != '' ) {
			$this->reviewNotes = "<br /><div class='flaggedrevs_notes plainlinks'>";
			$this->reviewNotes .= wfMsgExt( 'revreview-note', 'parseinline',
				User::whoIs( $frev->getUser() ) );
			$this->reviewNotes .= '<br /><i>' .
				$wgUser->getSkin()->formatComment( $frev->getComment() ) . '</i></div>';
		}
	}

	/**
	 * Adds a notice saying that this revision is pending review
	 * @param FlaggedRevision $srev The stable version
	 * @return void
	 */
	public function setPendingNotice( FlaggedRevision $srev ) {
		global $wgLang;
		$this->load();
		$time = $wgLang->date( $srev->getTimestamp(), true );
		$pendingNotice = wfMsgExt( 'revreview-pendingnotice', 'parseinline', $time );
		$this->reviewNotice .= "<div id='mw-fr-reviewnotice' class='flaggedrevs_preview plainlinks'>" . 
			$pendingNotice . "</div>";
	}

	/**
	* When viewing a diff:
	* (a) Add the review form to the top of the page
	* (b) Mark off which versions are checked or not
	* (c) When comparing the stable revision to the current:
	* 	(i)  Show a tag with some explanation for the diff
	*	(ii) List any template/file changes pending review
	*/
	public function addToDiffView( $diff, $oldRev, $newRev ) {
		global $wgRequest, $wgUser, $wgOut, $wgMemc;
		$this->load();
		# Exempt printer-friendly output
		if ( $wgOut->isPrintable() ) {
			return true;
		# Multi-page diffs are useless and misbehave (bug 19327). Sanity check $newRev.
		} elseif ( $this->isMultiPageDiff || !$newRev ) {
			return true;
		# Page must be reviewable.
		} elseif ( !$this->article->isReviewable() ) {
			return true;
		}
		$srev = $this->article->getStableRev();
		# Check if this is a diff-to-stable. If so:
		# (a) prompt reviewers to review the changes
		# (b) list template/file changes if only includes are pending
		if ( $srev
			&& $this->isDiffFromStable
			&& !$this->article->stableVersionIsSynced() ) // pending changes
		{
			$changeDiv = '';
			$this->reviewFormRev = $newRev;
			$changeList = array();
			# Page not synced only due to includes?
			if ( !$this->article->revsArePending() ) {
				# Add a list of links to each changed template...
				$changeList = self::fetchTemplateChanges( $srev );
				# Add a list of links to each changed file...
				$changeList = array_merge( $changeList, self::fetchFileChanges( $srev ) );
				# Correct bad cache which said they were not synced...
				if ( !count( $changeList ) ) {
					global $wgParserCacheExpireTime;
					$key = wfMemcKey( 'flaggedrevs', 'includesSynced', $this->article->getId() );
					$data = FlaggedRevs::makeMemcObj( "true" );
					$wgMemc->set( $key, $data, $wgParserCacheExpireTime );
				}
			}
			# If there are pending revs or templates/files changes, notify the user...
			if ( $this->article->revsArePending() || count( $changeList ) ) {
				$changeDiv = '';
				# If the user can review then prompt them to review them...
				if ( $wgUser->isAllowed( 'review' ) ) {
					# Set a key to note that someone is viewing this
					$this->markDiffUnderReview( $oldRev, $newRev );
					// Reviewer just edited...
					if ( $wgRequest->getInt( 'shownotice' )
						&& $newRev->isCurrent()
						&& $newRev->getRawUserText() == $wgUser->getName() )
					{
						$title = $this->article->getTitle(); // convenience
						// @TODO: make diff class cache this
						$n = $title->countRevisionsBetween( $oldRev->getId(), $newRev->getId() );
						if ( $n ) {
							$msg = 'revreview-update-edited-prev'; // previous pending edits
						} else {
							$msg = 'revreview-update-edited'; // just couldn't autoreview
						}
					// All other cases...
					} else {
						$msg = 'revreview-update'; // generic "please review" notice...
					}
					$changeDiv .= wfMsgExt( $msg, 'parse' );
				}
				# Add include change list...
				if ( count( $changeList ) ) {
					$changeDiv .= '<p>' .
						wfMsgExt( 'revreview-update-includes', 'parseinline' ) .
						'&#160;' . implode( ', ', $changeList ) . '</p>';
					# Add include usage notice...
					if ( FlaggedRevs::inclusionSetting() == FR_INCLUDES_STABLE ) {
						$changeDiv .= wfMsgExt( 'revreview-update-use', 'parse' );
					}
				}
			}
			if ( $changeDiv != '' ) {
				if ( $wgUser->isAllowed( 'review' ) ) {
					$this->diffNoticeBox = $changeDiv; // add as part of form
				} else {
					$css = 'flaggedrevs_diffnotice plainlinks';
					$wgOut->addHTML(
						"<div id='mw-fr-difftostable' class='$css'>$changeDiv</div>\n"
					);
				}
			}
		}
		# Add a link to diff from stable to current as needed.
		# Show review status of the diff revision(s). Uses a <table>.
		$wgOut->addHTML(
			'<div id="mw-fr-diff-headeritems">' .
			self::diffLinkAndMarkers( $this->article, $oldRev, $newRev ) .
			'</div>'
		);
		return true;
	}

	// get new diff header items for in-place AJAX page review
	public static function AjaxBuildDiffHeaderItems() {
		$args = func_get_args(); // <oldid, newid>
		if ( count( $args ) >= 2 ) {
			$oldid = (int)$args[0];
			$newid = (int)$args[1];
			$oldRev = Revision::newFromId( $oldid );
			$newRev = Revision::newFromId( $newid );
			if ( $newRev && $newRev->getTitle() ) {
				$fa = FlaggedArticle::getTitleInstance( $newRev->getTitle() );
				return self::diffLinkAndMarkers( $fa, $oldRev, $newRev );
			}
		}
		return '';
	}

	/**
	* (a) Add a link to diff from stable to current as needed
	* (b) Show review status of the diff revision(s). Uses a <table>.
	* Note: used by ajax function to rebuild diff page
	*/
	public static function diffLinkAndMarkers( FlaggedArticle $article, $oldRev, $newRev ) {
		$s = '<form id="mw-fr-diff-dataform">';
		$s .= Html::hidden( 'oldid', $oldRev ? $oldRev->getId() : 0 );
		$s .= Html::hidden( 'newid', $newRev ? $newRev->getId() : 0 );
		$s .= "</form>\n";
		if ( $newRev ) { // sanity check
			$s .= self::diffToStableLink( $article, $oldRev, $newRev );
			$s .= self::diffReviewMarkers( $article, $oldRev, $newRev );
		}
		return $s;
	}

	/**
	* Add a link to diff-to-stable for reviewable pages
	*/
	protected static function diffToStableLink(
		FlaggedArticle $article, $oldRev, Revision $newRev
	) {
		global $wgUser;
		$srev = $article->getStableRev();
		if ( !$srev ) {
			return ''; // nothing to do
		}
		$review = '';
		# Is this already the full diff-to-stable?
		$fullStableDiff = $newRev->isCurrent()
			&& self::isDiffToStable( $srev, $oldRev, $newRev );
		# Make a link to the full diff-to-stable if:
		# (a) Actual revs are pending and (b) We are not viewing the full diff-to-stable
		if ( $article->revsArePending() && !$fullStableDiff ) {
			$review = $wgUser->getSkin()->makeKnownLinkObj(
				$article->getTitle(),
				wfMsgHtml( 'review-diff2stable' ),
				'oldid=' . $srev->getRevId() . '&diff=cur&diffonly=0'
			);
			$review = wfMsgHtml( 'parentheses', $review );
			$review = "<div class='fr-diff-to-stable' align='center'>$review</div>";
		}
		return $review;
	}

	/**
	* Add [checked version] and such to left and right side of diff
	*/
	protected static function diffReviewMarkers( FlaggedArticle $article, $oldRev, $newRev ) {
		$table = '';
		$srev = $article->getStableRev();
		# Diff between two revisions
		if ( $oldRev && $newRev ) {
			list( $msg, $class ) = self::getDiffRevMsgAndClass( $oldRev, $srev );
			$table .= "<table class='fr-diff-ratings'><tr>";
			$table .= "<td width='50%' align='center'>";
			$table .= "<span class='$class'>[" .
				wfMsgHtml( $msg ) . "]</span>";

			list( $msg, $class ) = self::getDiffRevMsgAndClass( $newRev, $srev );
			$table .= "</td><td width='50%' align='center'>";
			$table .= "<span class='$class'>[" .
				wfMsgHtml( $msg ) . "]</span>";

			$table .= "</td></tr></table>\n";
		# New page "diffs" - just one rev
		} elseif ( $newRev ) {
			list( $msg, $class ) = self::getDiffRevMsgAndClass( $newRev, $srev );
			$table .= "<table class='fr-diff-ratings'>";
			$table .= "<tr><td align='center'><span class='$class'>";
			$table .= '[' . wfMsgHtml( $msg ) . ']';
			$table .= "</span></td></tr></table>\n";
		}
		return $table;
	}

	protected static function getDiffRevMsgAndClass(
		Revision $rev, FlaggedRevision $srev = null
	) {
		$tier = FlaggedRevs::getRevQuality( $rev->getPage(), $rev->getId() );
		if ( $tier !== false ) {
			$msg = $tier
				? 'revreview-hist-quality'
				: 'revreview-hist-basic';
		} else {
			$msg = ( $srev && $rev->getTimestamp() > $srev->getRevTimestamp() ) // bug 15515
				? 'revreview-hist-pending'
				: 'revreview-hist-draft';
		}
		$css = FlaggedRevsXML::getQualityColor( $tier );
		return array( $msg, $css );
	}

	// Fetch template changes for a reviewed revision since review
	// @returns array
	protected static function fetchTemplateChanges( FlaggedRevision $frev ) {
		global $wgUser;
		$skin = $wgUser->getSkin();
		$diffLinks = array();
		$changes = $frev->findPendingTemplateChanges();
		foreach ( $changes as $tuple ) {
			list( $title, $revIdStable ) = $tuple;
			$diffLinks[] = $skin->makeLinkObj( $title,
				$title->getPrefixedText(),
				'diff=cur&oldid=' . (int)$revIdStable );
		}
		return $diffLinks;
	}

	// Fetch file changes for a reviewed revision since review
	// @returns array
	protected static function fetchFileChanges( FlaggedRevision $frev ) {
		global $wgUser;
		$skin = $wgUser->getSkin();
		$diffLinks = array();
		$changes = $frev->findPendingFileChanges( 'noForeign' );
		foreach ( $changes as $tuple ) {
			list( $title, $revIdStable ) = $tuple;
			// @TODO: change when MW has file diffs
			$diffLinks[] = $skin->makeLinkObj( $title, $title->getPrefixedText() );
		}
		return $diffLinks;
	}

	// Mark that someone is viewing a portion or all of the diff-to-stable
	protected function markDiffUnderReview( Revision $oldRev, Revision $newRev ) {
		global $wgMemc;
		$key = wfMemcKey( 'stableDiffs', 'underReview', $oldRev->getID(), $newRev->getID() );
		$wgMemc->set( $key, '1', 6 * 60 );
	}

	/**
	* Set $this->isDiffFromStable and $this->isMultiPageDiff fields
	* Note: $oldRev could be false
	*/
	public function setViewFlags( $diff, $oldRev, $newRev ) {
		$this->load();
		// We only want valid diffs that actually make sense...
		if ( $newRev && $oldRev && $newRev->getTimestamp() >= $oldRev->getTimestamp() ) {
			// Is this a diff between two pages?
			if ( $newRev->getPage() != $oldRev->getPage() ) {
				$this->isMultiPageDiff = true;
			// Is there a stable version?
			} elseif ( $this->article->isReviewable() ) {
				$srev = $this->article->getStableRev();
				// Is this a diff of a draft rev against the stable rev?
				if ( self::isDiffToStable( $srev, $oldRev, $newRev ) ) {
					$this->isDiffFromStable = true;
					$this->isReviewableDiff = true;
				// Is this a diff of a draft rev against a reviewed rev?
				} elseif (
					FlaggedRevision::newFromTitle( $diff->getTitle(), $oldRev->getId() ) ||
					FlaggedRevision::newFromTitle( $diff->getTitle(), $newRev->getId() )
				) {
					$this->isReviewableDiff = true;
				}
			}
			$this->diffRevs = array( 'old' => $oldRev->getId(), 'new' => $newRev->getId() );
		}
		return true;
	}

	// Is a diff from $oldRev to $newRev a diff-to-stable?
	protected static function isDiffToStable( $srev, $oldRev, $newRev ) {
		return ( $srev && $oldRev && $newRev
			&& $oldRev->getPage() == $newRev->getPage() // no multipage diffs
			&& $oldRev->getId() == $srev->getRevId()
			&& $newRev->getTimestamp() >= $oldRev->getTimestamp() // no backwards diffs
 		);
	}

	/**
	* Redirect users out to review the changes to the stable version.
	* Only for people who can review and for pages that have a stable version.
	*/
	public function injectPostEditURLParams( &$sectionAnchor, &$extraQuery ) {
		global $wgUser;
		$this->load();
		# Don't show this for pages that are not reviewable
		if ( !$this->article->isReviewable() ) {
			return true;
		}
		# Get the stable version, from master
		$frev = $this->article->getStableRev( FR_MASTER );
		if ( !$frev ) {
			return true;
		}
		# Get latest revision Id (lag safe)
		$latest = $this->article->getTitle()->getLatestRevID( Title::GAID_FOR_UPDATE );
		if ( $latest == $frev->getRevId() ) {
			return true; // only for pages with pending edits
		}
		// If the edit was not autoreviewed, and the user can actually make a
		// new stable version, then go to the diff...
		if ( $frev->userCanSetFlags( $wgUser ) ) {
			$extraQuery .= $extraQuery ? '&' : '';
			// Override diffonly setting to make sure the content is shown
			$extraQuery .= 'oldid=' . $frev->getRevId() . '&diff=cur&diffonly=0&shownotice=1';
		// ...otherwise, go to the current revision after completing an edit.
		// This allows for users to immediately see their changes.
		} else {
			$extraQuery .= $extraQuery ? '&' : '';
			$extraQuery .= 'stable=0';
			// Show a notice at the top of the page for non-reviewers...
			if ( !$wgUser->isAllowed( 'review' ) && $this->article->isStableShownByDefault() ) {
				$extraQuery .= '&shownotice=1';
				if ( $sectionAnchor ) {
					// Pass a section parameter in the URL as needed to add a link to
					// the "your changes are pending" box on the top of the page...
					$section = str_replace(
						array( ':' , '.' ), array( '%3A', '%' ), // hack: reverse special encoding
						substr( $sectionAnchor, 1 ) // remove the '#'
					);
					$extraQuery .= '&fromsection=' . $section;
					$sectionAnchor = ''; // go to the top of the page to see notice
				}
			}
		}
		return true;
	}

	/**
	* If submitting the edit will leave it pending, then change the button text
	* Note: interacts with 'review pending changes' checkbox
	* @TODO: would be nice if hook passed in button attribs, not XML
	*/
	public function changeSaveButton( EditPage $editPage, array &$buttons ) {
		if ( !$this->editWillRequireReview( $editPage ) ) {
			return true; // edit will go live or be reviewed on save
		}
		if ( extension_loaded( 'domxml' ) ) {
			wfDebug( "Warning: you have the obsolete domxml extension for PHP. Please remove it!\n" );
			return true; # PECL extension conflicts with the core DOM extension (see bug 13770)
		} elseif ( isset( $buttons['save'] ) && extension_loaded( 'dom' ) ) {
			$dom = new DOMDocument();
			$dom->loadXML( $buttons['save'] ); // load button XML from hook
			foreach ( $dom->getElementsByTagName( 'input' ) as $input ) { // one <input>
				$input->setAttribute( 'value', wfMsg( 'revreview-submitedit' ) );
				$input->setAttribute( 'title', // keep accesskey
					wfMsgExt( 'revreview-submitedit-title', 'parsemag' ) .
						' [' . wfMsg( 'accesskey-save' ) . ']' );
				# Change submit button text & title
				$buttons['save'] = $dom->saveXML( $dom->documentElement );
			}
		}
		return true;
	}

	/**
	* If this edit will not go live on submit (accounting for wpReviewEdit)
	* @param EditPage $editPage
	* @return bool
	*/
	protected function editWillRequireReview( EditPage $editPage ) {
		global $wgRequest;
		$title = $this->article->getTitle(); // convenience
		if ( !$this->editRequiresReview( $editPage ) ) {
			return false; // edit will go live immediatly
		} elseif ( $wgRequest->getCheck( 'wpReviewEdit' ) && $title->userCan( 'review' ) ) {
			return false; // edit checked off to be reviewed on save
		}
		return true; // edit needs review
	}

	/**
	* If this edit will not go live on submit unless wpReviewEdit is checked
	* @param EditPage $editPage
	* @return bool
	*/
	protected function editRequiresReview( EditPage $editPage ) {
		if ( !$this->article->editsRequireReview() ) {
			return false; // edits go live immediatly
		} elseif ( $this->editWillBeAutoreviewed( $editPage ) ) {
			return false; // edit will be autoreviewed anyway
		}
		return true; // edit needs review
	}

	/**
	* If this edit will be auto-reviewed on submit
	* Note: checking wpReviewEdit does not count as auto-reviewed
	* @param EditPage $editPage
	* @return bool
	*/
	protected function editWillBeAutoreviewed( EditPage $editPage ) {
		$title = $this->article->getTitle(); // convenience
		if ( !$this->article->isReviewable() ) {
			return false;
		}
		if ( $title->userCan( 'autoreview' ) ) {
			if ( FlaggedRevs::autoReviewNewPages() && !$this->article->exists() ) {
				return true; // edit will be autoreviewed
			}
			if ( !isset( $editPage->fr_baseFRev ) ) {
				$baseRevId = self::getBaseRevId( $editPage );
				$editPage->fr_baseFRev = FlaggedRevision::newFromTitle( $title, $baseRevId );
			}
			if ( $editPage->fr_baseFRev ) {
				return true; // edit will be autoreviewed
			}
		}
		return false; // edit won't be autoreviewed
	}

	/**
	* Add a "review pending changes" checkbox to the edit form iff:
	* (a) there are currently any revisions pending (bug 16713)
	* (b) this is an unreviewed page (bug 23970)
	*/
	public function addReviewCheck( EditPage $editPage, array &$checkboxes, &$tabindex ) {
		global $wgRequest;
		$title = $this->article->getTitle(); // convenience
		if ( !$this->article->isReviewable() || !$title->userCan( 'review' ) ) {
			return true; // not needed
		} elseif ( $this->editWillBeAutoreviewed( $editPage ) ) {
			return true; // edit will be auto-reviewed
		}
		if ( self::getBaseRevId( $editPage ) == $this->article->getLatest() ) {
			# For pages with either no stable version, or an outdated one, let
			# the user decide if he/she wants it reviewed on the spot. One might
			# do this if he/she just saw the diff-to-stable and *then* decided to edit.
			# Note: check not shown when editing old revisions, which is confusing.
			$checkbox = Xml::check(
				'wpReviewEdit',
				$wgRequest->getCheck( 'wpReviewEdit' ),
				array( 'tabindex' => ++$tabindex, 'id' => 'wpReviewEdit' )
			);
			$attribs = array( 'for' => 'wpReviewEdit' );
			// For reviewed pages...
			if ( $this->article->getStable() ) {
				// For pending changes...
				if ( $this->article->revsArePending() ) {
					$n = $this->article->getPendingRevCount();
					$attribs['title'] = wfMsg( 'revreview-check-flag-p-title' );
					$labelMsg = wfMsgExt( 'revreview-check-flag-p', 'parseinline', $n );
				// For just the user's changes...
				} else {
					$attribs['title'] = wfMsgExt( 'revreview-check-flag-y-title', 'parsemag' );
					$labelMsg = wfMsgExt( 'revreview-check-flag-y', 'parseinline' );
				}
			// For unreviewed pages...
			} else {
				$attribs['title'] = wfMsg( 'revreview-check-flag-u-title' );
				$labelMsg = wfMsgExt( 'revreview-check-flag-u', 'parseinline' );
			}
			$label = Xml::element( 'label', $attribs, $labelMsg );
			$checkboxes['reviewed'] = $checkbox . '&#160;' . $label;
		}
		return true;
	}

	/**
	* (a) Add a hidden field that has the rev ID the text is based off.
	* (b) If an edit was undone, add a hidden field that has the rev ID of that edit.
	* Needed for autoreview and user stats (for autopromote).
	* Note: baseRevId trusted for Reviewers - text checked for others.
	*/
	public function addRevisionIDField( EditPage $editPage, OutputPage $out ) {
		$this->load();
		$revId = self::getBaseRevId( $editPage );
		$out->addHTML( "\n" . Html::hidden( 'baseRevId', $revId ) );
		$out->addHTML( "\n" . Html::hidden( 'undidRev',
			empty( $editPage->undidRev ) ? 0 : $editPage->undidRev )
		);
		return true;
	}

	/**
	* Guess the rev ID the text of this form is based off
	* Note: baseRevId trusted for Reviewers - check text for others.
	* @return int
	*/
	protected static function getBaseRevId( EditPage $editPage ) {
		global $wgRequest;
		if ( !isset( $editPage->fr_baseRevId ) ) {
			$article = $editPage->getArticle(); // convenience
			$latestId = $article->getLatest(); // current rev
			$undo = $wgRequest->getIntOrNull( 'undo' );
			# Undoing consecutive top edits...
			if ( $undo && $undo === $latestId ) {
				# Treat this like a revert to a base revision.
				# We are undoing all edits *after* some rev ID (undoafter).
				# If undoafter is not given, then it is the previous rev ID.
				$revId = $wgRequest->getInt( 'undoafter',
					$article->getTitle()->getPreviousRevisionID( $latestId, Title::GAID_FOR_UPDATE ) );
			# Undoing other edits...
			} elseif ( $undo ) {
				$revId = $latestId; // current rev is the base rev
			# Other edits...
			} else {
				# If we are editing via oldid=X, then use that rev ID.
				# Otherwise, check if the client specified the ID (bug 23098).
				$revId = $article->getOldID()
					? $article->getOldID()
					: $wgRequest->getInt( 'baseRevId' ); // e.g. "show changes"/"preview"
			}
			# Zero oldid => current revision
			if ( !$revId ) {
				$revId = $latestId;
			}
			$editPage->fr_baseRevId = $revId;
		}
		return $editPage->fr_baseRevId;
	}

	 /**
	 * Adds brief review notes to a page.
	 * @param OutputPage $out
	 */
	public function addReviewNotes( &$data ) {
		$this->load();
		if ( $this->reviewNotes ) {
			$data .= $this->reviewNotes;
		}
		return true;
	}
	
	/*
	 * If this is a diff page then replace the article contents with a link
	 * to the specific revision. This will be replaced with article content
	 * using javascript and an api call.
	 */
	public function addCustomContentHtml( OutputPage $out, $newRevId ) {
		$this->load();
		if ( $newRevId ) {
			$out->addHTML( "<div id='mw-fr-revisioncontents'><span class='plainlinks'>" );
			$out->addWikiMsg( 'revcontents-getcontents',
				$this->article->getTitle()->getPrefixedDBKey(), $newRevId );
			$out->addHTML( "</span></div>" );
		}
	}
}
