<?php
if(IN_MANAGER_MODE!="true") die("<b>INCLUDE_ORDERING_ERROR</b><br /><br />Please use the MODX Content Manager instead of accessing this file directly.");
if(!$modx->hasPermission('messages')) {
	$modx->webAlertAndQuit($_lang["error_no_privileges"]);
}

$sendto = $_REQUEST['sendto'];
$userid = $_REQUEST['user'];
$groupid = $_REQUEST['group'];
$subject = addslashes($_REQUEST['messagesubject']);
if($subject=="") $subject="(no subject)";
$message = addslashes($_REQUEST['messagebody']);
if($message=="") $message="(no message)";
$postdate = time();

if($sendto=='u') {
	if($userid==0) {
		$modx->webAlertAndQuit($_lang["error_no_user_selected"]);
	}
	$modx->db->insert(
		array(
			'recipient' => $userid,
			'sender'    => $modx->getLoginUserID(),
			'subject'   => $subject,
			'message'   => $message,
			'postdate'  => $postdate,
			'type'      => 'Message',
			'private'   => 1,
		), $modx->getFullTableName('user_messages'));
}

if($sendto=='g') {
	if($groupid==0) {
		$modx->webAlertAndQuit($_lang["error_no_group_selected"]);
	}
	$sql = "SELECT internalKey FROM $dbase.`".$table_prefix."user_attributes` WHERE $dbase.`".$table_prefix."user_attributes`.role=$groupid";
	$rs = $modx->db->query($sql);
	while ($row=$modx->db->getRow($rs)) {
		if($row['internalKey']!=$modx->getLoginUserID()) {
		$modx->db->insert(
			array(
				'recipient' => $row['internalKey'],
				'sender'    => $modx->getLoginUserID(),
				'subject'   => $subject,
				'message'   => $message,
				'postdate'  => $postdate,
				'type'      => 'Message',
				'private'   => 0,
			), $modx->getFullTableName('user_messages'));
		}
	}
}


if($sendto=='a') {
	$sql = "SELECT id FROM $dbase.`".$table_prefix."manager_users`";
	$rs = $modx->db->query($sql);
	while ($row=$modx->db->getRow($rs)) {
		if($row['id']!=$modx->getLoginUserID()) {
		$modx->db->insert(
			array(
				'recipient' => $row['id'],
				'sender'    => $modx->getLoginUserID(),
				'subject'   => $subject,
				'message'   => $message,
				'postdate'  => $postdate,
				'type'      => 'Message',
				'private'   => 0,
			), $modx->getFullTableName('user_messages'));
		}
	}
}

$header = "Location: index.php?a=10";
header($header);
?>