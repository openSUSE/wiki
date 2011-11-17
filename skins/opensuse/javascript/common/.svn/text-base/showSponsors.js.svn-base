/*
*
* JavaScript function for opensuse.org
*
* Slideshow for sponsor logos
*
* Author: Frank Sundermeyer <fs@opensuse.org>
* $Id: $
*/

function showSponsors () {
    var INTERVAL = 3; // in seconds
    var RANDOM = Math.round(Math.random()*(SPONSORS.length-1));

    setTimeout("showSponsors()",INTERVAL*1000);
    document.getElementById("sponsorSlidehref").href=SPONSORS[RANDOM][0];
    document.getElementById("sponsorSlideimg").src=SPONSORS[RANDOM][1];
}

