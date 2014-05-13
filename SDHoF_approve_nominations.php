<?php
/**
 * SDHoF approve nominations
 *
 * To activate this extension, add the following into your LocalSettings.php file:
 * require_once("$IP/extensions/SDHoF_approve_nominations/SDHoF_approve_nominations.php");
 *
 * @ingroup Extensions
 * @author Nischay Nahata for WikiWorks.com
 * @version 1.0
 */
 
/**
 * Protect against register_globals vulnerabilities.
 * This line must be present before any global variable is referenced.
 */
if( !defined( 'MEDIAWIKI' ) ) {
        echo( "This is an extension to the MediaWiki package and cannot be run standalone.\n" );
        die( -1 );
}

$wgExtensionCredits['parser extensions'][] = array(
        'path'           => __FILE__,
        'name'           => 'SDHoF Approve Nominations',
        'version'        => '0.1',
        'author'         => 'Nischay Nahata', 
);

$wgExtensionMessagesFiles['SDHoF_approve_nominations'] = __DIR__ . '/SDHoF_approve_nominations.i18n.php';
$wgAutoloadClasses['ApiApprove'] = __DIR__ . '/SDHoF_approve_nominations_Api_Approve.php';
$wgAPIModules['apiapprove'] = 'ApiApprove';

function NominateAndNotify($parser, $action, $emailAddress) {
    global $wgTitle, $wgScriptPath, $wgUser;

    if ($wgTitle->getNamespace() !== NS_USER) {
       return '';
    }

    if (!in_array('bureaucrat', $wgUser->getGroups()) || !in_array('sysop', $wgUser->getGroups())) {
       return '';
    }

    if ($action != 'approve' && $action != 'reject') {
       return 'The first parameter of #NominateAndNotify can only be approve or reject, you gave ' . $action;
    }

    $htmlOut = Xml::openElement( 'form', array(
    	     'name' => 'nominateAndNotify',
	     'class' => '',
	     'action' => "{$wgScriptPath}/api.php",
	     'method' => 'get'
	     )
    );

    $htmlOut .= Xml::openElement( 'input',
           array(
               'type' => 'hidden',
               'name' => 'action',
               'value' => 'apiapprove',
           )
    );

    $htmlOut .= Xml::openElement( 'input',
           array(
               'type' => 'hidden',
               'name' => 'email',
               'value' => $emailAddress,
           )
    );

    $htmlOut .= Xml::openElement( 'input',
           array(
               'type' => 'hidden',
               'name' => 'approveaction',
               'value' => $action == 'approve' ? 'approve' : 'reject',
           )
    );

    $htmlOut .= Xml::openElement( 'input',
           array(
               'type' => 'hidden',
               'name' => 'title',
               'value' => $wgTitle->getFullText(),
           )
    );

    $htmlOut .= Xml::openElement( 'input',
           array(
               'type' => 'submit',
               'value' => $action == 'approve' ? 'Approve and Notify' : 'Reject and Notify',
           )
    );

    $htmlOut .= Xml::closeElement( 'form' );

    return array( $htmlOut, 'noparse' => true, 'isHTML' => true );
}


function NominateAndNotifyInit( Parser &$parser ) {
     $parser->setFunctionHook( 'NominateAndNotify', 'NominateAndNotify' );
     return true;
}


$wgHooks['ParserFirstCallInit'][] = 'NominateAndNotifyInit';

$sdhofPressReleaseEmailAddress = 'press@example.com';
$sdhofSenderEmailAddress = 'sender@example.com';

$wgAvailableRights[] = 'approve-power';
$wgGroupPermissions['sysop']['approve-power'] = true;