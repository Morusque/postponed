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
	$canonicalName = $name;
	$users = $doc->getElementsByTagName('users')->item(0)->getElementsByTagName('user');
	$points = 0;
	$thisHash = "";
	$matchedUser = null;
	for ($i=0 ; $i < $users->length && !$userFound ; $i++) {
		$thisUserName = $users->item($i)->getAttribute("name");
		if (sameName($thisUserName, $name)) {
			$userFound = true;
			$canonicalName = $thisUserName;
			$matchedUser = $users->item($i);
			$thisHash = $matchedUser->getAttribute("password");
			$currentUserId = $matchedUser->getAttribute("id");
			if (authenticate($typedPassword, $token, $thisHash, 'users/'.$currentUserId.'.xml')) {
				$goodPass = true;
			}
		}
	}

	$newToken = "";
	if ($goodPass) {
		$userXml = 'users/'.$currentUserId.'.xml';
		$userDoc = new DOMDocument();
		$userDoc->Load($userXml);
		$points = grantDailyPoints($userDoc);
		// upgrades old unsalted md5 passwords to salted hashes (only possible when the actual password was sent)
		if (isLegacyHash($thisHash) && $typedPassword!="" && checkPassword($typedPassword, $thisHash)) {
			$newHash = password_hash($typedPassword, PASSWORD_DEFAULT);
			if (is_writable($baseXml)) {
				$matchedUser->setAttribute("password", $newHash);
				$doc->save($baseXml);
				$userDoc->getElementsByTagName('data')->item(0)->setAttribute("password", $newHash);
			}
		}
		if (is_writable($userXml)) {
			// hands out a fresh login token, its hash is stored in the user file
			// this way the browser can keep a cookie that is not the password itself
			$newToken = bin2hex(random_bytes(16));
			$userDoc->getElementsByTagName('data')->item(0)->setAttribute("tokenHash", hash('sha256', $newToken));
			$userDoc->save($userXml);
		}
	}

	$data = array('found'=>$userFound,'goodPass'=>$goodPass,'currentUserId'=>$currentUserId,'points'=>$points,'name'=>$canonicalName,'token'=>$newToken);
	echo json_encode($data);
	
?>