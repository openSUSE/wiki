<?php

if ( function_exists( 'wfLoadExtension' ) ) {
	wfLoadExtension( 'LeapVersion' );
	return true;
} else {
	die( 'This version of the LeapVersion extension requires MediaWiki 1.25+' );
}
