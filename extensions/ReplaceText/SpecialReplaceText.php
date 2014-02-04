<?php

class ReplaceText extends SpecialPage {
	private $target, $replacement, $use_regex, $category, $prefix, $edit_pages, $move_pages, $selected_namespaces;

	public function __construct() {
		parent::__construct( 'ReplaceText', 'replacetext' );
	}

	function execute( $query ) {
		if ( !$this->getUser()->isAllowed( 'replacetext' ) ) {
			throw new PermissionsError( 'replacetext' );
		}

		$this->setHeaders();
		$out = $this->getOutput();
		if ( !is_null( $out->getResourceLoader()->getModule( 'mediawiki.special' ) ) ) {
			$out->addModuleStyles( 'mediawiki.special' );
		}
		$this->doSpecialReplaceText();
	}

	function getSelectedNamespaces() {
		$all_namespaces = SearchEngine::searchableNamespaces();
		$selected_namespaces = array();
		foreach ( $all_namespaces as $ns => $name ) {
			if ( $this->getRequest()->getCheck( 'ns' . $ns ) ) {
				$selected_namespaces[] = $ns;
			}
		}
		return $selected_namespaces;
	}

	/**
	 * Helper function to display a hidden field for different versions
	 * of MediaWiki.
	 */
	function hiddenField( $name, $value ) {
		return "\t" . Html::hidden( $name, $value ) . "\n";
	}

	function doSpecialReplaceText() {
		wfProfileIn( __METHOD__ );
		$out = $this->getOutput();
		$request = $this->getRequest();

		$linker = class_exists( 'DummyLinker' ) ? new DummyLinker : new Linker;

		$this->target = $request->getText( 'target' );
		$this->replacement = $request->getText( 'replacement' );
		$this->use_regex = $request->getBool( 'use_regex' );
		$this->category = $request->getText( 'category' );
		$this->prefix = $request->getText( 'prefix' );
		$this->edit_pages = $request->getBool( 'edit_pages' );
		$this->move_pages = $request->getBool( 'move_pages' );
		$this->selected_namespaces = $this->getSelectedNamespaces();

		if ( $request->getCheck( 'continue' ) && $this->target === '' ) {
			$this->showForm( 'replacetext_givetarget' );
			wfProfileOut( __METHOD__ );
			return;
		}

		if ( $request->getCheck( 'replace' ) ) {
			$replacement_params = array();
			$replacement_params['user_id'] = $this->getUser()->getId();
			$replacement_params['target_str'] = $this->target;
			$replacement_params['replacement_str'] = $this->replacement;
			$replacement_params['use_regex'] = $this->use_regex;
			$replacement_params['edit_summary'] = $this->msg(
				'replacetext_editsummary',
				$this->target, $this->replacement
			)->inContentLanguage()->plain();
			$replacement_params['create_redirect'] = false;
			$replacement_params['watch_page'] = false;
			foreach ( $request->getValues() as $key => $value ) {
				if ( $key == 'create-redirect' && $value == '1' ) {
					$replacement_params['create_redirect'] = true;
				} elseif ( $key == 'watch-pages' && $value == '1' ) {
					$replacement_params['watch_page'] = true;
				}
			}
			$jobs = array();
			foreach ( $request->getValues() as $key => $value ) {
				if ( $value == '1' && $key !== 'replace' && $key !== 'use_regex' ) {
					if ( strpos( $key, 'move-' ) !== false ) {
						$title = Title::newFromID( substr( $key, 5 ) );
						$replacement_params['move_page'] = true;
					} else {
						$title = Title::newFromID( $key );
					}
					if ( $title !== null )
						$jobs[] = new ReplaceTextJob( $title, $replacement_params );
				}
			}
			Job::batchInsert( $jobs );

			$count = $this->getLanguage()->formatNum( count( $jobs ) );
			$out->addWikiMsg(
				'replacetext_success',
				"<code><nowiki>{$this->target}</nowiki></code>",
				"<code><nowiki>{$this->replacement}</nowiki></code>",
				$count
			);

			// Link back
			$out->addHTML(
				$linker->link( $this->getTitle(),
					$this->msg( 'replacetext_return' )->escaped() )
			);

			wfProfileOut( __METHOD__ );
			return;
		} elseif ( $request->getCheck( 'target' ) ) { // very long elseif, look for "end elseif"
			// first, check that at least one namespace has been
			// picked, and that either editing or moving pages
			// has been selected
			if ( count( $this->selected_namespaces ) == 0 ) {
				$this->showForm( 'replacetext_nonamespace' );
				wfProfileOut( __METHOD__ );
				return;
			}
			if ( ! $this->edit_pages && ! $this->move_pages ) {
				$this->showForm( 'replacetext_editormove' );
				wfProfileOut( __METHOD__ );
				return;
			}

			$titles_for_edit = array();
			$titles_for_move = array();
			$unmoveable_titles = array();

			// if user is replacing text within pages...
			if ( $this->edit_pages ) {
				$res = $this->doSearchQuery(
					$this->target,
					$this->selected_namespaces,
					$this->category,
					$this->prefix,
					$this->use_regex
				);

				foreach ( $res as $row ) {
					$title = Title::makeTitleSafe( $row->page_namespace, $row->page_title );
					$context = $this->extractContext( $row->old_text, $this->target, $this->use_regex );
					$titles_for_edit[] = array( $title, $context );
				}
			}
			if ( $this->move_pages ) {
				$res = $this->getMatchingTitles(
					$this->target,
					$this->selected_namespaces,
					$this->category,
					$this->prefix,
					$this->use_regex
				);

				foreach ( $res as $row ) {
					$title = Title::makeTitleSafe( $row->page_namespace, $row->page_title );
					// see if this move can happen
					$cur_page_name = str_replace( '_', ' ', $row->page_title );

					if ( $this->use_regex ) {
						$new_page_name = preg_replace( "/".$this->target."/U", $this->replacement, $cur_page_name );
					} else {
						$new_page_name = str_replace( $this->target, $this->replacement, $cur_page_name );
					}

					$new_title = Title::makeTitleSafe( $row->page_namespace, $new_page_name );
					$err = $title->isValidMoveOperation( $new_title );

					if ( $title->userCan( 'move' ) && !is_array( $err ) ) {
						$titles_for_move[] = $title;
					} else {
						$unmoveable_titles[] = $title;
					}
				}
			}

			// if no results were found, check to see if a bad
			// category name was entered
			if ( count( $titles_for_edit ) == 0 && count( $titles_for_move ) == 0 ) {
				$bad_cat_name = false;

				if ( ! empty( $this->category ) ) {
					$category_title = Title::makeTitleSafe( NS_CATEGORY, $this->category );
					if ( ! $category_title->exists() ) $bad_cat_name = true;
				}

				if ( $bad_cat_name ) {
					$link = $linker->link( $category_title, htmlspecialchars( ucfirst( $this->category ) ) );
					$out->addHTML(
						$this->msg( 'replacetext_nosuchcategory' )->rawParams( $link )->escaped()
					);
				} else {
					if ( $this->edit_pages ) {
						$out->addWikiMsg( 'replacetext_noreplacement', "<code><nowiki>{$this->target}</nowiki></code>" );
					}

					if ( $this->move_pages ) {
						$out->addWikiMsg( 'replacetext_nomove', "<code><nowiki>{$this->target}</nowiki></code>" );
					}
				}
				// link back to starting form
				$out->addHTML( '<p>' . $linker->link( $this->getTitle(), $this->msg( 'replacetext_return' )->escaped() ) . '</p>' );
			} else {
				// Show a warning message if the replacement
				// string is either blank or found elsewhere on
				// the wiki (since undoing the replacement
				// would be difficult in either case).
				$warning_msg = null;

				if ( $this->replacement === '' ) {
					$warning_msg = $this->msg('replacetext_blankwarning')->text();
				} elseif ( count( $titles_for_edit ) > 0 ) {
					$res = $this->doSearchQuery( $this->replacement, $this->selected_namespaces, $this->category, $this->prefix, $this->use_regex );
					$count = $res->numRows();
					if ( $count > 0 ) {
						$warning_msg = $this->msg( 'replacetext_warning' )->numParams( $count )
							->params( "<code><nowiki>{$this->replacement}</nowiki></code>" )->text();
					}
				} elseif ( count( $titles_for_move ) > 0 ) {
					$res = $this->getMatchingTitles( $this->replacement, $this->selected_namespaces, $this->category, $this->prefix, $this->use_regex );
					$count = $res->numRows();
					if ( $count > 0 ) {
						$warning_msg = $this->msg( 'replacetext_warning' )->numParams( $count )
							->params( $this->replacement )->text();
					}
				}

				if ( ! is_null( $warning_msg ) ) {
					$out->addWikiText("<div class=\"errorbox\">$warning_msg</div><br clear=\"both\" />");
				}

				$this->pageListForm( $titles_for_edit, $titles_for_move, $unmoveable_titles );
			}
			wfProfileOut( __METHOD__ );
			return;
		}

		// if we're still here, show the starting form
		$this->showForm();
		wfProfileOut( __METHOD__ );
	}

	function showForm( $warning_msg = null ) {
		global $wgVersion;

		$out = $this->getOutput();

		$out->addHTML(
			Xml::openElement(
				'form',
				array(
					'id' => 'powersearch',
					'action' => $this->getTitle()->getFullUrl(),
					'method' => 'post'
				)
			) . "\n" .
			$this->hiddenField( 'title', $this->getTitle()->getPrefixedText() ) .
			$this->hiddenField( 'continue', 1 )
		);
		if ( is_null( $warning_msg ) ) {
			$out->addWikiMsg( 'replacetext_docu' );
		} else {
			$out->wrapWikiMsg(
				"<div class=\"errorbox\">\n$1\n</div><br clear=\"both\" />",
				$warning_msg
			);
		}

		$out->addHTML( '<table><tr><td style="vertical-align: top;">' );
		$out->addWikiMsg( 'replacetext_originaltext' );
		$out->addHTML( '</td><td>' );
		// 'width: auto' style is needed to override MediaWiki's
		// normal 'width: 100%', which causes the textarea to get
		// zero width in IE
		$out->addHTML( Xml::textarea( 'target', $this->target, 50, 2, array( 'style' => 'width: auto;' ) ) );
		$out->addHTML( '</td></tr><tr><td style="vertical-align: top;">' );
		$out->addWikiMsg( 'replacetext_replacementtext' );
		$out->addHTML( '</td><td>' );
		$out->addHTML( Xml::textarea( 'replacement', $this->replacement, 50, 2, array( 'style' => 'width: auto;' ) ) );
		$out->addHTML( '</td></tr></table>' );
		$out->addHTML( Xml::tags( 'p', null,
				Xml::checkLabel(
					$this->msg( 'replacetext_useregex' )->text(),
					'use_regex', 'use_regex'
				)
			) . "\n" .
			Xml::element( 'p',
				array( 'style' => 'font-style: italic' ),
				$this->msg( 'replacetext_regexdocu' )->text()
			)
		);

		// The interface is heavily based on the one in Special:Search.
		$namespaces = SearchEngine::searchableNamespaces();
		$tables = $this->namespaceTables( $namespaces );
		$out->addHTML(
			"<div class=\"mw-search-formheader\"></div>\n" .
			"<fieldset id=\"mw-searchoptions\">\n" . 
			Xml::tags( 'h4', null, $this->msg( 'powersearch-ns' )->parse() )
		);
		// The ability to select/unselect groups of namespaces in the
		// search interface exists only in some skins, like Vector -
		// check for the presence of the 'powersearch-togglelabel'
		// message to see if we can use this functionality here.
		if ( $this->msg( 'powersearch-togglelabel' )->isDisabled() ) {
			// do nothing
		} elseif ( version_compare( $wgVersion, '1.20', '>=' ) ) {
			// In MediaWiki 1.20, this became a lot simpler after
			// the main work was passed off to Javascript
			$out->addHTML(
				Html::element(
					'div',
					array( 'id' => 'mw-search-togglebox' )
				)
			);
		} else { // MW <= 1.19
			$out->addHTML(
				Xml::tags(
					'div',
					array( 'id' => 'mw-search-togglebox' ),
					Xml::label( $this->msg( 'powersearch-togglelabel' )->text(), 'mw-search-togglelabel' ) .
					Xml::element(
						'input',
						array(
							'type'=>'button',
							'id' => 'mw-search-toggleall',
							// 'onclick' value needed for MW 1.16
							'onclick' => 'mwToggleSearchCheckboxes("all");',
							'value' => $this->msg( 'powersearch-toggleall' )->text()
						)
					) .
					Xml::element(
						'input',
						array(
							'type'=>'button',
							'id' => 'mw-search-togglenone',
							// 'onclick' value needed for MW 1.16
							'onclick' => 'mwToggleSearchCheckboxes("none");',
							'value' => $this->msg( 'powersearch-togglenone' )->text()
						)
					)
				)
			);
		} // end if
		$out->addHTML(
			Xml::element( 'div', array( 'class' => 'divider' ), '', false ) .
			"$tables\n</fieldset>"
		);
		// @todo FIXME: raw html messages
		$category_search_label = $this->msg( 'replacetext_categorysearch' )->text();
		$prefix_search_label = $this->msg( 'replacetext_prefixsearch' )->text();
		$out->addHTML(
			"<fieldset id=\"mw-searchoptions\">\n" . 
			Xml::tags( 'h4', null, $this->msg( 'replacetext_optionalfilters' )->parse() ) .
			Xml::element( 'div', array( 'class' => 'divider' ), '', false ) .
			"<p>$category_search_label\n" .
			Xml::input( 'category', 20, $this->category, array( 'type' => 'text' ) ) . '</p>' .
			"<p>$prefix_search_label\n" .
			Xml::input( 'prefix', 20, $this->prefix, array( 'type' => 'text' ) ) . '</p>' .
			"</fieldset>\n" .
			"<p>\n" .
			Xml::checkLabel( $this->msg( 'replacetext_editpages' )->text(), 'edit_pages', 'edit_pages', true ) . '<br />' .
			Xml::checkLabel( $this->msg( 'replacetext_movepages' )->text(), 'move_pages', 'move_pages' ) .
			"</p>\n" .
			Xml::submitButton( $this->msg( 'replacetext_continue' )->text() ) .
			Xml::closeElement( 'form' )
		);
		// Add Javascript specific to Special:Search
		$out->addModules( 'mediawiki.special.search' );
	}

	/**
	 * Copied almost exactly from MediaWiki's SpecialSearch class, i.e.
	 * the search page
	 */
	function namespaceTables( $namespaces, $rowsPerTable = 3 ) {
		global $wgContLang;
		// Group namespaces into rows according to subject.
		// Try not to make too many assumptions about namespace numbering.
		$rows = array();
		$tables = "";
		foreach ( $namespaces as $ns => $name ) {
			$subj = MWNamespace::getSubject( $ns );
			if ( !array_key_exists( $subj, $rows ) ) {
				$rows[$subj] = "";
			}
			$name = str_replace( '_', ' ', $name );
			if ( '' == $name ) {
				$name = $this->msg( 'blanknamespace' )->text();
			}
			$rows[$subj] .= Xml::openElement( 'td', array( 'style' => 'white-space: nowrap' ) ) .
				Xml::checkLabel( $name, "ns{$ns}", "mw-search-ns{$ns}", in_array( $ns, $namespaces ) ) .
				Xml::closeElement( 'td' ) . "\n";
		}
		$rows = array_values( $rows );
		$numRows = count( $rows );
		// Lay out namespaces in multiple floating two-column tables so they'll
		// be arranged nicely while still accommodating different screen widths
		// Float to the right on RTL wikis
		$tableStyle = $wgContLang->isRTL() ?
			'float: right; margin: 0 0 0em 1em' : 'float: left; margin: 0 1em 0em 0';
		// Build the final HTML table...
		for ( $i = 0; $i < $numRows; $i += $rowsPerTable ) {
			$tables .= Xml::openElement( 'table', array( 'style' => $tableStyle ) );
			for ( $j = $i; $j < $i + $rowsPerTable && $j < $numRows; $j++ ) {
				$tables .= "<tr>\n" . $rows[$j] . "</tr>";
			}
			$tables .= Xml::closeElement( 'table' ) . "\n";
		}
		return $tables;
	}

	function pageListForm( $titles_for_edit, $titles_for_move, $unmoveable_titles ) {
		global $wgLang, $wgScriptPath;

		$out = $this->getOutput();
		$linker = class_exists( 'DummyLinker' ) ? new DummyLinker : new Linker;

		$formOpts = array( 'id' => 'choose_pages', 'method' => 'post', 'action' => $this->getTitle()->getFullUrl() );
		$out->addHTML(
			Xml::openElement( 'form', $formOpts ) . "\n" .
			$this->hiddenField( 'title', $this->getTitle()->getPrefixedText() ) .
			$this->hiddenField( 'target', $this->target ) .
			$this->hiddenField( 'replacement', $this->replacement ) .
			$this->hiddenField( 'use_regex', $this->use_regex )
		);

		$out->addScriptFile( "$wgScriptPath/extensions/ReplaceText/ReplaceText.js" );

		if ( count( $titles_for_edit ) > 0 ) {
			$out->addWikiMsg( 'replacetext_choosepagesforedit', "<code><nowiki>{$this->target}</nowiki></code>", "<code><nowiki>{$this->replacement}</nowiki></code>",
				$wgLang->formatNum( count( $titles_for_edit ) ) );

			foreach ( $titles_for_edit as $title_and_context ) {
				/**
				 * @var $title Title
				 */
				list( $title, $context ) = $title_and_context;
				$out->addHTML(
					Xml::check( $title->getArticleID(), true ) .
					$linker->link( $title ) . " - <small>$context</small><br />\n"
				);
			}
			$out->addHTML( '<br />' );
		}

		if ( count( $titles_for_move ) > 0 ) {
			$out->addWikiMsg( 'replacetext_choosepagesformove', $this->target, $this->replacement, $wgLang->formatNum( count( $titles_for_move ) ) );
			foreach ( $titles_for_move as $title ) {
				$out->addHTML(
					Xml::check( 'move-' . $title->getArticleID(), true ) .
					$linker->link( $title ) . "<br />\n"
				);
			}
			$out->addHTML( '<br />' );
			$out->addWikiMsg( 'replacetext_formovedpages' );
			$out->addHTML(
				Xml::checkLabel( $this->msg( 'replacetext_savemovedpages' )->text(), 'create-redirect', 'create-redirect', true ) . "<br />\n" .
				Xml::checkLabel( $this->msg( 'replacetext_watchmovedpages' )->text(), 'watch-pages', 'watch-pages', false )
			);
			$out->addHTML( '<br />' );
		}

		$out->addHTML(
			"<br />\n" .
			Xml::submitButton( $this->msg( 'replacetext_replace' )->text() ) . "\n" .
			$this->hiddenField( 'replace', 1 )
		);

		// Only show "invert selections" link if there are more than
		// five pages.
		if ( count( $titles_for_edit ) + count( $titles_for_move ) > 5 ) {
			$buttonOpts = array(
				'type' => 'button',
				'value' => $this->msg( 'replacetext_invertselections' )->text(),
				'onclick' => 'invertSelections(); return false;'
			);

			$out->addHTML(
				Xml::element( 'input', $buttonOpts )
			);
		}

		$out->addHTML( '</form>' );

		if ( count( $unmoveable_titles ) > 0 ) {
			$out->addWikiMsg( 'replacetext_cannotmove', $wgLang->formatNum( count( $unmoveable_titles ) ) );
			$text = "<ul>\n";
			foreach ( $unmoveable_titles as $title ) {
				$text .= "<li>{$linker->link( $title )}<br />\n";
			}
			$text .= "</ul>\n";
			$out->addHTML( $text );
		}
	}

	/**
	 * Extract context and highlights search text
	 *
	 * @todo The bolding needs to be fixed for regular expressions.
	 */
	function extractContext( $text, $target, $use_regex = false ) {
		global $wgLang;

		wfProfileIn( __METHOD__ );
		$cw = $this->getUser()->getOption( 'contextchars', 40 );

		// Get all indexes
		if ( $use_regex ) {
			preg_match_all( "/$target/", $text, $matches, PREG_OFFSET_CAPTURE );
		} else {
			$targetq = preg_quote( $target, '/' );
			preg_match_all( "/$targetq/", $text, $matches, PREG_OFFSET_CAPTURE );
		}

		$poss = array();
		foreach ( $matches[0] as $_ ) {
			$poss[] = $_[1];
		}

		$cuts = array();
		for ( $i = 0; $i < count( $poss ); $i++ ) {
			$index = $poss[$i];
			$len = strlen( $target );

			// Merge to the next if possible
			while ( isset( $poss[$i + 1] ) ) {
				if ( $poss[$i + 1] < $index + $len + $cw * 2 ) {
					$len += $poss[$i + 1] - $poss[$i];
					$i++;
				} else {
					break; // Can't merge, exit the inner loop
				}
			}
			$cuts[] = array( $index, $len );
		}

		$context = '';
		foreach ( $cuts as $_ ) {
			list( $index, $len, ) = $_;
			$context .= $this->convertWhiteSpaceToHTML(
				$wgLang->truncate( substr( $text, 0, $index ), - $cw, '...', false )
			);
			$snippet = $this->convertWhiteSpaceToHTML( substr( $text, $index, $len ) );
			if ( $use_regex ) {
				$targetStr = "/$target/U";
			} else {
				$targetq = preg_quote( $this->convertWhiteSpaceToHTML( $target ), '/' );
				$targetStr = "/$targetq/i";
			}
			$context .= preg_replace( $targetStr, '<span class="searchmatch">\0</span>', $snippet );

			$context .= $this->convertWhiteSpaceToHTML(
				$wgLang->truncate( substr( $text, $index + $len ), $cw, '...', false )
			);
		}
		wfProfileOut( __METHOD__ );
		return $context;
	}

	private function convertWhiteSpaceToHTML( $msg ) {
		$msg = htmlspecialchars( $msg );
		$msg = preg_replace( '/^ /m', '&#160; ', $msg );
		$msg = preg_replace( '/ $/m', ' &#160;', $msg );
		$msg = preg_replace( '/  /', '&#160; ', $msg );
		# $msg = str_replace( "\n", '<br />', $msg );
		return $msg;
	}

	function getMatchingTitles( $str, $namespaces, $category, $prefix, $use_regex = false ) {
		$dbr = wfGetDB( DB_SLAVE );

		$tables = array( 'page' );
		$vars = array( 'page_title', 'page_namespace' );

		$str = str_replace( ' ', '_', $str );
		if ( $use_regex ) {
			$comparisonCond = $this->regexCond( $dbr, 'page_title', $str );
		} else {
			$any = $dbr->anyString();
			$comparisonCond = 'page_title ' . $dbr->buildLike( $any, $str, $any );
		}
		$conds = array(
			$comparisonCond,
			'page_namespace' => $namespaces,
		);

		$this->categoryCondition( $category, $tables, $conds );
		$this->prefixCondition( $prefix, $conds );
		$sort = array( 'ORDER BY' => 'page_namespace, page_title' );

		return $dbr->select( $tables, $vars, $conds, __METHOD__ , $sort );
	}

	function doSearchQuery( $search, $namespaces, $category, $prefix, $use_regex = false ) {
		$dbr = wfGetDB( DB_SLAVE );
		$tables = array( 'page', 'revision', 'text' );
		$vars = array( 'page_id', 'page_namespace', 'page_title', 'old_text' );
		if ( $use_regex ) {
			$comparisonCond = $this->regexCond( $dbr, 'old_text', $search );
		} else {
			$any = $dbr->anyString();
			$comparisonCond = 'old_text ' . $dbr->buildLike( $any, $search, $any );
		}
		$conds = array(
			$comparisonCond,
			'page_namespace' => $namespaces,
			'rev_id = page_latest',
			'rev_text_id = old_id'
		);

		$this->categoryCondition( $category, $tables, $conds );
		$this->prefixCondition( $prefix, $conds );
		$sort = array( 'ORDER BY' => 'page_namespace, page_title' );

		return $dbr->select( $tables, $vars, $conds, __METHOD__ , $sort );
	}

	protected function categoryCondition( $category, &$tables, &$conds ) {
		if ( strval( $category ) !== '' ) {
			$category = Title::newFromText( $category )->getDbKey();
			$tables[] = 'categorylinks';
			$conds[] = 'page_id = cl_from';
			$conds['cl_to'] = $category;
		}
	}

	protected function prefixCondition( $prefix, &$conds ) {
		if ( strval( $prefix ) === '' ) {
			return;
		}

		$dbr = wfGetDB( DB_SLAVE );
		$title = Title::newFromText( $prefix );
		if ( !is_null( $title ) ) {
			$prefix = $title->getDbKey();
		}
		$any = $dbr->anyString();
		$conds[] = 'page_title ' . $dbr->buildLike( $prefix, $any );
	}

	private function regexCond( $dbr, $column, $regex ) {
		if ( $dbr instanceof DatabasePostgres ) {
			$op = '~';
		} else {
			$op = 'REGEXP';
		}
		return "$column $op " . $dbr->addQuotes( $regex );
	}
}
