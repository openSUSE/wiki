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
        global $wgServer, $hermesHost, $hermesUser, $hermesPwd;
        global $wgCanonicalNamespaceNames;

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
            $last_revision = $rc->mAttribs['rc_this_oldid'] - 1;
            $author = $rc->mAttribs['rc_user_text'];
            $log = $rc->mAttribs['rc_comment'];
            $lines = $rc->mAttribs['rc_new_len'] - $rc->mAttribs['rc_old_len'];
            $title = Title::makeTitle($rc->mAttribs['rc_namespace'], $rc->mAttribs['rc_title']);
            $namespace_nr = $rc->mAttribs['rc_namespace'];
            $namespace_name = $wgCanonicalNamespaceNames[$namespace_nr];
            $is_minor = $rc->mAttribs['rc_minor'];
            $bot_edit = $rc->mAttribs['rc_bot'];
            $patrolled = $rc->mAttribs['rc_patrolled'];
            $lang = $rc->mExtra['lang'];
            $last_change = $rc->mExtra['lastTimestamp'];
            $file = $title->getLocalURL();
            $url = $title->getFullURL();
            $hermesSender = "wiki_noreply@opensuse.org";

            $url = $hermesHost . "?rm=notify&_type=WIKI:CHANGE&sender=" . urlencode($hermesSender) .
               "&change_type=" . urlencode($action) .
               "&page=" . urlencode($title) .
               "&revision=" . urlencode($revision) .
               "&author=" . urlencode($author) .
               "&log=" . urlencode($log) .
               "&lines=" . urlencode($lines) .
               "&file=" . urlencode($file) .
               "&host=" . urlencode($branch) .
               "&last_revision=" . urlencode($last_revision) .
               "&url=" . urlencode($url) .
               "&last_change=" . urlencode($last_change) .
               "&lang=" . urlencode($lang) .
               "&patrolled=" . urlencode($patrolled) .
               "&bot_edit=" . urlencode($bot_edit) .
               "&is_minor=" . urlencode($is_minor) .
               "&namespace=" . urlencode($namespace_name);

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            if ($hermesUser != '' && $hermesPwd != '') {
                curl_setopt($ch, CURLOPT_USERPWD, $hermesUser . ":" . $hermesPwd);
                curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
            }
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            // 1s timeout -> fire and forget, we are not interested in the reply
            curl_setopt($ch, CURLOPT_TIMEOUT, '5');

            $data = curl_exec($ch);
            curl_close($ch);
            error_log("Changed $title, Hermes response: $data");

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
