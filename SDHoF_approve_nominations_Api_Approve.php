<?php

class ApiApprove extends ApiBase {

    public function execute() {
        global $wgScript, $wgUser;

	$approvedNS = 'User_talk';

        $pageName = $this->getMain()->getVal('title');
	$pageName = str_replace( ' ', '_', $pageName );

	if (!$wgUser->isAllowed('approve-power')) {
    	   header("Location: " . $wgScript . '/' . $pageName );
    	   die();
	}

	$mailHeaders = "From: $sdhofSenderEmailAddress";

        $approve_action = $this->getMain()->getVal('approveaction');

	if ($approve_action == 'approve') {
	   $parts = explode( ':', $pageName );
	   $newPageName = count($parts) == 2 ? $approvedNS . ':' . $parts[1] : $approvedNS . ':' . $parts[0];

	   $oldTitle = Title::newFromText($pageName);
	   $newTitle = Title::newFromText($newPageName);

	   $error = $oldTitle->moveTo($newTitle, false, 'Approved', true);

	   if ($error !== true) {
	      var_dump($error);
	      die();
	   }

	   // Send all the necessary mails

	   mail(
	      $this->getMain()->getVal('email'),
	      wfMessage('approve-mail-subject-submitter', $pageName) ,
	      wfMessage('approve-mail-message-submitter', $pageName, $newTitle->getFullURL()),
	      $mailHeaders
	   );

	   mail(
	      $sdhofPressReleaseEmailAddress,
	      wfMessage('approve-mail-subject-press', $pageName) ,
	      wfMessage('approve-mail-message-press', $pageName, $newTitle->getFullURL()),
	      $mailHeaders
	   );

	   header("Location: " . $wgScript . '/' . $newPageName );
    	   die();
	} else {

	   mail(
	      $this->getMain()->getVal('email'),
	      wfMessage('reject-mail-subject-submitter', $pageName) ,
	      wfMessage('reject-mail-message-submitter', $pageName),
	      $mailHeaders
	   );

	   header("Location: " . $wgScript . '/' . $pageName );
    	   die();
	}
    }


    public function getDescription() {
         return 'Api to approve or reject a proposal.';
     }
 
    public function getAllowedParams() {
        return array_merge( (array)parent::getAllowedParams(), array(
            'title' => array (
                ApiBase::PARAM_TYPE => 'string',
                ApiBase::PARAM_REQUIRED => true
            ),
            'email' => array (
                ApiBase::PARAM_TYPE => 'string',
                ApiBase::PARAM_REQUIRED => true
            ),
            'approveaction' => array (
                ApiBase::PARAM_TYPE => 'string',
                ApiBase::PARAM_REQUIRED => true
            ),
        ));
    }
 
    public function getParamDescription() {
        return array_merge( parent::getParamDescription(), array(
            'title' => 'The title of the proposal page',
            'email' => 'The email address of the proposal creator',
	    'approveaction' => 'The desired action',
        ) );
    }
 
     public function getExamples() {
         return array(
         );
    }
}