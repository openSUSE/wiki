<?php
/** \file
 * \brief Contains code for the UserMerge Class (extends SpecialPage).
 */

/**
 * Special page class for the User Merge and Delete extension
 * allows sysops to merge references from one user to another user.
 * It also supports deleting users following merge.
 *
 * @ingroup Extensions
 * @author Tim Laqua <t.laqua@gmail.com>
 * @author Thomas Gries <mail@tgries.de>
 * @author Matthew April <Matthew.April@tbs-sct.gc.ca>
 *
 */

class UserMerge extends SpecialPage {
	function __construct() {
		parent::__construct( 'UserMerge', 'usermerge' );
	}

	function execute( $par ) {
		$this->setHeaders();

		$user = $this->getUser();

		if ( !$user->isAllowed( 'usermerge' ) ) {
			throw new PermissionsError( 'usermerge' );
		}

		$out = $this->getOutput();
		$request = $this->getRequest();

		// init variables
		$olduser_text = '';
		$newuser_text = '';
		$deleteUserCheck = false;
		$validOldUser = false;
		$validNewUser = false;

		if ( strlen( $request->getText( 'olduser' ) . $request->getText( 'newuser' ) ) > 0 || $request->getText( 'deleteuser' ) ) {
			// POST data found
			$olduser = Title::newFromText( $request->getText( 'olduser' ) );
			$olduser_text = is_object( $olduser ) ? $olduser->getText() : '';

			$newuser = Title::newFromText( $request->getText( 'newuser' ) );
			$newuser_text = is_object( $newuser ) ? $newuser->getText() : '';

			if ( $request->getText( 'deleteuser' ) ) {
				$deleteUserCheck = true;
			}

			if ( strlen( $olduser_text ) > 0 ) {
				$objOldUser = User::newFromName( $olduser_text );
				$olduserID = $objOldUser->idForName();

				if ( !is_object( $objOldUser ) || $objOldUser->getID() == 0 ) {
					$validOldUser = false;
					$out->wrapWikiMsg( "<div class='error'>\n$1</div>", 'usermerge-badolduser' );
				} elseif ( $olduserID == $user->getID() ) {
					$validOldUser = false;
					$out->wrapWikiMsg( "<div class='error'>\n$1</div>", 'usermerge-noselfdelete' );
				} else {
					global $wgUserMergeProtectedGroups;

					$boolProtected = false;
					foreach ( $objOldUser->getGroups() as $userGroup ) {
						if ( in_array( $userGroup, $wgUserMergeProtectedGroups ) ) {
							$boolProtected = true;
						}
					}

					if ( $boolProtected ) {
						$validOldUser = false;
						$out->wrapWikiMsg( "<div class='error'>\n$1</div>", 'usermerge-protectedgroup' );
					} else {
						$validOldUser = true;

						if ( strlen( $newuser_text ) > 0 ) {
							$objNewUser = User::newFromName( $newuser_text );
							$newuserID = $objNewUser->idForName();

							if ( !is_object( $objNewUser ) || $newuserID === 0 ) {
								if ( $newuser_text === 'Anonymous' ) {
									// Merge to anonymous
									$validNewUser = true;
									$newuserID = 0;
								} else {
									// invalid newuser entered
									$validNewUser = false;
									$out->wrapWikiMsg( "<div class='error'>$1</div>", 'usermerge-badnewuser' );
								}
							} elseif( $olduserID == $newuserID ) {
								$validNewUser = false;
								$out->wrapWikiMsg( "<div class='error'>$1</div>", array( 'usermerge-same-old-and-new-user' ) );
							} else {
								// newuser looks good
								$validNewUser = true;
							}
						} else {
							// empty newuser string
							$validNewUser = false;
							$newuser_text = "Anonymous";
							$out->wrapWikiMsg( "<div class='error'>$1</div>", array( 'usermerge-nonewuser', $newuser_text ) );
						}
					}
				}
			} else {
				$validOldUser = false;
				$out->addHTML(
					Html::rawElement( 'span',
						array( 'class' => 'warning' ),
						$this->msg( 'usermerge-noolduser' )->escaped()
					) .
					Html::element( 'br' ) .
					"\n"
				);
			}
		}

		$out->addHtml(

			Html::rawElement( 'form',

				array(
					'method' => 'post',
					'action' => $this->getTitle()->getLocalUrl(),
					'id' => 'usermergeform'
				),

				Html::rawElement( 'fieldset',

					array(),
					Html::element( 'legend',
						array(),
						$this->msg( 'usermerge-fieldset' )->text()
					) .

					Html::rawElement( 'table',

						array( 'id' => 'mw-usermerge-table' ),
						Html::rawElement( 'tr',

							array(),
							Html::rawElement( 'td',
								array( 'class' => 'mw-label' ),
								Html::element( 'label',
									array( 'for' => 'olduser' ),
									$this->msg( 'usermerge-olduser' )->text()
								)
							) .

							Html::rawElement( 'td',
								array( 'class' => 'mw-input' ),
								Html::input(
									'olduser',
									$olduser_text,
									'text',
									array(
										'tabindex' => '1',
										'size' => '20',
										'onFocus' => "document.getElementById( 'olduser' ).select;"
									)
								),
								' '
							)
						) .

						Html::rawElement( 'tr',

							array(),
							Html::rawElement( 'td',
								array( 'class' => 'mw-label' ),
								Html::element( 'label',
									array( 'for' => 'newuser' ),
									$this->msg( 'usermerge-newuser' )->text()
								)
							) .

							Html::rawElement( 'td',
								array( 'class' => 'mw-input' ),
								Html::input(
									'newuser',
									$newuser_text,
									'text',
									array(
										'tabindex' => '2',
										'size' => '20',
										'onFocus' => "document.getElementById( 'newuser' ).select;"
									)
								)
						)

						) .

						Html::rawElement( 'tr',

							array(),
							Html::rawElement( 'td',
								array(),
								"&#160;"
							) .

							Html::rawElement( 'td',
								array( 'class' => 'mw-input' ),
								Xml::checkLabel(
									$this->msg( 'usermerge-deleteolduser' )->text(),
									'deleteuser',
									'deleteuser',
									$deleteUserCheck,
									array( 'tabindex' => '3' )
								)
							)

						) .

						Html::rawElement( 'tr',

							array(),
							Html::rawElement( 'td',
								array(),
								"&#160;"
							) .

							Html::rawElement( 'td',
								array( 'class' => 'mw-submit' ),
								Xml::submitButton(
									$this->msg( 'usermerge-submit' )->text(),
									array( 'tabindex' => '4' )
								)
							)

						)

					)

				) .

				Html::Hidden( 'token', $user->getEditToken() )

			) . "\n"

		);

		if ( $validNewUser && $validOldUser ) {
			// go time, baby
			if ( !$user->matchEditToken( $request->getVal( 'token' ) ) ) {
				// bad editToken
				$out->addHTML(
					Html::rawElement( 'span',
						array( 'class' => 'warning' ),
						$this->msg( 'usermerge-badtoken' )->escaped()
					) .
					Html::element( 'br' ) . "\n"
				);
			} else {
				// good editToken
				$this->mergeEditcount( $newuserID, $olduserID );
				$this->mergeUser( $objNewUser, $newuser_text, $newuserID, $objOldUser, $olduser_text, $olduserID );
				if ( $request->getText( 'deleteuser' ) ) {
					$this->movePages( $newuser_text, $olduser_text );
					$this->deleteUser( $objOldUser, $olduserID, $olduser_text );
				}
			}
		}
	}

	/**
	 * Function to delete users following a successful mergeUser call
	 *
	 * Removes user entries from the user table and the user_groups table
	 *
	 * @param $objOldUser User
	 * @param $olduserID int ID of user to delete
	 * @param $olduser_text string Username of user to delete
	 *
	 * @return bool Always returns true - throws exceptions on failure.
	 */
	private function deleteUser( $objOldUser, $olduserID, $olduser_text ) {
		$dbw = wfGetDB( DB_MASTER );
		$dbw->delete(
			'user_groups',
			array( 'ug_user' => $olduserID )
		);
		$dbw->delete(
			'user',
			array( 'user_id' => $olduserID )
		);
		$this->getOutput()->addHTML(
			$this->msg( 'usermerge-userdeleted', $olduser_text, $olduserID )->escaped() .
			Html::element( 'br' ) . "\n"
		);

		$log = new LogPage( 'usermerge' );
		$log->addEntry( 'deleteuser', $this->getUser()->getUserPage(), '', array( $olduser_text, $olduserID ) );

		wfRunHooks( 'DeleteAccount', array( &$objOldUser ) );

		$users = $dbw->selectField(
			'user',
			'COUNT(*)',
			array()
		);
		$dbw->update( 'site_stats',
			array( 'ss_users' => $users ),
			array( 'ss_row_id' => 1 )
		);
		return true;
	}

	/**
	 * Deduplicate watchlist entries
	 * which old (merge-from) and new (merge-to) users are watching
	 *
	 * @param $oldUser User
	 * @param $newUser User
	 *
	 * @return bool
	 */
	private function deduplicateWatchlistEntries( $oldUser, $newUser ) {

		$dbw = wfGetDB( DB_MASTER );
		$dbw->begin( __METHOD__ );

		$res = $dbw->select(
			array(
				'w1' => 'watchlist',
				'w2' => 'watchlist'
			),
			array(
				'w2.wl_namespace',
				'w2.wl_title'
			),
			array(
				'w1.wl_user' => $newUser->getID(),
				'w2.wl_user' => $oldUser->getID()
			),
			__METHOD__,
			array( 'FOR UPDATE' ),
			array(
				'w2' => array(
					'INNER JOIN',
					array(
						'w1.wl_namespace = w2.wl_namespace',
						'w1.wl_title = w2.wl_title'
					),
				)
			)
		);

		# Construct an array to delete all watched pages of the old user
		# which the new user already watches
		$conds = array();

		foreach ( $res as $result ) {
			$conds[] = $dbw->makeList(
				array(
					'wl_user' => $oldUser->getID(),
					'wl_namespace' => $result->wl_namespace,
					'wl_title' => $result->wl_title
				),
				LIST_AND
			);
		}

		if ( empty( $conds ) ) {
			$dbw->commit( __METHOD__ );
			return true;
		}

		# Perform a multi-row delete

		# requires
		# MediaWiki database function with fixed https://bugzilla.wikimedia.org/50078
		# i.e. MediaWiki core after 505dbb331e16a03d87cb4511ee86df12ea295c40 (20130625)
		$dbw->delete(
			'watchlist',
			$dbw->makeList( $conds, LIST_OR ),
			__METHOD__
		);

		$dbw->commit( __METHOD__ );

		return true;
	}


	/**
	 * Function to merge database references from one user to another user
	 *
	 * Merges database references from one user ID or username to another user ID or username
	 * to preserve referential integrity.
	 *
	 * @param $objNewUser User
	 * @param $newuser_text string Username to merge references TO
	 * @param $newuserID int ID of user to merge references TO
	 * @param $objOldUser User
	 * @param $olduser_text string Username of user to remove references FROM
	 * @param $olduserID int ID of user to remove references FROM
	 *
	 * @return bool Always returns true - throws exceptions on failure.
	 */
	private function mergeUser( $objNewUser, $newuser_text, $newuserID, $objOldUser, $olduser_text, $olduserID ) {
		// Fields to update with the format:
		// array( tableName, idField, textField )
		$updateFields = array(
			array( 'archive', 'ar_user', 'ar_user_text' ),
			array( 'revision', 'rev_user', 'rev_user_text' ),
			array( 'filearchive', 'fa_user', 'fa_user_text' ),
			array( 'image', 'img_user', 'img_user_text' ),
			array( 'oldimage', 'oi_user', 'oi_user_text' ),
			array( 'recentchanges', 'rc_user', 'rc_user_text' ),
			array( 'logging', 'log_user' ),
			array( 'ipblocks', 'ipb_user', 'ipb_address' ),
			array( 'ipblocks', 'ipb_by', 'ipb_by_text' ),
			array( 'watchlist', 'wl_user' ),
		);

		$dbw = wfGetDB( DB_MASTER );
		$out = $this->getOutput();

		$this->deduplicateWatchlistEntries( $objOldUser, $objNewUser );

		foreach ( $updateFields as $fieldInfo ) {
			$tableName = array_shift( $fieldInfo );
			$idField = array_shift( $fieldInfo );

			$dbw->update(
				$tableName,
				array( $idField => $newuserID ) + array_fill_keys( $fieldInfo, $newuser_text ),
				array( $idField => $olduserID ),
				__METHOD__
			);

			$out->addHTML(
				$this->msg(
					'usermerge-updating',
					$tableName,
					$olduserID,
					$newuserID
				)->escaped() .
				Html::element( 'br' ) . "\n"
			);

			foreach ( $fieldInfo as $textField ) {
				$out->addHTML(
					$this->msg(
						'usermerge-updating',
						$tableName,
						$olduser_text,
						$newuser_text
					)->escaped() .
					Html::element( 'br' ) . "\n"
				);
			}
		}

		$dbw->delete( 'user_newtalk', array( 'user_id' => $olduserID ) );

		$out->addHTML(
			Html::element( 'hr' ) . "\n" .
			$this->msg( 'usermerge-success', $olduser_text, $olduserID, $newuser_text, $newuserID )->escaped() .
			Html::element( 'br' ) . "\n"
		);

		$log = new LogPage( 'usermerge' );
		$log->addEntry(
			'mergeuser',
			$this->getUser()->getUserPage(),
			'',
			array( $olduser_text, $olduserID, $newuser_text, $newuserID )
		);

		wfRunHooks( 'MergeAccountFromTo', array( &$objOldUser, &$objNewUser ) );

		return true;
	}


	/**
	 * Function to add edit count
	 *
	 * Adds edit count of both users
	 *
	 * @param $newuserID int ID of user to merge references TO
	 * @param $olduserID int ID of user to remove references FROM
	 *
	 * @return bool Always returns true - throws exceptions on failure.
	 *
	 * @author Matthew April <Matthew.April@tbs-sct.gc.ca>
	 */
	private function mergeEditcount( $newuserID, $olduserID ) {
		$dbw = wfGetDB( DB_MASTER );

		$olduserEdits = $dbw->selectField(
			'user',
			'user_editcount',
			array( 'user_id' => $olduserID ),
			__METHOD__
		);
		if ( $olduserEdits === false ) {
			$olduserEdits = 0;
		}

		$newuserEdits = $dbw->selectField(
			'user',
			'user_editcount',
			array( 'user_id' => $newuserID ),
			__METHOD__
		);
		if ( $newuserEdits === false ) {
			$newuserEdits = 0;
		}

		$totalEdits = $olduserEdits + $newuserEdits;

		# don't run querys if neither user has any edits
		if ( $totalEdits > 0 ) {
			# update new user with total edits
			$dbw->update( 'user',
				array( 'user_editcount' => $totalEdits ),
				array( 'user_id' => $newuserID ),
				__METHOD__
			);

			# clear old users edits
			$dbw->update( 'user',
				array( 'user_editcount' => 0 ),
				array( 'user_id' => $olduserID ),
				__METHOD__
			);
		}

		$this->getOutput()->addHTML(
			$this->msg(
				'usermerge-editcount-merge-success',
				$olduserEdits, $olduserID, $newuserEdits, $newuserID, $totalEdits
			)->escaped() .
			Html::element( 'br' ) . "\n"
		);

		return true;
	}

	/**
	 * Function to merge user pages
	 *
	 * Deletes all pages when merging to Anon
	 * Moves user page when the target user page does not exist or is empty
	 * Deletes redirect if nothing links to old page
	 * Deletes the old user page when the target user page exists
	 *
	 * @param $newuser_text string Username to merge pages TO
	 * @param $olduser_text string Username of user to remove pages FROM
	 *
	 * @return bool True on completion
	 *
	 * @author Matthew April <Matthew.April@tbs-sct.gc.ca>
	 */
	private function movePages( $newuser_text, $olduser_text ) {
		global $wgContLang;

		$oldusername = trim( str_replace( '_', ' ', $olduser_text ) );
		$oldusername = Title::makeTitle( NS_USER, $oldusername );
		$newusername = Title::makeTitleSafe( NS_USER, $wgContLang->ucfirst( $newuser_text ) );

		# select all user pages and sub-pages
		$dbr = wfGetDB( DB_SLAVE );
		$pages = $dbr->select( 'page',
			array( 'page_namespace', 'page_title' ),
			array(
				'page_namespace' => array( NS_USER, NS_USER_TALK ),
				$dbr->makeList( array(
					'page_title' => $dbr->buildLike( $oldusername->getDBkey() . '/', $dbr->anyString() ),
					'page_title' => $oldusername->getDBkey()
					),
					LIST_OR
				)
			)
		 );

		$output = '';

		foreach ( $pages as $row ) {

			$oldPage = Title::makeTitleSafe( $row->page_namespace, $row->page_title );
			$newPage = Title::makeTitleSafe( $row->page_namespace,
				preg_replace( '!^[^/]+!', $newusername->getDBkey(), $row->page_title ) );

			if ( $newuser_text === "Anonymous" ) { # delete ALL old pages
				if ( $oldPage->exists() ) {
					$oldPageArticle = new Article( $oldPage, 0 );
					$oldPageArticle->doDeleteArticle( $this->msg( 'usermerge-autopagedelete' )->inContentLanguage()->text() );

					$oldLink = Linker::linkKnown( $oldPage );
					$output .= Html::rawElement( 'li',
						array( 'class' => 'mw-renameuser-pe' ),
						$this->msg( 'usermerge-page-deleted' )->rawParams( $oldLink )->escaped()
					);

				}
			} elseif ( $newPage->exists()
				&& !$oldPage->isValidMoveTarget( $newPage )
				&& $newPage->getLength() > 0 ) { # delete old pages that can't be moved

				$oldPageArticle = new Article( $oldPage, 0 );
				$oldPageArticle->doDeleteArticle( $this->msg( 'usermerge-autopagedelete' )->text() );

				$link = Linker::linkKnown( $oldPage );
				$output .= Html::rawElement( 'li',
					array( 'class' => 'mw-renameuser-pe' ),
					$this->msg( 'usermerge-page-deleted' )->rawParams( $link )->escaped()
				);

			} else { # move content to new page
				# delete target page if it exists and is blank
				if ( $newPage->exists() ) {
					$newPageArticle = new Article( $newPage, 0 );
					$newPageArticle->doDeleteArticle( $this->msg( 'usermerge-autopagedelete' )->inContentLanguage()->text() );
				}

				# move to target location
				$success = $oldPage->moveTo(
					$newPage,
					false,
					$this->msg(
						'usermerge-move-log',
						$oldusername->getText(),
						$newusername->getText() )->inContentLanguage()->text()
				);

				if ( $success === true ) {
					$oldLink = Linker::linkKnown(
						$oldPage,
						null,
						array(),
						array( 'redirect' => 'no' )
					);
					$newLink = Linker::linkKnown( $newPage );
					$output .= Html::rawElement( 'li',
						array( 'class' => 'mw-renameuser-pm' ),
						$this->msg( 'usermerge-page-moved' )->rawParams( $oldLink, $newLink )->escaped()
					);
				} else {
					$oldLink = Linker::linkKnown( $oldPage );
					$newLink = Linker::linkKnown( $newPage );
					$output .= Html::rawElement( 'li',
						array( 'class' => 'mw-renameuser-pu' ),
						$this->msg( 'usermerge-page-unmoved' )->rawParams( $oldLink, $newLink )->escaped()
					);
				}

				# check if any pages link here
				$res = $dbr->selectField( 'pagelinks',
					'pl_title',
					array( 'pl_title' => $olduser_text ),
					__METHOD__
				);
				if ( !$dbr->numRows( $res ) ) {
					# nothing links here, so delete unmoved page/redirect
					$oldPageArticle = new Article( $oldPage, 0 );
					$oldPageArticle->doDeleteArticle( $this->msg( 'usermerge-autopagedelete' )->inContentLanguage()->text() );
				}
			}

		}

		if ( $output ) {
			$this->getOutput()->addHTML(
				Html::rawElement( 'ul',
					array(),
					$output
				)
			);
		}

		return true;
	}

}
