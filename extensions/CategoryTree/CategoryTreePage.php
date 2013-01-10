<?php
/**
 * Special page for the  CategoryTree extension, an AJAX based gadget
 * to display the category structure of a wiki
 *
 * @file
 * @ingroup Extensions
 * @author Daniel Kinzler, brightbyte.de
 * @copyright © 2006 Daniel Kinzler
 * @license GNU General Public Licence 2.0 or later
 */

if ( !defined( 'MEDIAWIKI' ) ) {
	echo( "This file is part of an extension to the MediaWiki software and cannot be used standalone.\n" );
	die( 1 );
}

class CategoryTreePage extends SpecialPage {
	var $target = '';

	/**
	 * @var CategoryTree
	 */
	var $tree = null;

	function __construct() {
		parent::__construct( 'CategoryTree', '', true );
	}

	/**
	 * @param $name
	 * @return mixed
	 */
	function getOption( $name ) {
		global $wgCategoryTreeDefaultOptions;

		if ( $this->tree ) {
			return $this->tree->getOption( $name );
		} else {
			return $wgCategoryTreeDefaultOptions[$name];
		}
	}

	/**
	 * Main execution function
	 * @param $par array Parameters passed to the page
	 */
	function execute( $par ) {
		global $wgCategoryTreeDefaultOptions, $wgCategoryTreeSpecialPageOptions, $wgCategoryTreeForceHeaders;

		$this->setHeaders();
		$request = $this->getRequest();
		if ( $par ) {
			$this->target = $par;
		} else {
			$this->target = $request->getVal( 'target', wfMessage( 'rootcategory' )->text() );
		}

		$this->target = trim( $this->target );

		# HACK for undefined root category
		if ( $this->target == '<rootcategory>' || $this->target == '&lt;rootcategory&gt;' ) {
			$this->target = null;
		}

		$options = array();

		# grab all known options from the request. Normalization is done by the CategoryTree class
		foreach ( $wgCategoryTreeDefaultOptions as $option => $default ) {
			if ( isset( $wgCategoryTreeSpecialPageOptions[$option] ) ) {
				$default = $wgCategoryTreeSpecialPageOptions[$option];
			}

			$options[$option] = $request->getVal( $option, $default );
		}

		$this->tree = new CategoryTree( $options );

		$output = $this->getOutput();
		$output->addWikiMsg( 'categorytree-header' );

		$this->executeInputForm();

		if ( $this->target !== '' && $this->target !== null ) {
			if ( !$wgCategoryTreeForceHeaders ) {
				CategoryTree::setHeaders( $output );
			}

			$title = CategoryTree::makeTitle( $this->target );

			if ( $title && $title->getArticleID() ) {
				$output->addHTML( Xml::openElement( 'div', array( 'class' => 'CategoryTreeParents' ) ) );
				$output->addHTML( wfMessage( 'categorytree-parents' )->parse() );
				$output->addHTML( wfMessage( 'colon-separator' )->escaped() );

				$parents = $this->tree->renderParents( $title );

				if ( $parents == '' ) {
					$output->addHTML( wfMessage( 'categorytree-no-parent-categories' )->parse() );
				} else {
					$output->addHTML( $parents );
				}

				$output->addHTML( Xml::closeElement( 'div' ) );

				$output->addHTML( Xml::openElement( 'div', array( 'class' => 'CategoryTreeResult' ) ) );
				$output->addHTML( $this->tree->renderNode( $title, 1 ) );
				$output->addHTML( Xml::closeElement( 'div' ) );
			} else {
				$output->addHTML( Xml::openElement( 'div', array( 'class' => 'CategoryTreeNotice' ) ) );
				$output->addHTML( wfMessage( 'categorytree-not-found', $this->target )->parse() );
				$output->addHTML( Xml::closeElement( 'div' ) );
			}
		}
	}

	/**
	 * Input form for entering a category
	 */
	function executeInputForm() {
		global $wgScript;
		$thisTitle = SpecialPage::getTitleFor( $this->getName() );
		$namespaces = $this->getRequest()->getVal( 'namespaces', '' );
		//mode may be overriden by namespaces option
		$mode = ( $namespaces == '' ? $this->getOption( 'mode' ) : CT_MODE_ALL );
		$modeSelector = Xml::openElement( 'select', array( 'name' => 'mode' ) );
		$modeSelector .= Xml::option( wfMessage( 'categorytree-mode-categories' )->plain(), 'categories', $mode == CT_MODE_CATEGORIES );
		$modeSelector .= Xml::option( wfMessage( 'categorytree-mode-pages' )->plain(), 'pages', $mode == CT_MODE_PAGES );
		$modeSelector .= Xml::option( wfMessage( 'categorytree-mode-all' )->plain(), 'all', $mode == CT_MODE_ALL );
		$modeSelector .= Xml::closeElement( 'select' );
		$table = Xml::buildForm( array(
			'categorytree-category' => Xml::input( 'target', 20, $this->target, array( 'id' => 'target' ) ) ,
			'categorytree-mode-label' => $modeSelector,
			'namespace' => Html::namespaceSelector(
				array( 'selected' => $namespaces, 'all' => '' ),
				array( 'name' => 'namespaces', 'id' => 'namespaces' )
			)
		), 'categorytree-go' );
		$preTable = Xml::element( 'legend', null, wfMessage( 'categorytree-legend' )->plain() );
		$preTable .= Html::Hidden( 'title', $thisTitle->getPrefixedDbKey() );
		$fieldset = Xml::tags( 'fieldset', array(), $preTable . $table );
		$output = $this->getOutput();
		$output->addHTML( Xml::tags( 'form', array( 'name' => 'categorytree', 'method' => 'get', 'action' => $wgScript, 'id' => 'mw-categorytree-form' ), $fieldset ) );
	}
}
