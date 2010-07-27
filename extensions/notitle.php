<?php
 
$NoTitle = new NoTitle();
 
$wgHooks['MagicWordMagicWords'][] = array($NoTitle, 'addMagicWord');
$wgHooks['MagicWordwgVariableIDs'][] = array($NoTitle, 'addMagicWordId');
$wgHooks['LanguageGetMagic'][] = array($NoTitle, 'addMagicWordLanguage');
$wgHooks['ParserAfterStrip'][] = array($NoTitle, 'checkForMagicWord');
$wgHooks['BeforePageDisplay'][] = array($NoTitle, 'hideTitle');
 
class NoTitle
{
  function NoTitle() {}
 
  function addMagicWord(&$magicWords) {
    $magicWords[] = 'MAG_NOTITLE';
    return true;
  }
 
  function addMagicWordId(&$magicWords) {
    $magicWords[] = MAG_NOTITLE;
    return true;
  }
 
  function addMagicWordLanguage(&$magicWords, $langCode) {
    switch($langCode) {
    default:
      $magicWords[MAG_NOTITLE] = array(0, '__NOTITLE__');
    }
    return true;
  }
 
  function checkForMagicWord(&$parser, &$text, &$strip_state) {
    $mw = MagicWord::get('MAG_NOTITLE');
 
    if (!in_array($action, array('edit', 'submit')) && $mw->matchAndRemove($text)) {
      $parser->mOptions->mHideTitle = true;
      $parser->disableCache();
    }
 
    return true;
  }
  function hideTitle(&$page) {
 
    if ($page->parserOptions()->mHideTitle) {
      $page->mScripts .= '<style>h1.firstHeading { display:none; } </style>';
    }
 
    return true;
  }
}

?>
