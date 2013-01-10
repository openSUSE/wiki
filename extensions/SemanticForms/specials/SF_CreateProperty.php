<?php
/**
 * A special page holding a form that allows the user to create a semantic
 * property.
 *
 * @author Yaron Koren
 * @file
 * @ingroup SF
 */

/**
 * @ingroup SFSpecialPages
 */
class SFCreateProperty extends SpecialPage {

	/**
	 * Constructor
	 */
	function __construct() {
		parent::__construct( 'CreateProperty' );
	}

	function execute( $query ) {
		$this->setHeaders();
		$this->printCreatePropertyForm( $query );
	}

	static function createPropertyText( $property_type, $default_form, $allowed_values_str ) {
		global $smwgContLang;
		$prop_labels = $smwgContLang->getPropertyLabels();
		$type_tag = "[[{$prop_labels['_TYPE']}::$property_type]]";
		$text = wfMessage( 'sf_property_isproperty', $type_tag )->inContentLanguage()->text();
		if ( $default_form !== '' ) {
			global $sfgContLang;
			$sf_prop_labels = $sfgContLang->getPropertyLabels();
			$default_form_tag = "[[{$sf_prop_labels[SF_SP_HAS_DEFAULT_FORM]}::$default_form]]";
			$text .= ' ' . wfMessage( 'sf_property_linkstoform', $default_form_tag )->inContentLanguage()->text();
		}
		if ( $allowed_values_str !== '' ) {
			// replace the comma substitution character that has no chance of
			// being included in the values list - namely, the ASCII beep
			global $sfgListSeparator;
			$allowed_values_str = str_replace( "\\$sfgListSeparator", "\a", $allowed_values_str );
			$allowed_values_array = explode( $sfgListSeparator, $allowed_values_str );
			$text .= "\n\n" . wfMessage( 'sf_property_allowedvals' )
				->numParams( count( $allowed_values_array ) )->inContentLanguage()->text();
			foreach ( $allowed_values_array as $i => $value ) {
				// replace beep back with comma, trim
				$value = str_replace( "\a", $sfgListSeparator, trim( $value ) );
				$prop_labels = $smwgContLang->getPropertyLabels();
				$text .= "\n* [[" . $prop_labels['_PVAL'] . "::$value]]";
			}
		}
		return $text;
	}

	function printCreatePropertyForm( $query ) {
		global $wgOut, $wgRequest, $sfgScriptPath;
		global $smwgContLang;

		// Cycle through the query values, setting the appropriate
		// local variables.
		$presetPropertyName = str_replace( '_', ' ', $query );
		if ( $presetPropertyName !== '' ) {
			$wgOut->setPageTitle( wfMessage( 'sf-createproperty-with-name', $presetPropertyName)->text() );
			$property_name = $presetPropertyName;
		} else {
			$property_name = $wgRequest->getVal( 'property_name' );
		}
		$property_type = $wgRequest->getVal( 'property_type' );
		$default_form = $wgRequest->getVal( 'default_form' );
		$allowed_values = $wgRequest->getVal( 'values' );

		$save_button_text = wfMessage( 'savearticle' )->text();
		$preview_button_text = wfMessage( 'preview' )->text();

		$property_name_error_str = '';
		$save_page = $wgRequest->getCheck( 'wpSave' );
		$preview_page = $wgRequest->getCheck( 'wpPreview' );
		if ( $save_page || $preview_page ) {
			# validate property name
			if ( $property_name === '' ) {
				$property_name_error_str = wfMessage( 'sf_blank_error' )->text();
			} else {
				// Redirect to wiki interface.
				$wgOut->setArticleBodyOnly( true );
				$title = Title::makeTitleSafe( SMW_NS_PROPERTY, $property_name );
				$full_text = self::createPropertyText( $property_type, $default_form, $allowed_values );
				$text = SFUtils::printRedirectForm( $title, $full_text, "", $save_page, $preview_page, false, false, false, null, null );
				$wgOut->addHTML( $text );
				return;
			}
		}

		$datatype_labels = $smwgContLang->getDatatypeLabels();

		$javascript_text = <<<END
function toggleDefaultForm(property_type) {
	var default_form_div = document.getElementById("default_form_div");
	if (property_type == '{$datatype_labels['_wpg']}') {
		default_form_div.style.display = "";
	} else {
		default_form_div.style.display = "none";
	}
}

function toggleAllowedValues(property_type) {
	var allowed_values_div = document.getElementById("allowed_values");
	// Page, String, Number, Email - is that a reasonable set of types
	// for which enumerations should be allowed?
	if (property_type == '{$datatype_labels['_wpg']}' ||
		property_type == '{$datatype_labels['_str']}' ||
		property_type == '{$datatype_labels['_num']}' ||
		property_type == '{$datatype_labels['_ema']}') {
		allowed_values_div.style.display = "";
	} else {
		allowed_values_div.style.display = "none";
	}
}

END;

		global $wgContLang;
		$mw_namespace_labels = $wgContLang->getNamespaces();
		$name_label = wfMessage( 'sf_createproperty_propname' )->escaped();
		$type_label = wfMessage( 'sf_createproperty_proptype' )->escaped();
		$text = <<<END
	<form action="" method="post">

END;
		$text .= "\n<p>";
		// set 'title' as hidden field, in case there's no URL niceness
		if ( $presetPropertyName === '' ) {
			$text .= Html::hidden( 'title', $this->getTitle()->getPrefixedText() ) . "\n";
			$text .= "$name_label\n";
			$text .= Html::input ( 'property_name', '', array( 'size' => 25 ) );
			$text .= Html::element( 'span', array( 'style' => "color: red;" ), $property_name_error_str );
		}
		$text .= "\n$type_label\n";
		$select_body = "";
		foreach ( $datatype_labels as $label ) {
			$select_body .= "\t" . Html::element( 'option', null, $label ) . "\n";
		}
		$text .= Html::rawElement( 'select', array( 'id' => 'property_dropdown', 'name' => 'property_type', 'onChange' => 'toggleDefaultForm(this.value); toggleAllowedValues(this.value);' ), $select_body ) . "\n";

		$default_form_input = wfMessage( 'sf_createproperty_linktoform' )->escaped();
		$values_input = wfMessage( 'sf_createproperty_allowedvalsinput' )->escaped();
		$text .= <<<END
	<div id="default_form_div" style="padding: 5px 0 5px 0; margin: 7px 0 7px 0;">
	<p>$default_form_input
	<input size="20" name="default_form" value="" /></p>
	</div>
	<div id="allowed_values" style="margin-bottom: 15px;">
	<p>$values_input</p>
	<p><input size="80" name="values" value="" /></p>
	</div>

END;
		$edit_buttons = "\t" . Html::input( 'wpSave', $save_button_text, 'submit', array( 'id' => 'wpSave' ) );
		$edit_buttons .= "\t" . Html::input( 'wpPreview', $preview_button_text, 'submit', array( 'id' => 'wpPreview' ) );
		$text .= "\t" . Html::rawElement( 'div', array( 'class' => 'editButtons' ), $edit_buttons ) . "\n";
		$text .= "\t</form>\n";

		$wgOut->addExtensionStyle( $sfgScriptPath . "/skins/SemanticForms.css" );
		$wgOut->addScript( '<script type="text/javascript">' . $javascript_text . '</script>' );
		$wgOut->addHTML( $text );
	}

}
