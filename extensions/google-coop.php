<?php
# Google Custom Search Engine Extension
# 
# Tag :
#   <Googlecoop></Googlecoop> or <Googlecoop/>
# Ex :
#   Add this tag to the wiki page you configed at your Google co-op control panel.
#
# Enjoy !

$wgExtensionFunctions[] = 'GoogleCoop';
$wgExtensionCredits['parserhook'][] = array(
        'name' => 'Google Co-op Extension',
        'description' => 'Using Google Co-op',
        'author' => 'Liang Chen The BiGreat',
        'url' => 'http://www.mediawiki.org/wiki/Extension:Google_Custom_Search_Engine'
);
 
function GoogleCoop() {
        global $wgParser;
        $wgParser->setHook('Googlecoop', 'renderGoogleCoop');
}
 
# The callback function for converting the input text to HTML output
function renderGoogleCoop($input) {
 
        $output='<form action="http://'.$_SERVER["SERVER_NAME"].'/Portal:GoogleSearch" id="cse-search-box" style="margin-left:10px;"><div><input type="hidden" name="cx" value="013285077636246033335:2wluvjftece" /><input type="hidden" name="cof" value="FORID:10" /><input type="hidden" name="ie" value="UTF-8" /><input type="text" name="q" size="31" /><input type="submit" name="sa" value="Search" /></div></form><script type="text/javascript" src="http://www.google.com/cse/brand?form=cse-search-box&lang=en"></script><div id="cse-search-results" style="margin-left:10px;"></div><script type="text/javascript">var googleSearchIframeName = "cse-search-results";var googleSearchFormName = "cse-search-box";var googleSearchFrameWidth = 600;var googleSearchDomain = "www.google.com";var googleSearchPath = "/cse";</script><script type="text/javascript" src="http://www.google.com/afsonline/show_afs_search.js"></script>';

        return $output;
}
?>
