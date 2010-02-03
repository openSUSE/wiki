<?php
global $wgUser,$wgAuth ;

# include this file in index.php after$mediaWiki->initialize() 

# The user has logged in at anouther .opensuse.org site

if (isset($_SERVER['HTTP_X_USERNAME']) && $wgUser->isAnon()) {
    error_log("User '" . $_SERVER['HTTP_X_USERNAME'] . "' is logged in ichain but not the wiki, doing it automatically");
    $wgUser = $wgUser->newFromName( $_SERVER['HTTP_X_USERNAME'] );
    $wgAuth->updateUser( $wgUser );
    $wgUser->setCookies();
}

# The user has logged out at another .opensuse.org site

if (!isset($_SERVER['HTTP_X_USERNAME']) && !$wgUser->isAnon()){
    error_log('iChain anonymous, but wiki logged in... running logout() hook');
    
    $wgUser->logout();
    $wgUser->setCookies();
};

?>
