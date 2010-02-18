<?php
/**
 * Contains code for Hermes Notification
 *
 * @licence MIT/X11
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

if ( !defined( 'MEDIAWIKI' ) ) {
	echo "Mediawiki Hermes extension";
	exit( 1 );
}

global $wgExtensionCredits;
$wgExtensionCredits['other'][] = array(
	'name' => "Hermes Notify",
	'version' => 1.0,
	'author' => "Thomas Schmidt (tom@opensuse.org)",
	'description' => "Sends change notifications to the hermes server",
	'url' => 'http://en.opensuse.org/Hermes',
);

global $wgExtensionFunctions;
$wgExtensionFunctions[] = 'initHermesNotify';

class hermesNotify {
	function notifyHermes(&$rc) {
        require("http.php");
        global $wgServer, $hermesHost, $hermesUser, $hermesPwd;

        switch ( $rc->mAttribs['rc_type'] ) {
		case RC_EDIT:
			$action = "modify";
			break;
		case RC_NEW:
			$action = "add";
			break;
		case RC_MOVE:
			$action = "rename";
			break;
		case RC_LOG:
			$action = "log";
			break;
		case RC_MOVE_OVER_REDIRECT:
			$action = "rename";
			break;
		default:
			$action = "modify";
	}

	$branch = "$wgServer";
	$revision = $rc->mAttribs['rc_this_oldid'];
	$author = $rc->mAttribs['rc_user_text'];
	$log = $rc->mAttribs['rc_comment'];
	$lines = $rc->mAttribs['rc_new_len'] - $rc->mAttribs['rc_old_len'];
	$title = Title::makeTitle($rc->mAttribs['rc_namespace'], $rc->mAttribs['rc_title']);
	$file = $title->getLocalURL();
	$url = htmlentities($title->getFullURL("oldid=$revision"));
        $hermesSender = "wiki_noreply@opensuse.org";

        $url = "https://notify.opensuse.org/index.cgi?rm=notify&_type=WIKI:CHANGE&sender=" . urlencode($hermesSender) . 
               "&change_type=" . urlencode($action) . 
               "&page=" . urlencode($title) . 
               "&revision=" . urlencode($revision) . 
               "&author=" . urlencode($author) . 
               "&log=" . urlencode($log) . 
               "&lines=" . urlencode($lines) . 
               "&file=" . urlencode($file) . 
               "&url=" . urlencode($url);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_USERPWD, $hermesUser . ":" . $hermesPwd);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $data = curl_exec($ch);
        curl_close($ch);

        error_log("Hermes response: $data");

	# all is always well
	return true;
	}
}


function initHermesNotify() {
	# make sure our settings are set
	global $hermesHost;
	if ( $hermesHost == '' ) {
		die ('You need to set $hermesHost to a valid Hermes instance or deactivate the HermesNotifier plugin.');
	}
	global $wgHooks;
	$cnObject = new HermesNotify();
	$wgHooks['RecentChange_save'][] = array( &$cnObject, 'notifyHermes' );
}
