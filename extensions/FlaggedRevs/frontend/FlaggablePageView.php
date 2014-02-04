<?php
/**
 * Class representing a web view of a MediaWiki page
 */
class FlaggablePageView extends ContextSource {
	protected $out = null;
	protected $article = null;

	protected $diffRevs = null; // assoc array of old and new Revisions for diffs
	protected $oldRevIncludes = null; // ( array of templates, array of file)
	protected $isReviewableDiff = false;
	protected $isDiffFromStable = false;
	protected $isMultiPageDiff = false;
	protected $reviewNotice = '';
	protected $diffNoticeBox = '';
	protected $diffIncChangeBox = '';
	protected $reviewFormRev = false;

	protected $loaded = false;
	protected $noticesDone = false;

	protected static $instance = null;

	/*
	 * Get the FlaggablePageView for this request
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
	 * Clear the FlaggablePageView for this request.
	 * Only needed when page redirection changes the environment.
	 */
	public function clear() {
		self::$instance = null;
	}

	/*
	 * Load the global FlaggableWikiPage instance
	 */
	protected function load() {
		if ( !$this->loaded ) {
			$this->loaded = true;
			$this->article = self::globalArticleInstance();
			if ( $this->article == null ) {
				throw new MWException( 'FlaggablePageView has no context article!' );
			}
			$this->out = $this->getOutput(); // convenience
		}
	}

	/**
	 * Get the FlaggableWikiPage instance associated with $wgTitle,
	 * or false if there isn't such a title
	 * @return FlaggableWikiPage|null
	 */
	public static function globalArticleInstance() {
		$title = RequestContext::getMain()->getTitle();
		if ( $title ) {
			return FlaggableWikiPage::getTitleInstance( $title );
		}
		return null;
	}

	/**
	 * Check if the old and new diff revs are set for this page view
	 * @return bool
	 */
	public function diffRevsAreSet() {
		return (bool)$this->diffRevs;
	}

	/**
	 * Is this web response for a request to view a page where both:
	 * (a) no specific page version was requested via URL params
	 * (b) a stable version exists and is to be displayed
	 * This factors in site/page config, user preferences, and web request params.
	 * @return bool
	 */
	protected function showingStableAsDefault() {
		$request = $this->getRequest();
		$reqUser = $this->getUser();
		$this->load();
		# This only applies to viewing the default version of pages...
		if ( !$this->isDefaultPageView( $request ) ) {
			return false;
		# ...and the page must be reviewable and have a stable version
		} elseif ( !$this->article->getStableRev() ) {
			return false;
		}
		# Check user preferences ("show stable by default?")
		$pref = (int)$reqUser->getOption( 'flaggedrevsstable' );
		if ( $pref == FR_SHOW_STABLE_ALWAYS ) {
			return true;
		} elseif ( $pref == FR_SHOW_STABLE_NEVER ) {
			return false;
		}
		# Viewer may be in a group that sees the draft by default
		if ( $this->userViewsDraftByDefault( $reqUser ) ) {
			return false;
		}
		# Does the stable version override the draft?
		$config = $this->article->getStabilitySettings();
		return (bool)$config['override'];
	}

	/**
	 * Is this web response for a request to view a page where both:
	 * (a) the stable version of a page was requested (?stable=1)
	 * (b) the stable version exists and is to be displayed
	 * @return bool
	 */
	protected function showingStableByRequest() {
		$request = $this->getRequest();
		$this->load();
		# Are we explicity requesting the stable version?
		if ( $request->getIntOrNull( 'stable' ) === 1 ) {
			# This only applies to viewing a version of the page...
			if ( !$this->isPageView( $request ) ) {
				return false;
			# ...with no version parameters other than ?stable=1...
			} elseif ( $request->getVal( 'oldid' ) || $request->getVal( 'stableid' ) ) {
				return false; // over-determined
			# ...and the page must be reviewable and have a stable version
			} elseif ( !$this->article->getStableRev() ) {
				return false;
			}
			return true; // show stable version
		}
		return false;
	}

	/**
	 * Is this web response for a request to view a page
	 * where a stable version exists and is to be displayed
	 * @return bool
	 */
	public function showingStable() {
		return $this->showingStableByRequest() || $this->showingStableAsDefault();
	}

	/**
	 * Should this be using a simple icon-based UI?
	 * Check the user's preferences first, using the site settings as the default.
	 * @return bool
	 */
	public function useSimpleUI() {
		global $wgSimpleFlaggedRevsUI;
		$reqUser = $this->getUser();
		return $reqUser->getOption( 'flaggedrevssimpleui', intval( $wgSimpleFlaggedRevsUI ) );
	}

	/**
	 * Should this user see the draft revision of pages by default?
	 * @param $user User
	 * @return bool
	 */
	protected function userViewsDraftByDefault( $user ) {
		global $wgFlaggedRevsExceptions;
		# Check user preferences ("show stable by default?")
		if ( $user->getOption( 'flaggedrevsstable' ) ) {
			return false;
		}
		# Viewer sees current by default (editors, insiders, ect...) ?
		foreach ( $wgFlaggedRevsExceptions as $group ) {
			if ( $group == 'user' ) {
				if ( $user->getId() ) {
					return true;
				}
			} elseif ( in_array( $group, $user->getGroups() ) ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Is this a view page action (including diffs)?
	 * @param $request WebRequest
	 * @return bool
	 */
	protected function isPageViewOrDiff( WebRequest $request ) {
		global $mediaWiki;
		$action = isset( $mediaWiki ) ?
			$mediaWiki->getAction( $request ) :
			$request->getVal( 'action', 'view' ); // cli
		return self::isViewAction( $action );
	}

	/**
	 * Is this a view page action (not including diffs)?
	 * @param $request WebRequest
	 * @return bool
	 */
	protected function isPageView( WebRequest $request ) {
		return $this->isPageViewOrDiff( $request )
			&& $request->getVal( 'diff' ) === null;
	}

	/**
	 * Is this a web request to just *view* the *default* version of a page?
	 * @param $request WebRequest
	 * @return bool
	 */
	protected function isDefaultPageView( WebRequest $request ) {
		global $mediaWiki;
		$action = isset( $mediaWiki ) ?
			$mediaWiki->getAction( $request ) :
			$request->getVal( 'action', 'view' ); // cli
		return ( self::isViewAction( $action )
			&& $request->getVal( 'oldid' ) === null
			&& $request->getVal( 'stable' ) === null
			&& $request->getVal( 'stableid' ) === null
			&& $request->getVal( 'diff' ) === null
		);
	}

	/**
	 * Is this a view page action?
	 * @param $action string from MediaWiki::getAction()
	 * @return bool
	 */
	protected static function isViewAction( $action ) {
		return ( $action == 'view' || $action == 'purge' || $action == 'render' );
	}

	/**
	 * Output review notice
	 */
	public function displayTag() {
		$this->load();
		// Sanity check that this is a reviewable page
		if ( $this->article->isReviewable() ) {
			$this->out->appendSubtitle( $this->reviewNotice );
		}
		return true;
	}

	/**
	 * Add a stable link when viewing old versions of an article that
	 * have been reviewed. (e.g. for &oldid=x urls)
	 */
	public function addStableLink() {
		$request = $this->getRequest();
		$this->load();
		if ( !$this->article->isReviewable() || !$request->getVal( 'oldid' ) ) {
			return true;
		}
		if ( !$this->out->isPrintable() ) {
			# We may have nav links like "direction=prev&oldid=x"
			$revID = $this->getOldIDFromRequest();
			$frev = FlaggedRevision::newFromTitle( $this->article->getTitle(), $revID );
			# Give a notice if this rev ID corresponds to a reviewed version...
			if ( $frev ) {
				$time = $this->getLanguage()->date( $frev->getTimestamp(), true );
				$flags = $frev->getTags();
				$quality = FlaggedRevs::isQuality( $flags );
				$msg = $quality ? 'revreview-quality-source' : 'revreview-basic-source';
				$tag = $this->msg( $msg, $frev->getRevId(), $time )->parse();
				# Hide clutter
				if ( !$this->useSimpleUI() && !empty( $flags ) ) {
					$tag .= FlaggedRevsXML::ratingToggle() .
						"<div id='mw-fr-revisiondetails'>" .
						$this->msg( 'revreview-oldrating' )->escaped() .
						FlaggedRevsXML::addTagRatings( $flags ) . '</div>';
				}
				$css = 'flaggedrevs_notice plainlinks noprint';
				$tag = "<div id='mw-fr-revisiontag-old' class='$css'>$tag</div>";
				$this->out->addHTML( $tag );
			}
		}
		return true;
	}

	/**
	 * @return mixed int/false/null
	 */
	protected function getRequestedStableId() {
		$request = $this->getRequest();
		$reqId = $request->getVal( 'stableid' );
		if ( $reqId === "best" ) {
			$reqId = $this->article->getBestFlaggedRevId();
		}
		return $reqId;
	}

	/**
	 * Replaces a page with the last stable version if possible
	 * Adds stable version status/info tags and notes
	 * Adds a quick review form on the bottom if needed
	 */
	public function setPageContent( &$outputDone, &$useParserCache ) {
		$request = $this->getRequest();
		$this->load();
		# Only trigger on page views with no oldid=x param
		if ( !$this->isPageView( $request ) || $request->getVal( 'oldid' ) ) {
			return true;
		# Only trigger for reviewable pages that exist
		} elseif ( !$this->article->exists() || !$this->article->isReviewable() ) {
			return true;
		}
		$tag = ''; // review tag box/bar message
		$old = $stable = false;
		# Check the newest stable version.
		$srev = $this->article->getStableRev();
		$stableId = $srev ? $srev->getRevId() : 0;
		$frev = $srev; // $frev is the revision we are looking at
		# Check for any explicitly requested reviewed version (stableid=X)...
		$reqId = $this->getRequestedStableId();
		if ( $reqId ) {
			if ( !$stableId ) {
				$reqId = false; // must be invalid
			# Treat requesting the stable version by ID as &stable=1
			} elseif ( $reqId != $stableId ) {
				$old = true; // old reviewed version requested by ID
				$frev = FlaggedRevision::newFromTitle( $this->article->getTitle(), $reqId );
				if ( !$frev ) {
					$reqId = false; // invalid ID given
				}
			} else {
				$stable = true; // stable version requested by ID
			}
		}
		// $reqId is null if nothing requested, false if invalid
		if ( $reqId === false ) {
			$this->out->addWikiText( $this->msg( 'revreview-invalid' )->text() );
			$this->out->returnToMain( false, $this->article->getTitle() );
			# Tell MW that parser output is done
			$outputDone = true;
			$useParserCache = false;
			return true;
		}
		// Is the page config altered?
		$prot = FlaggedRevsXML::lockStatusIcon( $this->article );
		if ( $frev ) { // has stable version?
			// Looking at some specific old stable revision ("&stableid=x")
			// set to override given the relevant conditions. If the user is
			// requesting the stable revision ("&stableid=x"), defer to override
			// behavior below, since it is the same as ("&stable=1").
			if ( $old ) {
				# Tell MW that parser output is done by setting $outputDone
				$outputDone = $this->showOldReviewedVersion( $frev, $tag, $prot );
				$useParserCache = false;
			// Stable version requested by ID or relevant conditions met to
			// to override page view with the stable version.
			} elseif ( $stable || $this->showingStable() ) {
				# Tell MW that parser output is done by setting $outputDone
				$outputDone = $this->showStableVersion( $srev, $tag, $prot );
				$useParserCache = false;
			// Looking at some specific old revision (&oldid=x) or if FlaggedRevs is not
			// set to override given the relevant conditions (like &stable=0).
			} else {
				$this->showDraftVersion( $srev, $tag, $prot );
			}
		} else {
			// Looking at a page with no stable version; add "no reviewed version" tag.
			$this->showUnreviewedPage( $tag, $prot );
		}
		# Some checks for which tag CSS to use
		if ( $this->useSimpleUI() ) {
			$tagClass = 'flaggedrevs_short';
		} else {
			if ( !$frev ) {
				$tagClass = 'flaggedrevs_notice';
			} elseif ( FlaggedRevs::isPristine( $frev->getTags() ) ) {
				$tagClass = 'flaggedrevs_pristine';
			} elseif ( FlaggedRevs::isQuality( $frev->getTags() ) ) {
				$tagClass = 'flaggedrevs_quality';
			} else {
				$tagClass = 'flaggedrevs_basic';
			}
		}
		# Wrap tag contents in a div
		if ( $tag != '' ) {
			$css = "{$tagClass} plainlinks noprint";
			$notice = "<div id=\"mw-fr-revisiontag\" class=\"{$css}\">{$tag}</div>\n";
			$this->reviewNotice .= $notice;
		}
		return true;
	}

	/**
	 * If the page has a stable version and it shows by default,
	 * tell search crawlers to index only that version of the page.
	 * Also index the draft as well if they are synced (bug 27173).
	 * However, any URL with ?stableid=x should not be indexed (as with ?oldid=x).
	 */
	public function setRobotPolicy() {
		$request = $this->getRequest();
		if ( $this->article->getStableRev() && $this->article->isStableShownByDefault() ) {
			if ( $this->showingStable() ) {
				return; // stable version - index this
			} elseif ( !$request->getVal( 'stableid' )
				&& $this->out->getRevisionId() == $this->article->getStable()
				&& $this->article->stableVersionIsSynced() )
			{
				return; // draft that is synced with the stable version - index this
			}
			$this->out->setRobotPolicy( 'noindex,nofollow' ); // don't index this version
		}
	}

	/**
	 * @param $tag review box/bar info
	 * @param $prot protection notice
	 * Tag output function must be called by caller
	 */
	protected function showUnreviewedPage( &$tag, $prot ) {
		if ( $this->out->isPrintable() ) {
			return; // all this function does is add notices; don't show them
		}
		$icon = FlaggedRevsXML::draftStatusIcon();
		// Simple icon-based UI
		if ( $this->useSimpleUI() ) {
			$tag .= $prot . $icon . $this->msg( 'revreview-quick-none' )->parse();
		// Standard UI
		} else {
			$tag .= $prot . $icon . $this->msg( 'revreview-noflagged' )->parse();
		}
	}

	/**
	 * Tag output function must be called by caller
	 * Parser cache control deferred to caller
	 * @param FlaggedRevision $srev stable version
	 * @param string $tag review box/bar info
	 * @param string $prot protection notice icon
	 * @return void
	 */
	protected function showDraftVersion( FlaggedRevision $srev, &$tag, $prot ) {
		$request = $this->getRequest();
		$reqUser = $this->getUser();
		$this->load();
		if ( $this->out->isPrintable() ) {
			return; // all this function does is add notices; don't show them
		}
		$flags = $srev->getTags();
		$time = $this->getLanguage()->date( $srev->getTimestamp(), true );
		# Get quality level
		$quality = FlaggedRevs::isQuality( $flags );
		# Get stable version sync status
		$synced = $this->article->stableVersionIsSynced();
		if ( $synced ) { // draft == stable
			$diffToggle = ''; // no diff to show
		} else { // draft != stable
			# The user may want the diff (via prefs)
			$diffToggle = $this->getTopDiffToggle( $srev, $quality );
			if ( $diffToggle != '' ) $diffToggle = " $diffToggle";
			# Make sure there is always a notice bar when viewing the draft.
			if ( $this->useSimpleUI() ) { // we already one for detailed UI
				$this->setPendingNotice( $srev, $diffToggle );
			}
		}
		# Give a "your edit is pending" notice to newer users if
		# an unreviewed edit was completed...
		if ( $request->getVal( 'shownotice' )
			&& $this->article->getUserText( Revision::RAW ) == $reqUser->getName()
			&& $this->article->revsArePending()
			&& !$reqUser->isAllowed( 'review' ) )
		{
			$revsSince = $this->article->getPendingRevCount();
			$pending = $prot;
			if ( $this->showRatingIcon() ) {
				$pending .= FlaggedRevsXML::draftStatusIcon();
			}
			$pending .= $this->msg( 'revreview-edited', $srev->getRevId(), $revsSince )->parse();
			$anchor = $request->getVal( 'fromsection' );
			if ( $anchor != null ) {
				// Hack: reverse some of the Sanitizer::escapeId() encoding
				$section = urldecode( str_replace( // bug 35661
					array( ':' , '.' ), array( '%3A', '%' ), $anchor
				) );
				$section = str_replace( '_', ' ', $section ); // prettify
				$pending .= $this->msg( 'revreview-edited-section', $anchor, $section )->parseAsBlock();
			}
			# Notice should always use subtitle
			$this->reviewNotice = "<div id='mw-fr-reviewnotice' " .
				"class='flaggedrevs_preview plainlinks noprint'>$pending</div>";
		# Otherwise, construct some tagging info for non-printable outputs.
		# Also, if low profile UI is enabled and the page is synced, skip the tag.
		# Note: the "your edit is pending" notice has all this info, so we never add both.
		} elseif ( !( $this->article->lowProfileUI() && $synced ) ) {
			$revsSince = $this->article->getPendingRevCount();
			// Simple icon-based UI
			if ( $this->useSimpleUI() ) {
				if ( !$reqUser->getId() ) {
					$msgHTML = ''; // Anons just see simple icons
				} elseif ( $synced ) {
					$msg = $quality ?
						'revreview-quick-quality-same' :
						'revreview-quick-basic-same';
					$msgHTML = $this->msg( $msg, $srev->getRevId(), $revsSince )->parse();
				} else {
					$msg = $quality ?
						'revreview-quick-see-quality' :
						'revreview-quick-see-basic';
					$msgHTML = $this->msg( $msg, $srev->getRevId(), $revsSince )->parse();
				}
				$icon = '';
				# For protection based configs, show lock only if it's not redundant.
				if ( $this->showRatingIcon() ) {
					$icon = $synced ?
						FlaggedRevsXML::stableStatusIcon( $quality ) :
						FlaggedRevsXML::draftStatusIcon();
				}
				$msgHTML = $prot . $icon . $msgHTML;
				$tag .= FlaggedRevsXML::prettyRatingBox( $srev, $msgHTML,
					$revsSince, 'draft', $synced, false );
			// Standard UI
			} else {
				if ( $synced ) {
					$msg = $quality ?
						'revreview-quality-same' :
						'revreview-basic-same';
				} else {
					$msg = $quality ?
						'revreview-newest-quality' :
						'revreview-newest-basic';
					// Messages: revreview-newest-quality-i, revreview-newest-basic-i
					$msg .= ( $revsSince == 0 ) ? '-i' : '';
				}
				$msgHTML = $this->msg( $msg, $srev->getRevId(), $time, $revsSince )->parse();
				$icon = $synced ?
					FlaggedRevsXML::stableStatusIcon( $quality ) :
					FlaggedRevsXML::draftStatusIcon();
				$tag .= $prot . $icon . $msgHTML . $diffToggle;
			}
		}
	}

	/**
	 * Tag output function must be called by caller
	 * Parser cache control deferred to caller
	 * @param FlaggedRevision $frev selected flagged revision
	 * @param string $tag review box/bar info
	 * @param string $prot protection notice icon
	 * @return ParserOutput
	 */
	protected function showOldReviewedVersion( FlaggedRevision $frev, &$tag, $prot ) {
		$reqUser = $this->getUser();
		$this->load();
		$flags = $frev->getTags();
		$time = $this->getLanguage()->date( $frev->getTimestamp(), true );
		# Set display revision ID
		$this->out->setRevisionId( $frev->getRevId() );
		# Get quality level
		$quality = FlaggedRevs::isQuality( $flags );

		# Construct some tagging for non-printable outputs. Note that the pending
		# notice has all this info already, so don't do this if we added that already.
		if ( !$this->out->isPrintable() ) {
			// Simple icon-based UI
			if ( $this->useSimpleUI() ) {
				$icon = '';
				# For protection based configs, show lock only if it's not redundant.
				if ( $this->showRatingIcon() ) {
					$icon = FlaggedRevsXML::stableStatusIcon( $quality );
				}
				$revsSince = $this->article->getPendingRevCount();
				if ( !$reqUser->getId() ) {
					$msgHTML = ''; // Anons just see simple icons
				} else {
					$msg = $quality ?
						'revreview-quick-quality-old' :
						'revreview-quick-basic-old';
					$msgHTML = $this->msg( $msg, $frev->getRevId(), $revsSince )->parse();
				}
				$msgHTML = $prot . $icon . $msgHTML;
				$tag = FlaggedRevsXML::prettyRatingBox( $frev, $msgHTML,
					$revsSince, 'oldstable', false /*synced*/ );
			// Standard UI
			} else {
				$icon = FlaggedRevsXML::stableStatusIcon( $quality );
				$msg = $quality ?
					'revreview-quality-old' :
					'revreview-basic-old';
				$tag = $prot . $icon;
				$tag .= $this->msg( $msg, $frev->getRevId(), $time )->parse();
				# Hide clutter
				if ( !empty( $flags ) ) {
					$tag .= FlaggedRevsXML::ratingToggle();
					$tag .= "<div id='mw-fr-revisiondetails'>" .
						$this->msg( 'revreview-oldrating' )->escaped() .
						FlaggedRevsXML::addTagRatings( $flags ) . '</div>';
				}
			}
		}

		# Get the new stable parser output...
		$pOpts = $this->article->makeParserOptions( $reqUser );
		$pOpts->setEditSection( false ); // old revision
		$parserOut = FlaggedRevs::parseStableRevision( $frev, $pOpts );

		# Parse and output HTML
		$redirHtml = $this->getRedirectHtml( $frev );
		if ( $redirHtml == '' ) { // page is not a redirect...
			# Add the stable output to the page view
			$this->out->addParserOutput( $parserOut );
		} else { // page is a redirect...
			$this->out->addHtml( $redirHtml );
			# Add output to set categories, displaytitle, etc.
			$this->out->addParserOutputNoText( $parserOut );
		}

		return $parserOut;
	}

	/**
	 * Tag output function must be called by caller
	 * Parser cache control deferred to caller
	 * @param \FlaggedRevision|\stable $srev stable version
	 * @param string $tag review box/bar info
	 * @param string $prot protection notice
	 * @return ParserOutput
	 */
	protected function showStableVersion( FlaggedRevision $srev, &$tag, $prot ) {
		$reqUser = $this->getUser();
		$this->load();
		$flags = $srev->getTags();
		$time = $this->getLanguage()->date( $srev->getTimestamp(), true );
		# Set display revision ID
		$this->out->setRevisionId( $srev->getRevId() );
		# Get quality level
		$quality = FlaggedRevs::isQuality( $flags );

		$synced = $this->article->stableVersionIsSynced();
		# Construct some tagging
		if ( !$this->out->isPrintable() && !( $this->article->lowProfileUI() && $synced ) ) {
			$revsSince = $this->article->getPendingRevCount();
			// Simple icon-based UI
			if ( $this->useSimpleUI() ) {
				$icon = '';
				# For protection based configs, show lock only if it's not redundant.
				if ( $this->showRatingIcon() ) {
					$icon = FlaggedRevsXML::stableStatusIcon( $quality );
				}
				if ( !$reqUser->getId() ) {
					$msgHTML = ''; // Anons just see simple icons
				} else {
					$msg = $quality ?
						'revreview-quick-quality' :
						'revreview-quick-basic';
					# Uses messages 'revreview-quick-quality-same', 'revreview-quick-basic-same'
					$msg = $synced ? "{$msg}-same" : $msg;
					$msgHTML = $this->msg( $msg, $srev->getRevId(), $revsSince )->parse();
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
				$tag .= $this->msg( $msg, $srev->getRevId(), $time, $revsSince )->parse();
				if ( !empty( $flags ) ) {
					$tag .= FlaggedRevsXML::ratingToggle();
					$tag .= "<div id='mw-fr-revisiondetails'>" .
						FlaggedRevsXML::addTagRatings( $flags ) . '</div>';
				}
			}
		}

		# Get parsed stable version and output HTML
		$pOpts = $this->article->makeParserOptions( $reqUser );
		if ( !$this->article->getTitle()->quickUserCan( 'edit', $reqUser ) ) {
			$pOpts->setEditSection( false );
		}
		$parserCache = FRParserCacheStable::singleton();
		$parserOut = $parserCache->get( $this->article, $pOpts );

		# Do not use the parser cache if it lacks mImageTimeKeys and there is a
		# chance that a review form will be added to this page (which requires the versions).
		$canReview = $this->article->getTitle()->userCan( 'review' );
		if ( $parserOut && ( !$canReview || FlaggedRevs::parserOutputIsVersioned( $parserOut ) ) ) {
			# Cache hit. Note that redirects are not cached.
			$this->out->addParserOutput( $parserOut );
		} else {
			# Get the new stable parser output...
			if ( FlaggedRevs::inclusionSetting() == FR_INCLUDES_CURRENT && $synced ) {
				# We can try the current version cache, since they are the same revision
				$parserOut = ParserCache::singleton()->get( $this->article, $pOpts );
			} else {
				$parserOut = false;
			}

			if ( !$parserOut ) {
				$parserOut = FlaggedRevs::parseStableRevision( $srev, $pOpts );
			}

			$redirHtml = $this->getRedirectHtml( $srev );
			if ( $redirHtml == '' ) { // page is not a redirect...
				# Update the stable version cache
				$parserCache->save( $parserOut, $this->article, $pOpts );
				# Add the stable output to the page view
				$this->out->addParserOutput( $parserOut );
			} else { // page is a redirect...
				$this->out->addHtml( $redirHtml );
				# Add output to set categories, displaytitle, etc.
				$this->out->addParserOutputNoText( $parserOut );
			}
			# Update the stable version dependancies
			FlaggedRevs::updateStableOnlyDeps( $this->article, $parserOut );
		}

		# Update page sync status for tracking purposes.
		# NOTE: avoids master hits and doesn't have to be perfect for what it does
		if ( $this->article->syncedInTracking() != $synced ) {
			if ( wfGetLB()->safeGetLag( wfGetDB( DB_SLAVE ) ) <= 5 ) { // avoid write-delay cycles
				$this->article->updateSyncStatus( $synced );
			}
		}

		return $parserOut;
	}

	// Get fancy redirect arrow and link HTML
	protected function getRedirectHtml( $frev ) {
		$rTargets = $frev->getRevision()->getContent()->getRedirectChain();
		if ( $rTargets ) {
			$article = new Article( $this->article->getTitle() );
			return $article->viewRedirect( $rTargets );
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
	 * Get collapsible diff-to-stable html to add to the review notice as needed
	 * @param FlaggedRevision $srev, stable version
	 * @param bool $quality, revision is quality
	 * @return string, the html line (either "" or "<diff toggle><diff div>")
	 */
	protected function getTopDiffToggle( FlaggedRevision $srev, $quality ) {
		$reqUser = $this->getUser();
		$this->load();
		if ( !$reqUser->getBoolOption( 'flaggedrevsviewdiffs' ) ) {
			return false; // nothing to do here
		}
		# Diff should only show for the draft
		$oldid = $this->getOldIDFromRequest();
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
		$leftNote = $quality ?
			'revreview-hist-quality' :
			'revreview-hist-basic';
		$lClass = FlaggedRevsXML::getQualityColor( (int)$quality );
		// @todo FIXME: i18n Hard coded brackets.
		$leftNote = "<span class='$lClass'>[" . $this->msg( $leftNote )->escaped() . "]</span>";
		# Review status of right diff revision...
		$rClass = FlaggedRevsXML::getQualityColor( false );
		// @todo FIXME: i18n Hard coded brackets.
		$rightNote = "<span class='$rClass'>[" .
			$this->msg( 'revreview-hist-pending' )->escaped() . "]</span>";
		# Get the actual body of the diff...
		$diffEngine = new DifferenceEngine( $title, $srev->getRevId(), $latest );
		$diffBody = $diffEngine->getDiffBody();
		if ( strlen( $diffBody ) > 0 ) {
			$nEdits = $revsSince - 1; // full diff-to-stable, no need for query
			if ( $nEdits ) {
				$limit = 100;
				$nUsers = $title->countAuthorsBetween( $srev->getRevId(), $latest, $limit );
				$multiNotice = DifferenceEngine::intermediateEditsMsg( $nEdits, $nUsers, $limit );
			} else {
				$multiNotice = '';
			}
			$diffEngine->showDiffStyle(); // add CSS
			$this->isDiffFromStable = true; // alter default review form tags
			return
				FlaggedRevsXML::diffToggle() .
				"<div id='mw-fr-stablediff'>\n" .
				$this->getFormattedDiff( $diffBody, $multiNotice, $leftNote, $rightNote ) .
				"</div>\n";
		}
		return '';
	}

	// $n number of in-between revs
	protected function getFormattedDiff(
		$diffBody, $multiNotice, $leftStatus, $rightStatus
	) {
		$tableClass = 'diff diff-contentalign-' .
			htmlspecialchars( $this->getTitle()->getPageLanguage()->alignStart() );
		if ( $multiNotice != '' ) {
			$multiNotice = "<tr><td colspan='4' align='center' class='diff-multi'>" .
				$multiNotice . "</td></tr>";
		}
		return
			"<table border='0' width='98%' cellpadding='0' cellspacing='4' class='$tableClass'>" .
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
	 * Depends on $request
	 */
	public function imagePageFindFile( &$normalFile, &$displayFile ) {
		$request = $this->getRequest();
		$this->load();
		# Determine timestamp. A reviewed version may have explicitly been requested...
		$frev = null;
		$time = false;
		$reqId = $request->getVal( 'stableid' );
		if ( $reqId ) {
			$frev = FlaggedRevision::newFromTitle( $this->article->getTitle(), $reqId );
		} elseif ( $this->showingStable() ) {
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
		}
		if ( !$time ) {
			# Try request parameter
			$time = $request->getVal( 'filetimestamp', false );
		}

		if ( !$time ) {
			return; // Use the default behavior
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
		$this->load();
		# Add a notice if there are pending edits...
		$srev = $this->article->getStableRev();
		if ( $srev && $this->article->revsArePending() ) {
			$revsSince = $this->article->getPendingRevCount();
			$tag = "<div id='mw-fr-revisiontag-edit' class='flaggedrevs_notice plainlinks'>" .
				FlaggedRevsXML::lockStatusIcon( $this->article ) . # flag protection icon as needed
				FlaggedRevsXML::pendingEditNotice( $this->article, $srev, $revsSince ) . "</div>";
			$this->out->addHTML( $tag );
		}
		return true;
	}

	public function getEditNotices( Title $title, $oldid, array &$notices ) {
		// HACK: EditPage invokes addToEditView() before this function, so $this->noticesDone
		// will only be true if we're being called by EditPage, in which case we need to do nothing
		// to avoid duplicating the notices.
		$this->load();
		if ( $this->noticesDone || !$this->article->isReviewable() ) {
			return;
		}
		// HACK fake EditPage
		$editPage = new EditPage( new Article( $title, $oldid ) );
		$editPage->oldid = $oldid;
		$reqUser = $this->getUser();

		// HACK this duplicates logic from addToEditView()
		$log = $this->stabilityLogNotice( false );
		if ( $log ) {
			$notices[$this->article->isPageLocked() ? 'revreview-locked' : 'revreview-unlocked'] = $log;
		} else if ( $this->editWillRequireReview( $editPage ) ) {
			$notices['revreview-editnotice'] = $this->msg( 'revreview-editnotice' )->parseAsBlock();
		}
		$frev = $this->article->getStableRev();
		if ( $frev && $this->article->revsArePending() ) {
			$revsSince = $this->article->getPendingRevCount();
			$pendingMsg = FlaggedRevsXML::pendingEditNoticeMessage( $this->article, $frev, $revsSince );
			$notices[$pendingMsg->getKey()] = '<div class="plainlinks">' . $pendingMsg->parseAsBlock() . '</div>';
		}
		$latestId = $this->article->getLatest();
		$revId  = $oldid ? $oldid : $latestId;
		if ( $frev && $frev->getRevId() < $latestId // changes were made
			&& $reqUser->getBoolOption( 'flaggedrevseditdiffs' ) // not disabled via prefs
			&& $revId === $latestId // only for current rev
		) {
			// Construct a link to the diff
			$diffUrl = $this->article->getTitle()->getFullURL( array(
				'diff' => $revId, 'oldid' => $frev->getRevId() )
			);
			$notices['review-edit-diff'] = $this->msg( 'review-edit-diff' )->parse() . ' ' .
				FlaggedRevsXML::diffToggle( $diffUrl );
		}
	}

	/**
	 * Adds stable version tags to page when editing
	 */
	public function addToEditView( EditPage $editPage ) {
		global $wgParser;
		$reqUser = $this->getUser();
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
			# Let users know if their edit will have to be reviewed.
			# Note: if the log excerpt was shown then this is redundant.
			if ( !$log && $this->editWillRequireReview( $editPage ) ) {
				$items[] = $this->msg( 'revreview-editnotice' )->parse();
			}
			# Add a notice if there are pending edits...
			if ( $this->article->revsArePending() ) {
				$revsSince = $this->article->getPendingRevCount();
				$items[] = FlaggedRevsXML::pendingEditNotice( $this->article, $frev, $revsSince );
			}
			# Show diff to stable, to make things less confusing.
			# This can be disabled via user preferences and other conditions...
			if ( $frev->getRevId() < $latestId // changes were made
				&& $reqUser->getBoolOption( 'flaggedrevseditdiffs' ) // not disable via prefs
				&& $revId == $latestId // only for current rev
				&& $editPage->section != 'new' // not for new sections
				&& $editPage->formtype != 'diff' // not "show changes"
			) {
				# Left diff side...
				$leftNote = $quality ?
					'revreview-hist-quality' :
					'revreview-hist-basic';
				$lClass = FlaggedRevsXML::getQualityColor( (int)$quality );
				// @todo i18n FIXME: Hard coded brackets
				$leftNote = "<span class='$lClass'>[" .
					$this->msg( $leftNote )->escaped() . "]</span>";
				# Right diff side...
				$rClass = FlaggedRevsXML::getQualityColor( false );
				// @todo i18n FIXME: Hard coded brackets
				$rightNote = "<span class='$rClass'>[" .
					$this->msg( 'revreview-hist-pending' )->escaped() . "]</span>";
				# Get the stable version source
				$text = $frev->getRevText();
				# Are we editing a section?
				$section = ( $editPage->section == "" ) ?
					false : intval( $editPage->section );
				if ( $section !== false ) {
					$text = $wgParser->getSection( $text, $section );
				}
				if ( $text !== false && strcmp( $text, $editPage->textbox1 ) !== 0 ) {
					$diffEngine = new DifferenceEngine( $this->article->getTitle() );
					$diffBody = $diffEngine->generateDiffBody( $text, $editPage->textbox1 );
					$diffHtml =
						$this->msg( 'review-edit-diff' )->parse() . ' ' .
						FlaggedRevsXML::diffToggle() .
						"<div id='mw-fr-stablediff'>" .
						$this->getFormattedDiff( $diffBody, '', $leftNote, $rightNote ) .
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
				$this->out->addHTML( $html );
			}
		}
		$this->noticesDone = true;
		return true;
	}

	protected function stabilityLogNotice( $showToggle = true ) {
		$this->load();
		$s = '';
		# Only for pages manually made to be stable...
		if ( $this->article->isPageLocked() ) {
			$s = $this->msg( 'revreview-locked' )->parse();
			if ( $showToggle ) {
				$s .= ' ' . FlaggedRevsXML::logDetailsToggle();
			}
			$s .= FlaggedRevsXML::stabilityLogExcerpt( $this->article );
		# ...or unstable
		} elseif ( $this->article->isPageUnlocked() ) {
			$s = $this->msg( 'revreview-unlocked' )->parse();
			if ( $showToggle ) {
				$s .= ' ' . FlaggedRevsXML::logDetailsToggle();
			}
			$s .= FlaggedRevsXML::stabilityLogExcerpt( $this->article );
		}
		return $s;
	}

	public function addToNoSuchSection( EditPage $editPage, &$s ) {
		$this->load();
		$srev = $this->article->getStableRev();
		# Add notice for users that may have clicked "edit" for a
		# section in the stable version that isn't in the draft.
		if ( $srev && $this->article->revsArePending() ) {
			$revsSince = $this->article->getPendingRevCount();
			if ( $revsSince ) {
				$s .= "<div class='flaggedrevs_editnotice plainlinks'>" .
					$this->msg( 'revreview-pending-nosection',
						$srev->getRevId(), $revsSince )->parse() . "</div>";
			}
		}
		return true;
	}

	/**
	 * Add unreviewed pages links
	 */
	public function addToCategoryView() {
		$reqUser = $this->getUser();
		$this->load();
		if ( !$reqUser->isAllowed( 'review' ) ) {
			return true;
		}
		if ( !FlaggedRevs::useOnlyIfProtected() ) {
			# Add links to lists of unreviewed pages and pending changes in this category
			$category = $this->article->getTitle()->getText();
			$this->out->appendSubtitle(
				Html::rawElement(
					'span',
					array( 'class' => 'plainlinks', 'id' => 'mw-fr-category-oldreviewed' ),
					$this->msg( 'flaggedrevs-categoryview', urlencode( $category ) )->parse()
				)
			);
		}
		return true;
	}

	/**
	 * Add review form to pages when necessary on a regular page view (action=view).
	 * If $output is an OutputPage then this prepends the form onto it.
	 * If $output is a string then this appends the review form to it.
	 * @param mixed string|OutputPage
	 * @return bool
	 */
	public function addReviewForm( &$output ) {
		$request = $this->getRequest();
		$reqUser = $this->getUser();
		$this->load();
		if ( $this->out->isPrintable() ) {
			return false; // Must be on non-printable output
		}
		# User must have review rights
		if ( !$reqUser->isAllowed( 'review' ) ) {
			return true;
		}
		# Page must exist and be reviewable
		if ( !$this->article->exists() || !$this->article->isReviewable() ) {
			return true;
		}
		# Must be a page view action...
		if ( !$this->isPageViewOrDiff( $request ) ) {
			return true;
		}
		# Get the revision being displayed
		$rev = false;
		if ( $this->reviewFormRev ) { // diff
			$rev = $this->reviewFormRev; // $newRev for diffs stored here
		} elseif ( $this->out->getRevisionId() ) { // page view
			$rev = Revision::newFromId( $this->out->getRevisionId() );
		}
		# Build the review form as needed
		if ( $rev && ( !$this->diffRevs || $this->isReviewableDiff ) ) {
			$form = new RevisionReviewFormUI( $this->getContext(), $this->article, $rev );
			# Default tags and existence of "reject" button depend on context
			if ( $this->diffRevs ) {
				$form->setDiffPriorRev( $this->diffRevs['old'] );
			}
			# Review notice box goes in top of form
			$form->setTopNotice( $this->diffNoticeBox );
			$form->setBottomNotice( $this->diffIncChangeBox );

			# Set the file version we are viewing (for File: pages)
			$form->setFileVersion( $this->out->getFileVersion() );
			# $wgOut may not already have the inclusion IDs, such as for diffonly=1.
			# fr_unversionedIncludes indicates that ParserOutput added to $wgOut lacked inclusion IDs.
			# If they're lacking, then we use getRevIncludes() to get the draft inclusion versions.
			# Note: showStableVersion() already makes sure that $wgOut has the stable inclusion versions.
			if ( $this->out->getRevisionId() == $rev->getId() && empty( $this->out->fr_unversionedIncludes ) ) {
				$tmpVers = $this->out->getTemplateIds();
				$fileVers = $this->out->getFileSearchOptions();
			} elseif ( $this->oldRevIncludes ) { // e.g. diffonly=1, stable diff
				# We may have already fetched the inclusion IDs to get the template/file changes.
				list( $tmpVers, $fileVers ) = $this->oldRevIncludes; // reuse
			} else { // e.g. diffonly=1, other diffs
				# $wgOut may not already have the inclusion IDs, such as for diffonly=1.
				# RevisionReviewForm will fetch them as needed however.
				list( $tmpVers, $fileVers ) =
					FRInclusionCache::getRevIncludes( $this->article, $rev, $reqUser );
			}
			$form->setIncludeVersions( $tmpVers, $fileVers );

			list( $html, $status ) = $form->getHtml();
			# Diff action: place the form at the top of the page
			if ( $output instanceof OutputPage ) {
				$output->prependHTML( $html );
			# View action: place the form at the bottom of the page
			} else {
				$output .= $html;
			}
		}
		return true;
	}

	/**
	 * Add link to stable version setting to protection form
	 */
	public function addStabilizationLink() {
		$request = $this->getRequest();
		$this->load();
		if ( FlaggedRevs::useProtectionLevels() ) {
			return true; // simple custom levels set for action=protect
		}
		# Check only if the title is reviewable
		if ( !FlaggedRevs::inReviewNamespace( $this->article->getTitle() ) ) {
			return true;
		}
		$action = $request->getVal( 'action', 'view' );
		if ( $action == 'protect' || $action == 'unprotect' ) {
			$title = SpecialPage::getTitleFor( 'Stabilization' );
			# Give a link to the page to configure the stable version
			$frev = $this->article->getStableRev();
			if ( $frev && $frev->getRevId() == $this->article->getLatest() ) {
				$this->out->prependHTML( "<span class='revreview-visibility revreview-visibility-synced plainlinks'>" .
					$this->msg( 'revreview-visibility-synced',
						$title->getPrefixedText() )->parse() . "</span>" );
			} elseif ( $frev ) {
				$this->out->prependHTML( "<span class='revreview-visibility revreview-visibility-outdated plainlinks'>" .
					$this->msg( 'revreview-visibility-outdated',
						$title->getPrefixedText() )->parse() . "</span>" );
			} else {
				$this->out->prependHTML( "<span class='revreview-visibility revreview-visibility-nostable plainlinks'>" .
					$this->msg( 'revreview-visibility-nostable',
						$title->getPrefixedText() )->parse() . "</span>" );
			}
		}
		return true;
	}

	/**
	 * Modify an array of action links, as used by SkinTemplateNavigation and
	 * SkinTemplateTabs, to inlude flagged revs UI elements
	 */
	public function setActionTabs( $skin, array &$actions ) {
		$reqUser = $this->getUser();
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
			!$this->article->getTitle()->isTalkPage() &&
			is_array( $actions ) &&
			!isset( $actions['protect'] ) &&
			!isset( $actions['unprotect'] ) &&
			$reqUser->isAllowed( 'stablesettings' ) &&
			$title->exists() )
		{
			$stableTitle = SpecialPage::getTitleFor( 'Stabilization' );
			// Add the tab
			$actions['default'] = array(
				'class' => false,
				'text' => $this->msg( 'stabilization-tab' )->text(),
				'href' => $stableTitle->getLocalUrl( 'page=' . $title->getPrefixedUrl() )
			);
		}
		return true;
	}

	/**
	 * Modify an array of tab links to include flagged revs UI elements
	 * @param Skin $skin
	 * @param array $views
	 * @param string $type ('flat' for SkinTemplateTabs, 'nav' for SkinTemplateNavigation)
	 * @return bool
	 */
	public function setViewTabs( Skin $skin, array &$views, $type ) {
		$this->load();
		if ( !FlaggedRevs::inReviewNamespace( $this->article->getTitle() ) ) {
			return true; // short-circuit for non-reviewable pages
		}
		# Hack for bug 16734 (some actions update and view all at once)
		if ( $this->pageWriteOpRequested() && wfGetDB( DB_MASTER )->doneWrites() ) {
			# Tabs need to reflect the new stable version so users actually
			# see the results of their action (i.e. "delete"/"rollback")
			$this->article->loadPageData( 'fromdbmaster' );
		}
		$srev = $this->article->getStableRev();
		if ( !$srev ) {
			return true; // No stable revision exists
		}
		$synced = $this->article->stableVersionIsSynced();
		$pendingEdits = !$synced && $this->article->isStableShownByDefault();
		// Set the edit tab names as needed...
		if ( $pendingEdits ) {
			if ( isset( $views['edit'] ) ) {
				$views['edit']['text'] = $this->msg( 'revreview-edit' )->text();
				if ( $this->showingStable() ) { // bug 31489; direct user to current
					$views['edit']['href'] = $skin->getTitle()->getFullURL( 'action=edit' );
				}
			}
			if ( isset( $views['viewsource'] ) ) {
				$views['viewsource']['text'] = $this->msg( 'revreview-source' )->text();
				if ( $this->showingStable() ) { // bug 31489; direct user to current
					$views['viewsource']['href'] = $skin->getTitle()->getFullURL( 'action=edit' );
				}
			}
		}
		# Add "pending changes" tab if the page is not synced
		if ( !$synced ) {
			$this->addDraftTab( $views, $srev, $type );
		}
		return true;
	}

	// Add "pending changes" tab and set tab selection CSS
	protected function addDraftTab( array &$views, FlaggedRevision $srev, $type ) {
		$request = $this->getRequest();
		$title = $this->article->getTitle(); // convenience
		$tabs = array(
			'read' => array( // view stable
				'text'  => '', // unused
				'href'  => $title->getLocalUrl( 'stable=1' ),
				'class' => ''
			),
			'draft' => array( // view draft
				'text'  => $this->msg( 'revreview-current' )->text(),
				'href'  => $title->getLocalUrl( 'stable=0&redirect=no' ),
				'class' => 'collapsible'
			),
		);
		// Set tab selection CSS
		if ( $this->showingStable() || $request->getVal( 'stableid' ) ) {
			// We are looking a the stable version or an old reviewed one
			$tabs['read']['class'] = 'selected';
		} elseif ( $this->isPageViewOrDiff( $request ) ) {
			$ts = null;
			if ( $this->out->getRevisionId() ) { // @TODO: avoid same query in Skin.php
				$ts = ( $this->out->getRevisionId() == $this->article->getLatest() ) ?
					$this->article->getTimestamp() : // skip query
					Revision::getTimestampFromId( $title, $this->out->getRevisionId() );
			}
			// Are we looking at a pending revision?
			if ( $ts > $srev->getRevTimestamp() ) { // bug 15515
				$tabs['draft']['class'] .= ' selected';
			// Are there *just* pending template/file changes.
			} elseif ( $this->article->onlyTemplatesOrFilesPending()
				&& $this->out->getRevisionId() == $this->article->getStable() )
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
	 * Check if a flaggedrevs relevant write op was done this page view
	 * @return bool
	 */
	protected function pageWriteOpRequested() {
		$request = $this->getRequest();
		# Hack for bug 16734 (some actions update and view all at once)
		$action = $request->getVal( 'action' );
		if ( $action === 'rollback' ) {
			return true;
		} elseif ( $action === 'delete' && $request->wasPosted() ) {
			return true;
		}
		return false;
	}

	protected function getOldIDFromRequest() {
		$article = new Article( $this->article->getTitle() );
		return $article->getOldIDFromRequest();
	}

	/**
	 * Adds a notice saying that this revision is pending review
	 * @param FlaggedRevision $srev The stable version
	 * @param string $diffToggle either "" or " <diff toggle><diff div>"
	 * @return void
	 */
	public function setPendingNotice( FlaggedRevision $srev, $diffToggle = '' ) {
		$this->load();
		$time = $this->getLanguage()->date( $srev->getTimestamp(), true );
		$revsSince = $this->article->getPendingRevCount();
		$msg = $srev->getQuality() ?
			'revreview-newest-quality' :
			'revreview-newest-basic';
		$msg .= ( $revsSince == 0 ) ? '-i' : '';
		# Add bar msg to the top of the page...
		$css = 'flaggedrevs_preview plainlinks';
		// Messages: revreview-newest-quality-i, revreview-newest-basic-i
		$msgHTML = $this->msg( $msg, $srev->getRevId(), $time, $revsSince )->parse();
		$this->reviewNotice .= "<div id='mw-fr-reviewnotice' class='$css'>" .
			"$msgHTML$diffToggle</div>";
	}

	/**
	 * When viewing a diff:
	 * (a) Add the review form to the top of the page
	 * (b) Mark off which versions are checked or not
	 * (c) When comparing the stable revision to the current:
	 *   (i)  Show a tag with some explanation for the diff
	 *   (ii) List any template/file changes pending review
	 */
	public function addToDiffView( $diff, $oldRev, $newRev ) {
		global $wgMemc, $wgParserCacheExpireTime;
		$request = $this->getRequest();
		$reqUser = $this->getUser();
		$this->load();
		# Exempt printer-friendly output
		if ( $this->out->isPrintable() ) {
			return true;
		# Multi-page diffs are useless and misbehave (bug 19327). Sanity check $newRev.
		} elseif ( $this->isMultiPageDiff || !$newRev ) {
			return true;
		# Page must be reviewable.
		} elseif ( !$this->article->isReviewable() ) {
			return true;
		}
		$srev = $this->article->getStableRev();
		if ( $srev && $this->isReviewableDiff ) {
			$this->reviewFormRev = $newRev;
		}
		# Check if this is a diff-to-stable. If so:
		# (a) prompt reviewers to review the changes
		# (b) list template/file changes if only includes are pending
		if ( $srev
			&& $this->isDiffFromStable
			&& !$this->article->stableVersionIsSynced() ) // pending changes
		{
			$changeText = '';
			# Page not synced only due to includes?
			if ( !$this->article->revsArePending() ) {
				# Add a list of links to each changed template...
				$changeList = self::fetchTemplateChanges( $srev );
				# Add a list of links to each changed file...
				$changeList = array_merge( $changeList, self::fetchFileChanges( $srev ) );
				# Correct bad cache which said they were not synced...
				if ( !count( $changeList ) ) {
					$key = wfMemcKey( 'flaggedrevs', 'includesSynced', $this->article->getId() );
					$data = FlaggedRevs::makeMemcObj( "true" );
					$wgMemc->set( $key, $data, $wgParserCacheExpireTime );
				}
			# Otherwise, check for includes pending on top of edits pending...
			} else {
				$incs = FRInclusionCache::getRevIncludes( $this->article, $newRev, $reqUser );
				$this->oldRevIncludes = $incs; // process cache
				# Add a list of links to each changed template...
				$changeList = self::fetchTemplateChanges( $srev, $incs[0] );
				# Add a list of links to each changed file...
				$changeList = array_merge( $changeList, self::fetchFileChanges( $srev, $incs[1] ) );
			}
			# If there are pending revs or templates/files changes, notify the user...
			if ( $this->article->revsArePending() || count( $changeList ) ) {
				# If the user can review then prompt them to review them...
				if ( $reqUser->isAllowed( 'review' ) ) {
					// Reviewer just edited...
					if ( $request->getInt( 'shownotice' )
						&& $newRev->isCurrent()
						&& $newRev->getRawUserText() == $reqUser->getName() )
					{
						$title = $this->article->getTitle(); // convenience
						// @TODO: make diff class cache this
						$n = $title->countRevisionsBetween( $oldRev, $newRev );
						if ( $n ) {
							$msg = 'revreview-update-edited-prev'; // previous pending edits
						} else {
							$msg = 'revreview-update-edited'; // just couldn't autoreview
						}
					// All other cases...
					} else {
						$msg = 'revreview-update'; // generic "please review" notice...
					}
					$this->diffNoticeBox = $this->msg( $msg )->parseAsBlock(); // add as part of form
				}
				# Add include change list...
				if ( count( $changeList ) ) { // just inclusion changes
					$changeText .= "<p>" .
						$this->msg( 'revreview-update-includes' )->parse() .
						'&#160;' . implode( ', ', $changeList ) . "</p>\n";
				}
			}
			# template/file change list
			if ( $changeText != '' ) {
				if ( $reqUser->isAllowed( 'review' ) ) {
					$this->diffIncChangeBox = "<p>$changeText</p>";
				} else {
					$css = 'flaggedrevs_diffnotice plainlinks';
					$this->out->addHTML(
						"<div id='mw-fr-difftostable' class='$css'>$changeText</div>\n"
					);
				}
			}
		}
		# Add a link to diff from stable to current as needed.
		# Show review status of the diff revision(s). Uses a <table>.
		$this->out->addHTML(
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
				$fa = FlaggableWikiPage::getTitleInstance( $newRev->getTitle() );
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
	public static function diffLinkAndMarkers( FlaggableWikiPage $article, $oldRev, $newRev ) {
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
		FlaggableWikiPage $article, $oldRev, Revision $newRev
	) {
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
			$review = Linker::linkKnown(
				$article->getTitle(),
				wfMessage( 'review-diff2stable' )->escaped(),
				array(),
				array( 'oldid' => $srev->getRevId(), 'diff' => 'cur' ) + FlaggedRevs::diffOnlyCGI()
			);
			$review = wfMessage( 'parentheses' )->rawParams( $review )->escaped();
			$review = "<div class='fr-diff-to-stable' align='center'>$review</div>";
		}
		return $review;
	}

	/**
	 * Add [checked version] and such to left and right side of diff
	 */
	protected static function diffReviewMarkers( FlaggableWikiPage $article, $oldRev, $newRev ) {
		$table = '';
		$srev = $article->getStableRev();
		# Diff between two revisions
		if ( $oldRev && $newRev ) {
			list( $msg, $class ) = self::getDiffRevMsgAndClass( $oldRev, $srev );
			$table .= "<table class='fr-diff-ratings'><tr>";
			$table .= "<td width='50%' align='center'>";
			// @todo i18n FIXME: Hard coded brackets
			$table .= "<span class='$class'>[" .
				wfMessage( $msg )->escaped() . "]</span>";

			list( $msg, $class ) = self::getDiffRevMsgAndClass( $newRev, $srev );
			$table .= "</td><td width='50%' align='center'>";
			// @todo i18n FIXME: Hard coded brackets
			$table .= "<span class='$class'>[" .
				wfMessage( $msg )->escaped() . "]</span>";

			$table .= "</td></tr></table>\n";
		# New page "diffs" - just one rev
		} elseif ( $newRev ) {
			list( $msg, $class ) = self::getDiffRevMsgAndClass( $newRev, $srev );
			$table .= "<table class='fr-diff-ratings'>";
			$table .= "<tr><td align='center'><span class='$class'>";
			// @todo i18n FIXME: Hard coded brackets
			$table .= '[' . wfMessage( $msg )->escaped() . ']';
			$table .= "</span></td></tr></table>\n";
		}
		return $table;
	}

	protected static function getDiffRevMsgAndClass(
		Revision $rev, FlaggedRevision $srev = null
	) {
		$tier = FlaggedRevision::getRevQuality( $rev->getId() );
		if ( $tier !== false ) {
			$msg = $tier ?
				'revreview-hist-quality' :
				'revreview-hist-basic';
		} else {
			$msg = ( $srev && $rev->getTimestamp() > $srev->getRevTimestamp() ) ? // bug 15515
				'revreview-hist-pending' :
				'revreview-hist-draft';
		}
		$css = FlaggedRevsXML::getQualityColor( $tier );
		return array( $msg, $css );
	}

	// Fetch template changes for a reviewed revision since review
	// @return array
	protected static function fetchTemplateChanges( FlaggedRevision $frev, $newTemplates = null ) {
		$diffLinks = array();
		if ( $newTemplates === null ) {
			$changes = $frev->findPendingTemplateChanges();
		} else {
			$changes = $frev->findTemplateChanges( $newTemplates );
		}
		foreach ( $changes as $tuple ) {
			list( $title, $revIdStable, $hasStable ) = $tuple;
			$link = Linker::linkKnown(
				$title,
				htmlspecialchars( $title->getPrefixedText() ),
				array(),
				array( 'diff' => 'cur', 'oldid' => $revIdStable ) );
			if ( !$hasStable ) {
				$link = "<strong>$link</strong>";
			}
			$diffLinks[] = $link;
		}
		return $diffLinks;
	}

	// Fetch file changes for a reviewed revision since review
	// @return array
	protected static function fetchFileChanges( FlaggedRevision $frev, $newFiles = null ) {
		$diffLinks = array();
		if ( $newFiles === null ) {
			$changes = $frev->findPendingFileChanges( 'noForeign' );
		} else {
			$changes = $frev->findFileChanges( $newFiles, 'noForeign' );
		}
		foreach ( $changes as $tuple ) {
			list( $title, $revIdStable, $hasStable ) = $tuple;
			// @TODO: change when MW has file diffs
			$link = Linker::link( $title, htmlspecialchars( $title->getPrefixedText() ) );
			if ( !$hasStable ) {
				$link = "<strong>$link</strong>";
			}
			$diffLinks[] = $link;
		}
		return $diffLinks;
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
			$this->diffRevs = array( 'old' => $oldRev, 'new' => $newRev );
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
		$reqUser = $this->getUser();
		$this->load();
		$this->article->loadPageData( 'fromdbmaster' );
		# Get the stable version from the master
		$frev = $this->article->getStableRev();
		if ( !$frev || !$this->article->revsArePending() ) {
			return true; // only for pages with pending edits
		}
		$params = array();
		// If the edit was not autoreviewed, and the user can actually make a
		// new stable version, then go to the diff...
		if ( $frev->userCanSetFlags( $reqUser ) ) {
			$params += array( 'oldid' => $frev->getRevId(), 'diff' => 'cur', 'shownotice' => 1 );
			$params += FlaggedRevs::diffOnlyCGI();
		// ...otherwise, go to the draft revision after completing an edit.
		// This allows for users to immediately see their changes.
		} else {
			$params += array( 'stable' => 0 );
			// Show a notice at the top of the page for non-reviewers...
			if ( !$reqUser->isAllowed( 'review' ) && $this->article->isStableShownByDefault() ) {
				$params += array( 'shownotice' => 1 );
				if ( $sectionAnchor ) {
					// Pass a section parameter in the URL as needed to add a link to
					// the "your changes are pending" box on the top of the page...
					$params += array( 'fromsection' => substr( $sectionAnchor, 1 ) ); // strip #
					$sectionAnchor = ''; // go to the top of the page to see notice
				}
			}
		}
		if ( $extraQuery !== '' ) {
			$extraQuery .= '&';
		}
		$extraQuery .= wfArrayToCgi( $params ); // note: EditPage will add initial "&"
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
				$input->setAttribute( 'value', $this->msg( 'revreview-submitedit' )->text() );
				// @todo i18n FIXME: Hard coded brackets.
				$input->setAttribute( 'title', // keep accesskey
					$this->msg( 'revreview-submitedit-title' )->text() .
						' [' . $this->msg( 'accesskey-save' )->text() . ']' );
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
		$request = $this->getRequest(); // convenience
		$title = $this->article->getTitle(); // convenience
		if ( !$this->editRequiresReview( $editPage ) ) {
			return false; // edit will go live immediately
		} elseif ( $request->getCheck( 'wpReviewEdit' ) && $title->userCan( 'review' ) ) {
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
		if ( $title->quickUserCan( 'autoreview' ) ) {
			if ( FlaggedRevs::autoReviewNewPages() && !$this->article->exists() ) {
				return true; // edit will be autoreviewed
			}
			if ( !isset( $editPage->fr_baseFRev ) ) {
				$baseRevId = self::getBaseRevId( $editPage, $this->getRequest() );
				$baseRevId2 = self::getAltBaseRevId( $editPage, $this->getRequest() );
				$editPage->fr_baseFRev = FlaggedRevision::newFromTitle( $title, $baseRevId );
				if ( !$editPage->fr_baseFRev && $baseRevId2 ) {
					$editPage->fr_baseFRev = FlaggedRevision::newFromTitle( $title, $baseRevId2 );
				}
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
		$this->load();
		$request = $this->getRequest();
		$title = $this->article->getTitle(); // convenience
		if ( !$this->article->isReviewable() || !$title->userCan( 'review' ) ) {
			return true; // not needed
		} elseif ( $this->editWillBeAutoreviewed( $editPage ) ) {
			return true; // edit will be auto-reviewed
		}
		if ( self::getBaseRevId( $editPage, $request ) == $this->article->getLatest() ) {
			# For pages with either no stable version, or an outdated one, let
			# the user decide if he/she wants it reviewed on the spot. One might
			# do this if he/she just saw the diff-to-stable and *then* decided to edit.
			# Note: check not shown when editing old revisions, which is confusing.
			$checkbox = Xml::check(
				'wpReviewEdit',
				$request->getCheck( 'wpReviewEdit' ),
				array( 'tabindex' => ++$tabindex, 'id' => 'wpReviewEdit' )
			);
			$attribs = array( 'for' => 'wpReviewEdit' );
			// For reviewed pages...
			if ( $this->article->getStable() ) {
				// For pending changes...
				if ( $this->article->revsArePending() ) {
					$n = $this->article->getPendingRevCount();
					$attribs['title'] = $this->msg( 'revreview-check-flag-p-title' )->text();
					$labelMsg = $this->msg( 'revreview-check-flag-p', $n )->parse();
				// For just the user's changes...
				} else {
					$attribs['title'] = $this->msg( 'revreview-check-flag-y-title' )->parse();
					$labelMsg = $this->msg( 'revreview-check-flag-y' )->parse();
				}
			// For unreviewed pages...
			} else {
				$attribs['title'] = $this->msg( 'revreview-check-flag-u-title' )->text();
				$labelMsg = $this->msg( 'revreview-check-flag-u' )->parse();
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
		$out->addHTML( "\n" . Html::hidden( 'baseRevId',
			self::getBaseRevId( $editPage, $this->getRequest() ) ) );
		$out->addHTML( "\n" . Html::hidden( 'altBaseRevId',
			self::getAltBaseRevId( $editPage, $this->getRequest() ) ) );
		$out->addHTML( "\n" . Html::hidden( 'undidRev',
			empty( $editPage->undidRev ) ? 0 : $editPage->undidRev )
		);
	}

	/**
	 * Guess the rev ID the text of this form is based off
	 * Note: baseRevId trusted for Reviewers - check text for others.
	 * @param EditPage $editPage
	 * @param WebRequest $request
	 * @return int
	 */
	protected static function getBaseRevId( EditPage $editPage, WebRequest $request ) {
		if ( $editPage->isConflict ) {
			return 0; // throw away these values (bug 33481)
		}
		if ( !isset( $editPage->fr_baseRevId ) ) {
			$article = $editPage->getArticle(); // convenience
			$latestId = $article->getLatest(); // current rev
			# Undoing edits...
			if ( $request->getIntOrNull( 'undo' ) ) {
				$revId = $latestId; // current rev is the base rev
			# Other edits...
			} else {
				# If we are editing via oldid=X, then use that rev ID.
				# Otherwise, check if the client specified the ID (bug 23098).
				$revId = $article->getOldID() ?
					$article->getOldID() :
					$request->getInt( 'baseRevId' ); // e.g. "show changes"/"preview"
			}
			# Zero oldid => draft revision
			$editPage->fr_baseRevId = $revId ?: $latestId;
		}
		return $editPage->fr_baseRevId;
	}

	/**
	 * Guess the alternative rev ID the text of this form is based off.
	 * When undoing the top X edits, the base can be though of as either
	 * the current or the edit X edits prior to the latest.
	 * Note: baseRevId trusted for Reviewers - check text for others.
	 * @param EditPage $editPage
	 * @param WebRequest $request
	 * @return int
	 */
	protected static function getAltBaseRevId( EditPage $editPage, WebRequest $request ) {
		if ( $editPage->isConflict ) {
			return 0; // throw away these values (bug 33481)
		}
		if ( !isset( $editPage->fr_altBaseRevId ) ) {
			$article = $editPage->getArticle(); // convenience
			$latestId = $article->getLatest(); // current rev
			$undo = $request->getIntOrNull( 'undo' );
			# Undoing consecutive top edits...
			if ( $undo && $undo === $latestId ) {
				# Treat this like a revert to a base revision.
				# We are undoing all edits *after* some rev ID (undoafter).
				# If undoafter is not given, then it is the previous rev ID.
				$revId = $request->getInt( 'undoafter',
					$article->getTitle()->getPreviousRevisionID( $latestId, Title::GAID_FOR_UPDATE ) );
			} else {
				$revId = $request->getInt( 'altBaseRevId' );
			}
			$editPage->fr_altBaseRevId = $revId;
		}
		return $editPage->fr_altBaseRevId;
	}
}
