<?php
if ( !defined( 'MEDIAWIKI' ) ) {
        echo( "This file is an extension to the MediaWiki software and cannot be used standalone.\n" );
        die();
}
 
$wgExtensionCredits['parserhook'][] = array(
        'name' => 'No title',
        'author' => '[https://www.mediawiki.org/wiki/User:Nx Nx]',
        'description' => 'Adds a magic word to hide the title heading',
        'url' => 'https://www.mediawiki.org/wiki/Extension:NoTitle'
);
 
$wgHooks['LanguageGetMagic'][] = 'NoTitle::addMagicWordLanguage';
$wgHooks['ParserBeforeTidy'][] = 'NoTitle::checkForMagicWord';
 
class NoTitle
{
  static function addMagicWordLanguage(&$magicWords, $langCode) {
    switch($langCode) {
    default:
      $magicWords['notitle'] = array(0, '__NOTITLE__');
    }
    MagicWord::$mDoubleUnderscoreIDs[] = 'notitle';
    return true;
  }
 
  static function checkForMagicWord(&$parser, &$text) {
    if ( isset( $parser->mDoubleUnderscores['notitle'] ) ) {
      $parser->mOutput->addHeadItem('<style type="text/css">/*<![CDATA[*/ .firstHeading, .subtitle, #siteSub, #contentSub, .pagetitle { display:none; } #jump-to-nav { margin:0; } /*]]>*/</style>');
    }
    return true;
  }
 
}
?>
