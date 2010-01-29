<?php
# EventCountdown extension
# Copyright 2006 Matt Curtis (matt.r.curtis at gmail.com)
#
# License:
#  This program is free software; you can redistribute it and/or modify
#  it under the terms of the GNU General Public License as published by
#  the Free Software Foundation; either version 2 of the License, or
#  (at your option) any later version.
#
#  This program is distributed in the hope that it will be useful,
#  but WITHOUT ANY WARRANTY; without even the implied warranty of
#  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
#  GNU General Public License for more details.
#
#  You should have received a copy of the GNU General Public License
#  along with this program; if not, write to the Free Software
#  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
#
#
# Usage:
#
# The <daysuntil> tag is replaced with the number of days until the date.
# Formatting uses php's strtotime(), so it's quite flexible on the input.
# The optional 'in="days"' argument will append "day" or "days" as
# appropriate.
#
#   E3 is <daysuntil in="days">10 May 2005</daysuntil> away.
#
# The <eventcountdown> tag will show its contents only until the
# date arrives. The contents can be wikitext. For example:
#
#   <eventcountdown date="10-May-2006">Get ready for '''E3'''!</eventcountdown>
#
# They are most useful when combined. For example, to display "x days until
# E3 2006" with a link to E3:
#
#   <eventcountdown date="10-May-2006"><daysuntil in="days">10-May-2006
#     </daysuntil> until [http://www.e3expo.com E3 2006]</eventcountdown>
#
# To activate the extension, include it from your LocalSettings.php
# with: require_once("extensions/EventCountdown.php");

$wgExtensionFunctions[] = "wfEventCountdownExtension";


function wfEventCountdownExtension() {
        global $wgParser;
        # register the extension with the WikiText parser
        # the first parameter is the name of the new tag.
        # In this case it defines the tag <example> ... </example>
        # the second parameter is the callback function for
        # processing the text between the tags
        $wgParser->setHook( "daysuntil", "runDaysUntil" );
        $wgParser->setHook( "eventcountdown", "runShowEventCountdown" );
}

function runDaysUntil( $input, $argv ) {
        $now = time();
        $then = strtotime($input);

        $daysUntil = getDaysBetween($now, $then);
        $output = $daysUntil;
        switch ($argv["in"]) {
        case "days":
                if ($daysUntil == 1) {
                        $output .= " day";
                }
                else {
                        $output .= " days";
                }
                break;

        default:
        }

        return $output;
}

function runShowEventCountdown( $input, $argv ) {
        $now = time();
        $then = strtotime($argv["date"]);
        $daysUntil = getDaysBetween($now, $then);

        $output = "";

        if ($daysUntil > 0) {
                global $wgOut;
                $output = $wgOut->parse($input, false);
        }

        return $output;
}

function getDaysBetween($date1, $date2) {
        $deltaSeconds = $date2 - $date1;
        $deltaDays = $deltaSeconds / (60 * 60 * 24);
        return ceil($deltaDays);
}

?>
