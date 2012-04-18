/*
 * JavaScript for WikiEditor Preview module
 */

$( document ).ready( function() {
	// Add preview module
	$j( 'textarea#wpTextbox1' ).wikiEditor( 'addModule', 'preview' );
} );
