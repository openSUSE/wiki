<?php
if ( !defined( 'MEDIAWIKI' ) ) {
	echo "FlaggedRevs extension\n";
	exit( 1 );
}

class PendingChanges extends SpecialPage
{
	public function __construct() {
		parent::__construct( 'PendingChanges' );
		$this->includable( true );
	}

	public function execute( $par ) {
		global $wgRequest, $wgUser;
		$this->setHeaders();
		$this->skin = $wgUser->getSkin();
		$this->currentUnixTS = wfTimestamp( TS_UNIX ); // now
		# Read params
		$this->namespace = $wgRequest->getIntOrNull( 'namespace' );
		$this->level = $wgRequest->getInt( 'level', - 1 );
		$this->category = trim( $wgRequest->getVal( 'category' ) );
		$catTitle = Title::makeTitleSafe( NS_CATEGORY, $this->category );
		$this->category = is_null( $catTitle ) ? '' : $catTitle->getText();
		$this->size = $wgRequest->getIntOrNull( 'size' );
		$this->watched = $wgRequest->getCheck( 'watched' );
		$this->stable = $wgRequest->getCheck( 'stable' );
		$feedType = $wgRequest->getVal( 'feed' );
		# Output appropriate format...
		if ( $feedType != null ) {
			$this->feed( $feedType );
		} else {
			if ( !$this->including() ) {
				$this->setSyndicated();
				$this->showForm();
			}
			$this->showList( $par );
		}
	}

	protected function setSyndicated() {
		global $wgOut, $wgRequest;
		$queryParams = array(
			'namespace' => $wgRequest->getIntOrNull( 'namespace' ),
			'level'     => $wgRequest->getIntOrNull( 'level' ),
			'category'  => $wgRequest->getVal( 'category' ),
		);
		$wgOut->setSyndicated( true );
		$wgOut->setFeedAppendQuery( wfArrayToCGI( $queryParams ) );
	}

	public function showForm() {
		global $wgUser, $wgOut, $wgScript;
		$action = htmlspecialchars( $wgScript );
		# Explanation text...
		$wgOut->addWikiMsg( 'pendingchanges-list' );
		$form =
			"<form action=\"$action\" method=\"get\">\n" .
			'<fieldset><legend>' . wfMsg( 'pendingchanges-legend' ) . '</legend>' .
			Html::hidden( 'title', $this->getTitle()->getPrefixedDBKey() );

		$items = array();
		if ( count( FlaggedRevs::getReviewNamespaces() ) > 1 ) {
			$items[] = "<span style='white-space: nowrap;'>" .
				FlaggedRevsXML::getNamespaceMenu( $this->namespace, '' ) . '</span>';
		}
		if ( FlaggedRevs::qualityVersions() ) {
			$items[] = "<span style='white-space: nowrap;'>" .
				FlaggedRevsXML::getLevelMenu( $this->level, 'revreview-filter-stable' ) .
				'</span>';
		}
		if ( !FlaggedRevs::isStableShownByDefault() && !FlaggedRevs::useOnlyIfProtected() ) {
			$items[] = "<span style='white-space: nowrap;'>" .
				Xml::check( 'stable', $this->stable, array( 'id' => 'wpStable' ) ) .
				Xml::label( wfMsg( 'pendingchanges-stable' ), 'wpStable' ) . '</span>';
		}
		if ( $items ) {
			$form .= implode( ' ', $items ) . '<br />';
		}
		$items = array();
		$items[] =
			Xml::label( wfMsg( "pendingchanges-category" ), 'wpCategory' ) . '&#160;' .
			Xml::input( 'category', 30, $this->category, array( 'id' => 'wpCategory' ) );
		if ( $wgUser->getId() ) {
			$items[] = Xml::check( 'watched', $this->watched, array( 'id' => 'wpWatched' ) ) .
				Xml::label( wfMsg( 'pendingchanges-onwatchlist' ), 'wpWatched' );
		}
		$form .= implode( ' ', $items ) . '<br />';
		$form .=
			Xml::label( wfMsg( 'pendingchanges-size' ), 'wpSize' ) .
			Xml::input( 'size', 4, $this->size, array( 'id' => 'wpSize' ) ) . ' ' .
			Xml::submitButton( wfMsg( 'allpagessubmit' ) ) . "\n" .
			"</fieldset></form>";
		$wgOut->addHTML( $form );
	}

	public function showList( $par ) {
		global $wgOut;
		$limit = false; // defer to Pager
		if ( $this->including() ) {
			$limit = $this->parseParams( $par );
		}
		$pager = new PendingChangesPager( $this, $this->namespace, $this->level,
			$this->category, $this->size, $this->watched, $this->stable );
		// Apply limit if transcluded
		if ( $limit ) $pager->mLimit = $limit;
		// Viewing the list normally...
		if ( !$this->including() ) {
			if ( $pager->getNumRows() ) {
				$wgOut->addHTML( $pager->getNavigationBar() );
				$wgOut->addHTML( $pager->getBody() );
				$wgOut->addHTML( $pager->getNavigationBar() );
			} else {
				$wgOut->addWikiMsg( 'pendingchanges-none' );
			}
		// If this list is transcluded...
		} else {
			if ( $pager->getNumRows() ) {
				$wgOut->addHTML( $pager->getBody() );
			} else {
				$wgOut->addWikiMsg( 'pendingchanges-none' );
			}
		}
	}

	// set namespace and category fields of $this
	// @returns int paging limit
	protected function parseParams( $par ) {
		global $wgLang;
		$bits = preg_split( '/\s*,\s*/', trim( $par ) );
		$limit = false;
		foreach ( $bits as $bit ) {
			if ( is_numeric( $bit ) ) {
				$limit = intval( $bit );
			}
			$m = array();
			if ( preg_match( '/^limit=(\d+)$/', $bit, $m ) ) {
				$limit = intval( $m[1] );
			}
			if ( preg_match( '/^namespace=(.*)$/', $bit, $m ) ) {
				$ns = $wgLang->getNsIndex( $m[1] );
				if ( $ns !== false ) {
					$this->namespace = $ns;
				}
			}
			if ( preg_match( '/^category=(.+)$/', $bit, $m ) ) {
				$this->category = $m[1];
			}
		}
		return $limit;
	}

	/**
	 * Output a subscription feed listing recent edits to this page.
	 * @param string $type
	 */
	protected function feed( $type ) {
		global $wgFeed, $wgFeedClasses, $wgFeedLimit, $wgOut, $wgRequest;
		if ( !$wgFeed ) {
			$wgOut->addWikiMsg( 'feed-unavailable' );
			return;
		}
		if ( !isset( $wgFeedClasses[$type] ) ) {
			$wgOut->addWikiMsg( 'feed-invalid' );
			return;
		}
		$feed = new $wgFeedClasses[$type](
			$this->feedTitle(),
			wfMsg( 'tagline' ),
			$this->getTitle()->getFullUrl()
		);
		$pager = new PendingChangesPager( $this, $this->namespace, $this->category );
		$limit = $wgRequest->getInt( 'limit', 50 );
		$pager->mLimit = min( $wgFeedLimit, $limit );

		$feed->outHeader();
		if ( $pager->getNumRows() > 0 ) {
			foreach ( $pager->mResult as $row ) {
				$feed->outItem( $this->feedItem( $row ) );
			}
		}
		$feed->outFooter();
	}

	protected function feedTitle() {
		global $wgContLanguageCode, $wgSitename;
		$page = SpecialPage::getPage( 'PendingChanges' );
		$desc = $page->getDescription();
		return "$wgSitename - $desc [$wgContLanguageCode]";
	}

	protected function feedItem( $row ) {
		$title = Title::MakeTitle( $row->page_namespace, $row->page_title );
		if ( $title ) {
			$date = $row->pending_since;
			$comments = $title->getTalkPage()->getFullURL();
			$curRev = Revision::newFromTitle( $title );
			return new FeedItem(
				$title->getPrefixedText(),
				FeedUtils::formatDiffRow( $title, $row->stable, $curRev->getId(),
					$row->pending_since, $curRev->getComment() ),
				$title->getFullURL(),
				$date,
				$curRev->getUserText(),
				$comments );
		} else {
			return null;
		}
	}

	public function formatRow( $row ) {
		global $wgLang, $wgUser, $wgMemc;
		$css = $quality = $underReview = '';

		$title = Title::newFromRow( $row );
		$link = $this->skin->makeKnownLinkObj( $title );
		$hist = $this->skin->makeKnownLinkObj( $title,
			wfMsgHtml( 'hist' ), 'action=history' );
		$stxt = ChangesList::showCharacterDifference( $row->rev_len, $row->page_len );
		$review = $this->skin->makeKnownLinkObj( $title,
			wfMsg( 'pendingchanges-diff' ),
			'diff=cur&oldid='.intval($row->stable).'&diffonly=0' );
		# Show quality level if there are several
		if ( FlaggedRevs::qualityVersions() ) {
			$quality = $row->quality
				? wfMsgHtml( 'revreview-lev-quality' )
				: wfMsgHtml( 'revreview-lev-basic' );
			$quality = " <b>[{$quality}]</b>";
		}
		# Is anybody watching?
		if ( !$this->including() && $wgUser->isAllowed( 'unreviewedpages' ) ) {
			$uw = UnreviewedPages::usersWatching( $title );
			$watching = $uw
				? wfMsgExt( 'pendingchanges-watched', 'parsemag', $wgLang->formatNum( $uw ) )
				: wfMsgHtml( 'pendingchanges-unwatched' );
			$watching = " {$watching}";
		} else {
			$uw = - 1;
			$watching = ''; // leave out data
		}
		# Get how long the first unreviewed edit has been waiting...
		if ( $row->pending_since ) {
			$firstPendingTime = wfTimestamp( TS_UNIX, $row->pending_since );
			$hours = ( $this->currentUnixTS - $firstPendingTime ) / 3600;
			// After three days, just use days
			if ( $hours > ( 3 * 24 ) ) {
				$days = round( $hours / 24, 0 );
				$age = wfMsgExt( 'pendingchanges-days', 'parsemag', $wgLang->formatNum( $days ) );
			// If one or more hours, use hours
			} elseif ( $hours >= 1 ) {
				$hours = round( $hours, 0 );
				$age = wfMsgExt( 'pendingchanges-hours', 'parsemag', $wgLang->formatNum( $hours ) );
			} else {
				$age = wfMsg( 'pendingchanges-recent' ); // hot off the press :)
			}
			// Oh-noes!
			$css = self::getLineClass( $hours, $uw );
			$css = $css ? " class='$css'" : "";
		} else {
			$age = ""; // wtf?
		}
		$key = wfMemcKey( 'stableDiffs', 'underReview', $row->stable, $row->page_latest );
		# Show if a user is looking at this page
		if ( $wgMemc->get( $key ) ) {
			$underReview = ' <span class="fr-under-review">' .
				wfMsgHtml( 'pendingchanges-viewing' ) . '</span>';
		}

		return( "<li{$css}>{$link} ({$hist}) {$stxt} ({$review}) <i>{$age}</i>" .
			"{$quality}{$watching}{$underReview}</li>" );
	}

	protected static function getLineClass( $hours, $uw ) {
		if ( $uw == 0 ) {
			return 'fr-unreviewed-unwatched';
		} else {
			return "";
		}
	}
}

/**
 * Query to list out outdated reviewed pages
 */
class PendingChangesPager extends AlphabeticPager {
	public $mForm, $mConds;
	private $category, $namespace;

	function __construct( $form, $namespace, $level = - 1, $category = '',
		$size = null, $watched = false, $stable = false )
	{
		$this->mForm = $form;
		# Must be a content page...
		$vnamespaces = FlaggedRevs::getReviewNamespaces();
		if ( is_null( $namespace ) ) {
			$namespace = $vnamespaces;
		} else {
			$namespace = intval( $namespace );
		}
		# Sanity check
		if ( !in_array( $namespace, $vnamespaces ) ) {
			$namespace = $vnamespaces;
		}
		$this->namespace = $namespace;
		# Sanity check level: 0 = checked; 1 = quality; 2 = pristine
		$this->level = ( $level >= 0 && $level <= 2 ) ? $level : - 1;
		$this->category = $category ? str_replace( ' ', '_', $category ) : null;
		$this->size = ( $size !== null ) ? intval( $size ) : null;
		$this->watched = (bool)$watched;
		$this->stable = $stable && !FlaggedRevs::isStableShownByDefault()
			&& !FlaggedRevs::useOnlyIfProtected();
		parent::__construct();
		// Don't get too expensive
		$this->mLimitsShown = array( 20, 50, 100 );
		$this->mLimit = min( $this->mLimit, 100 );
	}

	function formatRow( $row ) {
		return $this->mForm->formatRow( $row );
	}
	
	function getDefaultQuery() {
		$query = parent::getDefaultQuery();
		$query['category'] = $this->category;
		return $query;
	}
	
	function getDefaultDirections() {
		return false;
	}

	function getQueryInfo() {
		global $wgUser;
		$conds = $this->mConds;
		$tables = array( 'page', 'revision' );
		$fields = array( 'page_namespace', 'page_title', 'page_len', 'rev_len', 'page_latest' );
		# Show outdated "stable" versions
		if ( $this->level < 0 ) {
			$tables[] = 'flaggedpages';
			$fields[] = 'fp_stable AS stable';
			$fields[] = 'fp_quality AS quality';
			$fields[] = 'fp_pending_since AS pending_since';
			$conds[] = 'page_id = fp_page_id';
			# Overconstrain rev_page to force PK use
			$conds[] = 'rev_page = page_id AND rev_id = fp_stable';
			$conds[] = 'fp_pending_since IS NOT NULL';
			$useIndex = array( 'flaggedpages' => 'fp_pending_since', 'page' => 'PRIMARY' );
			# Filter by pages configured to be stable
			if ( $this->stable ) {
				$tables[] = 'flaggedpage_config';
				$conds[] = 'fp_page_id = fpc_page_id';
				$conds['fpc_override'] = 1;
			}
			# Filter by category
			if ( $this->category ) {
				$tables[] = 'categorylinks';
				$conds[] = 'cl_from = fp_page_id';
				$conds['cl_to'] = $this->category;
				$useIndex['categorylinks'] = 'cl_from';
			}
			$this->mIndexField = 'fp_pending_since';
		# Show outdated version for a specific review level
		} else {
			$tables[] = 'flaggedpage_pending';
			$fields[] = 'fpp_rev_id AS stable';
			$fields[] = 'fpp_quality AS quality';
			$fields[] = 'fpp_pending_since AS pending_since';
			$conds[] = 'page_id = fpp_page_id';
			# Overconstrain rev_page to force PK use
			$conds[] = 'rev_page = page_id AND rev_id = fpp_rev_id';
			$conds[] = 'fpp_pending_since IS NOT NULL';
			$useIndex = array( 'flaggedpage_pending' => 'fpp_quality_pending',
				'page' => 'PRIMARY' );
			# Filter by review level
			$conds['fpp_quality'] = $this->level;
			# Filter by pages configured to be stable
			if ( $this->stable ) {
				$tables[] = 'flaggedpage_config';
				$conds[] = 'fpp_page_id = fpc_page_id';
				$conds['fpc_override'] = 1;
			}
			# Filter by category
			if ( $this->category != '' ) {
				$tables[] = 'categorylinks';
				$conds[] = 'cl_from = fpp_page_id';
				$conds['cl_to'] = $this->category;
				$useIndex['categorylinks'] = 'cl_from';
			}
			$this->mIndexField = 'fpp_pending_since';
		}
		$fields[] = $this->mIndexField; // Pager needs this
		# Filter namespace
		if ( $this->namespace !== null ) {
			$conds['page_namespace'] = $this->namespace;
		}
		# Filter by watchlist
		if ( $this->watched && ( $uid = $wgUser->getId() ) ) {
			$tables[] = 'watchlist';
			$conds[] = "wl_user = '$uid'";
			$conds[] = 'page_namespace = wl_namespace';
			$conds[] = 'page_title = wl_title';
		}
		# Filter by bytes changed
		if ( $this->size !== null && $this->size >= 0 ) {
			# Get absolute difference for comparison. ABS(x-y)
			# is broken due to mysql unsigned int design.
			$conds[] = 'GREATEST(page_len,rev_len)-LEAST(page_len,rev_len) <= ' .
				intval( $this->size );
		}
		return array(
			'tables'  => $tables,
			'fields'  => $fields,
			'conds'   => $conds,
			'options' => array( 'USE INDEX' => $useIndex )
		);
	}

	function getIndexField() {
		return $this->mIndexField;
	}
	
	function getStartBody() {
		wfProfileIn( __METHOD__ );
		# Do a link batch query
		$lb = new LinkBatch();
		foreach ( $this->mResult as $row ) {
			$lb->add( $row->page_namespace, $row->page_title );
		}
		$lb->execute();
		wfProfileOut( __METHOD__ );
		return '<ul>';
	}
	
	function getEndBody() {
		return '</ul>';
	}
}
