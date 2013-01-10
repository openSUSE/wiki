<?php
/**
 * Javascript- and HTML-creation utilities for the display of a form
 *
 * @author Yaron Koren
 * @author Jeffrey Stuckman
 * @author Harold Solbrig
 * @author Eugene Mednikov
 * @file
 * @ingroup SF
 */

class SFFormUtils {
	static function setGlobalJSVariables( &$vars ) {
		global $sfgAutocompleteValues, $sfgAutocompleteOnAllChars;
		global $sfgFieldProperties, $sfgDependentFields;
		global $sfgShowOnSelect, $sfgScriptPath;
//		global $sfgInitJSFunctions, $sfgValidationJSFunctions;

		$vars['sfgAutocompleteValues'] = $sfgAutocompleteValues;
		$vars['sfgAutocompleteOnAllChars'] = $sfgAutocompleteOnAllChars;
		$vars['sfgFieldProperties'] = $sfgFieldProperties;
		$vars['sfgDependentFields'] = $sfgDependentFields;
		$vars['sfgShowOnSelect'] = $sfgShowOnSelect;
		$vars['sfgScriptPath'] = $sfgScriptPath;
//		$vars['sfgInitJSFunctions'] = $sfgInitJSFunctions;
//		$vars['sfgValidationJSFunctions'] = $sfgValidationJSFunctions;

		return true;
	}

	/**
	 * Add a hidden input for each field in the template call that's
	 * not handled by the form itself
	 */
	static function unhandledFieldsHTML( $templateName, $templateContents ) {
		// HTML element names shouldn't contain spaces
		$templateName = str_replace( ' ', '_', $templateName );
		$text = "";
		foreach ( $templateContents as $key => $value ) {
			if ( !is_null( $key ) && !is_numeric( $key ) ) {
				$key = urlencode( $key );
				$text .= Html::hidden( '_unhandled_' . $templateName . '_' . $key, $value );
			}
		}
		return $text;
	}

	/**
	 * Add unhandled fields back into the template call that the form
	 * generates, so that editing with a form will have no effect on them
	 */
	static function addUnhandledFields( $templateName ) {
		global $wgRequest;

		$templateName = str_replace( ' ', '_', $templateName );
		$prefix = '_unhandled_' . $templateName . '_';
		$prefixSize = strlen( $prefix );
		$additional_template_text = "";
		foreach ( $wgRequest->getValues() as $key => $value ) {
			if ( strpos( $key, $prefix ) === 0 ) {
				$field_name = urldecode( substr( $key, $prefixSize ) );
				$additional_template_text .= "|$field_name=$value\n";
			}
		}
		return $additional_template_text;
	}

	static function summaryInputHTML( $is_disabled, $label = null, $attr = array() ) {
		global $sfgTabIndex;

		$sfgTabIndex++;
		if ( $label == null )
			$label = wfMessage( 'summary' )->text();
		$disabled_text = ( $is_disabled ) ? " disabled" : "";
		$attr = Html::expandAttributes( $attr );
		$text = <<<END
	<span id='wpSummaryLabel'><label for='wpSummary'>$label</label></span>
	<input tabindex="$sfgTabIndex" type='text' value="" name='wpSummary' id='wpSummary' maxlength='200' size='60'$disabled_text$attr/>

END;
		return $text;
	}

	static function minorEditInputHTML( $is_disabled, $label = null, $attrs = array() ) {
		global $sfgTabIndex, $wgUser, $wgParser;

		$sfgTabIndex++;
		$checked = $wgUser->getOption( 'minordefault' );

		if ( $label == null ) {
			$label = $wgParser->recursiveTagParse( wfMessage( 'minoredit' )->text() );
		}

		$tooltip = wfMessage( 'tooltip-minoredit' )->text();
		$attrs += array(
			'id' => 'wpMinoredit',
			'accesskey' => wfMessage( 'accesskey-minoredit' )->text(),
			'tabindex' => $sfgTabIndex,
		);
		if ( $is_disabled ) {
			$attrs['disabled'] = true;
		}
		$text = "\t" . Xml::check( 'wpMinoredit', $checked, $attrs ) . "\n";
		$text .= "\t" . Html::element( 'label', array(
			'for' => 'wpMinoredit',
			'title' => $tooltip
		), $label ) . "\n";

		return $text;
	}

	static function watchInputHTML( $is_disabled, $is_checked = false, $label = null, $attrs = array() ) {
		global $sfgTabIndex, $wgUser, $wgTitle, $wgParser;

		$sfgTabIndex++;
		// figure out if the checkbox should be checked -
		// this code borrowed from /includes/EditPage.php
		if ( $wgUser->getOption( 'watchdefault' ) ) {
			# Watch all edits
			$is_checked = true;
		} elseif ( $wgUser->getOption( 'watchcreations' ) && !$wgTitle->exists() ) {
			# Watch creations
			$is_checked = true;
		} elseif ( $wgTitle->userIsWatching() ) {
			# Already watched
			$is_checked = true;
		}
		if ( $label == null )
			$label = $wgParser->recursiveTagParse( wfMessage( 'watchthis' )->text() );
		$attrs += array(
			'id' => 'wpWatchthis',
			'accesskey' => wfMessage( 'accesskey-watch' )->text(),
			'tabindex' => $sfgTabIndex,
		);
		if ( $is_disabled ) {
			$attrs['disabled'] = true;
		}
		$text = "\t" . Xml::check( 'wpWatchthis', $is_checked, $attrs ) . "\n";
		$tooltip = wfMessage( 'tooltip-watch' )->escaped();
		$text .= "\t" . Html::element( 'label', array(
			'for' => 'wpWatchthis',
			'title' => $tooltip
		), $label ) . "\n";

		return $text;
	}

	/**
	 * Helper function to display a simple button
	 */
	static function buttonHTML( $name, $value, $type, $attrs ) {
		return "\t\t" . Html::input( $name, $value, $type, $attrs ) . "\n";
	}

	static function saveButtonHTML( $is_disabled, $label = null, $attr = array() ) {
		global $sfgTabIndex;

		$sfgTabIndex++;
		if ( $label == null ) {
			$label = wfMessage( 'savearticle' )->text();
		}
		$temp = $attr + array(
			'id'        => 'wpSave',
			'tabindex'  => $sfgTabIndex,
			'accesskey' => wfMessage( 'accesskey-save' )->text(),
			'title'     => wfMessage( 'tooltip-save' )->text(),
		);
		if ( $is_disabled ) {
			$temp['disabled'] = true;
		}
		return self::buttonHTML( 'wpSave', $label, 'submit', $temp );
	}

	static function saveAndContinueButtonHTML( $is_disabled, $label = null, $attr = array() ) {
		global $sfgTabIndex;

		$sfgTabIndex++;

		if ( $label == null ) {
			$label = wfMessage( 'sf_formedit_saveandcontinueediting' )->text();
		}

		$temp = $attr + array(
			'id'        => 'wpSaveAndContinue',
			'tabindex'  => $sfgTabIndex,
			'disabled'  => true,
			'accesskey' => wfMessage( 'sf_formedit_accesskey_saveandcontinueediting' )->text(),
			'title'     => wfMessage( 'sf_formedit_tooltip_saveandcontinueediting' )->text(),
		);

		if ( $is_disabled ) {
			$temp['class'] = 'sf-save_and_continue disabled';
		} else {
			$temp['class'] = 'sf-save_and_continue';
		}

		return self::buttonHTML( 'wpSaveAndContinue', $label, 'button', $temp );
	}

	static function showPreviewButtonHTML( $is_disabled, $label = null, $attr = array() ) {
		global $sfgTabIndex;

		$sfgTabIndex++;
		if ( $label == null ) {
			$label = wfMessage( 'showpreview' )->text();
		}
		$temp = $attr + array(
			'id'        => 'wpPreview',
			'tabindex'  => $sfgTabIndex,
			'accesskey' => wfMessage( 'accesskey-preview' )->text(),
			'title'     => wfMessage( 'tooltip-preview' )->text(),
		);
		if ( $is_disabled ) {
			$temp['disabled'] = true;
		}
		return self::buttonHTML( 'wpPreview', $label, 'submit', $temp );
	}

	static function showChangesButtonHTML( $is_disabled, $label = null, $attr = array() ) {
		global $sfgTabIndex;

		$sfgTabIndex++;
		if ( $label == null ) {
			$label = wfMessage( 'showdiff' )->text();
		}
		$temp = $attr + array(
			'id'        => 'wpDiff',
			'tabindex'  => $sfgTabIndex,
			'accesskey' => wfMessage( 'accesskey-diff' )->text(),
			'title'     => wfMessage( 'tooltip-diff' )->text(),
		);
		if ( $is_disabled ) {
			$temp['disabled'] = true;
		}
		return self::buttonHTML( 'wpDiff', $label, 'submit', $temp );
	}

	static function cancelLinkHTML( $is_disabled, $label = null, $attr = array() ) {
		global $wgTitle, $wgParser;

		if ( $label == null ) {
			$label = $wgParser->recursiveTagParse( wfMessage( 'cancel' )->text() );
		}
		if ( $wgTitle == null ) {
			$cancel = '';
		}
		// if we're on the special 'FormEdit' page, just send the user
		// back to the previous page they were on
		elseif ( $wgTitle->isSpecial( 'FormEdit' ) ) {
			// For IE, we need to go back twice, past the redirect.
			$stepsBack = stristr( $_SERVER['HTTP_USER_AGENT'], "msie" ) ? 2 : 1;
			$cancel = "<a href=\"javascript:history.go(-$stepsBack);\">$label</a>";
		} else {
			$cancel = SFUtils::getLinker()->link( $wgTitle, $label, array(), array(), 'known' );
		}
		return "\t\t" . Html::rawElement( 'span', array( 'class' => 'editHelp' ), $cancel ) . "\n";
	}

	static function runQueryButtonHTML( $is_disabled = false, $label = null, $attr = array() ) {
		// is_disabled is currently ignored
		global $sfgTabIndex;

		$sfgTabIndex++;
		if ( $label == null ) {
			$label = wfMessage( 'runquery' )->text();
		}
		return self::buttonHTML( 'wpRunQuery', $label, 'submit',
			$attr + array(
			'id'        => 'wpRunQuery',
			'tabindex'  => $sfgTabIndex,
			'title'     => $label,
		) );
	}

	// Much of this function is based on MediaWiki's EditPage::showEditForm()
	static function formBottom( $is_disabled ) {
		global $wgUser;

		$summary_text = SFFormUtils::summaryInputHTML( $is_disabled );
		$text = <<<END
	<br /><br />
	<div class='editOptions'>
$summary_text	<br />

END;
		if ( $wgUser->isAllowed( 'minoredit' ) ) {
			$text .= SFFormUtils::minorEditInputHTML( $is_disabled );
		}

		if ( $wgUser->isLoggedIn() ) {
			$text .= SFFormUtils::watchInputHTML( $is_disabled );
		}

		$text .= <<<END
	<br />
	<div class='editButtons'>

END;
		$text .= SFFormUtils::saveButtonHTML( $is_disabled );
		$text .= SFFormUtils::showPreviewButtonHTML( $is_disabled );
		$text .= SFFormUtils::showChangesButtonHTML( $is_disabled );
		$text .= SFFormUtils::cancelLinkHTML( $is_disabled );
		$text .= <<<END
	</div><!-- editButtons -->
	</div><!-- editOptions -->

END;
		return $text;
	}

	// based on MediaWiki's EditPage::getPreloadedText()
	static function getPreloadedText( $preload ) {
		if ( $preload === '' ) {
			return '';
		} else {
			$preloadTitle = Title::newFromText( $preload );
			if ( isset( $preloadTitle ) && $preloadTitle->userCanRead() ) {
				$rev = Revision::newFromTitle( $preloadTitle );
				if ( is_object( $rev ) ) {
					$text = $rev->getText();
					// Remove <noinclude> sections and <includeonly> tags from text
					$text = StringUtils::delimiterReplace( '<noinclude>', '</noinclude>', '', $text );
					$text = strtr( $text, array( '<includeonly>' => '', '</includeonly>' => '' ) );
					return $text;
				}
			}
			return '';
		}
	}

	/**
	 * Used by 'RunQuery' page
	 */
	static function queryFormBottom() {
		return self::runQueryButtonHTML( false );
	}

	static function getMonthNames() {
		return array(
			wfMessage( 'january' )->inContentLanguage()->text(),
			wfMessage( 'february' )->inContentLanguage()->text(),
			wfMessage( 'march' )->inContentLanguage()->text(),
			wfMessage( 'april' )->inContentLanguage()->text(),
			// Needed to avoid using 3-letter abbreviation
			wfMessage( 'may_long' )->inContentLanguage()->text(),
			wfMessage( 'june' )->inContentLanguage()->text(),
			wfMessage( 'july' )->inContentLanguage()->text(),
			wfMessage( 'august' )->inContentLanguage()->text(),
			wfMessage( 'september' )->inContentLanguage()->text(),
			wfMessage( 'october' )->inContentLanguage()->text(),
			wfMessage( 'november' )->inContentLanguage()->text(),
			wfMessage( 'december' )->inContentLanguage()->text()
		);
	}

	static function getShowFCKEditor() {
		global $wgUser;

		// Differentiate between FCKeditor and the newer CKeditor,
		// which isn't handled here
		if ( !class_exists( 'FCKeditor' ) ) {
			return false;
		}

		$showFCKEditor = 0;
		if ( !$wgUser->getOption( 'riched_start_disabled' ) ) {
			$showFCKEditor += RTE_VISIBLE;
		}
		if ( $wgUser->getOption( 'riched_use_popup' ) ) {
			$showFCKEditor += RTE_POPUP;
		}
		if ( $wgUser->getOption( 'riched_use_toggle' ) ) {
			$showFCKEditor += RTE_TOGGLE_LINK;
		}

		if ( ( !empty( $_SESSION['showMyFCKeditor'] ) ) && ( $wgUser->getOption( 'riched_toggle_remember_state' ) ) )
		{
			// clear RTE_VISIBLE flag
			$showFCKEditor &= ~RTE_VISIBLE ;
			// get flag from session
			$showFCKEditor |= $_SESSION['showMyFCKeditor'] ;
		}
		return $showFCKEditor;
	}

	static function prepareTextForFCK( $text ) {
		global $wgTitle;

		$options = new FCKeditorParserOptions();
		$options->setTidy( true );
		$parser = new FCKeditorParser();
		$parser->setOutputType( OT_HTML );
		$text = $parser->parse( $text, $wgTitle, $options )->getText();
		return $text;
	}

	static function mainFCKJavascript( $showFCKEditor, $fieldArgs ) {
		global $wgUser, $wgScriptPath, $wgFCKEditorExtDir, $wgFCKEditorDir, $wgFCKEditorToolbarSet, $wgFCKEditorHeight;
		global $wgHooks, $wgExtensionFunctions;

		$numRows = isset( $fieldArgs['rows'] ) && $fieldArgs['rows'] > 0 ? $fieldArgs['rows'] : 5;
		$FCKEditorHeight = ( $wgFCKEditorHeight < 300 ) ? 300 : $wgFCKEditorHeight;

		$newWinMsg = wfMessage( 'rich_editor_new_window' )->text();
		$javascript_text = '
var showFCKEditor = ' . $showFCKEditor . ';
var popup = false;		//pointer to popup document
var firstLoad = true;
var editorMsgOn = "' . wfMessage( 'textrichditor' )->text() . '";
var editorMsgOff = "' . wfMessage( 'tog-riched_disable' )->text() . '";
var editorLink = "' . ( ( $showFCKEditor & RTE_VISIBLE ) ? wfMessage( 'tog-riched_disable' )->text() : wfMessage( 'textrichditor' )->text() ) . '";
var saveSetting = ' . ( $wgUser->getOption( 'riched_toggle_remember_state' ) ?  1 : 0 ) . ';
var RTE_VISIBLE = ' . RTE_VISIBLE . ';
var RTE_TOGGLE_LINK = ' . RTE_TOGGLE_LINK . ';
var RTE_POPUP = ' . RTE_POPUP . ';
';

		$showRef = 'false';
		if ( ( isset( $wgHooks['ParserFirstCallInit'] ) && in_array( 'wfCite', $wgHooks['ParserFirstCallInit'] ) ) || ( isset( $wgExtensionFunctions ) && in_array( 'wfCite', $wgExtensionFunctions ) ) ) {
			$showRef = 'true';
		}

		$showSource = 'false';
		if ( ( isset( $wgHooks['ParserFirstCallInit'] ) && in_array( 'efSyntaxHighlight_GeSHiSetup', $wgHooks['ParserFirstCallInit'] ) )
			|| ( isset( $wgExtensionFunctions ) && in_array( 'efSyntaxHighlight_GeSHiSetup', $wgExtensionFunctions ) ) ) {
			$showSource = 'true';
		}

		// at some point, the variable $wgFCKEditorDir got a "/"
		// appended to it - this makes a big difference. To support
		// all FCKeditor versions, append a slash if it's not there
		if ( substr( $wgFCKEditorDir, -1 ) != '/' ) {
			$wgFCKEditorDir .= '/';
		}

		$javascript_text .= <<<END
var oFCKeditor = new FCKeditor( "sf_free_text" );

//Set config
oFCKeditor.BasePath = '$wgScriptPath/$wgFCKEditorDir';
oFCKeditor.Config["CustomConfigurationsPath"] = "$wgScriptPath/$wgFCKEditorExtDir/fckeditor_config.js" ;
oFCKeditor.Config["EditorAreaCSS"] = "$wgScriptPath/$wgFCKEditorExtDir/css/fckeditor.css" ;
oFCKeditor.Config["showreferences"] = '$showRef';
oFCKeditor.Config["showsource"] = '$showSource';
oFCKeditor.ToolbarSet = "$wgFCKEditorToolbarSet";
oFCKeditor.ready = true;

//IE hack to call func from popup
function FCK_sajax(func_name, args, target) {
	sajax_request_type = 'POST' ;
	sajax_do_call(func_name, args, function (x) {
		// I know this is function, not object
		target(x);
		}
	);
}

// If the rows attribute was defined in the form, use the font size to
// calculate the editor window height
function getFontSize(el) {
	var x = document.getElementById(el);
	if (x.currentStyle) {
		// IE
		var y = x.currentStyle['lineheight'];
	} else if (window.getComputedStyle) {
		// FF, Opera
		var y = document.defaultView.getComputedStyle(x,null).getPropertyValue('line-height');
	}
	return y;
}
function getWindowHeight4editor() {
	var fsize = getFontSize('sf_free_text');
	// if value was not determined, return default val from $wgFCKEditorHeight
	if (!fsize) return $FCKEditorHeight;
	if (fsize.indexOf('px') == -1)  // we didn't get pixels
		// arbitary value, don't hassle with caluclating
		return $FCKEditorHeight;
	var px = parseFloat(fsize.replace(/\w{2}$/, ''));
	// the text in the edit window is slightly larger than the determined value
	px = px * 1.25;
	return Math.round( px * $numRows );
}

function onLoadFCKeditor()
{
	if (!(showFCKEditor & RTE_VISIBLE))
		showFCKEditor += RTE_VISIBLE;
	firstLoad = false;
	realTextarea = document.getElementById('sf_free_text');
	if ( realTextarea )
	{
		// Create the editor instance and replace the textarea.
		var height = $wgFCKEditorHeight;
		if (height == 0) {
			// the original onLoadFCKEditor() has a bunch of
			// browser-based calculations here, but let's just
			// keep it simple
			height = 300;
		}
		oFCKeditor.Height = height;
		oFCKeditor.ReplaceTextarea() ;

		FCKeditorInsertTags = function (tagOpen, tagClose, sampleText, oDoc)
		{
			var txtarea;

			if ( !(typeof(oDoc.FCK) == "undefined") && !(typeof(oDoc.FCK.EditingArea) == "undefined") )
			{
				txtarea = oDoc.FCK.EditingArea.Textarea ;
			}
			else if (oDoc.editform)
			{
				// if we have FCK enabled, behave differently...
				if ( showFCKEditor & RTE_VISIBLE )
				{
					SRCiframe = oDoc.getElementById ('sf_free_text___Frame') ;
					if ( SRCiframe )
					{
						if (window.frames[SRCiframe])
							SRCdoc = window.frames[SRCiframe].oDoc ;
						else
							SRCdoc = SRCiframe.contentDocument ;

						var SRCarea = SRCdoc.getElementById ('xEditingArea').firstChild ;

						if (SRCarea)
							txtarea = SRCarea ;
						else
							return false ;

					}
					else
					{
						return false ;
					}
				}
				else
				{
					txtarea = oDoc.editform.sf_free_text ;
				}
			}
			else
			{
				// some alternate form? take the first one we can find
				var areas = oDoc.getElementsByTagName( 'textarea' ) ;
				txtarea = areas[0] ;
			}

			var selText, isSample = false ;

			if ( oDoc.selection  && oDoc.selection.createRange )
			{ // IE/Opera

				//save window scroll position
				if ( oDoc.documentElement && oDoc.documentElement.scrollTop )
					var winScroll = oDoc.documentElement.scrollTop ;
				else if ( oDoc.body )
					var winScroll = oDoc.body.scrollTop ;

				//get current selection
				txtarea.focus() ;
				var range = oDoc.selection.createRange() ;
				selText = range.text ;
				//insert tags
				checkSelected();
				range.text = tagOpen + selText + tagClose ;
				//mark sample text as selected
				if ( isSample && range.moveStart )
				{
					if (window.opera)
						tagClose = tagClose.replace(/\\n/g,'') ; //check it out one more time
					range.moveStart('character', - tagClose.length - selText.length) ;
					range.moveEnd('character', - tagClose.length) ;
				}
				range.select();
				//restore window scroll position
				if ( oDoc.documentElement && oDoc.documentElement.scrollTop )
					oDoc.documentElement.scrollTop = winScroll ;
				else if ( oDoc.body )
					oDoc.body.scrollTop = winScroll ;

			}
			else if ( txtarea.selectionStart || txtarea.selectionStart == '0' )
			{ // Mozilla

				//save textarea scroll position
				var textScroll = txtarea.scrollTop ;
				//get current selection
				txtarea.focus() ;
				var startPos = txtarea.selectionStart ;
				var endPos = txtarea.selectionEnd ;
				selText = txtarea.value.substring( startPos, endPos ) ;

				//insert tags
				if (!selText)
				{
					selText = sampleText ;
					isSample = true ;
				}
				else if (selText.charAt(selText.length - 1) == ' ')
				{ //exclude ending space char
					selText = selText.substring(0, selText.length - 1) ;
					tagClose += ' ' ;
				}
				txtarea.value = txtarea.value.substring(0, startPos) + tagOpen + selText + tagClose +
								txtarea.value.substring(endPos, txtarea.value.length) ;
				//set new selection
				if (isSample)
				{
					txtarea.selectionStart = startPos + tagOpen.length ;
					txtarea.selectionEnd = startPos + tagOpen.length + selText.length ;
				}
				else
				{
					txtarea.selectionStart = startPos + tagOpen.length + selText.length + tagClose.length ;
					txtarea.selectionEnd = txtarea.selectionStart;
				}
				//restore textarea scroll position
				txtarea.scrollTop = textScroll;
			}
		}
	}
}
function checkSelected()
{
	if (!selText) {
		selText = sampleText;
		isSample = true;
	} else if (selText.charAt(selText.length - 1) == ' ') { //exclude ending space char
		selText = selText.substring(0, selText.length - 1);
		tagClose += ' '
	}
}
function initEditor()
{
	var toolbar = document.getElementById('sf_free_text');
	//show popup or toogle link
	if (showFCKEditor & (RTE_POPUP|RTE_TOGGLE_LINK)){
		var fckTools = document.createElement('div');
		fckTools.setAttribute('id', 'fckTools');

		var SRCtextarea = document.getElementById( "sf_free_text" ) ;
		if (showFCKEditor & RTE_VISIBLE) SRCtextarea.style.display = "none";
	}

	if (showFCKEditor & RTE_TOGGLE_LINK)
	{
		fckTools.innerHTML='[<a class="fckToogle" id="toggle_sf_free_text" href="javascript:void(0)" onclick="ToggleFCKEditor(\'toggle\',\'sf_free_text\')">'+ editorLink +'</a>] ';
	}
	if (showFCKEditor & RTE_POPUP)
	{
		var style = (showFCKEditor & RTE_VISIBLE) ? 'style="display:none"' : "";
		fckTools.innerHTML+='<span ' + style + ' id="popup_sf_free_text">[<a class="fckPopup" href="javascript:void(0)" onclick="ToggleFCKEditor(\'popup\',\'sf_free_text\')">{$newWinMsg}</a>]</span>';
	}

	if (showFCKEditor & (RTE_POPUP|RTE_TOGGLE_LINK)){
		//add new toolbar before wiki toolbar
		toolbar.parentNode.insertBefore( fckTools, toolbar );
	}

	if (showFCKEditor & RTE_VISIBLE)
	{
		if ( toolbar )		//insert wiki buttons
		{
			mwSetupToolbar = function() { return false ; } ;

			for (var i = 0; i < mwEditButtons.length; i++) {
				mwInsertEditButton(toolbar, mwEditButtons[i]);
			}
			for (var i = 0; i < mwCustomEditButtons.length; i++) {
				mwInsertEditButton(toolbar, mwCustomEditButtons[i]);
			}
		}
		onLoadFCKeditor();
	}
	return true;
}
addOnloadHook( initEditor );

END;
		return $javascript_text;
	}

	static function FCKToggleJavascript() {
		// add toggle link and handler
		$javascript_text = <<<END

function ToggleFCKEditor(mode, objId)
{
	var SRCtextarea = document.getElementById( objId ) ;
	if(mode == 'popup'){
		if (( showFCKEditor & RTE_VISIBLE) && ( FCKeditorAPI ))	//if FCKeditor is up-to-date
		{
			var oEditorIns = FCKeditorAPI.GetInstance( objId );
			var text = oEditorIns.GetData( oEditorIns.Config.FormatSource );
			SRCtextarea.value = text;			//copy text to textarea
		}
		FCKeditor_OpenPopup('oFCKeditor',objId);
		return true;
	}

	var oToggleLink = document.getElementById('toggle_'+ objId );
	var oPopupLink = document.getElementById('popup_'+ objId );

	if ( firstLoad )
	{
		// firstLoad = true => FCKeditor start invisible
		// "innerHTML" fails for IE - use "innerText" instead
		if (oToggleLink) {
			if (oToggleLink.innerText)
				oToggleLink.innerText = "Loading...";
			else
				oToggleLink.innerHTML = "Loading...";
		}
		sajax_request_type = 'POST' ;
		oFCKeditor.ready = false;
		sajax_do_call('wfSajaxWikiToHTML', [SRCtextarea.value], function ( result ){
			if ( firstLoad )	//still
			{
				SRCtextarea.value = result.responseText; //insert parsed text
				onLoadFCKeditor();
				// "innerHTML" fails for IE - use "innerText" instead
				if (oToggleLink) {
					if (oToggleLink.innerText)
						oToggleLink.innerText = editorMsgOff;
					else
						oToggleLink.innerHTML = editorMsgOff;
				}
				oFCKeditor.ready = true;
			}
		});
		return true;
	}

	if (!oFCKeditor.ready) return false;		//sajax_do_call in action
	if (!FCKeditorAPI) return false;			//not loaded yet
	var oEditorIns = FCKeditorAPI.GetInstance( objId );
	var oEditorIframe  = document.getElementById( objId+'___Frame' );
	var FCKtoolbar = document.getElementById('toolbar');
	var bIsWysiwyg = ( oEditorIns.EditMode == FCK_EDITMODE_WYSIWYG );

	//FCKeditor visible -> hidden
	if ( showFCKEditor & RTE_VISIBLE)
	{
		var text = oEditorIns.GetData( oEditorIns.Config.FormatSource );
		SRCtextarea.value = text;
		if ( bIsWysiwyg ) oEditorIns.SwitchEditMode();		//switch to plain
		var text = oEditorIns.GetData( oEditorIns.Config.FormatSource );
		//copy from FCKeditor to textarea
		SRCtextarea.value = text;
		if (saveSetting)
		{
			sajax_request_type = 'GET' ;
			sajax_do_call( 'wfSajaxToggleFCKeditor', ['hide'], function(){} ) ;		//remember closing in session
		}
		if (oToggleLink) {
			if (oToggleLink.innerText)
				oToggleLink.innerText = editorMsgOn;
			else
				oToggleLink.innerHTML = editorMsgOn;
		}
		if (oPopupLink) oPopupLink.style.display = '';
		showFCKEditor -= RTE_VISIBLE;
		oEditorIframe.style.display = 'none';
		//FCKtoolbar.style.display = '';
		SRCtextarea.style.display = '';
	}
	//FCKeditor hidden -> visible
	else
	{
		if ( bIsWysiwyg ) oEditorIns.SwitchEditMode();		//switch to plain
		SRCtextarea.style.display = 'none';
		//copy from textarea to FCKeditor
		oEditorIns.EditingArea.Textarea.value = SRCtextarea.value
		//FCKtoolbar.style.display = 'none';
		oEditorIframe.style.display = '';
		if ( !bIsWysiwyg ) oEditorIns.SwitchEditMode();		//switch to WYSIWYG
		showFCKEditor += RTE_VISIBLE;
		if (oToggleLink) {
			if (oToggleLink.innerText)
				oToggleLink.innerText = editorMsgOff;
			else
				oToggleLink.innerHTML = editorMsgOff;
		}
		if (oPopupLink) oPopupLink.style.display = 'none';
	}
	return true;
}

END;
		return $javascript_text;
	}

	static function FCKPopupJavascript() {
		global $wgFCKEditorExtDir;
		$javascript_text = <<<END

function FCKeditor_OpenPopup(jsID, textareaID)
{
	popupUrl = '${wgFCKEditorExtDir}/FCKeditor.popup.html';
	popupUrl = popupUrl + '?var='+ jsID + '&el=' + textareaID;
	window.open(popupUrl, null, 'toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=no,resizable=1,dependent=yes');
	return 0;
}

END;
		return $javascript_text;
	}


	/**
	 * Parse the form definition and return it
	 */
	public static function getFormDefinition( &$parser, &$form_def = null, &$form_id = null ) {

		$cachedDef = self::getFormDefinitionFromCache( $form_id, $parser );

		if ( $cachedDef ) {
			return $cachedDef;
		}

		if ( $form_id !== null ) {

			$form_article = Article::newFromID( $form_id );
			$form_def = $form_article->getContent();

		} elseif ( $form_def == null ) {

			// No id, no text -> nothing to do
			return '';

		}

		// Remove <noinclude> sections and <includeonly> tags from form definition
		$form_def = StringUtils::delimiterReplace( '<noinclude>', '</noinclude>', '', $form_def );
		$form_def = strtr( $form_def, array( '<includeonly>' => '', '</includeonly>' => '' ) );

		// add '<nowiki>' tags around every triple-bracketed form
		// definition element, so that the wiki parser won't touch
		// it - the parser will remove the '<nowiki>' tags, leaving
		// us with what we need
		$form_def = "__NOEDITSECTION__" . strtr( $form_def, array( '{{{' => '<nowiki>{{{', '}}}' => '}}}</nowiki>' ) );

		$title = is_object( $parser->getTitle() ) ? $parser->getTitle():new Title();

		// parse wiki-text
		$output = $parser->parse( $form_def, $title, $parser->getOptions() );
		$form_def = $output->getText();

		self::cacheFormDefinition( $form_id, $parser, $output );

		return $form_def;
	}

	/**
	 *	Get a form definition from cache
	 */
	protected static function getFormDefinitionFromCache ( &$form_id, &$parser ) {

		global $sfgCacheFormDefinitions;

		// use cache if allowed
		if ( $sfgCacheFormDefinitions && $form_id !== null ) {

			$cache = self::getFormCache();

			// create a cache key consisting of owner name, article id and user options
			$cachekey = self::getCacheKey( $form_id, $parser );

			$cached_def = $cache->get( $cachekey );

			// Cache hit?
			if ( $cached_def !== false && $cached_def !== null ) {

				wfDebug( "Cache hit: Got form definition $cachekey from cache\n" );
				return $cached_def;

			} else {
				wfDebug( "Cache miss: Form definition $cachekey not found in cache\n" );
			}
		}

		return null;
	}

	/**
	 *	Store a form definition in cache
	 */
	protected static function cacheFormDefinition ( &$form_id, &$parser, &$output ) {

		global $sfgCacheFormDefinitions;

		// store in  cache if allowed
		if ( $sfgCacheFormDefinitions && $form_id !== null ) {

			$cache = self::getFormCache();
			$cachekey = self::getCacheKey( $form_id, $parser );

			if ( $output->getCacheTime() == -1 ) {

				$form_article = Article::newFromID( $form_id );
				self::purgeCache( $form_article );
				wfDebug( "Caching disabled for form definition $cachekey\n" );

			} else {

				$cachekeyForForm = self::getCacheKey( $form_id );

				// update list of form definitions
				$arrayOfStoredDatasets = $cache->get( $cachekeyForForm );
				$arrayOfStoredDatasets[ $cachekey ] = $cachekey; // just need the key defined, don't care for the value

				// We cache indefinitely ignoring $wgParserCacheExpireTime.
				// The reasoning is that there really is not point in expiring
				// rarely changed forms automatically (after one day per
				// default). Instead the cache is purged on storing/purging a
				// form definition.
				// A side effect of this is, that there is no need to
				// distinguish between MW <1.17 and >=1.17.

				// store form definition with current user options
				$cache->set( $cachekey, $output->getText() );

				// store updated list of form definitions
				$cache->set( $cachekeyForForm, $arrayOfStoredDatasets );

				wfDebug( "Cached form definition $cachekey\n" );
			}

		}

		return null;
	}

	/**
	 * Deletes the form definition associated with the given wiki page
	 * from the main cache.
	 *
	 * @param Page $wikipage
	 * @return Bool
	 */
	public static function purgeCache ( &$wikipage ) {

		if ( ! is_null( $wikipage ) && ( $wikipage->getTitle()->getNamespace() == SF_NS_FORM ) ) {

			$cache = self::getFormCache();

			$cachekeyForForm = self::getCacheKey( $wikipage->getId() );

			// get references to stored datasets
			$arrayOfStoredDatasets = $cache->get( $cachekeyForForm );

			if ( $arrayOfStoredDatasets !== false ) {

				// delete stored datasets
				foreach ( $arrayOfStoredDatasets as $key ) {
					$cache->delete( $key );
					wfDebug( "Deleted cached form definition $key.\n" );
				}

				// delete references to datasets
				$cache->delete( $cachekeyForForm );
				wfDebug( "Deleted cached form definition references $cachekeyForForm.\n" );
			}


		}

		return true;
	}

	/**
	 *  Get the cache object used by the form cache
	 */
	public static function getFormCache() {
		global $sfgFormCacheType, $wgParserCacheType;
		$ret = wfGetCache( ( $sfgFormCacheType !== null ) ? $sfgFormCacheType : $wgParserCacheType  );
		return $ret;
	}


	/**
	 * Get a cache key.
	 *
	 * @param $formId or null
	 * @param Parser $parser or null
	 * @return String
	 */
	public static function getCacheKey( $formId = null, &$parser = null ) {

		if ( is_null( $formId ) ) {
			return wfMemcKey( 'ext.SemanticForms.formdefinition' );
		} elseif ( is_null( $parser ) ) {
			return wfMemcKey( 'ext.SemanticForms.formdefinition', $formId );
		} else {
			$optionsHash = $parser->getOptions()->optionsHash( ParserOptions::legacyOptions() );
			return wfMemcKey( 'ext.SemanticForms.formdefinition', $formId, $optionsHash );
		}
	}

}
