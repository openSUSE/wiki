<?php
   # Copyright (C) 2004 Brion Vibber <brion@pobox.com>
   # http://www.mediawiki.org/
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

   #
   # LdapAuthentication.php 
   # Infos availible at http://bugzilla.wikipedia.org/show_bug.cgi?id=814
   #
   # Version 1.0a / 07.10.2005
   # including the fixes describend in comment #50 #51 and #52
   #

   require_once( 'AuthPlugin.php' );

   class LdapAuthenticationPlugin extends AuthPlugin {
      var $email, $lang, $realname, $nickname, $SearchType;
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
         global $wgLDAPAddLDAPUsers;
         global $wgLDAPProxyAgent, $wgLDAPProxyAgentPassword;
         global $wgLDAPBaseDN;
         global $wgLDAPSearchAttributes;

         //If we can't add LDAP users, we don't really need to check
         //if the user exists, the authenticate method will do this for
         //us. This will decrease hits to the LDAP server.
         if (!$wgLDAPAddLDAPUsers) {
            return true;
         }

         $searchstring = $this->getSearchString($username);	
         $ldapconn = $this->connect();
         if ($ldapconn) {
            ldap_set_option( $ldapconn, LDAP_OPT_PROTOCOL_VERSION, 3);
            ldap_set_option( $ldapconn, LDAP_OPT_REFERRALS, 0);
            if ($this->SearchType == "proxy" && isset($wgLDAPProxyAgent)) {
               $userdn = $this->getUserDN($ldapconn, $username);

               if ($userdn == '') {
                  return false;
               }
               $bind = @ldap_bind( $ldapconn, $wgLDAPProxyAgent, $wgLDAPProxyAgentPassword );
            } else {
               //If we don't have a proxy DN, then lets try an anonymous search
               $bind = @ldap_bind($ldapconn);
            }
            if (!$bind) {
               return false;
            }
            if ($this->SearchType == 'proxy') {
               //we need to do a subbase search for the entry
               $searchattr = $wgLDAPSearchAttributes[$_SESSION['wsDomain']];
               $filter = "($searchattr=$username)";
               $entry = @ldap_search($ldapconn, $searchstring, $filter);
            } else {
               //search for the entry itself
               $entry = @ldap_read($ldapconn, $searchstring, "objectclass=*");
            }
            //lets clean up
            @ldap_unbind();
            if (!$entry) {
               //user wasn't found
               return false;
            } else {
               return true;
            }
         } else {
            return false;
         }

      }

      /**
      * Connect to the external database.
      *
      * @return resource
      * @access private
      */
      function connect() {
         global $wgLDAPDomainNames, $wgLDAPServerNames;
         global $wgLDAPUseSSL;

         if ( $wgLDAPUseSSL ) {
            $serverpre = "ldaps://";
         } else {
            $serverpre = "ldap://";
         }

         $servers = "";
         $tmpservers = $wgLDAPServerNames[$_SESSION['wsDomain']];
         $tok = strtok($tmpservers, " ");
         while ($tok) {
            $servers = $servers . " " . $serverpre . $tok;
            $tok = strtok(" ");
         }
         $servers = rtrim($servers);
         $ldapconn = @ldap_connect( $servers );
         return $ldapconn;
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
         global $wgLDAPRetrievePrefs;
         global $wgLDAPProxyAgent;
         global $wgLDAPGroupDN;
         global $wgLDAPRequireAuthAttribute, $wgLDAPAuthAttribute;
         $username{0} = strtolower($username{0});
         if ( '' == $password ) {
            return false;
         }
         $userdn = $this->getSearchString($username);
         $searchtype = $this->SearchType ? $this->SearchType : 'proxy';
         $ldapconn = $this->connect();
         if ( $ldapconn ) {
            ldap_set_option( $ldapconn, LDAP_OPT_PROTOCOL_VERSION, 3);
            ldap_set_option( $ldapconn, LDAP_OPT_REFERRALS, 0);
            if ($searchtype == "proxy" && isset($wgLDAPProxyAgent)) {
               $userdn = $this->getUserDN($ldapconn, $username);
               if ($userdn == '') {
                  return false;
               }
            }
            $bind = @ldap_bind($ldapconn, $userdn, $password);
            if (!$bind) {
               return false;
            }
            if ($wgLDAPRequireAuthAttribute) {
               $filter = "(" . $wgLDAPAuthAttribute[$_SESSION['wsDomain']] . ")";
               $attributes = array("dn");
               $entry = ldap_read($ldapconn, $userdn, $filter, $attributes);
               $info = ldap_get_entries($ldapconn, $entry);
               if ($info["count"] < 1) { return false; }
            }
            if ($wgLDAPGroupDN) {
               return $this->isMemberOfLdapGroup($ldapconn, $userdn, $wgLDAPGroupDN);
            }
            if ($wgLDAPRetrievePrefs) {
               $entry = @ldap_read($ldapconn, $userdn, "objectclass=*");
               $info = @ldap_get_entries($ldapconn, $entry);
               if ($info['count'] > 0 ) {
                  $this->email    = isset ($info[0]["mail"])              ? $info[0]["mail"][0] : '';
                  $this->lang     = isset ($info[0]["preferredlanguage"]) ? $info[0]["preferredlanguage"][0] : ''; 
                  $this->nickname = isset ($info[0]["displayname"])       ? $info[0]["displayname"][0] : ''; 
                  $this->realname = isset ($info[0]["fullName"])                ? $info[0]["cn"][0] : ''; 
               }
            }
            // Lets clean up.
            @ldap_unbind();
         } else {
            return false;
         }
         return true;
      }

      /**
      * Modify options in the login template.
      * 
      * @param UserLoginTemplate $template
      * @access public
      */
      function modifyUITemplate( &$template ) {
         global $wgLDAPDomainNames, $wgLDAPUseLocal;
         $template->set( 'usedomain', true  );
         $template->set( 'create',    false );
         $template->set( 'useemail',  false );
         $tempDomArr = $wgLDAPDomainNames;
         if ( $wgLDAPUseLocal ) {
            array_push( $tempDomArr, 'local' );
         }
         $template->set( 'domainnames', $tempDomArr );
      }

      /**
      * Return true if the wiki should create a new local account automatically
      * when asked to login a user who doesn't exist locally but does in the
      * external auth database.
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
      function setPassword( $user, &$password ) {
         global $wgLDAPUpdateLDAP, $wgLDAPWriterDN, $wgLDAPWriterPassword;
         global $wgLDAPUseLocal;
         global $wgLDAPProxyAgent;

         if ($_SESSION['wsDomain'] == 'local') {
            return true;
         } else if (!$wgLDAPUpdateLDAP) {
            return false;
         }
         $pwd_md5 = base64_encode(pack('H*',sha1($password)));
         $pass = "{SHA}".$pwd_md5;
         $userdn = $this->getSearchString($user->getName());
         $searchtype = $this->SearchType;
         $ldapconn = $this->connect();
         if ($ldapconn) {
            ldap_set_option( $ldapconn, LDAP_OPT_PROTOCOL_VERSION, 3);
            ldap_set_option( $ldapconn, LDAP_OPT_REFERRALS, 0);
            if ($searchtype == "proxy" && isset($wgLDAPProxyAgent)) {
               $userdn = $this->getUserDN($ldapconn, $user->getName());
               if ($userdn == '') {
                  return false;
               }
            }
            if (!isset($wgLDAPWriterDN)) {
               return false;
            }
            $bind = @ldap_bind( $ldapconn, $wgLDAPWriterDN, $wgLDAPWriterPassword );
            if (!$bind) {
               return false;
            }
            $values["userpassword"] = $pass;
            $password = '';
            $success = ldap_modify($ldapconn, $userdn, $values);
            @ldap_unbind();
            if ($success) {
               return true;
            } else {
               return false;
            }
         } else {
            return false;
         }
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
         global $wgLDAPUpdateLDAP, $wgLDAPWriterDN, $wgLDAPWriterPassword;
         global $wgLDAPProxyAgent;
         if (!$wgLDAPUpdateLDAP || $_SESSION['wsDomain'] == 'local') {
            return true;
         }
         $this->email = $user->getEmail();
         $this->realname = $user->getRealName();
         $this->nickname = $user->getOption('nickname');
         $this->language = $user->getOption('language');
         $userdn = $this->getSearchString($user->getName());
         $searchtype = $this->SearchType;
         $ldapconn = $this->connect();
         if ($ldapconn) {
            ldap_set_option( $ldapconn, LDAP_OPT_PROTOCOL_VERSION, 3);
            ldap_set_option( $ldapconn, LDAP_OPT_REFERRALS, 0);
            if ($searchtype == "proxy" && isset($wgLDAPProxyAgent)) {
               $userdn = $this->getUserDN($ldapconn, $user->getName());
               if ($userdn == '') {
                  return false;
               }
            }
            $bind = @ldap_bind( $ldapconn, $wgLDAPWriterDN, $wgLDAPWriterPassword );
            if (!$bind) {
               return false;
            }
            if ('' != $this->email) { $values["mail"] = $this->email; }
            if ('' != $this->nickname) { $values["displayname"] = $this->nickname; }
            if ('' != $this->realname) { $values["cn"] = $this->realname; }
            if ('' != $this->language) { $values["preferredlanguage"] = $this->language; }
            if (0 != sizeof($values) && ldap_modify($ldapconn, $userdn, $values)) {
               @ldap_unbind();
               return true;
            } else {
               @ldap_unbind();
               return false;
            }
         } else {
            return false;
         }
      }

      function canCreateAccounts() {
         global $wgLDAPAddLDAPUsers;
         if ($wgLDAPAddLDAPUsers) {
            return true;
         }
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
         global $wgLDAPAddLDAPUsers, $wgLDAPWriterDN, $wgLDAPWriterPassword;

         if (!$wgLDAPAddLDAPUsers || 'local' == $_SESSION['wsDomain']) {
            return true;
         }
         $this->email = $user->getEmail();
         $this->realname = $user->getRealName();
         $username = $user->getName();
         $pwd_md5 = base64_encode(pack('H*',sha1($password)));
         $pass = "{SHA}".$pwd_md5;
         $userdn = $this->getSearchString($username);
         $searchtype = $this->SearchType;
         $ldapconn = $this->connect();
         if ($ldapconn) {
            ldap_set_option( $ldapconn, LDAP_OPT_PROTOCOL_VERSION, 3);
            ldap_set_option( $ldapconn, LDAP_OPT_REFERRALS, 0);
            if ($searchtype == "proxy" && isset($wgLDAPProxyAgent)) {
               #$userdn = $this->getUserDN($ldapconn, $username);
               #if ($userdn == '') {
                  #	return false;
                  #}
                  //not supported yet
                  return false;
               }
               $bind = @ldap_bind( $ldapconn, $wgLDAPWriterDN, $wgLDAPWriterPassword );
               if (!$bind) {
                  return false;
               }
               $values["uid"] = $username;
               $values["sn"] = $username;
               if ('' != $this->email) { $values["mail"] = $this->email; }
               if ('' != $this->realname) {$values["cn"] = $this->realname; }
               else { $values["cn"] = $username; }
               $values["userpassword"] = $pass;
               $values["objectclass"] = "inetorgperson";
               if (@ldap_add($ldapconn, $userdn, $values)) {
                  @ldap_unbind();
                  return true;
               } else {
                  @ldap_unbind();
                  return false;
               }
            } else {
               return false;
            }
         }

         /**
         * Set the domain this plugin is supposed to use when authenticating.
         *
         * @param string $domain
         * @access public	
         */
         function setDomain( $domain ) {
            $_SESSION['wsDomain'] = $domain;
         }

         /**
         * Check to see if the specific domain is a valid domain.
         * 
         * @param string $domain
         * @return bool
         * @access public
         */
         function validDomain( $domain ) {
            global $wgLDAPDomainNames, $wgLDAPUseLocal;
            if (in_array($domain, $wgLDAPDomainNames) || ($wgLDAPUseLocal && 'local' == $domain)) {
               return true;
            } else {
               return false;
            }
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
            if ('' != $this->lang) {
               $user->setOption('language',$this->lang);
            }
            if ('' != $this->nickname) {
               $user->setOption('nickname',$this->nickname);
            }
            if ('' != $this->realname) {
               $user->setRealName($this->realname);
            }
            if ('' != $this->email) {
               $user->setEmail($this->email);
            }
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
            global $wgLDAPUseLocal, $wgLDAPMailPassword;
            if ($wgLDAPUseLocal || $wgLDAPMailPassword) {
               return false;
            } else {
               return true;
            }
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
            //We are creating an LDAP user, it is very important that we do
            //NOT set a local password because it could compromise the
            //security of our domain.
            if ('local' == $_SESSION['wsDomain']) {
               return;
            }
            $user->setPassword( '' );
            if ('' != $this->lang) {
               $user->setOption('language',$this->lang);
            }
            if ('' != $this->nickname) {
               $user->setOption('nickname',$this->nickname);
            }
            if ('' != $this->realname) {
               $user->setRealName($this->realname);
            }
            if ('' != $this->email) {
               $user->setEmail($this->email);
            }
         }

         function getSearchString($username) {
            global $wgLDAPSearchStrings, $wgLDAPBaseDNs;
            $userdn = $wgLDAPBaseDNs[$_SESSION['wsDomain']];
            $this->SearchType = "proxy";
            if (!isset($userdn)) {
               $tmpuserdn = $wgLDAPSearchStrings[$_SESSION['wsDomain']];
               $userdn = str_replace("USER-NAME",$username,$tmpuserdn);
               $this->SearchType = "nonproxy";
            }
            return $userdn;
         }

         function getUserDN($ldapconn, $username) {
            global $wgLDAPProxyAgent, $wgLDAPProxyAgentPassword;
            global $wgLDAPSearchAttributes;
            global $wgLDAPRequireAuthAttribute, $wgLDAPAuthAttribute;
            // initialize with the default return value
            $userdn = '';

            if (isset($wgLDAPProxyAgent[$_SESSION['wsDomain']]) && isset($wgLDAPProxyAgentPassword[$_SESSION['wsDomain']])) {
               $bind = ldap_bind(
                  $ldapconn,
                  $wgLDAPProxyAgent[$_SESSION['wsDomain']],
                  $wgLDAPProxyAgentPassword[$_SESSION['wsDomain']]
               );
            }
            else {
               // fallback to anonymous bind
               $bind = @ldap_bind( $ldapconn );
            };

            if (!$bind) {
               return $userdn;
            }

            //we need to do a subbase search for the entry
            if ($wgLDAPRequireAuthAttribute) {
               $auth_filter = "(" . $wgLDAPAuthAttribute[$_SESSION['wsDomain']] . ")";
               $srch_filter = "(" . $wgLDAPSearchAttributes[$_SESSION['wsDomain']] . "=$username)";
               $filter = "(&" . $srch_filter . $auth_filter . ")";
            }
            else {
               $filter = "(" . $wgLDAPSearchAttributes[$_SESSION['wsDomain']] . "=$username)";
            }

            $attributes = array("dn");
            $search = $this->getSearchString($username);

            $entry = ldap_search($ldapconn, $search, $filter, $attributes);
            if (!$entry) {
               return $userdn;
            }

            $info = @ldap_get_entries($ldapconn, $entry);
            if ($info['count'] > 0 && isset($info[0]['dn'])) {
               $userdn = $info[0]["dn"];
            }
            return $userdn;
         }

         function isMemberOfLdapGroup($ldapconn, $userDN, $groupDN) {
            global $wgLDAPProxyAgent, $wgLDAPProxyAgentPassword;
            global $wgLDAPSearchAttributes;
            //If we are using a proxyagent, we should search the groups using a proxyagent.
            //If not, we'll search as the user we already binded as.
            if (isset($wgLDAPProxyAgent[$_SESSION['wsDomain']]) && isset($wgLDAPProxyAgentPassword[$_SESSION['wsDomain']])) {
               $bind = @ldap_bind(
                  $ldapconn,
                  $wgLDAPProxyAgent[$_SESSION['wsDomain']],
                  $wgLDAPProxyAgentPassword[$_SESSION['wsDomain']]
               );
               if (!$bind) {
                  return '';
               }
            }
            //we need to do a subbase search for the entry
            $filter = '(member='.$userDN.')';
            $info = ldap_get_entries($ldapconn,@ldap_search($ldapconn, $groupDN, $filter));
            return ($info['count']>=1);
         }
      }

   ?>
