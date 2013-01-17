<?php
/**
 * OpenSUSE bento skin
 */

if( !defined( 'MEDIAWIKI' ) ) die();

require_once( "skins/bento.php" );

class SkinBentoFluid extends SkinTemplate {
    function initPage( OutputPage $out ) {
        parent::initPage( $out );
        $this->skinname  = 'bentofluid';
        $this->stylename = 'bentofluid';
        $this->template  = 'BentoTemplate';

    }
    function setupSkinUserCss( OutputPage $out ) {
        parent::setupSkinUserCss( $out );
        // Append to the default screen common & print styles...
        $out->addStyle( 'https://static.opensuse.org/themes/bento/css/style.fluid.css', 'screen' );
        $out->addStyle( 'https://static.opensuse.org/themes/bento/css/print.css', 'print' );
    }
}

?>
