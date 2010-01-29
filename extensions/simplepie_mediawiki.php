<?php
/****************************************************
SIMPLEPIE PLUGIN FOR MEDIAWIKI
Add feeds to your MediaWiki installations.

Requires SimplePie 1.0 Beta 2 or newer.

Version: 1.2
Updated: 19 June 2006
Copyright: 2006 Ryan Parman, Geoffrey Sneddon
http://simplepie.org

*****************************************************
LICENSE:

GNU Lesser General Public License 2.1 (LGPL)
http://creativecommons.org/licenses/LGPL/2.1/

*****************************************************
Please submit all bug reports and feature requests to the SimplePie forums.
http://simplepie.org/support/

****************************************************/

if (isset($_GET['i']) && !empty($_GET['i'])) {
	require("./simplepie.inc");
	$feed = new SimplePie();
	$feed->bypass_image_hotlink();
	$feed->init();
}
else {
	require("./extensions/simplepie.inc");
	$wgExtensionFunctions[] = "SimplePieMW";
}

function SimplePieMW() {
	global $wgParser;
	$wgParser->setHook( "feed", "SimplePieMWCallback" );
}

function SimplePieMWCallback($input, $argv) {
	$feed = new SimplePie();
	$feed->feed_url($input);
	$feed->cache_location("/srv/www/vhosts/opensuse.org/shared/tmp/rsscache/");
	$feed->bypass_image_hotlink();
	$feed->bypass_image_hotlink_page('./extensions/simplepie_mediawiki.php');
	$success = $feed->init();

	if ($success && $feed->data) {
		$flink = $feed->get_feed_link();
		$ftitle = $feed->get_feed_title();

		$output='';
		$output .= '<div class="simplepie">';
		if (!isset($argv['showtitle']) || empty($argv['showtitle']) || $argv['showtitle'] == "true") {
			if (isset($argv['alttitle']) && !empty($argv['alttitle'])) {
				if ($ftitle != '' && $flink != '') $output .= "<h3><a href=\"$flink\">" . $argv['alttitle'] . "</a></h3>";
				else if ($ftitle != '') $output .= "<h3>" . $argv['alttitle'] . "</h3>";
			}
			else {
				if ($ftitle != '' && $flink != '') $output .= "<h3><a href=\"$flink\">$ftitle</a></h3>";
				else if ($ftitle != '') $output .= "<h3>$ftitle</h3>";
			}
		}
		$output .= '<ol>';

		$max = $feed->get_item_quantity();
		if (isset($argv['items']) && !empty($argv['items'])) $max = $feed->get_item_quantity($argv['items']);

		for($x=0; $x<$max; $x++) {
			$item = $feed->get_item($x);
			$link = $item->get_permalink();
			$title = StupefyEntities($item->get_title());
			$full_desc = StupefyEntities($item->get_description());
			$desc = $full_desc;

			if (isset($argv['shortdesc']) && !empty($argv['shortdesc'])) {
				$suffix = '...';
				$short_desc = trim(str_replace("\n", ' ', str_replace("\r", ' ', strip_tags(StupefyEntities($item->get_description())))));
				$desc = substr($short_desc, 0, $argv['shortdesc']);
				$lastchar = substr($desc, -1, 1);
				if ($lastchar == '.' || $lastchar == '!' || $lastchar == '?') $suffix='';
				$desc .= $suffix;
			}

			if (isset($argv['showdesc']) && !empty($argv['showdesc']) && $argv['showdesc']==='false') {
				if (isset($argv['showdate']) && !empty($argv['showdate'])) {
					$output .= "<li><a href=\"$link\">$title</a> <span class=\"date\">" . $item->get_date($argv['showdate']) . "</span></li>";
				} else {
					$output .= "<li><a href=\"$link\">$title</a></li>";
				}
			} else {
				if (isset($argv['showdate']) && !empty($argv['showdate'])) {
					$output .= "<li><strong><a href=\"$link\">$title</a> <span class=\"date\">" . $item->get_date($argv['showdate']) . "</span></strong><br />$desc</li>";
				} else {
					$output .= "<li><strong><a href=\"$link\">$title</a></strong><br />$desc</li>";
				}
			}
		}

		$output .= '</ol>';
		$output .= '</div>';
	}
	else {
		if (isset($argv['error']) && !empty($argv['error'])) $output = $argv['error'];
		else if (isset($feed->error)) $output = $feed->error;
	}

	return $output;
}

// SmartyPants 1.5.1 changes rolled in May 2004 by Alex Rosenberg, http://monauraljerk.org/smartypants-php/
function StupefyEntities($s = '') {
	$inputs = array('&#8211;', '&#8212;', '&#8216;', '&#8217;', '&#8220;', '&#8221;', '&#8230;', '&#91;', '&#93;');
	$outputs = array('-', '--', "'", "'", '"', '"', '...', '[', ']');
	$s = str_replace($inputs, $outputs, $s);
	return $s;
}

?>
