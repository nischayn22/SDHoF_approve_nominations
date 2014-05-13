<?php
class ApiApprove extends ApiBase {

    public function execute() {
        global $wgScript, $wgUser;
	$approvedNS = 'User_talk';
        $pageName = $this->getMain()->getVal('title');
	$pageName = str_replace( ' ', '_', $pageName );

	if (!in_array('bureaucrat', $wgUser->getGroups()) || !in_array('sysop', $wgUser->getGroups())) {
    	   header("Location: " . $wgScript . '/' . $pageName );
    	   die();
	}

	$parts = explode( ':', $pageName );
	$newPageName = count($parts) == 2 ? $approvedNS . ':' . $parts[1] : $approvedNS . ':' . $parts[0];

	$oldTitle = Title::newFromText($pageName);
	$newTitle = Title::newFromText($newPageName);

	$error = $oldTitle->moveTo($newTitle, false, 'Approved', true);

	if ($error !== true)
	{
	  var_dump($error);
	  die();
	}

	// Send all the necessary mails
	
	// The message
	$message = "Hi, \r\n
Your project $pageName got approved.
You can see the page at " . $approvedNS . ":" . $parts[1] . "\r\n
\r\n
Regards,\r\n
sender";

	// Send
	mail('projectowner@example.com', 'Your project ' . $pageName . ' got approved', $message);




	// The second email
	$message = "Hi, \r\n
The project $pageName got approved.
You can see the page at " . $approvedNS . ":" . $parts[1] . "\r\n
\r\n
Regards,\r\n
sender";

	// Send
	mail('media@example.com', 'The project ' . $pageName . ' got approved', $message);


    	header("Location: " . $wgScript . '/' . $newPageName );
    	die();
    }


    // Description
    public function getDescription() {
         return 'Api to approve or reject a proposal.';
     }
 
    public function getAllowedParams() {
        return array_merge( (array)parent::getAllowedParams(), array(
            'title' => array (
                ApiBase::PARAM_TYPE => 'string',
                ApiBase::PARAM_REQUIRED => true
            ),
        ));
    }
 
     // Describe the parameter
    public function getParamDescription() {
        return array_merge( parent::getParamDescription(), array(
            'title' => 'The title of the proposal page'
        ) );
    }
 
     // Get examples
     public function getExamples() {
         return array(
             'api.php?action=apisampleoutput&face=O_o&format=xml'
             => 'Get a sideways look (and the usual predictions)'
         );
    }
}