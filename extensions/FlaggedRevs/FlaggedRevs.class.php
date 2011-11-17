<?php
/**
 * Class containing utility functions for a FlaggedRevs environment
 *
 * Class is lazily-initialized, calling load() as needed
 */
class FlaggedRevs {
	# Tag name/level config
	protected static $dimensions = array();
	protected static $minSL = array();
	protected static $minQL = array();
	protected static $minPL = array();
	protected static $qualityVersions = false;
	protected static $pristineVersions = false;
	protected static $tagRestrictions = array();
	protected static $binaryFlagging = true;
	# Namespace config
	protected static $reviewNamespaces = array();
	protected static $patrolNamespaces = array();
	# Restriction levels/config
	protected static $restrictionLevels = array();
	
	protected static $loaded = false;

	public static function load() {
		global $wgFlaggedRevsTags, $wgFlaggedRevTags;
		if ( self::$loaded ) {
			return true;
		}
		self::$loaded = true;
		$flaggedRevsTags = null;
		if ( isset( $wgFlaggedRevTags ) ) {
			$flaggedRevsTags = $wgFlaggedRevTags; // b/c
			wfWarn( 'Please use $wgFlaggedRevsTags instead of $wgFlaggedRevTags in config.' );
		} elseif ( isset( $wgFlaggedRevsTags ) ) {
			$flaggedRevsTags = $wgFlaggedRevsTags;
		}
		# Assume true, then set to false if needed
		if ( !empty( $flaggedRevsTags ) ) {
			self::$qualityVersions = true;
			self::$pristineVersions = true;
			self::$binaryFlagging = ( count( $flaggedRevsTags ) <= 1 );
		}
		foreach ( $flaggedRevsTags as $tag => $levels ) {
			# Sanity checks
			$safeTag = htmlspecialchars( $tag );
			if ( !preg_match( '/^[a-zA-Z]{1,20}$/', $tag ) || $safeTag !== $tag ) {
				throw new MWException( 'FlaggedRevs given invalid tag name!' );
			}
			# Define "quality" and "pristine" reqs
			if ( is_array( $levels ) ) {
				$minQL = $levels['quality'];
				$minPL = $levels['pristine'];
				$ratingLevels = $levels['levels'];
			# B/C, $levels is just an integer (minQL)
			} else {
				global $wgFlaggedRevPristine, $wgFlaggedRevValues;
				$ratingLevels = isset( $wgFlaggedRevValues ) ?
					$wgFlaggedRevValues : 1;
				$minQL = $levels; // an integer
				$minPL = isset( $wgFlaggedRevPristine ) ?
					$wgFlaggedRevPristine : $ratingLevels + 1;
				wfWarn( 'Please update the format of $wgFlaggedRevsTags in config.' );
			}
			# Set FlaggedRevs tags
			self::$dimensions[$tag] = array();
			for ( $i = 0; $i <= $ratingLevels; $i++ ) {
				self::$dimensions[$tag][$i] = "{$tag}-{$i}";
			}
			if ( $ratingLevels > 1 ) {
				self::$binaryFlagging = false; // more than one level
			}
			# Sanity checks
			if ( !is_integer( $minQL ) || !is_integer( $minPL ) ) {
				throw new MWException( 'FlaggedRevs given invalid tag value!' );
			}
			if ( $minQL > $ratingLevels ) {
				self::$qualityVersions = false;
				self::$pristineVersions = false;
			}
			if ( $minPL > $ratingLevels ) {
				self::$pristineVersions = false;
			}
			self::$minQL[$tag] = max( $minQL, 1 );
			self::$minPL[$tag] = max( $minPL, 1 );
			self::$minSL[$tag] = 1;
		}
		global $wgFlaggedRevsTagsRestrictions, $wgFlagRestrictions;
		if ( isset( $wgFlagRestrictions ) ) {
			self::$tagRestrictions = $wgFlagRestrictions; // b/c
			wfWarn( 'Please use $wgFlaggedRevsTagsRestrictions instead of $wgFlagRestrictions in config.' );
		} else {
			self::$tagRestrictions = $wgFlaggedRevsTagsRestrictions;
		}
		# Make sure that the restriction levels are unique
		global $wgFlaggedRevsRestrictionLevels;
		self::$restrictionLevels = array_unique( $wgFlaggedRevsRestrictionLevels );
		self::$restrictionLevels = array_filter( self::$restrictionLevels, 'strlen' );
		# Make sure no talk namespaces are in review namespace
		global $wgFlaggedRevsNamespaces, $wgFlaggedRevsPatrolNamespaces;
		foreach ( $wgFlaggedRevsNamespaces as $ns ) {
			if ( MWNamespace::isTalk( $ns ) ) {
				throw new MWException( 'FlaggedRevs given talk namespace in $wgFlaggedRevsNamespaces!' );
			} else if ( $ns == NS_MEDIAWIKI ) {
				throw new MWException( 'FlaggedRevs given NS_MEDIAWIKI in $wgFlaggedRevsNamespaces!' );
			}
		}
		self::$reviewNamespaces = $wgFlaggedRevsNamespaces;
		# Note: reviewable *pages* override patrollable ones
		self::$patrolNamespaces = $wgFlaggedRevsPatrolNamespaces;
		# !$wgFlaggedRevsAutoReview => !$wgFlaggedRevsAutoReviewNew
		global $wgFlaggedRevsAutoReview, $wgFlaggedRevsAutoReviewNew;
		if ( !$wgFlaggedRevsAutoReview ) {
			$wgFlaggedRevsAutoReviewNew = false;
		}
	}
	
	# ################ Basic config accessors #################

	/**
	 * Is there only one tag and it has only one level?
	 * @returns bool
	 */
	public static function binaryFlagging() {
		self::load();
		return self::$binaryFlagging;
	}
	
	/**
	 * If there only one tag and it has only one level, return it
	 * @returns string
	 */
	public static function binaryTagName() {
		self::load();
		if ( !self::binaryFlagging() ) {
			return null;
		}
		$tags = array_keys( self::$dimensions );
		return empty( $tags ) ? null : $tags[0];
	}
	
	/**
	 * Are quality versions enabled?
	 * @returns bool
	 */
	public static function qualityVersions() {
		self::load();
		return self::$qualityVersions;
	}
	
	/**
	 * Are pristine versions enabled?
	 * @returns bool
	 */
	public static function pristineVersions() {
		self::load();
		return self::$pristineVersions;
	}

	/**
	 * Allow auto-review edits directly to the stable version by reviewers?
	 * @returns bool
	 */
	public static function autoReviewEdits() {
		global $wgFlaggedRevsAutoReview;
		return (bool)$wgFlaggedRevsAutoReview;
	}

	/**
	 * Auto-review new pages with the minimal level?
	 * @returns bool
	 */
	public static function autoReviewNewPages() {
		global $wgFlaggedRevsAutoReviewNew;
		return (bool)$wgFlaggedRevsAutoReviewNew;
	}

	/**
	 * Get the maximum level that $tag can be autoreviewed to
	 * @param string $tag
	 * @returns int
	 */
	public static function maxAutoReviewLevel( $tag ) {
		global $wgFlaggedRevsTagsAuto;
		self::load();
		if ( !self::autoReviewEdits() ) {
			return 0; // no auto-review allowed at all
		}
		if ( isset( $wgFlaggedRevsTagsAuto[$tag] ) ) {
			return (int)$wgFlaggedRevsTagsAuto[$tag];
		} else {
			return 1; // B/C (before $wgFlaggedRevsTagsAuto)
		}
	}

	/**
	 * Is a "stable version" used as the default display
	 * version for all pages in reviewable namespaces?
	 * @returns bool
	 */
	public static function isStableShownByDefault() {
		global $wgFlaggedRevsOverride;
		if ( self::useOnlyIfProtected() ) {
			return false; // must be configured per-page
		}
		return (bool)$wgFlaggedRevsOverride;
	}

	/**
	 * Are pages reviewable only if they have been manually
	 * configured by an admin to use a "stable version" as the default?
	 * @returns bool
	 */
	public static function useOnlyIfProtected() {
		global $wgFlaggedRevsProtection;
		return (bool)$wgFlaggedRevsProtection;
	}

	/**
	 * Return the include handling configuration
	 * @returns int
	 */
	public static function inclusionSetting() {
		global $wgFlaggedRevsHandleIncludes;
		return $wgFlaggedRevsHandleIncludes;
	}

	/**
	 * Should tags only be shown for unreviewed content for this user?
	 * @returns bool
	 */
	public static function lowProfileUI() {
		global $wgFlaggedRevsLowProfile;
		return $wgFlaggedRevsLowProfile;
	}

	/**
	 * Are there site defined protection levels for review
	 * @returns bool
	 */
	public static function useProtectionLevels() {
		global $wgFlaggedRevsProtection;
		return $wgFlaggedRevsProtection && self::getRestrictionLevels();
	}

	/**
	 * Get the autoreview restriction levels available
	 * @returns array
	 */
	public static function getRestrictionLevels() {
		self::load();
		return self::$restrictionLevels;
	}

	/**
	 * Should comments be allowed on pages and forms?
	 * @returns bool
	 */
	public static function allowComments() {
		global $wgFlaggedRevsComments;
		return $wgFlaggedRevsComments;
	}

	/**
	 * Get the array of tag dimensions and level messages
	 * @returns array
	 */
	public static function getDimensions() {
		self::load();
		return self::$dimensions;
	}

	/**
	 * Get the associative array of tag dimensions
	 * (tags => array(levels => msgkey))
	 * @returns array
	 */
	public static function getTags() {
		self::load();
		return array_keys( self::$dimensions );
	}

	/**
	 * Get the associative array of tag restrictions
	 * (tags => array(rights => levels))
	 * @returns array
	 */
	public static function getTagRestrictions() {
		self::load();
		return self::$tagRestrictions;
	}
	
	/**
	 * Get the UI name for a tag
	 * @param string $tag
	 * @returns string
	 */
	public static function getTagMsg( $tag ) {
		return wfMsgExt( "revreview-$tag", array( 'escapenoentities' ) );
	}
	
	/**
	 * Get the levels for a tag. Gives map of level to message name.
	 * @param string $tag
	 * @returns associative array (integer -> string)
	 */
	public static function getTagLevels( $tag ) {
		self::load();
		return isset( self::$dimensions[$tag] ) ?
			self::$dimensions[$tag] : array();
	}
	
	/**
	 * Get the the UI name for a value of a tag
	 * @param string $tag
	 * @param int $value
	 * @returns string
	 */
	public static function getTagValueMsg( $tag, $value ) {
		self::load();
		if ( !isset( self::$dimensions[$tag] ) ) {
			return '';
		} elseif ( !isset( self::$dimensions[$tag][$value] ) ) {
			return '';
		}
		# Return empty string if not there
		return wfMsgExt( 'revreview-' . self::$dimensions[$tag][$value],
			array( 'escapenoentities' ) );
	}
	
	/**
	 * Are there no actual dimensions?
	 * @returns bool
	 */
	public static function dimensionsEmpty() {
		self::load();
		return empty( self::$dimensions );
	}

	/**
	 * Get corresponding text for the api output of flagging levels
	 *
	 * @param int $level
	 * @return string
	 */
	public static function getQualityLevelText( $level ) {
		static $levelText = array(
			0 => 'stable',
			1 => 'quality',
			2 => 'pristine'
		);
		if ( isset( $levelText[$level] ) ) {
			return $levelText[$level];
		} else {
			return '';
		}
	}
	
	/**
	 * Get the URL path to /client
	 * @return string
	 */
	public static function styleUrlPath() {
		global $wgFlaggedRevsStylePath, $wgExtensionAssetsPath;
		return str_replace( '$wgExtensionAssetsPath', $wgExtensionAssetsPath, $wgFlaggedRevsStylePath );
	}

	# ################ Permission functions #################	
	
	/**
	 * Returns true if a user can set $tag to $value.
	 * @param User $user
	 * @param string $tag
	 * @param int $value
	 * @returns bool
	 */
	public static function userCanSetTag( $user, $tag, $value ) {
		# Sanity check tag and value
		$levels = self::getTagLevels( $tag );
		$highest = count( $levels ) - 1;
		if ( !$levels || $value < 0 || $value > $highest ) {
			return false; // flag range is invalid
		}
		$restrictions = self::getTagRestrictions();
		# No restrictions -> full access
		if ( !isset( $restrictions[$tag] ) ) {
			return true;
		}
		# Validators always have full access
		if ( $user->isAllowed( 'validate' ) ) {
			return true;
		}
		# Check if this user has any right that lets him/her set
		# up to this particular value
		foreach ( $restrictions[$tag] as $right => $level ) {
			if ( $value <= $level && $level > 0 && $user->isAllowed( $right ) ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Returns true if a user can set $flags for a revision via review.
	 * Requires the same for $oldflags if given.
	 * @param User $user
	 * @param array $flags, suggested flags
	 * @param array $oldflags, pre-existing flags
	 * @returns bool
	 */
	public static function userCanSetFlags( $user, array $flags, $oldflags = array() ) {
		if ( !$user->isAllowed( 'review' ) ) {
			return false; // User is not able to review pages
		}
		# Check if all of the required site flags have a valid value
		# that the user is allowed to set...
		foreach ( self::getDimensions() as $qal => $levels ) {
			$level = isset( $flags[$qal] ) ? $flags[$qal] : 0;
			if ( !self::userCanSetTag( $user, $qal, $level ) ) {
				return false; // user cannot set proposed flag
			} elseif ( isset( $oldflags[$qal] )
				&& !self::userCanSetTag( $user, $qal, $oldflags[$qal] ) )
			{
				return false; // user cannot change old flag
			}
		}
		return true;
	}

	/**
	* Check if a user can set the autoreview restiction level to $right
	* @param User $user
	* @param string $right the level
	* @returns bool
	*/
	public static function userCanSetAutoreviewLevel( $user, $right ) {
		if ( $right == '' ) {
			return true; // no restrictions (none)
		}
		if ( !in_array( $right, FlaggedRevs::getRestrictionLevels() ) ) {
			return false; // invalid restriction level
		}
		# Don't let them choose levels above their own rights
		if ( $right == 'sysop' ) {
			// special case, rewrite sysop to protect and editprotected
			if ( !$user->isAllowed( 'protect' ) && !$user->isAllowed( 'editprotected' ) ) {
				return false;
			}
		} elseif ( !$user->isAllowed( $right ) ) {
			return false;
		}
		return true;
	}

	# ################ Parsing functions #################

	/** 
	 * All included pages/arguments are expanded out
	 * @param Title $title
	 * @param string $text
	 * @param int $id Source revision Id
	 * @return array( string, array, array )
	 */
	public static function expandText( Title $title, $text, $id ) {
		global $wgParser;
		# Notify Parser if includes should be stabilized
		$resetManager = false;
		$incManager = FRInclusionManager::singleton();
		if ( $id && self::inclusionSetting() != FR_INCLUDES_CURRENT ) {
			# Use FRInclusionManager to do the template/file version query
			# up front unless the versions are already specified there...
			if ( !$incManager->parserOutputIsStabilized() ) {
				$frev = FlaggedRevision::newFromTitle( $title, $id );
				if ( $frev ) {
					$incManager->stabilizeParserOutput( $title, $frev );
					$resetManager = true; // need to reset when done
				}
			}
		}
		$options = self::makeParserOptions(); // default options
		$outputText = $wgParser->preprocess( $text, $title, $options, $id );
		$out = $wgParser->mOutput;
		# Stable parse done!
		if ( $resetManager ) {
			$incManager->clear(); // reset the FRInclusionManager as needed
		}
		# Return data array
		return array( $outputText, $out->mTemplateIds, $out->fr_includeErrors );
	}

	/**
	 * Get the HTML output of a revision based on $text.
	 * @param Title $title
	 * @param string $text
	 * @param int $id Source revision Id
	 * @return ParserOutput
	 */
	public static function parseStableText( Title $title, $text, $id, $parserOptions ) {
		global $wgParser;
		# Notify Parser if includes should be stabilized
		$resetManager = false;
		$incManager = FRInclusionManager::singleton();
		if ( $id && self::inclusionSetting() != FR_INCLUDES_CURRENT ) {
			# Use FRInclusionManager to do the template/file version query
			# up front unless the versions are already specified there...
			if ( !$incManager->parserOutputIsStabilized() ) {
				$frev = FlaggedRevision::newFromTitle( $title, $id );
				if ( $frev ) {
					$incManager->stabilizeParserOutput( $title, $frev );
					$resetManager = true; // need to reset when done
				}
			}
		}
		# Parse the new body, wikitext -> html
		$parserOut = $wgParser->parse( $text, $title, $parserOptions, true, true, $id );
		# Stable parse done!
		if ( $resetManager ) {
			$incManager->clear(); // reset the FRInclusionManager as needed
		}
	   	return $parserOut;
	}

	/**
	* Get standard parser options
	* @param User $user (optional)
	* @returns ParserOptions
	*/
	public static function makeParserOptions( $user = null ) {
		global $wgUser;
		$user = $user ? $user : $wgUser; // assume current
		$options = ParserOptions::newFromUser( $user );
		# Show inclusion/loop reports
		$options->enableLimitReport();
		# Fix bad HTML
		$options->setTidy( true );
		return $options;
	}

	/**
	* Get the page cache for the stable version of an article
	* @param Article $article
	* @param User $user
	* @return mixed (ParserOutput/false)
	*/
	public static function getPageCache( Article $article, $user ) {
		global $parserMemc, $wgCacheEpoch;
		wfProfileIn( __METHOD__ );
		# Make sure it is valid
		if ( !$article->getId() ) {
			wfProfileOut( __METHOD__ );
			return null;
		}
		$parserCache = ParserCache::singleton();
		$key = self::getCacheKey( $parserCache, $article, $user );
		# Get the cached HTML
		wfDebug( "Trying parser cache $key\n" );
		$value = $parserMemc->get( $key );
		if ( is_object( $value ) ) {
			wfDebug( "Found.\n" );
			# Delete if article has changed since the cache was made
			$canCache = $article->checkTouched();
			$cacheTime = $value->getCacheTime();
			$touched = $article->mTouched;
			if ( !$canCache || $value->expired( $touched ) ) {
				if ( !$canCache ) {
					wfIncrStats( "pcache_miss_invalid" );
					wfDebug( "Invalid cached redirect, touched $touched, epoch $wgCacheEpoch, cached $cacheTime\n" );
				} else {
					wfIncrStats( "pcache_miss_expired" );
					wfDebug( "Key expired, touched $touched, epoch $wgCacheEpoch, cached $cacheTime\n" );
				}
				$parserMemc->delete( $key );
				$value = false;
			} else {
				wfIncrStats( "pcache_hit" );
			}
		} else {
			wfDebug( "Parser cache miss.\n" );
			wfIncrStats( "pcache_miss_absent" );
			$value = false;
		}
		wfProfileOut( __METHOD__ );
		return $value;
	}

	/**
	 * Like ParserCache::getKey() with stable-pcache instead of pcache
	 */
	protected static function getCacheKey( $parserCache, Article $article, $popts ) {
		if( $popts instanceof User ) {
			$popts = ParserOptions::newFromUser( $popts );
		}
		$key = $parserCache->getKey( $article, $popts );
		$key = str_replace( ':pcache:', ':stable-pcache:', $key );
		return $key;
	}

	/**
	* @param Article $article
	* @param ParserOptions $popts
	* @param parserOutput $parserOut
	* Updates the stable cache of a page with the given $parserOut
	*/
	public static function updatePageCache(
		Article $article, $popts, ParserOutput $parserOut = null
	) {
		global $parserMemc, $wgParserCacheExpireTime, $wgEnableParserCache;
		wfProfileIn( __METHOD__ );
		# Make sure it is valid and $wgEnableParserCache is enabled
		if ( !$wgEnableParserCache || !$parserOut ) {
			wfProfileOut( __METHOD__ );
			return false;
		}
		$parserCache = ParserCache::singleton();
		$key = self::getCacheKey( $parserCache, $article, $popts );
		# Add cache mark to HTML
		$now = wfTimestampNow();
		$parserOut->setCacheTime( $now );
		# Save the timestamp so that we don't have to load the revision row on view
		$parserOut->mTimestamp = $article->getTimestamp();
		$parserOut->mText .= "\n<!-- Saved in stable version parser cache with key $key and timestamp $now -->";
		# Set expire time
		if ( $parserOut->containsOldMagic() ) {
			$expire = 3600; // 1 hour
		} else {
			$expire = $wgParserCacheExpireTime;
		}
		# Save to objectcache
		$parserMemc->set( $key, $parserOut, $expire );
		wfProfileOut( __METHOD__ );
		return true;
	}

	/**
	* @param Article $article
	* @param parserOutput $parserOut
	* Updates the stable-only cache dependancy table
	*/
	public static function updateCacheTracking( Article $article, ParserOutput $stableOut ) {
		wfProfileIn( __METHOD__ );
		if ( !wfReadOnly() ) {
			$frDepUpdate = new FRDependencyUpdate( $article->getTitle(), $stableOut );
			$frDepUpdate->doUpdate();
		}
		wfProfileOut( __METHOD__ );
	}

	# ################ Tracking/cache update update functions #################

 	/**
	* Update the page tables with a new stable version.
	* @param Title $title
	* @param mixed $sv, the new stable version (optional)
	* @param mixed $oldSv, the old stable version (optional)
	* @return bool stable version text/file changed and FR_INCLUDES_STABLE
	*/
	public static function stableVersionUpdates( Title $title, $sv = null, $oldSv = null ) {
		$changed = false;
		if ( $oldSv === null ) { // optional
			$oldSv = FlaggedRevision::newFromStable( $title, FR_MASTER );
		}
		if ( $sv === null ) { // optional
			$sv = FlaggedRevision::determineStable( $title, FR_MASTER );
		}
		if ( !$sv ) {
			# Empty flaggedrevs data for this page if there is no stable version
			self::clearTrackingRows( $title->getArticleID() );
			# Check if pages using this need to be refreshed...
			if ( FlaggedRevs::inclusionSetting() == FR_INCLUDES_STABLE ) {
				$changed = (bool)$oldSv;
			}
		} else {
			$article = new Article( $title );
			# Update flagged page related fields
			FlaggedRevs::updateStableVersion( $article, $sv->getRevision() );
			# Lazily rebuild dependancies on next parse (we invalidate below)
			FlaggedRevs::clearStableOnlyDeps( $title );
			# Check if pages using this need to be invalidated/purged...
			if ( FlaggedRevs::inclusionSetting() == FR_INCLUDES_STABLE ) {
				$changed = (
					!$oldSv ||
					$sv->getRevId() != $oldSv->getRevId() ||
					$sv->getFileTimestamp() != $oldSv->getFileTimestamp() ||
					$sv->getFileSha1() != $oldSv->getFileSha1()
				);
			}
		}
		# Clear page cache
		$title->invalidateCache();
		self::purgeSquid( $title );
		return $changed;
	}

 	/**
	* @param Title $title
	* Updates squid cache for a title. Defers till after main commit().
	*/
	public static function purgeSquid( Title $title ) {
		global $wgDeferredUpdateList;
		$wgDeferredUpdateList[] = new FRSquidUpdate( $title );
	}

 	/**
	* @param Article $article
	* @param Revision $rev, the new stable version
	* @param mixed $latest, the latest rev ID (optional)
	* Updates the tracking tables and pending edit count cache. Called on edit.
	*/
	public static function updateStableVersion(
		Article $article, Revision $rev, $latest = null
	) {
		if ( !$article->getId() ) {
			return true; // no bogus entries
		}
		# Get the latest revision ID if not set
		if ( !$latest ) {
			$latest = $article->getTitle()->getLatestRevID( Title::GAID_FOR_UPDATE );
		}
		# Get the highest quality revision (not necessarily this one)
		$dbw = wfGetDB( DB_MASTER );
		$maxQuality = $dbw->selectField( array( 'flaggedrevs', 'revision' ),
			'fr_quality',
			array( 'fr_page_id' => $article->getId(),
				'rev_id = fr_rev_id',
				'rev_page = fr_page_id',
				'rev_deleted & ' . Revision::DELETED_TEXT => 0
			),
			__METHOD__,
			array( 'ORDER BY' => 'fr_quality DESC', 'LIMIT' => 1 )
		);
		# Get the timestamp of the first edit after the stable version (if any)...
		$nextTimestamp = null;
		if ( $rev->getId() != $latest ) {
			$timestamp = $dbw->timestamp( $rev->getTimestamp() );
			$nextEditTS = $dbw->selectField( 'revision',
				'rev_timestamp',
				array(
					'rev_page' => $article->getId(),
					"rev_timestamp > " . $dbw->addQuotes( $timestamp ) ),
				__METHOD__,
				array( 'ORDER BY' => 'rev_timestamp ASC', 'LIMIT' => 1 )
			);
			if ( $nextEditTS ) { // sanity check
				$nextTimestamp = $nextEditTS;
			}
		}
		# Alter table metadata
		$dbw->replace( 'flaggedpages',
			array( 'fp_page_id' ),
			array(
				'fp_page_id'       => $article->getId(),
				'fp_stable'        => $rev->getId(),
				'fp_reviewed'      => ( $nextTimestamp === null ) ? 1 : 0,
				'fp_quality'       => ( $maxQuality === false ) ? null : $maxQuality,
				'fp_pending_since' => $dbw->timestampOrNull( $nextTimestamp )
			),
			__METHOD__
		);
		# Update pending edit tracking table
		self::updatePendingList( $article, $latest );
		return true;
	}

 	/**
	* @param Article $article
	* @param mixed $latest, the latest rev ID (optional)
	* Updates the flaggedpage_pending table
	*/
	public static function updatePendingList( Article $article, $latest = null ) {
		$data = array();
		$level = self::pristineVersions() ? FR_PRISTINE : FR_QUALITY;
		if ( !self::qualityVersions() ) {
			$level = FR_CHECKED;
		}
		# Get the latest revision ID if not set
		if ( !$latest ) {
			$latest = $article->getTitle()->getLatestRevID( Title::GAID_FOR_UPDATE );
		}
		$pageId = $article->getId();
		# Update pending times for each level, going from highest to lowest
		$dbw = wfGetDB( DB_MASTER );
		$higherLevelId = 0;
		$higherLevelTS = '';
		while ( $level >= 0 ) {
			# Get the latest revision of this level...
			$row = $dbw->selectRow( array( 'flaggedrevs', 'revision' ),
				array( 'fr_rev_id', 'rev_timestamp' ),
				array( 'fr_page_id' => $pageId,
					'fr_quality' => $level,
					'rev_id = fr_rev_id',
					'rev_page = fr_page_id',
					'rev_deleted & ' . Revision::DELETED_TEXT => 0,
					'rev_id > ' . intval( $higherLevelId )
				),
				__METHOD__,
				array( 'ORDER BY' => 'fr_rev_id DESC', 'LIMIT' => 1 )
			);
			# If there is a revision of this level, track it...
			# Revisions reviewed to one level  count as reviewed
			# at the lower levels (i.e. quality -> checked).
			if ( $row ) {
				$id = $row->fr_rev_id;
				$ts = $row->rev_timestamp;
			} else {
				$id = $higherLevelId; // use previous (quality -> checked)
				$ts = $higherLevelTS; // use previous (quality -> checked)
			}
			# Get edits that actually are pending...
			if ( $id && $latest > $id ) {
				# Get the timestamp of the edit after this version (if any)
				$nextTimestamp = $dbw->selectField( 'revision',
					'rev_timestamp',
					array( 'rev_page' => $pageId, "rev_timestamp > " . $dbw->addQuotes( $ts ) ),
					__METHOD__,
					array( 'ORDER BY' => 'rev_timestamp ASC', 'LIMIT' => 1 )
				);
				$data[] = array(
					'fpp_page_id'       => $pageId,
					'fpp_quality'       => $level,
					'fpp_rev_id'        => $id,
					'fpp_pending_since' => $nextTimestamp
				);
				$higherLevelId = $id;
				$higherLevelTS = $ts;
			}
			$level--;
		}
		# Clear any old junk, and insert new rows
		$dbw->delete( 'flaggedpage_pending', array( 'fpp_page_id' => $pageId ), __METHOD__ );
		$dbw->insert( 'flaggedpage_pending', $data, __METHOD__ );
	}

 	/**
	* Do cache updates for when the stable version of a page changed.
	* Invalidates/purges pages that include the given page.
	* @param Title $title
	* @param bool $recursive
	*/
	public static function HTMLCacheUpdates( Title $title ) {
		global $wgDeferredUpdateList;
		# Invalidate caches of articles which include this page...
		$wgDeferredUpdateList[] = new HTMLCacheUpdate( $title, 'templatelinks' );
		if ( $title->getNamespace() == NS_FILE ) {
			$wgDeferredUpdateList[] = new HTMLCacheUpdate( $title, 'imagelinks' );
		}
		$wgDeferredUpdateList[] = new FRExtraCacheUpdate( $title );
	}

 	/**
	* Invalidates/purges pages where only stable version includes this page.
	* @param Title $title
	*/
	public static function extraHTMLCacheUpdate( Title $title ) {
		global $wgDeferredUpdateList;
		$wgDeferredUpdateList[] = new FRExtraCacheUpdate( $title );
	}

	# ################ Revision functions #################

	/**
	 * Get flags for a revision
	 * @param Title $title
	 * @param int $rev_id
	 * @param $flags, FR_MASTER
	 * @return Array
	*/
	public static function getRevisionTags( Title $title, $rev_id, $flags = 0 ) {
		$db = ( $flags & FR_MASTER ) ?
			wfGetDB( DB_MASTER ) : wfGetDB( DB_SLAVE );
		$tags = (string)$db->selectField( 'flaggedrevs',
			'fr_tags',
			array( 'fr_rev_id' => $rev_id,
				'fr_page_id' => $title->getArticleId() ),
			__METHOD__
		);
		return FlaggedRevision::expandRevisionTags( strval( $tags ) );
	}

	/**
	 * @param int $page_id
	 * @param int $rev_id
	 * @param $flags, FR_MASTER
	 * @returns mixed (int or false)
	 * Get quality of a revision
	 */
	public static function getRevQuality( $page_id, $rev_id, $flags = 0 ) {
		$db = ( $flags & FR_MASTER ) ?
			wfGetDB( DB_MASTER ) : wfGetDB( DB_SLAVE );
		return $db->selectField( 'flaggedrevs',
			'fr_quality',
			array( 'fr_page_id' => $page_id, 'fr_rev_id' => $rev_id ),
			__METHOD__,
			array( 'USE INDEX' => 'PRIMARY' )
		);
	}

	/**
	 * @param Title $title
	 * @param int $rev_id
	 * @param $flags, FR_MASTER
	 * @returns bool
	 * Useful for quickly pinging to see if a revision is flagged
	 */
	public static function revIsFlagged( Title $title, $rev_id, $flags = 0 ) {
		$quality = self::getRevQuality( $title->getArticleId(), $rev_id, $flags );
		return ( $quality !== false );
	}
	
	/**
	 * Get the "prime" flagged revision of a page
	 * @param Article $article
	 * @returns mixed (integer/false)
	 * Will not return a revision if deleted
	 */
	public static function getPrimeFlaggedRevId( Article $article ) {
		$dbr = wfGetDB( DB_SLAVE );
		# Get the highest quality revision (not necessarily this one).
		$oldid = $dbr->selectField( array( 'flaggedrevs', 'revision' ),
			'fr_rev_id',
			array(
				'fr_page_id' => $article->getId(),
				'rev_page = fr_page_id',
				'rev_id = fr_rev_id'
			),
			__METHOD__,
			array(
				'ORDER BY' => 'fr_quality DESC, fr_rev_id DESC',
				'USE INDEX' => array( 'flaggedrevs' => 'page_qal_rev', 'revision' => 'PRIMARY' )
			)
		);
		return $oldid;
	}
	
	/**
	 * Mark a revision as patrolled if needed
	 * @param Revision $rev
	 * @returns bool DB write query used
	 */
	public static function markRevisionPatrolled( Revision $rev ) {
		$rcid = $rev->isUnpatrolled();
		# Make sure it is now marked patrolled...
		if ( $rcid ) {
			$dbw = wfGetDB( DB_MASTER );
			$dbw->update( 'recentchanges',
				array( 'rc_patrolled' => 1 ),
				array( 'rc_id' => $rcid ),
				__METHOD__
			);
			return true;
		}
		return false;
	}

	# ################ Page configuration functions #################

	/**
	 * Get visibility settings/restrictions for a page
	 * @param Title $title, page title
	 * @param int $flags, FR_MASTER
	 * @returns array (associative) (select,override,autoreview,expiry)
	 */
	public static function getPageVisibilitySettings( Title $title, $flags = 0 ) {
		$db = ( $flags & FR_MASTER ) ?
			wfGetDB( DB_MASTER ) : wfGetDB( DB_SLAVE );
		$row = $db->selectRow( 'flaggedpage_config',
			array( 'fpc_override', 'fpc_level', 'fpc_expiry' ),
			array( 'fpc_page_id' => $title->getArticleID() ),
			__METHOD__
		);
		if ( $row ) {
			# This code should be refactored, now that it's being used more generally.
			$expiry = Block::decodeExpiry( $row->fpc_expiry );
			# Only apply the settings if they haven't expired
			if ( !$expiry || $expiry < wfTimestampNow() ) {
				$row = null; // expired
				self::purgeExpiredConfigurations();
				self::stableVersionUpdates( $title ); // re-find stable version
			}
		}
		// Is there a non-expired row?
		if ( $row ) {
			$level = $row->fpc_level;
			if ( !self::isValidRestriction( $row->fpc_level ) ) {
				$level = ''; // site default; ignore fpc_level
			}
			$config = array(
				'override'   => $row->fpc_override ? 1 : 0,
				'autoreview' => $level,
				'expiry'	 => Block::decodeExpiry( $row->fpc_expiry ) // TS_MW
			);
			# If there are protection levels defined check if this is valid...
			if ( self::useProtectionLevels() ) {
				$level = self::getProtectionLevel( $config );
				if ( $level == 'invalid' || $level == 'none' ) {
					// If 'none', make sure expiry is 'infinity'
					$config = self::getDefaultVisibilitySettings(); // revert to default (none)
				}
			}
		} else {
			# Return the default config if this page doesn't have its own
			$config = self::getDefaultVisibilitySettings();
		}
		return $config;
	}

	/**
	 * Get default page configuration settings
	 */
	public static function getDefaultVisibilitySettings() {
		return array(
			# Keep this consistent across settings:
			# # 1 -> override, 0 -> don't
			'override'   => self::isStableShownByDefault() ? 1 : 0,
			'autoreview' => '',
			'expiry'     => 'infinity'
		);
	}

	
	/**
	 * Find what protection level a config is in
	 * @param array $config
	 * @returns string
	 */
	public static function getProtectionLevel( array $config ) {
		if ( !self::useProtectionLevels() ) {
			throw new MWException( 'getProtectionLevel() called with $wgFlaggedRevsProtection off' );
		}
		$defaultConfig = self::getDefaultVisibilitySettings();
		# Check if the page is not protected at all...
		if ( $config['override'] == $defaultConfig['override']
			&& $config['autoreview'] == '' )
		{
			return "none"; // not protected
		}
		# All protection levels have 'override' on
		if ( $config['override'] ) {
			# The levels are defined by the 'autoreview' settings
			if ( in_array( $config['autoreview'], self::getRestrictionLevels() ) ) {
				return $config['autoreview'];
			}
		}
		return "invalid";
	}

	/**
	 * Check if an fpc_level value is valid
	 * @param string $right
	 */
	public static function isValidRestriction( $right ) {
		if ( $right == '' ) {
			return true; // no restrictions (none)
		}
		return in_array( $right, self::getRestrictionLevels(), true );
	}

	/**
	 * Purge expired restrictions from the flaggedpage_config table.
	 * The stable version of pages may change and invalidation may be required.
	 */
	public static function purgeExpiredConfigurations() {
		$dbw = wfGetDB( DB_MASTER );
		$pageIds = array();
		$pagesClearTracking = array();
		$config = self::getDefaultVisibilitySettings(); // config is to be reset
		$encCutoff = $dbw->addQuotes( $dbw->timestamp() );
		$ret = $dbw->select( 'flaggedpage_config',
			array( 'fpc_page_id' ),
			array( 'fpc_expiry < ' . $encCutoff ),
			__METHOD__
			// array( 'FOR UPDATE' )
		);
		foreach( $ret as $row ) {
			// If FlaggedRevs got "turned off" for this page (due to not
			// having the stable version as the default), then clear it
			// from the tracking tables...
			if ( !$config['override'] && self::useOnlyIfProtected() ) {
				$pagesClearTracking[] = $row->fpc_page_id; // no stable version
			}
			$pageIds[] = $row->fpc_page_id; // page with expired config
		}
		// Clear the expired config for these pages
		if ( count( $pageIds ) ) {
			$dbw->delete( 'flaggedpage_config',
				array( 'fpc_page_id' => $pageIds, 'fpc_expiry < ' . $encCutoff ),
				__METHOD__ );
		}
		// Clear the tracking rows where needed
		if ( count( $pagesClearTracking ) ) {
			self::clearTrackingRows( $pagesClearTracking );
		}
	}

	# ################ Other utility functions #################

	/**
	 * @param string $val
	 * @return Object (val,time) tuple
	 * Get a memcache storage object
	 */
	public static function makeMemcObj( $val ) {
		$data = (object) array();
		$data->value = $val;
		$data->time = wfTimestampNow();
		return $data;
	}

	/**
	* @param mixed $data makeMemcObj() tuple (false/Object)
	* @param Article $article
	* @return mixed
	* Return memc value if not expired
	*/
	public static function getMemcValue( $data, Article $article ) {
		if ( is_object( $data ) && $data->time >= $article->getTouched() ) {
			return $data->value;
		}
		return false;
	}

	/**
	* @param array $flags
	* @return bool, is this revision at basic review condition?
	*/
	public static function isChecked( array $flags ) {
		self::load();
		return self::tagsAtLevel( $flags, self::$minSL );
	}

	/**
	* @param array $flags
	* @return bool, is this revision at quality review condition?
	*/
	public static function isQuality( array $flags ) {
		self::load();
		return self::tagsAtLevel( $flags, self::$minQL );
	}

	/**
	* @param array $flags
	* @return bool, is this revision at pristine review condition?
	*/
	public static function isPristine( array $flags ) {
		self::load();
		return self::tagsAtLevel( $flags, self::$minPL );
	}
	
	// Checks if $flags meets $reqFlagLevels
	protected static function tagsAtLevel( array $flags, $reqFlagLevels ) {
		self::load();
		if ( empty( $flags ) ) {
			return false;
		}
		foreach ( self::$dimensions as $f => $x ) {
			if ( !isset( $flags[$f] ) || $reqFlagLevels[$f] > $flags[$f] ) {
				return false;
			}
		}
		return true;
	}

	/**
	* Get the quality tier of review flags
	* @param array $flags
	* @return int flagging tier (FR_PRISTINE,FR_QUALITY,FR_CHECKED,-1)
	*/
	public static function getLevelTier( array $flags ) {
		if ( self::isPristine( $flags ) ) {
			return FR_PRISTINE; // 2
		} elseif ( self::isQuality( $flags ) ) {
			return FR_QUALITY; // 1
		} elseif ( self::isChecked( $flags ) ) {
			return FR_CHECKED; // 0
		}
		return -1;
	}

	/**
	 * Get minimum level tags for a tier
	 * @param int $tier FR_PRISTINE/FR_QUALITY/FR_CHECKED
	 * @return array
	 */
	public static function quickTags( $tier ) {
		self::load();
		if ( $tier == FR_PRISTINE ) {
			return self::$minPL;
		} elseif ( $tier == FR_QUALITY ) {
			return self::$minQL;
		}
		return self::$minSL;
	}

	/**
	 * Get minimum tags that are closest to $oldFlags
	 * given the site, page, and user rights limitations.
	 * @param User $user
	 * @param array $oldFlags previous stable rev flags
	 * @return mixed array or null
	 */
	public static function getAutoReviewTags( $user, array $oldFlags ) {
		if ( !self::autoReviewEdits() ) {
			return null; // shouldn't happen
		}
		$flags = array();
		foreach ( self::getTags() as $tag ) {
			# Try to keep this tag val the same as the stable rev's
			$val = isset( $oldFlags[$tag] ) ? $oldFlags[$tag] : 1;
			$val = min( $val, self::maxAutoReviewLevel( $tag ) );
			# Dial down the level to one the user has permission to set
			while ( !self::userCanSetTag( $user, $tag, $val ) ) {
				$val--;
				if ( $val <= 0 ) {
					return null; // all tags vals must be > 0
				}
			}
			$flags[$tag] = $val;
		}
		return $flags;
	}	

	/**
	* Get the list of reviewable namespaces
	* @return array
	*/
	public static function getReviewNamespaces() {
		self::load(); // validates namespaces
		return self::$reviewNamespaces;
	}
	
	/**
	* Get the list of patrollable namespaces
	* @return array
	*/
	public static function getPatrolNamespaces() {
		self::load(); // validates namespaces
		return self::$patrolNamespaces;
	}
	
	
	/**
	* Is this page in reviewable namespace?
	* Note: this checks $wgFlaggedRevsWhitelist
	* @param Title, $title
	* @return bool
	*/
	public static function inReviewNamespace( Title $title ) {
		global $wgFlaggedRevsWhitelist;
		$namespaces = self::getReviewNamespaces();
		$ns = ( $title->getNamespace() == NS_MEDIA ) ?
			NS_FILE : $title->getNamespace(); // Treat NS_MEDIA as NS_FILE
		# Check for MW: pages and whitelist for exempt pages
		if ( in_array( $title->getPrefixedDBKey(), $wgFlaggedRevsWhitelist ) ) {
			return false;
		}
		return ( in_array( $ns, $namespaces ) );
	}
	
	/**
	* Is this page in patrollable namespace?
	* @param Title, $title
	* @return bool
	*/
	public static function inPatrolNamespace( Title $title ) {
		$namespaces = self::getPatrolNamespaces();
		$ns = ( $title->getNamespace() == NS_MEDIA ) ?
			NS_FILE : $title->getNamespace(); // Treat NS_MEDIA as NS_FILE
		return ( in_array( $ns, $namespaces ) );
	}

   	/**
	* Clear FlaggedRevs tracking tables for this page
	* @param mixed $pageId (int or array)
	*/
	public static function clearTrackingRows( $pageId ) {
		$dbw = wfGetDB( DB_MASTER );
		$dbw->delete( 'flaggedpages', array( 'fp_page_id' => $pageId ), __METHOD__ );
		$dbw->delete( 'flaggedrevs_tracking', array( 'ftr_from' => $pageId ), __METHOD__ );
		$dbw->delete( 'flaggedpage_pending', array( 'fpp_page_id' => $pageId ), __METHOD__ );
	}

   	/**
	* Clear tracking table of stable-only links for this page
	* @param mixed $pageId (int or array)
	*/
	public static function clearStableOnlyDeps( $pageId ) {
		$dbw = wfGetDB( DB_MASTER );
		$dbw->delete( 'flaggedrevs_tracking', array( 'ftr_from' => $pageId ), __METHOD__ );
	}

	# ################ Auto-review function #################

	/**
	* Automatically review an revision and add a log entry in the review log.
	*
	* This is called during edit operations after the new revision is added
	* and the page tables updated, but before LinksUpdate is called.
	*
	* $auto is here for revisions checked off to be reviewed. Auto-review
	* triggers on edit, but we don't want it to count as just automatic.
	* This also makes it so the user's name shows up in the page history.
	*
	* If $flags is given, then they will be the review tags. If not, the one
	* from the stable version will be used or minimal tags if that's not possible.
	* If no appropriate tags can be found, then the review will abort.
	*/
	public static function autoReviewEdit(
		Article $article, $user, Revision $rev, array $flags = null, $auto = true
	) {
		wfProfileIn( __METHOD__ );
		$title = $article->getTitle(); // convenience
		# Get current stable version ID (for logging)
		$oldSv = FlaggedRevision::newFromStable( $title, FR_MASTER );
		$oldSvId = $oldSv ? $oldSv->getRevId() : 0;
		# Set the auto-review tags from the prior stable version.
		# Normally, this should already be done and given here...
		if ( !is_array( $flags ) ) {
			if ( $oldSv ) {
				# Use the last stable version if $flags not given
				if ( $user->isAllowed( 'bot' ) ) {
					$flags = $oldSv->getTags(); // no change for bot edits
				} else {
					# Account for perms/tags...
					$flags = self::getAutoReviewTags( $user, $oldSv->getTags() );
				}
			} else { // new page?
				$flags = self::quickTags( FR_CHECKED ); // use minimal level
			}
			if ( !is_array( $flags ) ) {
				wfProfileOut( __METHOD__ );
				return false; // can't auto-review this revision
			}
		}
		# Get quality tier from flags
		$quality = 0;
		if ( self::isQuality( $flags ) ) {
			$quality = self::isPristine( $flags ) ? 2 : 1;
		}

		# Rev ID is not put into parser on edit, so do the same here.
		# Also, a second parse would be triggered otherwise.
		$editInfo = $article->prepareTextForEdit( $rev->getText() );
		$poutput = $editInfo->output; // revision HTML output

		# If this is an image page, store corresponding file info
		$fileData = array( 'name' => null, 'timestamp' => null, 'sha1' => null );
		if ( $title->getNamespace() == NS_FILE ) {
			$file = $article instanceof ImagePage ?
				$article->getFile() : wfFindFile( $title );
			if ( is_object( $file ) && $file->exists() ) {
				$fileData['name'] = $title->getDBkey();
				$fileData['timestamp'] = $file->getTimestamp();
				$fileData['sha1'] = $file->getSha1();
			}
		}

		# Our review entry
		$flaggedRevision = new FlaggedRevision( array(
			'page_id'       	=> $rev->getPage(),
			'rev_id'	      	=> $rev->getId(),
			'user'	       		=> $user->getId(),
			'timestamp'     	=> $rev->getTimestamp(),
			'comment'      	 	=> "",
			'quality'      	 	=> $quality,
			'tags'	       		=> FlaggedRevision::flattenRevisionTags( $flags ),
			'img_name'      	=> $fileData['name'],
			'img_timestamp' 	=> $fileData['timestamp'],
			'img_sha1'      	=> $fileData['sha1'],
			'templateVersions' 	=> $poutput->mTemplateIds,
			'fileVersions'     	=> $poutput->fr_fileSHA1Keys
		) );
		$flaggedRevision->insertOn( $auto );
		# Update the article review log
		FlaggedRevsLogs::updateLog( $title,
			$flags, array(), '', $rev->getId(), $oldSvId, true, $auto );

		# Update page and tracking tables and clear cache
		FlaggedRevs::stableVersionUpdates( $title );

		wfProfileOut( __METHOD__ );
		return true;
	}

	/**
	 * Get JS script params
	 */
	public static function getJSTagParams() {
		self::load();
		# Param to pass to JS function to know if tags are at quality level
		$tagsJS = array();
		foreach ( self::$dimensions as $tag => $x ) {
			$tagsJS[$tag] = array();
			$tagsJS[$tag]['levels'] = count( $x ) - 1;
			$tagsJS[$tag]['quality'] = self::$minQL[$tag];
			$tagsJS[$tag]['pristine'] = self::$minPL[$tag];
		}
		$params = array( 'tags' => (object)$tagsJS );
		return (object)$params;
	}
}
