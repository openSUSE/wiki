<?php

// Assumes $wgFlaggedRevsProtection is off
class Stabilization extends UnlistedSpecialPage {
	protected $form = null;

	public function __construct() {
		parent::__construct( 'Stabilization', 'stablesettings' );
	}

	public function execute( $par ) {
		$out = $this->getOutput();
		$user = $this->getUser();
		$request = $this->getRequest();

		$confirmed = $user->matchEditToken( $request->getVal( 'wpEditToken' ) );

		# Let anyone view, but not submit...
		if ( $request->wasPosted() ) {
			if ( !$user->isAllowed( 'stablesettings' ) ) {
				throw new PermissionsError( 'stablesettings' );
			}
			$block = $user->getBlock( !$confirmed );
			if ( $block ) {
				throw new UserBlockedError( $block );
			} elseif ( wfReadOnly() ) {
				throw new ReadOnlyError();
			}
		}
		# Set page title
		$this->setHeaders();

		# Target page
		$title = Title::newFromURL( $request->getVal( 'page', $par ) );
		if ( !$title ) {
			$out->showErrorPage( 'notargettitle', 'notargettext' );
			return;
		}
		$this->getSkin()->setRelevantTitle( $title );

		$this->form = new PageStabilityGeneralForm( $user );
		$form = $this->form; // convenience

		$form->setPage( $title );
		# Watch checkbox
		$form->setWatchThis( (bool)$request->getCheck( 'wpWatchthis' ) );
		# Get auto-review option...
		$form->setReviewThis( $request->getBool( 'wpReviewthis', true ) );
		# Reason
		$form->setReasonExtra( $request->getText( 'wpReason' ) );
		$form->setReasonSelection( $request->getVal( 'wpReasonSelection' ) );
		# Expiry
		$form->setExpiryCustom( $request->getText( 'mwStabilize-expiry' ) );
		$form->setExpirySelection( $request->getVal( 'wpExpirySelection' ) );
		# Default version
		$form->setOverride( (int)$request->getBool( 'wpStableconfig-override' ) );
		# Get autoreview restrictions...
		$form->setAutoreview( $request->getVal( 'mwProtect-level-autoreview' ) );
		$form->ready(); // params all set

		$status = $form->checkTarget();
		if ( $status === 'stabilize_page_notexists' ) {
			$out->addWikiMsg( 'stabilization-notexists', $title->getPrefixedText() );
			return;
		} elseif ( $status === 'stabilize_page_unreviewable' ) {
			$out->addWikiMsg( 'stabilization-notcontent', $title->getPrefixedText() );
			return;
		}

		# Form POST request...
		if ( $request->wasPosted() && $confirmed && $form->isAllowed() ) {
			$status = $form->submit();
			if ( $status === true ) {
				$out->redirect( $title->getFullUrl() );
			} else {
				$this->showForm( $this->msg( $status )->text() );
			}
		# Form GET request...
		} else {
			$form->preload();
			$this->showForm();
		}
	}

	public function showForm( $err = null ) {
		$out = $this->getOutput();

		$form = $this->form; // convenience
		$title = $this->form->getPage();
		$oldConfig = $form->getOldConfig();

		$s = ''; // form HTML string
		# Add any error messages
		if ( "" != $err ) {
			$out->setSubtitle( $this->msg( 'formerror' ) );
			$out->addHTML( "<p class='error'>{$err}</p>\n" );
		}
		# Add header text
		if ( !$form->isAllowed() ) {
			$s .= $this->msg( 'stabilization-perm', $title->getPrefixedText() )->parseAsBlock();
		} else {
			$s .= $this->msg( 'stabilization-text', $title->getPrefixedText() )->parseAsBlock();
		}
		# Borrow some protection messages for dropdowns
		$reasonDropDown = Xml::listDropDown(
			'wpReasonSelection',
			$this->msg( 'protect-dropdown' )->inContentLanguage()->text(),
			$this->msg( 'protect-otherreason-op' )->inContentLanguage()->text(),
			$form->getReasonSelection(),
			'mwStabilize-reason',
			4
		);
		$scExpiryOptions = $this->msg( 'protect-expiry-options' )->inContentLanguage()->text();
		$showProtectOptions = ( $scExpiryOptions !== '-' && $form->isAllowed() );
		$dropdownOptions = array(); // array of <label,value>
		# Add the current expiry as a dropdown option
		if ( $oldConfig['expiry'] && $oldConfig['expiry'] != 'infinity' ) {
			$timestamp = $this->getLanguage()->timeanddate( $oldConfig['expiry'] );
			$d = $this->getLanguage()->date( $oldConfig['expiry'] );
			$t = $this->getLanguage()->time( $oldConfig['expiry'] );
			$dropdownOptions[] = array(
				$this->msg( 'protect-existing-expiry', $timestamp, $d, $t )->text(), 'existing' );
		}
		# Add "other time" expiry dropdown option
		$dropdownOptions[] = array( $this->msg( 'protect-othertime-op' )->text(), 'othertime' );
		# Add custom expiry dropdown options (from MediaWiki message)
		foreach( explode( ',', $scExpiryOptions ) as $option ) {
			if ( strpos( $option, ":" ) === false ) {
				$show = $value = $option;
			} else {
				list( $show, $value ) = explode( ":", $option );
			}
			$dropdownOptions[] = array( $show, $value );
		}
		
		# Actually build the options HTML...
		$expiryFormOptions = '';
		foreach ( $dropdownOptions as $option ) {
			$show = htmlspecialchars( $option[0] );
			$value = htmlspecialchars( $option[1] );
			$expiryFormOptions .= Xml::option( $show, $value,
				$form->getExpirySelection() === $value ) . "\n";
		}

		# Build up the form...
		$s .= Xml::openElement( 'form', array( 'name' => 'stabilization',
			'action' => $this->getTitle()->getLocalUrl(), 'method' => 'post' ) );
		# Add stable version override and selection options
		$s .=
			Xml::fieldset( $this->msg( 'stabilization-def' )->text(), false ) . "\n" .
			Xml::radioLabel( $this->msg( 'stabilization-def1' )->text(), 'wpStableconfig-override', 1,
				'default-stable', 1 == $form->getOverride(), $this->disabledAttr() ) .
				'<br />' . "\n" .
			Xml::radioLabel( $this->msg( 'stabilization-def2' )->text(), 'wpStableconfig-override', 0,
				'default-current', 0 == $form->getOverride(), $this->disabledAttr() ) . "\n" .
			Xml::closeElement( 'fieldset' );
		# Add autoreview restriction select
		$s .= Xml::fieldset( $this->msg( 'stabilization-restrict' )->text(), false ) .
			$this->buildSelector( $form->getAutoreview() ) .
			Xml::closeElement( 'fieldset' ) .

			Xml::fieldset( $this->msg( 'stabilization-leg' )->text(), false ) .
			Xml::openElement( 'table' );
		# Add expiry dropdown to form...
		if ( $showProtectOptions && $form->isAllowed() ) {
			$s .= "
				<tr>
					<td class='mw-label'>" .
						Xml::label( $this->msg( 'stabilization-expiry' )->text(),
							'mwStabilizeExpirySelection' ) .
					"</td>
					<td class='mw-input'>" .
						Xml::tags( 'select',
							array(
								'id'        => 'mwStabilizeExpirySelection',
								'name'      => 'wpExpirySelection',
								'onchange'  => 'onFRChangeExpiryDropdown()',
							) + $this->disabledAttr(),
							$expiryFormOptions ) .
					"</td>
				</tr>";
		}
		# Add custom expiry field to form...
		$attribs = array( 'id' => "mwStabilizeExpiryOther",
			'onkeyup' => 'onFRChangeExpiryField()' ) + $this->disabledAttr();
		$s .= "
			<tr>
				<td class='mw-label'>" .
					Xml::label( $this->msg( 'stabilization-othertime' )->text(),
						'mwStabilizeExpiryOther' ) .
				'</td>
				<td class="mw-input">' .
					Xml::input( "mwStabilize-expiry", 50, $form->getExpiryCustom(), $attribs ) .
				'</td>
			</tr>';
		# Add comment input and submit button
		if ( $form->isAllowed() ) {
			$watchLabel = $this->msg( 'watchthis' )->parse();
			$watchAttribs = array( 'accesskey' => $this->msg( 'accesskey-watch' )->text(),
				'id' => 'wpWatchthis' );
			$watchChecked = ( $this->getUser()->getOption( 'watchdefault' )
				|| $title->userIsWatching() );
			$reviewLabel = $this->msg( 'stabilization-review' )->parse();

			$s .= ' <tr>
					<td class="mw-label">' .
						xml::label( $this->msg( 'stabilization-comment' )->text(),
							'wpReasonSelection' ) .
					'</td>
					<td class="mw-input">' .
						$reasonDropDown .
					'</td>
				</tr>
				<tr>
					<td class="mw-label">' .
						Xml::label( $this->msg( 'stabilization-otherreason' )->text(), 'wpReason' ) .
					'</td>
					<td class="mw-input">' .
						Xml::input( 'wpReason', 70, $form->getReasonExtra(),
							array( 'id' => 'wpReason', 'maxlength' => 255 ) ) .
					'</td>
				</tr>
				<tr>
					<td></td>
					<td class="mw-input">' .
						Xml::check( 'wpReviewthis', $form->getReviewThis(),
							array( 'id' => 'wpReviewthis' ) ) .
						"<label for='wpReviewthis'>{$reviewLabel}</label>" .
						'&#160;&#160;&#160;&#160;&#160;' .
						Xml::check( 'wpWatchthis', $watchChecked, $watchAttribs ) .
						"&#160;<label for='wpWatchthis' " .
						Xml::expandAttributes(
							array( 'title' => Linker::titleAttrib( 'watch', 'withaccess' ) ) ) .
						">{$watchLabel}</label>" .
					'</td>
				</tr>
				<tr>
					<td></td>
					<td class="mw-submit">' .
						Xml::submitButton( $this->msg( 'stabilization-submit' )->text() ) .
					'</td>
				</tr>' . Xml::closeElement( 'table' ) .
				Html::hidden( 'title', $this->getTitle()->getPrefixedDBKey() ) .
				Html::hidden( 'page', $title->getPrefixedText() ) .
				Html::hidden( 'wpEditToken', $this->getUser()->getEditToken() );
		} else {
			$s .= Xml::closeElement( 'table' );
		}
		$s .= Xml::closeElement( 'fieldset' ) . Xml::closeElement( 'form' );

		$out->addHTML( $s );

		$log = new LogPage( 'stable' );
		$out->addHTML( Xml::element( 'h2', null,
			htmlspecialchars( $log->getName() ) ) );
		LogEventsList::showLogExtract( $out, 'stable',
			$title->getPrefixedText(), '', array( 'lim' => 25 ) );

		# Add some javascript for expiry dropdowns
		$out->addScript(
			"<script type=\"text/javascript\">
				function onFRChangeExpiryDropdown() {
					document.getElementById('mwStabilizeExpiryOther').value = '';
				}
				function onFRChangeExpiryField() {
					document.getElementById('mwStabilizeExpirySelection').value = 'othertime';
				}
			</script>"
		);
	}

	protected function buildSelector( $selected ) {
		$allowedLevels = array();
		$levels = FlaggedRevs::getRestrictionLevels();
		array_unshift( $levels, '' ); // Add a "none" level
		foreach ( $levels as $key ) {
			# Don't let them choose levels they can't set, 
			# but *show* them all when the form is disabled.
			if ( $this->form->isAllowed()
				&& !FlaggedRevs::userCanSetAutoreviewLevel( $this->getUser(), $key ) )
			{
				continue;
			}
			$allowedLevels[] = $key;
		}
		$id = 'mwProtect-level-autoreview';
		$attribs = array(
			'id' => $id,
			'name' => $id,
			'size' => count( $allowedLevels ),
		) + $this->disabledAttr();

		$out = Xml::openElement( 'select', $attribs );
		foreach ( $allowedLevels as $key ) {
			$out .= Xml::option( $this->getOptionLabel( $key ), $key, $key == $selected );
		}
		$out .= Xml::closeElement( 'select' );
		return $out;
	}

	/**
	 * Prepare the label for a protection selector option
	 *
	 * @param string $permission Permission required
	 * @return string
	 */
	protected function getOptionLabel( $permission ) {
		if ( $permission == '' ) {
			return $this->msg( 'stabilization-restrict-none' )->text();
		} else {
			$key = "protect-level-{$permission}";
			$msg = $this->msg( $key );
			if ( $msg->isDisabled() ) {
				$msg = $this->msg( 'protect-fallback', $permission );
			}
			return $msg->text();
		}
	}

	// If the this form is disabled, then return the "disabled" attr array
	protected function disabledAttr() {
		return $this->form->isAllowed()
			? array()
			: array( 'disabled' => 'disabled' );
	}
}
