/* -- (c) Aaron Schulz, Daniel Arnold 2008 */

/* Every time you change this JS please bump $wgFlaggedRevStyleVersion in FlaggedRevs.php */

/*
* a) Disable submit in case of invalid input.
* b) Update colors when select changes (Opera already does this).
* c) Also remove comment box clutter in case of invalid input.
* NOTE: all buttons should exist (perhaps hidden though)
*/
FlaggedRevs.updateRatingForm = function() {
	var ratingform = document.getElementById('mw-fr-ratingselects');
	if( !ratingform ) return;
	var disabled = document.getElementById('fr-rating-controls-disabled');
	if( disabled ) return;

	var quality = true;
	var somezero = false;

	// Determine if this is a "quality" or "incomplete" review
	for( tag in wgFlaggedRevsParams.tags ) {
		var controlName = "wp" + tag;
		var levels = document.getElementsByName(controlName);
		if( !levels.length ) continue;

		var selectedlevel = 0; // default
		if( levels[0].nodeName == 'SELECT' ) {
			selectedlevel = levels[0].selectedIndex;
		} else if( levels[0].type == 'radio' ) {
			for( i = 0; i < levels.length; i++ ) {
				if( levels[i].checked ) {
					selectedlevel = i;
					break;
				}
			}
		} else if( levels[0].type == 'checkbox' ) {
			selectedlevel = (levels[0].checked) ? 1: 0;
		} else {
			return; // error: should not happen
		}

		// Get quality level for this tag
		qualityLevel = wgFlaggedRevsParams.tags[tag]['quality'];
	
		if( selectedlevel < qualityLevel ) {
			quality = false; // not a quality review
		}
		if( selectedlevel <= 0 ) {
			somezero = true;
		}
	}

	// (a) If only a few levels are zero ("incomplete") then disable submission.
	// (b) Re-enable submission for already accepted revs when ratings change.
	var asubmit = document.getElementById('mw-fr-submit-accept');
	if( asubmit ) {
		asubmit.disabled = somezero ? 'disabled' : '';
		asubmit.value = wgAjaxReview.flagMsg; // reset to "Accept"
	}

	// Update colors of <select>
	FlaggedRevs.updateRatingFormColors();
};

/*
* Disable 'accept' button if the revision was already reviewed
* NOTE: this is used so that they can be re-enabled if a rating changes
*/
FlaggedRevs.maybeDisableAcceptButton = function() {
	if ( typeof(jsReviewNeedsChange) != 'undefined' && jsReviewNeedsChange == 1 ) {
		var asubmit = document.getElementById('mw-fr-submit-accept');
		if( asubmit ) {
			asubmit.disabled = 'disabled';
		}
	}
};

hookEvent( "load", FlaggedRevs.maybeDisableAcceptButton );

FlaggedRevs.updateRatingFormColors = function() {
	for( tag in wgFlaggedRevsParams.tags ) {
		var controlName = "wp" + tag;
		var levels = document.getElementsByName(controlName);
		if( levels.length && levels[0].nodeName == 'SELECT' ) {
			selectedlevel = levels[0].selectedIndex;
			// Update color. Opera does this already, and doing so
			// seems to kill custom pretty opera skin form styling.
			if( navigator.appName != 'Opera' ) {
				value = levels[0].getElementsByTagName('option')[selectedlevel].value;
				levels[0].className = 'fr-rating-option-' + value;
			}
		}
	}
};

hookEvent( "load", FlaggedRevs.updateRatingFormColors );

// dependencies:
// * ajax.js:
  /*extern sajax_init_object, sajax_do_call */
// * wikibits.js:
  /*extern hookEvent, jsMsg */
// These should have been initialized in the generated js
if( typeof wgAjaxReview === "undefined" || !wgAjaxReview ) {
	wgAjaxReview = {};
}

wgAjaxReview.supported = true; // supported on current page and by browser
wgAjaxReview.inprogress = false; // ajax request in progress
wgAjaxReview.timeoutID = null; // see wgAjaxReview.ajaxCall

wgAjaxReview.ajaxCall = function() {
	if( !wgAjaxReview.supported ) {
		return true;
	} else if( wgAjaxReview.inprogress ) {
		return false;
	}
	if( !wfSupportsAjax() ) {
		// Lazy initialization so we don't toss up
		// ActiveX warnings on initial page load
		// for IE 6 users with security settings.
		wgAjaxReview.supported = false;
		return true;
	}
	var form = document.getElementById("mw-fr-reviewform");
	var notes = document.getElementById("wpNotes");
	var reason = document.getElementById("wpReason");
	if( !form ) {
		return false;
	}
	wgAjaxReview.inprogress = true;
	// Build up arguments
	var args = [];
	var inputs = form.getElementsByTagName("input");
	for( var i=0; i < inputs.length; i++) {
		// Different input types may occur depending on tags...
		if( inputs[i].name == "title" || inputs[i].name == "action" ) {
			continue; // No need to send these...
		} else if( inputs[i].type == "submit" ) {
			if( inputs[i].id == this.id ) {
				inputs[i].value = wgAjaxReview.sendingMsg; // show that we are submitting
				args.push( inputs[i].name + "|1" );
			}
		} else if( inputs[i].type == "checkbox" ) {
			args.push( inputs[i].name + "|" + (inputs[i].checked ? inputs[i].value : 0) );
		} else if( inputs[i].type == "radio" ) {
			if( inputs[i].checked ) { // must be checked
				args.push( inputs[i].name + "|" + inputs[i].value );
			}
		} else {
			args.push( inputs[i].name + "|" + inputs[i].value ); // text/hiddens...
		}
		inputs[i].disabled = "disabled";
	}
	if( notes ) {
		args.push( notes.name + "|" + notes.value );
		notes.disabled = "disabled";
	}
	var selects = form.getElementsByTagName("select");
	for( var i=0; i < selects.length; i++) {
		// Get the selected tag level...
		if( selects[i].selectedIndex >= 0 ) {
			var soption = selects[i].getElementsByTagName("option")[selects[i].selectedIndex];
			args.push( selects[i].name + "|" + soption.value );
		}
		selects[i].disabled = "disabled";
	}
	// Send!
	var old = sajax_request_type;
	sajax_request_type = "POST";
	sajax_do_call( "RevisionReview::AjaxReview", args, wgAjaxReview.processResult );
	sajax_request_type = old;
	// If the request isn't done in 30 seconds, allow user to try again
	wgAjaxReview.timeoutID = window.setTimeout(
		function() { wgAjaxReview.inprogress = false; wgAjaxReview.unlockForm(); },
		30000
	);
	return false;
};

wgAjaxReview.unlockForm = function() {
	var form = document.getElementById("mw-fr-reviewform");
	var notes = document.getElementById("wpNotes");
	var reason = document.getElementById("wpReason");
	if( !form ) {
		return false;
	}
	var inputs = form.getElementsByTagName("input");
	for( var i=0; i < inputs.length; i++) {
		if( inputs[i].type != 'submit' ) {
			inputs[i].disabled = "";
		} else {
			inputs[i].blur(); // focus off element (bug 24013)
		}
	}
	if( notes ) {
		notes.disabled = "";
	}
	if( reason ) {
		reason.disabled = "";
	}
	var selects = form.getElementsByTagName("select");
	for( var i=0; i < selects.length; i++) {
		selects[i].disabled = "";
	}
	return true;
};

wgAjaxReview.processResult = function(request) {
	if( !wgAjaxReview.supported ) {
		return;
	}
	wgAjaxReview.inprogress = false;
	if( wgAjaxReview.timeoutID ) {
		window.clearTimeout(wgAjaxReview.timeoutID);
	}
	var response = request.responseText; // full response text
	var msg = response.substr(6); // remove <err#> or <suc#>
	// Read new "last change time" timestamp for conflict handling
	var m = msg.match(/^<lct#(\d*)>(.+)/m);
	if( m ) msg = m[2]; // remove tag from msg
	var changeTime = m ? m[1] : null; // MW TS
	// Some form elements...
	var asubmit = document.getElementById('mw-fr-submit-accept');
	var usubmit = document.getElementById('mw-fr-submit-unaccept');
	var legend = document.getElementById('mw-fr-reviewformlegend');
	var diffNotice = document.getElementById('mw-fr-difftostable');
	var tagBox = document.getElementById('mw-fr-revisiontag');
	// On success...
	if( response.indexOf('<suc#>') == 0 ) {
		// (a) Update document title and form buttons...
		document.title = wgAjaxReview.actioncomplete;
		if( asubmit && usubmit ) {
			// Revision was flagged
			if( asubmit.value == wgAjaxReview.sendingMsg ) {
				asubmit.value = wgAjaxReview.flaggedMsg; // done!
				asubmit.style.fontWeight = 'bold';
				// Unlock and reset *unflag* button
				usubmit.value = wgAjaxReview.unflagMsg;
				usubmit.removeAttribute( 'style' ); // back to normal
				usubmit.disabled = '';
			// Revision was unflagged
			} else if( usubmit.value == wgAjaxReview.sendingMsg ) {
				usubmit.value = wgAjaxReview.unflaggedMsg; // done!
				usubmit.style.fontWeight = 'bold';
				// Unlock and reset *flag* button
				asubmit.value = wgAjaxReview.flagMsg;
				asubmit.removeAttribute( 'style' ); // back to normal
				asubmit.disabled = '';
			}
		}
		// (b) Remove review tag from drafts
		if( tagBox ) tagBox.style.display = 'none';
		// (c) Update diff-related items...
		var diffUIParams = document.getElementById('mw-fr-diff-dataform');
		if ( diffUIParams ) {
			// Hide "review this" box on diffs
			if( diffNotice ) diffNotice.style.display = 'none';
			// Update the contents of the mw-fr-diff-headeritems div
			wgAjaxReview.inprogress = true;
			var args = []; // <oldid, newid>
			args.push( diffUIParams.getElementsByTagName('input')[0].value );
			args.push( diffUIParams.getElementsByTagName('input')[1].value );
			// Send!
			var old = sajax_request_type;
			sajax_request_type = "GET";
			sajax_do_call( "FlaggedArticleView::AjaxBuildDiffHeaderItems",
				args, wgAjaxReview.processDiffHeaderItemsResult );
			sajax_request_type = old;
		}
	// On failure...
	} else {
		// (a) Update document title and form buttons...
		document.title = wgAjaxReview.actionfailed;
		if( asubmit && usubmit ) {
			// Revision was flagged
			if( asubmit.value == wgAjaxReview.sendingMsg ) {
				asubmit.value = wgAjaxReview.flagMsg; // back to normal
				asubmit.disabled = ''; // unlock flag button
			// Revision was unflagged
			} else if( usubmit.value == wgAjaxReview.sendingMsg ) {
				usubmit.value = wgAjaxReview.unflagMsg; // back to normal
				usubmit.disabled = ''; // unlock
			}
		}
		// (b) Output any error response message
		if ( response.indexOf('<err#>') == 0 ) {
			jsMsg( msg, 'review' ); // failure notice
		} else {
			jsMsg( request.responseText, 'review' ); // fatal notice
		}
		window.scroll(0,0); // scroll up to notice
	}
	// Update changetime for conflict handling
	if ( changeTime != null ) {
		document.getElementById('mw-fr-input-changetime').value = changeTime;
	}
	wgAjaxReview.unlockForm();
};

// update the contents of the mw-fr-diff-headeritems div
wgAjaxReview.processDiffHeaderItemsResult = function(request) {
	if( !wgAjaxReview.supported ) {
		return;
	}
	wgAjaxReview.inprogress = false;
	var response = request.responseText;
	var diffHeaderItems = document.getElementById("mw-fr-diff-headeritems");
	if( diffHeaderItems && response != '' ) {
		diffHeaderItems.innerHTML = response;
	}
};

wgAjaxReview.onLoad = function() {
	var asubmit = document.getElementById("mw-fr-submit-accept");
	if( asubmit ) {
		asubmit.onclick = wgAjaxReview.ajaxCall;
	}
	var usubmit = document.getElementById("mw-fr-submit-unaccept");
	if( usubmit ) {
		usubmit.onclick = wgAjaxReview.ajaxCall;
	}
};

hookEvent("load", wgAjaxReview.onLoad);
