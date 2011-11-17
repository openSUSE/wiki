<?php
    /**
     * @package MediaWiki
     */
     # Copyright (C) 2005 Macrus Rueckert <darix@suse.de>
     #
     # This program is free software; you can redistribute it and/or modify
     # it under the terms of the GNU General Public License as published by
     # the Free Software Foundation; either version 2 of the License, or
     # (at your option) any later version.
     #
     # This program is distributed in the hope that it will be useful,
     # but WITHOUT ANY WARRANTY; without even the implied warranty of
     # MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
     # GNU General Public License for more details.
     #
     # You should have received a copy of the GNU General Public License along
     # with this program; if not, write to the Free Software Foundation, Inc.,
     # 59 Temple Place - Suite 330, Boston, MA 02111-1307, USA.
     # http://www.gnu.org/copyleft/gpl.html

    /**
     * Authentication plugin interface. Instantiate a subclass of AuthPlugin
     * and set $wgAuth to it to authenticate against some external tool.
     *
     * The default behavior is not to do anything, and use the local user
     * database for all authentication. A subclass can require that all
     * accounts authenticate externally, or use it only as a fallback; also
     * you can transparently create internal wiki accounts the first time
     * someone logs in who can be authenticated externally.
     *
     * This interface is new, and might change a bit before 1.4.0 final is
     * done...
     *
     * @package MediaWiki
     */

    require_once( 'AuthPlugin.php' );
    /*
     * at this point we fake a bit.the iChain sends us the username
     * in the HTTP header but mediawiki wants it in the $_REQUEST header.
     */
    if (isset($_SERVER['HTTP_X_USERNAME']) && $_SERVER['HTTP_X_USERNAME'] != '') {
       global $wgRequest, $wgUser;
       #error_log('ichain username: ' . $_SERVER['HTTP_X_USERNAME']);
       $_POST['wpName'] = $_SERVER['HTTP_X_USERNAME'];
       $_POST['wpPassword'] = '';
       if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'submitlogin') {
          $_POST['action'] = 'submitlogin';
          $_SERVER['REQUEST_METHOD'] =  'POST';
          $_POST['wpLoginattempt'] = 'Log in';
          error_log('iChain login by: ' . $_SERVER['HTTP_X_USERNAME']);
       }
    };
 
    # include the file extensions/iChainLoginFix.php in index.php after mediawiki.initialize() to fix login issues that cannot be fixed here


    //use ichain for login
    class iChainAuthenticationPlugin extends AuthPlugin {
        /**
         * Check whether there exists a user account with the given name.
         * The name will be normalized to MediaWiki's requirements, so
         * you might need to munge it (for instance, for lowercase initial
         * letters).
         *
         * @param string $username
         * @return bool
         * @access public
         */
        function userExists( $username ) {
            return true;
        }

        /**
         * Check if a username+password pair is a valid login.
         * The name will be normalized to MediaWiki's requirements, so
         * you might need to munge it (for instance, for lowercase initial
         * letters).
         *
         * @param string $username
         * @param string $password
         * @return bool
         * @access public
         */
        function authenticate( $username, $password ) {
           if (isset($_SERVER['HTTP_X_USERNAME']) && $_SERVER['HTTP_X_USERNAME'] != '') {
                 return true;
           }
           return false;
        }

        /**
         * Modify options in the login template.
         *
         * @param UserLoginTemplate $template
         * @access public
         */
        /*
         *
         * describing our logic:
         * if a user enters:
         * http://fr.opensuse.org/index.php?title=Special:Userlogin&returnto=Special:Userlogin
         *
         * we will redirect him to the ics login site:
         * https://fr.opensuse.org/ICSlogin/?%22http://fr.opensuse.org/index.php?title=Special:Userlogin&returnto=Welcome_to_openSUSE.org&action=submitlogin%22
         *
         * ics will send us back to:
         * http://fr.opensuse.org/index.php?title=Special:Userlogin&action=submitlogin&returnto=Welcome_to_openSUSE.org
         *
         * at this url we will just check if the x-username header is set. if it is we will login the user.
         *
         */

         function modifyUITemplate( &$template ) {
            $template->set( 'usedomain', false );
            $template->set( 'create',    false );
            $template->set( 'useemail',  false );
            if (isset($_SERVER['HTTP_X_USERNAME']) && $_SERVER['HTTP_X_USERNAME'] != '' ) {
               $returnto  = 'Location: http://' . $_SERVER['SERVER_NAME'] . '/';
               $returnto .= isset($_REQUEST['returnto']) ? $_REQUEST['returnto'] : '/';
               // error_log($returnto);
               header ($returnto);
               exit (0);
            }
            else{
               /*
               $iChainMagicKey = '/ICSLogin/?';
               $returnto  = 'Location: https://' . $_SERVER['SERVER_NAME'] . '/ICSLogin/?%22http://' . $_SERVER['SERVER_NAME']  . '/';
               $returnto .= isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
               $returnto .= '%22';
               */
               $returnto = 'Location: http://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'] . '&action=submitlogin';
               // error_log($returnto);
               header ($returnto);
               exit (0);
            };
            error_log('we should have never reached thisS');
            error_log('$_REQUEST: ' . print_r($_REQUEST,1));
            error_log (strpos ($_SERVER['REQUEST_URI'], 'action=submitlogin'));
         }

        /**
         * Set the domain this plugin is supposed to use when authenticating.
         *
         * @param string $domain
         * @access public
         */
        function setDomain( $domain ) {
       	     $this->domain = $domain;
        }

        /**
         * Check to see if the specific domain is a valid domain.
         *
         * @param string $domain
         * @return bool
         * @access public
         */
        function validDomain( $domain ) {
            # Override this!
            return true;
        }

        /**
         * When a user logs in, optionally fill in preferences and such.
         * For instance, you might pull the email address or real name from the
         * external user database.
         *
         * The User object is passed by reference so it can be modified; don't
         * forget the & on your function declaration.
         *
         * @param User $user
         * @access public
         */
        function updateUser( &$user ) {
           # Override this and do something
           //$user->setOption('skin','opensuse');
           if (isset($_SERVER['HTTP_X_EMAIL']) && $_SERVER['HTTP_X_EMAIL'] != '' ) {
             $user->setEmail( $_SERVER['HTTP_X_EMAIL'] );
           } else {
             $user->setEmail( '' );
           }
           $user->saveSettings();
            return true;
        }


        /**
         * Return true if the wiki should create a new local account automatically
         * when asked to login a user who doesn't exist locally but does in the
         * external auth database.
         *
         * If you don't automatically create accounts, you must still create
         * accounts in some way. It's not possible to authenticate without
         * a local account.
         *
         * This is just a question, and shouldn't perform any actions.
         *
         * @return bool
         * @access public
         */
        function autoCreate() {
            return true;
        }

        /**
         * Set the given password in the authentication database.
         * Return true if successful.
         *
         * @param string $password
         * @return bool
         * @access public
         */
        function setPassword( $password ) {
            return true;
        }

        /**
         * Update user information in the external authentication database.
         * Return true if successful.
         *
         * @param User $user
         * @return bool
         * @access public
         */
        function updateExternalDB( $user ) {
            return true;
        }

        /**
         * Check to see if external accounts can be created.
         * Return true if external accounts can be created.
         * @return bool
         * @access public
         */
        function canCreateAccounts() {
            return false;
        }

        /**
         * Add a user to the external authentication database.
         * Return true if successful.
         *
         * @param User $user
         * @param string $password
         * @return bool
         * @access public
         */
        function addUser( $user, $password ) {
            return true;
        }


        /**
         * Return true to prevent logins that don't authenticate here from being
         * checked against the local database's password fields.
         *
         * This is just a question, and shouldn't perform any actions.
         *
         * @return bool
         * @access public
         */
        function strict() {
            return false;
        }

        function allowPasswordChange() {
            return false;
        }

        /**
         * When creating a user account, optionally fill in preferences and such.
         * For instance, you might pull the email address or real name from the
         * external user database.
         *
         * The User object is passed by reference so it can be modified; don't
         * forget the & on your function declaration.
         *
         * @param User $user
         * @access public
         */

        function initUser( &$user ) {
           // automatically creating a new wiki user on first login
           // $user->setPassword( '' );
           // $user->setOption('skin','opensuse');
           if (isset($_SERVER['HTTP_X_EMAIL']) && $_SERVER['HTTP_X_USERNAME'] != '') {
             $user->setEmail( $_SERVER['HTTP_X_EMAIL'] );
           } else {
             $user->setEmail( '' );
           }
           $user->saveSettings();
           return true;
        }
    }

    $wgHooks['UserLogoutComplete'][] = 'iChainLogout';
    function iChainLogout($user) {
      // http://de.opensuse.org/cmd/ICSLogout
        if (strpos($_SERVER['HTTP_HOST'],'stage') !== FALSE) {
      	  $returnto  = 'Location: https://espstage.provo.novell.com/AGLogout';
	}
	else {
      	  $returnto  = 'Location: https://esp.novell.com/cmd/ICSLogout';
	}
      header ($returnto);
      exit (0);

    };
?>
