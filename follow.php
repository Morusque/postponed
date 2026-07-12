<?php

	include 'functions.php';

	$baseXml = 'users/global.xml';
	$doc = new DOMDocument();
	$doc->Load($baseXml);

	$name = "";
	$typedPassword = "";
	$token = "";
	if (isset($_POST['name'])) $name=$_POST['name'];
	if (isset($_POST['password'])) $typedPassword=$_POST['password'];
	if (isset($_POST['token'])) $token=$_POST['token'];
	if (isset($_POST['id'])) $targetId=$_POST['id'];
	$action = "follow";
	if (isset($_POST['action'])) $action=$_POST['action'];

	$userFound = false;
	$goodPass = false;
	$currentUserId = -1;
	$users = $doc->getElementsByTagName('users')->item(0)->getElementsByTagName('user');
	for ($i=0 ; $i < $users->length ; $i++) {
		$thisUserName = $users->item($i)->getAttribute("name");
		if (sameName($thisUserName, $name)) {
			$userFound = true;
			$thisHash = $users->item($i)->getAttribute("password");
			$currentUserId = $users->item($i)->getAttribute("id");
			if (authenticate($typedPassword, $token, $thisHash, 'users/'.$currentUserId.'.xml')) {
				$goodPass = true;
			}
		}
	}

	if ($goodPass && isset($targetId)) {
		$userXml = 'users/' . $currentUserId . '.xml';
		$userDoc = new DOMDocument();
		$userDoc->Load($userXml);
		$followsList = $userDoc->getElementsByTagName('follows')->item(0);
		if (!$followsList) {
			$followsList = $userDoc->createElement("follows");
			$userDoc->getElementsByTagName('user')->item(0)->appendChild($followsList);
		}
		// which users are we (un)following ?
		$targetIds = array();
		$targetNames = array();
		if ($targetId === "all") {
			for ($i=0 ; $i < $users->length ; $i++) {
				if ($users->item($i)->getAttribute("id") != $currentUserId) {
					$targetIds[] = $users->item($i)->getAttribute("id");
					$targetNames[] = $users->item($i)->getAttribute("name");
				}
			}
		} else {
			$targetId = intval($targetId);
			for ($i=0 ; $i < $users->length ; $i++) {
				if ($users->item($i)->getAttribute("id") == $targetId && $targetId != $currentUserId) {
					$targetIds[] = $users->item($i)->getAttribute("id");
					$targetNames[] = $users->item($i)->getAttribute("name");
				}
			}
		}
		for ($t=0 ; $t < count($targetIds) ; $t++) {
			$alreadyFollowed = null;
			$follows = $followsList->getElementsByTagName('follow');
			for ($i=0 ; $i < $follows->length ; $i++) {
				if ($follows->item($i)->getAttribute("id") == $targetIds[$t]) $alreadyFollowed = $follows->item($i);
			}
			if ($action == "unfollow") {
				if ($alreadyFollowed !== null) $followsList->removeChild($alreadyFollowed);
			} else {
				if ($alreadyFollowed === null) {
					$thisFollow = $userDoc->createElement("follow");
					$thisFollow->setAttribute("id",$targetIds[$t]);
					$thisFollow->setAttribute("name",$targetNames[$t]);
					$followsList->appendChild($thisFollow);
				}
			}
		}
		saveDocAtomic($userDoc, $userXml);
	}

?>
