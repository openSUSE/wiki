<?php

require_once( 'includes/Feed.php' );

$wgExtensionFunctions[] = "wfWikiWebLogExtension";

function wfWikiWebLogExtension() {
        global $wgParser;
        $wgParser->setHook("feed", "renderFeed");
        $wgParser->setHook("blog", "renderBlog");
}

function renderBlog($input, $argv) {
        global $wgTitle;
        global $wgUser, $wgRequest;
        global $wgBlogCount, $wgBlogLimit;

        $bTitle = @$argv["title"];
        $bAuthor = @$argv["author"];
        $bDate = @$argv["date"];
        $bLink = @$argv["link"];
        $bLimit = @$argv["limit"];
        $bBody = $input;

        if(strcmp("", $bTitle) == 0) {
                $wgBlogCount = 0;
                $wgBlogLimit = $bLimit;
        }

        if (!$input) return "";

        $wgBlogCount++;
        if($wgBlogCount > $wgBlogLimit && isset($wgBlogLimit)) {
                return "";
        }

        $parser = new Parser();
        $parser->startExternalParse($wgTitle, ParserOptions::newFromUser( $wgUser ), OT_HTML);
        ob_start();
        $input = $parser->internalParse($bBody, 0, array(), false);
        ob_end_clean();
        $output = "<h3>$bTitle</h3>" 
                . "($bDate)" . $input;
        if(strcmp("", $bLink) != 0) {
                $output = $output . '<br/><div class="maintoc" align="right"><i><a href="' . $bLink . '">more...</a></i></div>';
        }
        return $output;
}

function renderFeed($input) {
        global $wgTitle, $wgDescription;
        global $wgUser, $wgRequest;
        global $wgBlogCount, $wgBlogLimit;
        global $wgParser, $wgSitename, $wgFeedClasses;
        global $blogFeed;

        $feedFormat = $wgRequest->getVal( 'feed' );
        $feedParser = new Parser();

        if(!$feedFormat) {
                $feedParser->setHook("blog", "renderBlog");
                $feedParser->startExternalParse($wgTitle, ParserOptions::newFromUser( $wgUser ), OT_HTML);
                $output = & $feedParser->parse($input, $wgTitle,  ParserOptions::newFromUser( $wgUser ));
                return $output->getText();
        } else {
                $feedParser->setHook("blog", "renderBlogFeed");
                $feedParser->startExternalParse($wgTitle, ParserOptions::newFromUser( $wgUser ), OT_HTML);
                $feedTitle = $wgSitename . ' - ' . $wgTitle->getText();
                $blogFeed = new $wgFeedClasses[$feedFormat]($feedTitle, $wgDescription, $wgTitle->getFullUrl() );
                $blogFeed->outHeader();
                $output = &$feedParser->parse($input, $wgTitle,  ParserOptions::newFromUser( $wgUser ));
                $blogFeed->outFooter();
                die();
        }
}

function renderBlogFeed($input, $argv) {
        global $wgTitle;
        global $wgUser, $wgRequest;
        global $wgBlogCount, $wgBlogLimit;
        global $blogFeed;

        if (!$input) return "";

        $bTitle = @$argv["title"];
        $bAuthor = @$argv["author"];
        $bDate = @$argv["date"];
        $bLink = @$argv["link"];
        $bLimit = @$argv["limit"];
        $bBody = $input;

        if(strcmp("", $bTitle) == 0) {
                $wgBlogCount = 0;
                $wgBlogLimit = $bLimit;

                return "";
        }
        $fields=explode("/", $wgTitle->getFullUrl());

        if(strncmp("/", $bLink, 1) == 0) {
                $bLink = $fields[0] . "//" . $fields[2] . $bLink;
        }

        $wgBlogCount++;
        if($wgBlogCount > $wgBlogLimit && isset($wgBlogLimit)) {
                return "";
        }

        $parser = new Parser();
        $parser->startExternalParse($wgTitle, ParserOptions::newFromUser( $wgUser ), OT_HTML);
        ob_start();
        $bBody = $parser->internalParse($bBody, 0, array(), false);
        ob_end_clean();
        $blogItem = new FeedItem($bTitle, $bBody, $bLink, strtotime($bDate) , "", "");
        $blogFeed->outItem($blogItem);

        return "";
}
