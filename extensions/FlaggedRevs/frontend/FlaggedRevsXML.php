<?php
/**
 * Class containing utility XML functions for a FlaggedRevs.
 * Includes functions for selectors, icons, notices, CSS, and form aspects.
 */
class FlaggedRevsXML {
	/**
	 * Get the URL path to where the client side resources are (JS, CSS, images..)
	 * @return string
	 */
	public static function styleUrlPath() {
		global $wgExtensionAssetsPath;
		return "$wgExtensionAssetsPath/FlaggedRevs/frontend/modules";
	}

	/**
	 * Get a selector of reviewable namespaces
	 * @param int $selected, namespace selected
	 * @param $all Mixed: Value of an item denoting all namespaces, or null to omit
	 * @return string
	 */
	public static function getNamespaceMenu( $selected = null, $all = null ) {
		global $wgContLang;
		$namespaces = FlaggedRevs::getReviewNamespaces();
		$s = "<label for='namespace'>" . wfMessage( 'namespace' )->escaped() . "</label>";
		if ( $selected !== '' ) {
			if ( is_null( $selected ) ) {
				# No namespace selected; let exact match work without hitting Main
				$selected = '';
			} else {
				# Let input be numeric strings without breaking the empty match.
				$selected = intval( $selected );
			}
		}
		$s .= "\n<select id='namespace' name='namespace' class='namespaceselector'>\n";
		$arr = $wgContLang->getFormattedNamespaces();
		if ( !is_null( $all ) ) {
			$arr = array( $all => wfMessage( 'namespacesall' )->text() ) + $arr; // should be first
		}
		foreach ( $arr as $index => $name ) {
			# Content pages only (except 'all')
			if ( $index !== $all && !in_array( $index, $namespaces ) ) {
				continue;
			}
			$name = $index !== 0 ? $name : wfMessage( 'blanknamespace' )->text();
			if ( $index === $selected ) {
				$s .= "\t" . Xml::element( "option", array( "value" => $index,
					"selected" => "selected" ), $name ) . "\n";
			} else {
				$s .= "\t" . Xml::element( "option", array( "value" => $index ), $name ) . "\n";
			}
		}
		$s .= "</select>\n";
		return $s;
	}

	/**
	 * Get a selector of review levels. Used for filters.
	 * @param int $selected, selected level
	 * @param string $all, all selector msg?
	 * @param int $max max level?
	 * @return string
	 */
	public static function getLevelMenu(
		$selected = null, $all = 'revreview-filter-all', $max = 2
	) {
		$s = "<label for='wpLevel'>" . wfMessage( 'revreview-levelfilter' )->escaped() . "</label>\n";
		$s .= Xml::openElement( 'select', array( 'name' => 'level', 'id' => 'wpLevel' ) );
		if ( $all !== false ) {
			$s .= Xml::option( wfMessage( $all )->text(), - 1, $selected === - 1 );
		}
		$s .= Xml::option( wfMessage( 'revreview-lev-basic' )->text(), 0, $selected === 0 );
		if ( FlaggedRevs::qualityVersions() ) {
			$s .= Xml::option( wfMessage( 'revreview-lev-quality' )->text(), 1, $selected === 1 );
		}
		if ( $max >= 2 && FlaggedRevs::pristineVersions() ) {
			$s .= Xml::option( wfMessage( 'revreview-lev-pristine' )->text(), 2, $selected === 2 );
		}
		# Note: Pristine not tracked at sp:QualityOversight (counts as quality)
		$s .= Xml::closeElement( 'select' ) . "\n";
		return $s;
	}

	/**
	 * Get a <select> of default page version (stable or draft). Used for filters.
	 * @param int $selected (0=draft, 1=stable, null=either )
	 * @return string
	 */
	public static function getDefaultFilterMenu( $selected = null ) {
		if ( is_null( $selected ) ) {
			$selected = ''; // "all"
		}
		$s = Xml::label( wfMessage( 'revreview-defaultfilter' )->text(), 'wpStable' ) . "\n";
		$s .= Xml::openElement( 'select',
			array( 'name' => 'stable', 'id' => 'wpStable' ) );
		$s .= Xml::option( wfMessage( 'revreview-def-all' )->text(), '', $selected == '' );
		$s .= Xml::option( wfMessage( 'revreview-def-stable' )->text(), 1, $selected === 1 );
		$s .= Xml::option( wfMessage( 'revreview-def-draft' )->text(), 0, $selected === 0 );
		$s .= Xml::closeElement( 'select' ) . "\n";
		return $s;
	}

	/**
	 * Get a <select> of options of 'autoreview' restriction levels. Used for filters.
	 * @param string $selected ('' for "any", 'none' for none)
	 * @return string
	 */
	public static function getRestrictionFilterMenu( $selected = '' ) {
		if ( is_null( $selected ) ) {
			$selected = ''; // "all"
		}
		$s = Xml::label( wfMessage( 'revreview-restrictfilter' )->text(), 'wpRestriction' ) . "\n";
		$s .= Xml::openElement( 'select',
			array( 'name' => 'restriction', 'id' => 'wpRestriction' ) );
		$s .= Xml::option( wfMessage( 'revreview-restriction-any' )->text(), '', $selected == '' );
		if ( !FlaggedRevs::useProtectionLevels() ) {
			# All "protected" pages have a protection level, not "none"
			$s .= Xml::option( wfMessage( 'revreview-restriction-none' )->text(),
				'none', $selected == 'none' );
		}
		foreach ( FlaggedRevs::getRestrictionLevels() as $perm ) {
			$key = "revreview-restriction-{$perm}";
			if ( wfMessage( $key )->isDisabled() ) {
				$msg = $perm; // fallback to user right key
			} else {
				$msg = wfMessage( $key )->text();
			}
			$s .= Xml::option( $msg, $perm, $selected == $perm );
		}
		$s .= Xml::closeElement( 'select' ) . "\n";
		return $s;
	}

	/**
	 * Get a selector of "approved"/"unapproved". Used for filters.
	 * @param int $selected, selected level
	 * @return string
	 */
	public static function getStatusFilterMenu( $selected = null ) {
		$s = "<label for='wpStatus'>" . wfMessage( 'revreview-statusfilter' )->escaped() . "</label>\n";
		$s .= Xml::openElement( 'select', array( 'name' => 'status', 'id' => 'wpStatus' ) );
		$s .= Xml::option( wfMessage( "revreview-filter-all" )->text(), - 1, $selected === - 1 );
		$s .= Xml::option( wfMessage( "revreview-filter-approved" )->text(), 1, $selected === 1 );
		$s .= Xml::option( wfMessage( "revreview-filter-reapproved" )->text(), 2, $selected === 2 );
		$s .= Xml::option( wfMessage( "revreview-filter-unapproved" )->text(), 3, $selected === 3 );
		$s .= Xml::closeElement( 'select' ) . "\n";
		return $s;
	}

	/**
	 * Get a selector of "auto"/"manual". Used for filters.
	 * @param int $selected, selected level
	 * @return string
	 */
	public static function getAutoFilterMenu( $selected = null ) {
		$s = "<label for='wpApproved'>" . wfMessage( 'revreview-typefilter' )->escaped() . "</label>\n";
		$s .= Xml::openElement( 'select', array( 'name' => 'automatic', 'id' => 'wpApproved' ) );
		$s .= Xml::option( wfMessage( "revreview-filter-all" )->text(), - 1, $selected === - 1 );
		$s .= Xml::option( wfMessage( "revreview-filter-manual" )->text(), 0, $selected === 0 );
		$s .= Xml::option( wfMessage( "revreview-filter-auto" )->text(), 1, $selected === 1 );
		$s .= Xml::closeElement( 'select' ) . "\n";
		return $s;
	}

	/**
	 * @param int $quality
	 * @return string, css color for this quality
	 */
	public static function getQualityColor( $quality ) {
		if ( $quality === false ) {
			return 'flaggedrevs-color-0';
		}
		switch( $quality ) {
			case 2:
				$css = 'flaggedrevs-color-3';
				break;
			case 1:
				$css = 'flaggedrevs-color-2';
				break;
			case 0:
				$css = 'flaggedrevs-color-1';
				break;
			default:
				$css = '';
		}
		return $css;
	}

	/**
	 * @param array $flags
	 * @param bool $prettyBox
	 * @param string $css, class to wrap box in
	 * @return string
	 * Generates a review box/tag
	 */
	public static function addTagRatings( $flags, $prettyBox = false, $css = '' ) {
		$tag = '';
		if ( $prettyBox ) {
			$tag .= "<table id='mw-fr-revisionratings-box' align='center' class='$css' cellpadding='0'>";
		}
		foreach ( FlaggedRevs::getTags() as $quality ) {
			$level = isset( $flags[$quality] ) ? $flags[$quality] : 0;
			$encValueText = wfMessage( "revreview-$quality-$level" )->escaped();
			$level = $flags[$quality];

			$levelmarker = $level * 20 + 20;
			if ( $prettyBox ) {
				$tag .= "<tr><td class='fr-text' valign='middle'>" .
					wfMessage( "revreview-$quality" )->escaped() .
					"</td><td class='fr-value$levelmarker' valign='middle'>" .
					$encValueText . "</td></tr>\n";
			} else {
				$tag .= "&#160;<span class='fr-marker-$levelmarker'><strong>" .
					wfMessage( "revreview-$quality" )->escaped() .
					"</strong>: <span class='fr-text-value'>$encValueText&#160;</span>&#160;" .
					"</span>\n";
			}
		}
		if ( $prettyBox ) {
			$tag .= '</table>';
		}
		return $tag;
	}

	/**
	 * @param FlaggedRevision $frev, the reviewed version
	 * @param string $shtml Short message HTML
	 * @param int $revsSince, revisions since review
	 * @param string $type (stable/draft/oldstable)
	 * @param bool $synced, does stable=current and this is one of them?
	 * @return string
	 * Generates a review box using a table using FlaggedRevsXML::addTagRatings()
	 */
	public static function prettyRatingBox(
		$frev, $shtml, $revsSince, $type = 'oldstable', $synced = false
	) {
		global $wgLang;
		# Get quality level
		$flags = $frev->getTags();
		$quality = FlaggedRevs::isQuality( $flags );
		$pristine = FlaggedRevs::isPristine( $flags );
		$time = $wgLang->date( $frev->getTimestamp(), true );
		# Some checks for which tag CSS to use
		if ( $pristine ) {
			$color = 'flaggedrevs-color-3';
		} elseif ( $quality ) {
			$color = 'flaggedrevs-color-2';
		} else {
			$color = 'flaggedrevs-color-1';
		}
		# Construct some tagging
		if ( $synced && ( $type == 'stable' || $type == 'draft' ) ) {
			$msg = $quality ?
				'revreview-quality-same' : 'revreview-basic-same';
			$html = wfMessage( $msg, $frev->getRevId(), $time, $revsSince )->parse();
		} elseif ( $type == 'oldstable' ) {
			$msg = $quality ?
				'revreview-quality-old' : 'revreview-basic-old';
			$html = wfMessage( $msg, $frev->getRevId(), $time )->parse();
		} else {
			if ( $type == 'stable' ) {
				$msg = $quality ?
					'revreview-quality' : 'revreview-basic';
			} else { // draft
				$msg = $quality ?
					'revreview-newest-quality' : 'revreview-newest-basic';
			}
			# For searching: uses messages 'revreview-quality-i', 'revreview-basic-i',
			# 'revreview-newest-quality-i', 'revreview-newest-basic-i'
			$msg .= ( $revsSince == 0 ) ? '-i' : '';
			$html = wfMessage( $msg, $frev->getRevId(), $time, $revsSince )->parse();
		}
		# Make fancy box...
		$box = '<div class="flaggedrevs_short_basic">';
		$box .= $shtml . self::ratingArrow();
		$box .= "</div>\n";
		// For rel-absolute child div (the fly-out)
		$box .= '<div id="mw-fr-revisiondetails-wrapper" style="position:relative;">';
		$box .= Xml::openElement( 'div',
			array(
				'id'    => 'mw-fr-revisiondetails',
				'class' => 'flaggedrevs_short_details',
				'style' => 'display:none' )
		);
		$box .= $html; // details text
		# Add any rating tags as needed...
		if ( $flags && !FlaggedRevs::binaryFlagging() ) {
			# Don't show the ratings on draft views
			if ( $type == 'stable' || $type == 'oldstable' ) {
				$box .= '<p>' . self::addTagRatings( $flags, true, $color ) . '</p>';
			}
		}
		$box .= Xml::closeElement( 'div' ) . "\n";
		$box .= "</div>\n";
		return $box;
	}

	/**
	 * Generates JS toggle arrow icon
	 * @return string
	 */
	public static function ratingArrow() {
		$encPath = htmlspecialchars( self::styleUrlPath() . '/img' );
		$img = '<img id="mw-fr-revisiontoggle" class="fr-toggle-arrow"';
		$img .= " src=\"{$encPath}/arrow-down.png\" style=\"display:none;\"";
		$img .= ' alt="' . wfMessage( 'revreview-toggle-title' )->escaped() . '" />';
		return $img;
	}

	/**
	 * Generates (+/-) JS toggle HTML (monospace to keep things in place)
	 * @return string
	 */
	public static function ratingToggle() {
		return '<a id="mw-fr-revisiontoggle" class="fr-toggle-symbol"' .
			' style="display:none;" title="' . wfMessage( 'revreview-toggle-title' )->escaped() . '" >' .
			wfMessage( 'revreview-toggle-show' )->escaped() . '</a>';
	}

	/**
	 * Generates (show/hide) JS toggle HTML
	 * @return string
	 */
	public static function diffToggle() {
		$toggle = '<a class="fr-toggle-text" title="' .
			wfMessage( 'revreview-diff-toggle-title' )->escaped() . '" >' .
			wfMessage( 'revreview-diff-toggle-show' )->escaped() . '</a>';
		return '<span id="mw-fr-difftoggle" style="display:none;">' .
			wfMessage( 'parentheses' )->rawParams( $toggle )->escaped() . '</span>';
	}

	/**
	 * Generates (show/hide) JS toggle HTML
	 * @return string
	 */
	public static function logToggle() {
		$toggle = '<a class="fr-toggle-text" title="' .
			wfMessage( 'revreview-log-toggle-title' )->escaped() . '" >' .
			wfMessage( 'revreview-log-toggle-show' )->escaped() . '</a>';
		return '<span id="mw-fr-logtoggle" class="fr-logtoggle-excerpt" style="display:none;">' .
			wfMessage( 'parentheses' )->rawParams( $toggle )->escaped() . '</span>';
	}

	/**
	 * Generates (show/hide) JS toggle HTML
	 * @return string
	 */
	public static function logDetailsToggle() {
		$toggle = '<a class="fr-toggle-text" title="' .
			wfMessage( 'revreview-log-details-title' )->escaped() . '" >' .
			wfMessage( 'revreview-log-details-show' )->escaped() . '</a>';
		return '<span id="mw-fr-logtoggle" class="fr-logtoggle-details" style="display:none;">' .
			wfMessage( 'parentheses' )->rawParams( $toggle )->escaped() . '</span>';
	}

	/*
	 * Creates CSS draft page icon
	 * @return string
	 */
	public static function draftStatusIcon() {
		$encPath = htmlspecialchars( self::styleUrlPath() . '/img' );
		$encTitle = wfMessage( 'revreview-draft-title' )->escaped();
		return "<img class=\"flaggedrevs-icon\" src=\"$encPath/1.png\"" .
			" alt=\"$encTitle\" title=\"$encTitle\" />";
	}
	
	/*
	 * Creates CSS stable page icon
	 * @param bool $isQuality
	 * @return string
	 */
	public static function stableStatusIcon( $isQuality ) {
		$encPath = htmlspecialchars( self::styleUrlPath() . '/img' );
		$file = $isQuality ? '3.png' : '2.png';
		$encTitle = $isQuality
			? wfMessage( 'revreview-quality-title' )->escaped()
			: wfMessage( 'revreview-basic-title' )->escaped();
		return "<img class=\"flaggedrevs-icon\" src=\"$encPath/$file\"" .
			" alt=\"$encTitle\" title=\"$encTitle\" />";
	}

	/*
	 * Creates CSS lock icon if page is locked/unlocked
	 * @param FlaggableWikiPage $flaggedArticle
	 * @return string
	 */
	public static function lockStatusIcon( $flaggedArticle ) {
		$encPath = htmlspecialchars( self::styleUrlPath() . '/img' );
		if ( $flaggedArticle->isPageLocked() ) {
			$encTitle = wfMessage( 'revreview-locked-title' )->escaped();
			return "<img class=\"flaggedrevs-icon\" src=\"$encPath/doc-magnify.png\"" .
				" alt=\"$encTitle\" title=\"$encTitle\" />";
		} elseif ( $flaggedArticle->isPageUnlocked() ) {
			$encTitle = wfMessage( 'revreview-unlocked-title' )->escaped();
			return "<img class=\"flaggedrevs-icon\" src=\"$encPath/doc-check.png\"" .
				" alt=\"$encTitle\" title=\"$encTitle\" />";
		}

		return '';
	}

	/*
	 * @param FlaggableWikiPage $flaggedArticle
	 * @param FlaggedRevision $frev
	 * @param int $revsSince
	 * @return string
	 * Creates "stable rev reviewed on"/"x pending edits" message
	 */
	public static function pendingEditNotice( $flaggedArticle, $frev, $revsSince ) {
		global $wgLang;
		$flags = $frev->getTags();
		$time = $wgLang->date( $frev->getTimestamp(), true );
		# Add message text for pending edits
		$msg = FlaggedRevs::isQuality( $flags )
			? 'revreview-pending-quality'
			: 'revreview-pending-basic';
		$tag = wfMessage( $msg, $frev->getRevId(), $time, $revsSince )->parse();
		return $tag;
	}

	/*
	 * @param Page $article
	 * @return string
	 * Creates a stability log excerpt
	 */
	public static function stabilityLogExcerpt( $article ) {
		$logHtml = '';
		$params = array(
			'lim'   => 1,
			'flags' => LogEventsList::NO_EXTRA_USER_LINKS
		);
		LogEventsList::showLogExtract( $logHtml, 'stable',
			$article->getTitle()->getPrefixedText(), '', $params );
		return "<div id=\"mw-fr-logexcerpt\">$logHtml</div>";
	}
}
