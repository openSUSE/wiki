/*
*
* JavaScript function for opensuse.org
*
* langShow: toggles style from hide to show
* langRedirect: redirects to another languiage wiki on change
*               of the language dropdown
*
* Author: Frank Sundermeyer <fs@opensuse.org>
* $Id: $
*/

function langShow () {
    document.getElementById('langsel').className = 'show';
}
function langRedirect () {
  var FORM = document.forms["langsel"];
  var SELECTOR = FORM.elements["lang"];
  var REDIRECT_URL = "";
  for (var i = 0; i < SELECTOR.length; i++) {
    if (SELECTOR[i].selected) {
      REDIRECT_URL = SELECTOR[i].value;
    }
  }
  if (REDIRECT_URL != "") {
    FORM.reset;
    window.location.href = REDIRECT_URL;
  }
  return true; 
}  

