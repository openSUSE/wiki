<?php
/**
 * Class containing draft template/file version usage for
 * Parser based on the source text of a revision ID & title.
 */
class FRInclusionCache {
	/**
	 * Get template and image versions from parsing a revision
	 * @param Page $article
	 * @param Revision $rev
	 * @param User $user
	 * @param string $regen use 'regen' to force regeneration
	 * @return array( templateIds, fileSHA1Keys )
	 * templateIds like ParserOutput->mTemplateIds
	 * fileSHA1Keys like ParserOutput->mImageTimeKeys
	 */
	public static function getRevIncludes(
		Page $article, Revision $rev, User $user, $regen = ''
	) {
		global $wgParser, $wgMemc;
		wfProfileIn( __METHOD__ );

		$key = self::getCacheKey( $article->getTitle(), $rev->getId() );
		if ( $regen === 'regen' ) {
			$versions = false; // skip cache
		} elseif ( $rev->isCurrent() ) {
			// Check cache entry against page_touched
			$versions = FlaggedRevs::getMemcValue( $wgMemc->get( $key ), $article );
		} else {
			// Old revs won't always be invalidated with template/file changes.
			// Also, we don't care if page_touched changed due to a direct edit.
			$versions = FlaggedRevs::getMemcValue( $wgMemc->get( $key ), $article, 'allowStale' );
			if ( is_array( $versions ) ) { // entry exists
				// Sanity check that the cache is reasonably up to date
				list( $templates, $files ) = $versions;
				if ( self::templatesStale( $templates ) || self::filesStale( $files ) ) {
					$versions = false; // no good
				}
			}
		}

		if ( !is_array( $versions ) ) { // cache miss
			$pOut = false;
			if ( $rev->isCurrent() ) {
				$parserCache = ParserCache::singleton();
				# Try current version parser cache for this user...
				$pOut = $parserCache->get( $article, $article->makeParserOptions( $user ) );
				if ( $pOut == false ) {
					# Try current version parser cache for the revision author...
					$optsUser = $rev->getUser()
						? User::newFromId( $rev->getUser() )
						: 'canonical';
					$pOut = $parserCache->get( $article, $article->makeParserOptions( $optsUser ) );
				}
			}
			// ParserOutput::mImageTimeKeys wasn't always there
			if ( $pOut == false || !FlaggedRevs::parserOutputIsVersioned( $pOut ) ) {
				$pOut = $wgParser->parse(
					$rev->getText(),
					$article->getTitle(), 
					ParserOptions::newFromUser( $user ), // Note: tidy off
					true,
					true,
					$rev->getId() 
				);
			}
			# Get the template/file versions used...
			$versions = array( $pOut->getTemplateIds(), $pOut->getFileSearchOptions() );
			# Save to cache (check cache expiry for dynamic elements)...
			$data = FlaggedRevs::makeMemcObj( $versions );
			$wgMemc->set( $key, $data, $pOut->getCacheExpiry() );
		}

		wfProfileOut( __METHOD__ );
		return $versions;
	}

	protected static function templatesStale( array $tVersions ) {
		# Do a link batch query for page_latest...
		$lb = new LinkBatch();
		foreach ( $tVersions as $ns => $tmps ) {
			foreach ( $tmps as $dbKey => $revIdDraft ) {
				$lb->add( $ns, $dbKey );
			}
		}
		$lb->execute();
		# Check if any of these templates have a newer version
		foreach ( $tVersions as $ns => $tmps ) {
			foreach ( $tmps as $dbKey => $revIdDraft ) {
				$title = Title::makeTitle( $ns, $dbKey );
				if ( $revIdDraft != $title->getLatestRevID() ) {
					return true;
				}
			}
		}
		return false;
	}

	protected static function filesStale( array $fVersions ) {
		# Check if any of these files have a newer version
		foreach ( $fVersions as $name => $timeAndSHA1 ) {
			$file = wfFindFile( $name );
			if ( $file ) {
				if ( $file->getTimestamp() != $timeAndSHA1['time'] ) {
					return true;
				}
			} else {
				if ( $timeAndSHA1['time'] ) {
					return true;
				}
			}
		}
		return false;
	}

	/**
	 * Set template and image versions from parsing a revision
	 * @param Title $title
	 * @param int $revId
	 * @param ParserOutput $pOut
	 */
	public static function setRevIncludes( Title $title, $revId, ParserOutput $pOut ) {
		global $wgMemc;
		$key = self::getCacheKey( $title, $revId );
		# Get the template/file versions used...
		$versions = array( $pOut->getTemplateIds(), $pOut->getFileSearchOptions() );
		# Save to cache (check cache expiry for dynamic elements)...
		$data = FlaggedRevs::makeMemcObj( $versions );
		$wgMemc->set( $key, $data, $pOut->getCacheExpiry() );
	}

	protected static function getCacheKey( Title $title, $revId ) {
		$hash = md5( $title->getPrefixedDBkey() );
		return wfMemcKey( 'flaggedrevs', 'revIncludes', $revId, $hash );
	}
}
