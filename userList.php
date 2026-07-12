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

	$userFound = false;
	$goodPass = false;
	$currentUserId = -1;
	$users = $doc->getElementsByTagName('users')->item(0)->getElementsByTagName('user');
	for ($i=0 ; $i < $users->length && !$userFound ; $i++) {
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

	$followedIds = array();
	if ($goodPass) {
		$userXml = 'users/' . $currentUserId . '.xml';
		$userDoc = new DOMDocument();
		$userDoc->Load($userXml);
		$followsList = $userDoc->getElementsByTagName('follows')->item(0);
		if ($followsList) {
			$follows = $followsList->getElementsByTagName('follow');
			for ($i=0 ; $i < $follows->length ; $i++) {
				$followedIds[] = $follows->item($i)->getAttribute("id");
			}
		}
	}

	if ($goodPass && $users->length > 1) {
		echo '<input class="enabledButton" type="button" style="width:150px;" value="follow everyone" onclick="follow(\'all\',\'follow\')" /><br/>';
	}

	echo '<table class="listTable" >';
	for ($i=$users->length-1 ; $i >=0 ; $i--) {
		$thisUserId = $users->item($i)->getAttribute("id");
		$thisUserName = $users->item($i)->getAttribute("name");
		$isFollowed = false;
		for ($j=0 ; $j < count($followedIds) && !$isFollowed ; $j++) {
			if ($followedIds[$j]==$thisUserId) $isFollowed = true;
		}
		echo '<tr>';
		echo '<td><div style="width:75px;word-wrap:break-word;">';
		if ($goodPass) {
			if ($thisUserId == $currentUserId) echo '<input class="disabledButton" type="button" style="width:70px;" userId="' . $thisUserId . '" value="you" />';
			else if ($isFollowed) echo '<input class="enabledButton" type="button" style="width:70px;" userId="' . $thisUserId . '" onclick="follow(' . $thisUserId . ',\'unfollow\')" value="unfollow" />';
			else echo '<input class="enabledButton" type="button" style="width:70px;" userId="' . $thisUserId . '" onclick="follow(' . $thisUserId . ',\'follow\')" value="follow" />';
		} else {
			echo '<input class="disabledButton" type="button" style="width:70px;" userId="' . $thisUserId . '" value="login to follow" />';
		}
		echo '</div></td>';
		echo '<td>';
		echo '<div style="width:95px;word-wrap:break-word;">' . $thisUserName . '</div>';
		echo '</td>';
		echo '</tr>';
	}
	echo '</table>';

?>
