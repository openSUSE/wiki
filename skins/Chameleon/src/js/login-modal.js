/**
 * Automatically focus username input after opening login modal.
 * This is a fallback for Firefox because autofocus property doesn't work.
 */
$(function() {
	$("#login-modal-toggle").click(function() {
		// Must set focus after modal shown, otherwise it won't focus or wrong
		// position of auto-fill menu
		setTimeout(function() {
			$("#login-username").focus();
		}, 500);
	});
});
