<?php
/* 
* SimpleFeed MediaWiki extension
* 
* Copyright (C) 2007-2008 Jonny Lamb <jonnylamb@jonnylamb.com>
* 
* This program is free software; you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation; either version 2 of the License, or 
* (at your option) any later version.
* 
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
* GNU General Public License for more details.
* 
* You should have received a copy of the GNU General Public License along
* with this program; if not, write to the Free Software Foundation, Inc.,
* 51 Franklin St, Fifth Floor, Boston, MA 02110-1301, USA.
* http://www.gnu.org/copyleft/gpl.html
*/

// Check to make sure we're actually in MediaWiki.
if (!defined('MEDIAWIKI'))
{
	echo 'This file is part of MediaWiki. It is not a valid entry point.';
	exit(1);
}

// Path to simplepie.inc (including leading slash).
$simplepie_path = './extensions/';

// Path to SimplePie cache folder (excluding leader slash).
// Defaults to "./extensions/cache"
$simplepie_cache_folder = $simplepie_path . 'cache/';

if ( ! @include($simplepie_path.'simplepie.inc') )
{
	define('SIMPLEPIE_NOT_FOUND', true);
}

$wgExtensionFunctions[] = 'wfSimpleFeed';
$wgExtensionCredits['parserhook'][] = array(
	'name' => 'SimpleFeed',
	'description' => 'Uses SimplePie to output RSS/atom feeds',
	'author' => 'Jonny Lamb',
	'url' => 'http://www.mediawiki.org/wiki/Extension:SimpleFeed'
);

function wfSimpleFeed()
{
	global $wgParser;
	$wgParser->setHook('feed', 'parseFeed');
}

function parseFeed($input, $args, &$parser)
{
	global $simplepie_cache_folder;

	// Disable page caching.
	$parser->disableCache();
	
	// Check to see whether SimplePie was actually included.
	if (defined('SIMPLEPIE_NOT_FOUND'))
	{
		return '<strong>Error</strong>: <tt>simplepie.inc</tt> was not found in the path. Please edit the path (beginning of extensions/SimpleFeed.php) or add <tt>simplefeed.inc</tt> to the current path.';
	}
	
	// Must have a feed URL and a template to go by outputting items.
	if (!isset($args['url']) or !isset($input))
	{
		return 0;
	}

	$feed = new SimplePie();
	$feed->set_cache_location($simplepie_cache_folder);

	$feed->set_feed_url($args['url']);

	// Get the feed information!
	$feed->init();

	$feed->handle_content_type();

	// Either use default date format (j F Y), or the $date(string) argument.
	// The date argument should conform to PHP's date function, nicely documented
	// at http://php.net/date.
	$date = (isset($args['date'])) ? $args['date'] : 'j F Y';

	$output = '';

	// Use the $entries(int) argument to determine how many entries to show.
	// Defaults to 5, and 0 is unlimited.
	if (isset($args['entries']))
	{
		$max = ($args['entries'] == 0) ? $feed->get_item_quantity() : $feed->get_item_quantity($args['entries']);
	}
	else
	{
		$max = $feed->get_item_quantity(5);
	}
	
	// Loop through each item.
	for ($i = 0; $i < $max; $i++)
	{
		$item = $feed->get_item($i);

		$itemwikitext = $input;

		// {PERMALINK} -> Link to the URL of the post.
		$itemwikitext = str_replace('{PERMALINK}', $item->get_permalink(), $itemwikitext);

		// {DATE} -> The posting date of the post, formatted in the aforementioned way.
		$itemwikitext = str_replace('{DATE}', $item->get_date($date), $itemwikitext);

		// {DESCRIPTION} -> The actual post (or post description if there's a tear).
		$itemwikitext = str_replace('{DESCRIPTION}', $item->get_description(), $itemwikitext);

		// If $type="planet" is used, the author is got from the post title.
		// e.g. title = "Joe Bloggs: I love Mediawiki"
		// This will make: {AUTHOR} -> "Joe Bloggs"
		//                 {TITLE} -> "I love Mediawiki"
		// If this is not set however, the title and author are received the usual way.
		if ($args['type'] == 'planet')
		{
			$title = preg_replace('/(.*): (.*)/sU', '\\2', $item->get_title());
			preg_match('/(.+?): (.+)/sU', $item->get_title(), $matches);
			$author = $matches[1];
		}
		else
		{
			$title = $item->get_title();
			// Often the author is hard to recieve. Maybe it's not a very important
			// thing to output into RSS...?
			$itemauthor = $item->get_author();
			$author = ($itemauthor != null) ? $itemauthor->get_name() : '';
		}

		// {TITLE} -> Title of the post.
		$itemwikitext = str_replace('{TITLE}', $title, $itemwikitext);

		// {AUTHOR} -> Author of the post.
		$itemwikitext = str_replace('{AUTHOR}', $author, $itemwikitext);

		// Add to the overall output the post just done.
		$output .= $itemwikitext;
	}

	// Parse the text into HTML between the <feed>[...]</feed> tags, with arguments replaced.
	$parserObject = $parser->parse($output, $parser->mTitle, $parser->mOptions, false, false);
	
	// Output formatted text.
	return $parserObject->getText();
}

?>
