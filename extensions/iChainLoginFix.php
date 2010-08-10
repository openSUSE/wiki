<?php
global $wgUser,$wgAuth ;

# include this file in index.php after$mediaWiki->initialize() 

# The user has logged in at another .opensuse.org site
if (isset($_SERVER['HTTP_X_USERNAME']) && $wgUser->isAnon() && validEmail()) {
    error_log("User '" . $_SERVER['HTTP_X_USERNAME'] . "' is logged in ichain but not the wiki, doing it automatically");
    $wgUser = $wgUser->newFromName( $_SERVER['HTTP_X_USERNAME'] );
    if (!($wgUser->getID() > 0)) {
        error_log("Creating new user " . $_SERVER['HTTP_X_USERNAME']);
        $wgUser->addToDatabase();
        $wgAuth->initUser( $wgUser, true );
        $wgUser->saveSettings();
        # Update user count
        $ssUpdate = new SiteStatsUpdate( 0, 0, 0, 0, 1 );
        $ssUpdate->doUpdate();
    } else {
        $wgUser->load();
    }

    $wgAuth->updateUser( $wgUser );
    $wgUser->setCookies();
}

# The user has logged out at another .opensuse.org site
if (!isset($_SERVER['HTTP_X_USERNAME']) && !$wgUser->isAnon()){
    error_log('iChain anonymous, but wiki logged in... running logout() hook');
    $wgUser->logout();
    $wgUser->setCookies();
};

# set the users email:
if (isset($_SERVER['HTTP_X_EMAIL']) && !$wgUser->isAnon() && $wgUser->getEmail() != $_SERVER['HTTP_X_EMAIL']) {
    error_log("Updating user email from iChain: " . $_SERVER['HTTP_X_EMAIL']);
    $wgUser->setEmail( $_SERVER['HTTP_X_EMAIL'] );
    $wgAuth->updateUser( $wgUser );
    $wgUser->setCookies();
}

function validEmail() {
    if (!session_id()) session_start();
    if (isset($_SERVER['HTTP_X_ENTITLEMENTGRANTED'])) {
        if (!strpos($_SERVER['HTTP_X_ENTITLEMENTGRANTED'],'EmailValidated--NR')) {
            if (!isset($_SESSION['redirected'])) {
                error_log($_SERVER['HTTP_X_USERNAME'] . " does not have a validated email address");
                if ($_SERVER['REQUEST_URI'] != "/Help:Email_validation") {
                    $_SESSION['redirected'] = true;
                    header( 'Location: http://' . $_SERVER['SERVER_NAME'] . '/Help:Email_validation' );
                    exit(0);
                }
            }
            return FALSE;
        }
    } else {
        error_log('Entitlements are not being passed!');
    }
    return TRUE;
}

?>
