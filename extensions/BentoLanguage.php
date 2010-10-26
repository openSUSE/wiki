<?php

global $bento_lang;

$avail_bento_langs = array("cs", "de", "el", "en", "es", "fi", "fr", "hu", "it", "ja", "nl", "pl", "pt", "ru", "sv", "tr", "vi", "zh_TW");
$bento_lang = "en";
$wiki_lang = substr($_SERVER['SERVER_NAME'], 0, strpos($_SERVER['SERVER_NAME'], '.') );
$wiki_lang = str_replace( 'stage', '', $wiki_lang );
$wiki_lang = str_replace( 'test', '', $wiki_lang );
$wiki_lang = str_replace( 'wiki', '', $wiki_lang );
$wiki_lang = str_replace( 'cz', 'cs', $wiki_lang );
$wiki_lang = str_replace( 'ch_tw', 'ch_TW', $wiki_lang );
if ( in_array( $wiki_lang , $avail_bento_langs ) ) {
    $bento_lang = $wiki_lang;
}

?>