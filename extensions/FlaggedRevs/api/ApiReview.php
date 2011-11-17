<?php

/*
 * Created on Dec 20, 2008
 *
 * API module for MediaWiki's FlaggedRevs extension
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 * http://www.gnu.org/copyleft/gpl.html
 */

/**
 * API module to review revisions
 *
 * @ingroup FlaggedRevs
 */
class ApiReview extends ApiBase {

	/**
	 * This function does essentially the same as RevisionReview::AjaxReview,
	 * except that it generates the template and image parameters itself.
	 */
	public function execute() {
		global $wgUser, $wgOut, $wgParser;
		$params = $this->extractRequestParams();
		// Check basic permissions
		if ( !$wgUser->isAllowed( 'review' ) ) {
			// FIXME: better msg?
			$this->dieUsageMsg( array( 'badaccess-group0' ) );
		} elseif ( $wgUser->isBlocked( false ) ) {
			$this->dieUsageMsg( array( 'blockedtext' ) );
		}
		// Construct submit form
		$form = new RevisionReviewForm( $wgUser );
		$revid = (int)$params['revid'];
		$rev = Revision::newFromId( $revid );
		if ( !$rev ) {
			$this->dieUsage( "Cannot find a revision with the specified ID.", 'notarget' );
		}
		$title = $rev->getTitle();
		$form->setPage( $title );
		$form->setOldId( $revid );
		$form->setApprove( empty( $params['unapprove'] ) );
		$form->setUnapprove( !empty( $params['unapprove'] ) );
		if ( isset( $params['comment'] ) )
			$form->setComment( $params['comment'] );
		if ( isset( $params['notes'] ) )
			$form->setNotes( $params['notes'] );
		// The flagging parameters have the form 'flag_$name'.
		// Extract them and put the values into $form->dims
		foreach ( FlaggedRevs::getTags() as $tag ) {
			$form->setDim( $tag, (int)$params['flag_' . $tag] );
		}
		if ( $form->getAction() === 'approve' ) {
			$parserOutput = null;
			// Now get the template and image parameters needed
			// If it is the current revision, try the parser cache first
			$article = new FlaggedArticle( $title, $revid );
			if ( $rev->isCurrent() ) {
				$parserCache = ParserCache::singleton();
				$parserOutput = $parserCache->get( $article, $wgOut->parserOptions() );
			}
			if ( !$parserOutput || !isset( $parserOutput->fr_fileSHA1Keys ) ) {
				// Miss, we have to reparse the page
				$text = $article->getContent();
				$options = FlaggedRevs::makeParserOptions();
				$parserOutput = $wgParser->parse(
					$text, $title, $options, true, true, $article->getLatest() );
			}
			// Set version parameters for review submission
			list( $templateParams, $imageParams, $fileVersion ) =
				RevisionReviewForm::getIncludeParams( $article,
					$parserOutput->mTemplateIds, $parserOutput->fr_fileSHA1Keys );
			$form->setTemplateParams( $templateParams );
			$form->setFileParams( $imageParams );
			$form->setFileVersion( $fileVersion );
			$key = RevisionReviewForm::validationKey(
				$templateParams, $imageParams, $fileVersion, $revid );
			$form->setValidatedParams( $key ); # always OK
		}

		$status = $form->ready(); // all params set
		if ( $status === 'review_page_unreviewable' ) {
			$this->dieUsage( "Provided page is not reviewable.", 'notreviewable' );
		// Check basic page permissions
		} elseif ( !$title->quickUserCan( 'review' ) || !$title->quickUserCan( 'edit' ) ) {
			$this->dieUsage( "Insufficient rights to set the specified flags.",
				'permissiondenied' );
		}

		# Try to do the actual review
		$status = $form->submit();
		# Approve/de-approve success
		if ( $status === true ) {
			$this->getResult()->addValue(
				null, $this->getModuleName(), array( 'result' => 'Success' ) );
		# De-approve failure
		} elseif ( $form->getAction() !== 'approve' ) {
			$this->dieUsage( "Cannot find a flagged revision with the specified ID.", 'notarget' );
		# Approval failures
		} else {
			if ( $status === 'review_too_low' ) {
				$this->dieUsage( "Either all or none of the flags have to be set to zero.",
					'mixedapproval' );
			} elseif ( $status === 'review_denied' ) {
				$this->dieUsage( "You don't have the necessary rights to set the specified flags.",
					'permissiondenied' );
			} elseif ( $status === 'review_bad_key' ) {
				$this->dieUsage( "You don't have the necessary rights to set the specified flags.",
					'permissiondenied' );
			} else {
				// FIXME: review_param_missing? better msg?
				$this->dieUsageMsg( array( 'unknownerror', '' ) );
			}
		}
	}

	public function mustBePosted() {
		return true;
	}
	
	public function isWriteMode() {
 		return true;
 	}

	public function getAllowedParams() {
		$pars = array(
			'revid'   	=> null,
			'token'   	=> null,
			'comment' 	=> null,
			'unapprove' => false
		);
		if ( FlaggedRevs::allowComments() ) {
			$pars['notes'] = null;
		}
		if ( !FlaggedRevs::binaryFlagging() ) {
			foreach ( FlaggedRevs::getDimensions() as $flagname => $levels ) {
				$pars['flag_' . $flagname] = array(
					ApiBase::PARAM_DFLT => 1, // default
					ApiBase::PARAM_TYPE => array_keys( $levels ) // array of allowed values
				);
			}
		}
		return $pars;
	}

	public function getParamDescription() {
		$desc = array(
			'revid'  	=> 'The revision ID for which to set the flags',
			'token'   	=> 'An edit token retrieved through prop=info',
			'comment' 	=> 'Comment for the review (optional)',
			'unapprove' => 'If set, revision will be unapproved rather than approved.'
		);
		if ( FlaggedRevs::allowComments() ) {
			$desc['notes'] = "Additional notes for the review. The ''validate'' right is needed to set this parameter.";
		}
		if ( !FlaggedRevs::binaryFlagging() ) {
			foreach ( FlaggedRevs::getTags() as $flagname ) {
				$desc['flag_' . $flagname] = "Set the flag ''{$flagname}'' to the specified value";
			}
		}
		return $desc;
	}

	public function getDescription() {
		return 'Review a revision via FlaggedRevs.';
	}
	
	public function getPossibleErrors() {
		return array_merge( parent::getPossibleErrors(), array(
			array( 'badaccess-group0' ),
			array( 'blockedtext' ),
			array( 'code' => 'notarget', 'info' => 'Provided revision or page can not be found.' ),
			array( 'code' => 'notreviewable', 'info' => 'Provided page is not reviewable.' ),
			array( 'code' => 'mixedapproval', 'info' => 'No flags can be set to zero when accepting a revision.' ),
			array( 'code' => 'permissiondenied', 'info' => 'Insufficient rights to set the specified flags.' ),
		) );
	}

	public function needsToken() {
		return true;
	}

    public function getTokenSalt() {
		return '';
	}

	protected function getExamples() {
		return 'api.php?action=review&revid=12345&token=123AB&flag_accuracy=1&comment=Ok';
	}

	public function getVersion() {
		return __CLASS__ . ': $Id: ApiReview.php 77276 2010-11-25 10:46:38Z aaron $';
	}
}
