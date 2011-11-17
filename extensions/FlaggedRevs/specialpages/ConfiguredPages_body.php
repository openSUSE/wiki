<?php
if ( !defined( 'MEDIAWIKI' ) ) {
	echo "FlaggedRevs extension\n";
	exit( 1 );
}

// Assumes $wgFlaggedRevsProtection is off
class ConfiguredPages extends SpecialPage
{
	public function __construct() {
        parent::__construct( 'ConfiguredPages' );
    }

	public function execute( $par ) {
        global $wgRequest, $wgUser;

		$this->setHeaders();
		$this->skin = $wgUser->getSkin();

		$this->namespace = $wgRequest->getIntOrNull( 'namespace' );
		$this->override = $wgRequest->getIntOrNull( 'stable' );
		$this->autoreview = $wgRequest->getVal( 'restriction', '' );

		$this->showForm();
		$this->showPageList();
	}

	protected function showForm() {
		global $wgOut, $wgScript;
		$wgOut->addWikiMsg( 'configuredpages-list' );
		$fields = array();
		# Namespace selector
		if ( count( FlaggedRevs::getReviewNamespaces() ) > 1 ) {
			$fields[] = FlaggedRevsXML::getNamespaceMenu( $this->namespace, '' );
		}
		# Default version selector
		$fields[] = FlaggedRevsXML::getDefaultFilterMenu( $this->override );
		# Restriction level selector
		if( FlaggedRevs::getRestrictionLevels() ) {
			$fields[] = FlaggedRevsXML::getRestrictionFilterMenu( $this->autoreview );
		}
		if ( count( $fields ) ) {
			$form = Xml::openElement( 'form',
				array( 'name' => 'configuredpages', 'action' => $wgScript, 'method' => 'get' ) );
			$form .= Html::hidden( 'title', $this->getTitle()->getPrefixedDBKey() );
			$form .= "<fieldset><legend>" . wfMsg( 'configuredpages' ) . "</legend>\n";
			$form .= implode( '&#160;', $fields ) . '<br/>';
			$form .= Xml::submitButton( wfMsg( 'go' ) );
			$form .= "</fieldset>\n";
			$form .= Xml::closeElement( 'form' );
			$wgOut->addHTML( $form );
		}
	}

	protected function showPageList() {
		global $wgOut;
		$pager = new ConfiguredPagesPager( $this, array(),
			$this->namespace, $this->override, $this->autoreview );
		if ( $pager->getNumRows() ) {
			$wgOut->addHTML( $pager->getNavigationBar() );
			$wgOut->addHTML( $pager->getBody() );
			$wgOut->addHTML( $pager->getNavigationBar() );
		} else {
			$wgOut->addWikiMsg( 'configuredpages-none' );
		}
		# Take this opportunity to purge out expired configurations
		FlaggedRevs::purgeExpiredConfigurations();
	}

	public function formatRow( $row ) {
		global $wgLang;
		$title = Title::makeTitle( $row->page_namespace, $row->page_title );
		# Link to page
		$link = $this->skin->makeKnownLinkObj( $title, $title->getPrefixedText() );
		# Link to page configuration
		$config = $this->skin->makeKnownLinkObj(
			SpecialPage::getTitleFor( 'Stabilization' ),
			wfMsgHtml( 'configuredpages-config' ),
			'page=' . $title->getPrefixedUrl()
		);
		# Show which version is the default (stable or draft)
		if ( intval( $row->fpc_override ) ) {
			$default = wfMsgHtml( 'configuredpages-def-stable' );
		} else {
			$default = wfMsgHtml( 'configuredpages-def-draft' );
		}
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
		return "<li>{$link} ({$config}) <b>[$default]</b> {$restr}<i>{$expiry_description}</i></li>";
	}
}

/**
 * Query to list out stable versions for a page
 */
class ConfiguredPagesPager extends AlphabeticPager {
	public $mForm, $mConds, $namespace, $override, $autoreview;

	// @param int $namespace (null for "all")
	// @param int $override (null for "either")
	// @param string $autoreview ('' for "all", 'none' for no restriction)
	function __construct(
		$form, $conds = array(), $namespace, $override, $autoreview
	) {
		$this->mForm = $form;
		$this->mConds = $conds;
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
		if ( !is_integer( $override ) ) {
			$override = null; // "all"
		}
		$this->override = $override;
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
		if ( $this->override !== null ) {
			$conds['fpc_override'] = $this->override;
		}
		if ( $this->autoreview !== null ) {
			$conds['fpc_level'] = $this->autoreview;
		}
		$conds['page_namespace'] = $this->namespace;
		# Be sure not to include expired items
		$encCutoff = $this->mDb->addQuotes( $this->mDb->timestamp() );
		$conds[] = "fpc_expiry > {$encCutoff}";
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
