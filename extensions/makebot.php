<?php

/**
 * Special page to allow local bureaucrats to grant/revoke the bot flag
 * for a particular user
 *
 * @package MediaWiki
 * @subpackage Extensions
 * @author Rob Church <robchur@gmail.com>
 * @copyright © 2006 Rob Church
 * @licence GNU General Public Licence 2.0 or later
 */
 
if( defined( 'MEDIAWIKI' ) ) {

	require_once( 'SpecialPage.php' );
	require_once( 'LogPage.php' );
	require_once( 'SpecialLog.php' );
	
	define( 'MW_MAKEBOT_GRANT', 1 );
	define( 'MW_MAKEBOT_REVOKE', 2 );
	
	$wgExtensionFunctions[] = 'efMakeBot';
	$wgAvailableRights[] = 'makebot';
	$wgExtensionCredits['specialpage'][] = array( 'name' => 'MakeBot', 'url' => 'http://meta.wikimedia.org/wiki/MakeBot', 'author' => 'Rob Church' );
	
	/**
	 * Determines who can use the extension; as a default, bureaucrats are permitted
	 */
	$wgGroupPermissions['bureaucrat']['makebot'] = true;
	
	/**
	 * Toggles whether or not a bot flag can be given to a user who is also a sysop or bureaucrat
	 */
	$wgMakeBotPrivileged = false;
	
	/**
	 * Populate the message cache and register the special page
	 */
	function efMakeBot() {
		global $wgHooks, $wgMessageCache;
		# Hooks for auditing
		$wgHooks['LogPageValidTypes'][] = 'efMakeBotAddLogType';
		$wgHooks['LogPageLogName'][] = 'efMakeBotAddLogName';
		$wgHooks['LogPageLogHeader'][] = 'efMakeBotAddLogHeader';
		$wgHooks['LogPageActionText'][] = 'efMakeBotAddActionText';
		# Basic messages
		$wgMessageCache->addMessage( 'makebot', 'Grant or revoke bot status' );
		$wgMessageCache->addMessage( 'makebot-header', "'''A local bureaucrat can use this page to grant or revoke [[Help:Bot|bot status]] to another user account.'''<br />Bot status hides a user's edits from [[Special:Recentchanges|recent changes]] and similar lists, and is useful for flagging users who make automated edits. This should be done in accordance with applicable policies." );
		$wgMessageCache->addMessage( 'makebot-username', 'Username:' );
		$wgMessageCache->addMessage( 'makebot-search', 'Go' );
		$wgMessageCache->addMessage( 'makebot-isbot', '[[User:$1|$1]] has bot status.' );
		$wgMessageCache->addMessage( 'makebot-notbot', '[[User:$1|$1]] does not have bot status.' );
		$wgMessageCache->addMessage( 'makebot-privileged', '[[User:$1|$1]] has [[Special:Listadmins|administrator or bureaucrat privileges]], and cannot be granted bot status.' );
		$wgMessageCache->addMessage( 'makebot-change', 'Change status:' );
		$wgMessageCache->addMessage( 'makebot-grant', 'Grant' );
		$wgMessageCache->addMessage( 'makebot-revoke', 'Revoke' );
		$wgMessageCache->addMessage( 'makebot-comment', 'Comment:' );
		$wgMessageCache->addMessage( 'makebot-granted', '[[User:$1|$1]] now has bot status.' );
		$wgMessageCache->addMessage( 'makebot-revoked', '[[User:$1|$1]] no longer has bot status.' );
		# Audit trail messages
		$wgMessageCache->addMessage( 'makebot-logpage', 'Bot status log' );
		$wgMessageCache->addMessage( 'makebot-logpagetext', 'This is a log of changes to users\' [[Help:Bot|bot]] status.' );
		$wgMessageCache->addMessage( 'makebot-logentrygrant', 'granted bot status to [[$1]]' );
		$wgMessageCache->addMessage( 'makebot-logentryrevoke', 'removed bot status from [[$1]]' );
		# Register page		
		SpecialPage::addPage( new MakeBot() );
	}
	
	/**
	 * Audit trail functions
	 */
	
	function efMakeBotAddLogType( &$types ) {
		if ( !in_array( 'makebot', $types ) )
			$types[] = 'makebot';
		return true;
	}
	
	function efMakeBotAddLogName( &$names ) {
		$names['makebot'] = 'makebot-logpage';
		return true;
	}
	
	function efMakeBotAddLogHeader( &$headers ) {
		$headers['makebot'] = 'makebot-logpagetext';
		return true;
	}
	
	function efMakeBotAddActionText( &$actions ) {
		$actions['makebot/grant'] = 'makebot-logentrygrant';
		$actions['makebot/revoke'] = 'makebot-logentryrevoke';
		return true;
	}
	
	class MakeBot extends SpecialPage {
	
		var $target = '';
	
		/**
		 * Constructor
		 */
		function MakeBot() {
			SpecialPage::SpecialPage( 'Makebot', 'makebot' );
		}
		
		/**
		 * Main execution function
		 * @param $par Parameters passed to the page
		 */
		function execute( $par ) {
			global $wgRequest, $wgOut, $wgMakeBotPrivileged, $wgUser;
			
			if( !$wgUser->isAllowed( 'makebot' ) ) {
				$wgOut->permissionRequired( 'makebot' );
				return;
			}
			
			$this->setHeaders();

			$this->target = $par
							? $par
							: $wgRequest->getText( 'username', '' );

			$wgOut->addWikiText( wfMsg( 'makebot-header' ) );
			$wgOut->addHtml( $this->makeSearchForm() );
			
			if( $this->target != '' ) {
				$wgOut->addHtml( wfElement( 'p', NULL, NULL ) );
				$user = User::newFromName( $this->target );
				if( is_object( $user ) && !is_null( $user ) ) {
					$user->loadFromDatabase();
					# Valid username, check existence
					if( $user->getID() ) {
						# Exists; check current privileges
						if( $this->canBecomeBot( $user ) ) {	
							if( $wgRequest->getCheck( 'dosearch' ) || !$wgRequest->wasPosted() || !$wgUser->matchEditToken( $wgRequest->getVal( 'token' ), 'makebot' ) ) {
								# Exists, check botness
								if( in_array( 'bot', $user->mGroups ) ) {
									# Has a bot flag
									$wgOut->addWikiText( wfMsg( 'makebot-isbot', $user->getName() ) );
									$wgOut->addHtml( $this->makeGrantForm( MW_MAKEBOT_REVOKE ) );
								} else {
									# Not a bot; show the grant form
									# Not a bot; check other privs
									$wgOut->addHtml( $this->makeGrantForm( MW_MAKEBOT_GRANT ) );
								}
							} elseif( $wgRequest->getCheck( 'grant' ) ) {
								# Grant the flag
								$user->addGroup( 'bot' );
								$this->addLogItem( 'grant', $user, trim( $wgRequest->getText( 'comment' ) ) );
								$wgOut->addWikiText( wfMsg( 'makebot-granted', $user->getName() ) );
							} elseif( $wgRequest->getCheck( 'revoke' ) ) {
								# Revoke the flag
								$user->removeGroup( 'bot' );
								$this->addLogItem( 'revoke', $user, trim( $wgRequest->getText( 'comment' ) ) );
								$wgOut->addWikiText( wfMsg( 'makebot-revoked', $user->getName() ) );
							}
							# Show log entries
							$this->showLogEntries( $user );
						} else {
							# User account is privileged and can't be given a bot flag
							$wgOut->addWikiText( wfMsg( 'makebot-privileged', $user->getName() ) );
						}
					} else {
						# Doesn't exist
						$wgOut->addWikiText( wfMsg( 'nosuchusershort', htmlspecialchars( $this->target ) ) );
					}
				} else {
					# Invalid username
					$wgOut->addWikiText( wfMsg( 'noname' ) );
				}
			}
			
		}
		
		/**
		 * Produce a form to allow for entering a username
		 * @return string
		 */
		function makeSearchForm() {
			$thisTitle = Title::makeTitle( NS_SPECIAL, $this->getName() );
			$form  = wfOpenElement( 'form', array( 'method' => 'post', 'action' => $thisTitle->getLocalUrl() ) );
			$form .= wfElement( 'label', array( 'for' => 'username' ), wfMsg( 'makebot-username' ) ) . ' ';
			$form .= wfElement( 'input', array( 'type' => 'text', 'name' => 'username', 'id' => 'username', 'value' => $this->target ) ) . ' ';
			$form .= wfElement( 'input', array( 'type' => 'submit', 'name' => 'dosearch', 'value' => wfMsg( 'makebot-search' ) ) );
			$form .= wfCloseElement( 'form' );
			return $form;
		}
		
		/**
		 * Produce a form to allow granting or revocation of the flag
		 * @param $type Either MW_MAKEBOT_GRANT or MW_MAKEBOT_REVOKE
		 *				where the trailing name refers to what's enabled
		 * @return string
		 */
		function makeGrantForm( $type ) {
			global $wgUser;
			$thisTitle = Title::makeTitle( NS_SPECIAL, $this->getName() );
			if( $type == MW_MAKEBOT_GRANT ) {
				$grant = true;
				$revoke = false;
			} else {
				$grant = false;
				$revoke = true;
			}
		
			# Start the table
			$form  = wfOpenElement( 'form', array( 'method' => 'post', 'action' => $thisTitle->getLocalUrl() ) );
			$form .= wfOpenElement( 'table' ) . wfOpenElement( 'tr' );
			# Grant/revoke buttons
			$form .= wfElement( 'td', array( 'align' => 'right' ), wfMsg( 'makebot-change' ) );
			$form .= wfOpenElement( 'td' );
			foreach( array( 'grant', 'revoke' ) as $button ) {
				$attribs = array( 'type' => 'submit', 'name' => $button, 'value' => wfMsg( 'makebot-' . $button ) );
				if( !$$button )
					$attribs['disabled'] = 'disabled';
				$form .= wfElement( 'input', $attribs );
			}
			$form .= wfCloseElement( 'td' ) . wfCloseElement( 'tr' );
			# Comment field
			$form .= wfOpenElement( 'td', array( 'align' => 'right' ) );
			$form .= wfElement( 'label', array( 'for' => 'comment' ), wfMsg( 'makebot-comment' ) );
			$form .= wfOpenElement( 'td' );
			$form .= wfElement( 'input', array( 'type' => 'text', 'name' => 'comment', 'id' => 'comment', 'size' => 45 ) );
			$form .= wfCloseElement( 'td' ) . wfCloseElement( 'tr' );
			# End table
			$form .= wfCloseElement( 'table' );
			# Username
			$form .= wfElement( 'input', array( 'type' => 'hidden', 'name' => 'username', 'value' => $this->target ) );
			# Edit token
			$form .= wfElement( 'input', array( 'type' => 'hidden', 'name' => 'token', 'value' => $wgUser->editToken( 'makebot' ) ) );
			$form .= wfCloseElement( 'form' );
			return $form;
		}
	
		/**
		 * Add logging entries for the specified action
		 * @param $type Either grant or revoke
		 * @param $target User receiving the action
		 * @param $comment Comment for the log item
		 */
		function addLogItem( $type, &$target, $comment = '' ) {
			$log = new LogPage( 'makebot' );
			$targetPage = $target->getUserPage();
			$log->addEntry( $type, $targetPage, $comment );
		}
		
		/**
		 * Show the bot status log entries for the specified user
		 * @param $user User to show the log for
		 */
		function showLogEntries( &$user ) {
			global $wgOut;
			$title = $user->getUserPage();
			$wgOut->addHtml( wfElement( 'h2', NULL, htmlspecialchars( LogPage::logName( 'makebot' ) ) ) );
			$logViewer = new LogViewer( new LogReader( new FauxRequest( array( 'page' => $title->getPrefixedText(), 'type' => 'makebot' ) ) ) );
			$logViewer->showList( $wgOut );
		}

		/**
		 * Can the specified user be given a bot flag
		 * Check existing privileges and configuration
		 * @param $user User to check
		 * @return bool True if permitted
		 */
		function canBecomeBot( &$user ) {
			global $wgMakeBotPrivileged;
			$user->loadFromDatabase();
			return $wgMakeBotPrivileged ||
					( !in_array( 'sysop', $user->mGroups ) &&
					  !in_array( 'bureaucrat', $user->mGroups ) );
		}
	
	}
	
} else {

	echo( "This file is an extension to the MediaWiki software and cannot be executed standalone.\n" );
	die( -1 );

}