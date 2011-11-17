<?php
if ( !defined( 'MEDIAWIKI' ) ) {
	echo "FlaggedRevs extension\n";
	exit( 1 );
}

// Assumes $wgFlaggedRevsProtection is on
class StablePages extends SpecialPage
{
	public function __construct() {
        parent::__construct( 'StablePages' );
    }

	public function execute( $par ) {
        global $wgRequest, $wgUser;

		$this->setHeaders();
		$this->skin = $wgUser->getSkin();

		$this->namespace = $wgRequest->getIntOrNull( 'namespace' );
		$this->autoreview = $wgRequest->getVal( 'restriction', '' );
		$this->indef = $wgRequest->getBool( 'indef', false );

		$this->showForm();
		$this->showPageList();
	}

	protected function showForm() {
		global $wgOut, $wgScript;
		$wgOut->addWikiMsg( 'stablepages-list' );
		$fields = array();
		# Namespace selector
		if ( count( FlaggedRevs::getReviewNamespaces() ) > 1 ) {
			$fields[] = FlaggedRevsXML::getNamespaceMenu( $this->namespace, '' );
		}
		# Restriction level selector
		if( FlaggedRevs::getRestrictionLevels() ) {
			$fields[] = FlaggedRevsXML::getRestrictionFilterMenu( $this->autoreview );
		}
		$fields[] = Xml::checkLabel( wfMsg( 'stablepages-indef' ), 'indef', 
			'stablepages-indef', $this->indef );
		# Use form if it has options
		if ( count( $fields ) ) {
			$form = Xml::openElement( 'form',
				array( 'name' => 'stablepages', 'action' => $wgScript, 'method' => 'get' ) );
			$form .= Html::hidden( 'title', $this->getTitle()->getPrefixedDBKey() );
			$form .= "<fieldset><legend>" . wfMsg( 'stablepages' ) . "</legend>\n";
			$form .= implode( '&#160;', $fields ) . '&nbsp';
			$form .= " " . Xml::submitButton( wfMsg( 'go' ) );
			$form .= "</fieldset>\n";
			$form .= Xml::closeElement( 'form' );
			$wgOut->addHTML( $form );
		}
	}

	protected function showPageList() {
		global $wgOut;
		$pager = new StablePagesPager(
			$this, array(), $this->namespace, $this->autoreview, $this->indef );
		if ( $pager->getNumRows() ) {
			$wgOut->addHTML( $pager->getNavigationBar() );
			$wgOut->addHTML( $pager->getBody() );
			$wgOut->addHTML( $pager->getNavigationBar() );
		} else {
			$wgOut->addWikiMsg( 'stablepages-none' );
		}
		# Take this opportunity to purge out expired configurations
		FlaggedRevs::purgeExpiredConfigurations();
	}

	public function formatRow( $row ) {
		global $wgLang;
		$title = Title::makeTitle( $row->page_namespace, $row->page_title );
		# Link to page
		$link = $this->skin->link( $title );
		# Helpful utility links
		$utilLinks = array();
		$utilLinks[] = $this->skin->link( $title,
			wfMsgHtml( 'stablepages-config' ),
			array(), array( 'action' => 'protect' ), 'known' );
		$utilLinks[] = $this->skin->link( $title,
			wfMsgHtml( 'history' ),
			array(), array( 'action' => 'history' ), 'known' );
		$utilLinks[] = $this->skin->link( SpecialPage::getTitleFor( 'Log', 'stable' ),
			wfMsgHtml( 'stable-logpage' ),
			array(), array( 'page' => $title->getPrefixedText() ), 'known' );
		# Autoreview/review restriction level
		$restr = '';
		if ( $row->fpc_level != '' ) {
			$restr = 'autoreview=' . htmlspecialchars( $row->fpc_level );
			$restr = "[$restr]";
		}
		# When these configuration settings expire
		if ( $row->fpc_expiry != 'infinity' && strlen( $row->fpc_expiry ) ) {
			$expiry_description = " (" . wfMsgForContent(
				'protect-expiring',
				$wgLang->timeanddate( $row->fpc_expiry ),
				$wgLang->date( $row->fpc_expiry ),
				$wgLang->time( $row->fpc_expiry )
			) . ")";
		} else {
			$expiry_description = "";
		}
		$utilLinks = $wgLang->pipeList( $utilLinks );
		return "<li>{$link} ({$utilLinks}) {$restr}<i>{$expiry_description}</i></li>";
	}
}

/**
 * Query to list out stable versions for a page
 */
class StablePagesPager extends AlphabeticPager {
	public $mForm, $mConds, $namespace, $override;

	// @param int $namespace (null for "all")
	// @param string $autoreview ('' for "all", 'none' for no restriction)
	function __construct( $form, $conds = array(), $namespace, $autoreview, $indef ) {
		$this->mForm = $form;
		$this->mConds = $conds;
		$this->indef = $indef;
		# Must be content pages...
		$validNS = FlaggedRevs::getReviewNamespaces();
		if ( is_integer( $namespace ) ) {
			if ( !in_array( $namespace, $validNS ) ) {
				$namespace = $validNS; // fallback to "all"
			}
		} else {
			$namespace = $validNS; // "all"
		}
		$this->namespace = $namespace;
		if ( $autoreview === 'none' ) {
			$autoreview = ''; // 'none' => ''
		} elseif ( $autoreview === '' ) {
			$autoreview = null; // '' => null
		}
		$this->autoreview = $autoreview;
		parent::__construct();
	}

	function formatRow( $row ) {
		return $this->mForm->formatRow( $row );
	}

	function getQueryInfo() {
		$conds = $this->mConds;
		$conds[] = 'page_id = fpc_page_id';
		$conds['fpc_override'] = 1;
		if( $this->autoreview !== null ) {
			$conds['fpc_level'] = $this->autoreview;
		}
		$conds['page_namespace'] = $this->namespace;
		# Be sure not to include expired items
		if( $this->indef ) {
			$conds['fpc_expiry'] = Block::infinity();
		} else {
			$encCutoff = $this->mDb->addQuotes( $this->mDb->timestamp() );
			$conds[] = "fpc_expiry > {$encCutoff}";
		}
		return array(
			'tables' => array( 'flaggedpage_config', 'page' ),
			'fields' => array( 'page_namespace', 'page_title', 'fpc_override',
				'fpc_expiry', 'fpc_page_id', 'fpc_level' ),
			'conds'  => $conds,
			'options' => array()
		);
	}

	function getIndexField() {
		return 'fpc_page_id';
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
