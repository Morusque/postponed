<?php

	include 'functions.php';

	$baseXml = 'users/global.xml';
	$doc = new DOMDocument();
	$doc->Load($baseXml);

	$name = "";
	$typedPassword = "";
	$password = "";
	if (isset($_POST['name'])) $name=$_POST['name'];
	if (isset($_POST['password'])) $typedPassword=$_POST['password'];
	if (!empty($typedPassword)) $password = password_hash($typedPassword, PASSWORD_DEFAULT);

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
			if (checkPassword($typedPassword, $thisHash)) {
				$goodPass = true;
			}
		}
	}

	if (strpos($name, '@') === false) {
		if ($currentUserId === -1) {
			$unusualCharacterFound = false;
			for ($i=0;$i<strlen($name);$i++) {
				$thisChar=substr($name,$i,1);
				if ($thisChar=='/'||$thisChar=='\\'||$thisChar=='"'||$thisChar=='\''||$thisChar=='<'||$thisChar=='>'||$thisChar=='\n') $unusualCharacterFound = true;
			}
			if (!$unusualCharacterFound) {
				if (strlen($typedPassword)>1) {
					if (strlen($name)<40 && strlen($name)>2) {
						if ($typedPassword!="password" && $name!="user name") {
							if (!is_writable($baseXml) || !is_writable('users')) {
								echo "the server cannot write the user files, fix the permissions of users/";
								exit;
							}
							$freeId=-1;
							do {
								$freeId++;
								$idFound = false;
								for ($i=0 ; $i < $users->length && !$idFound ; $i++) {
									if ($users->item($i)->getAttribute("id") == $freeId) $idFound = true;
								}
							} while($idFound);
							// updates global xml
							$newUser = $doc->createElement("user");
							$newUser->setAttribute('name',$name);
							$newUser->setAttribute('id',$freeId);
							$newUser->setAttribute('password',$password);
							$doc->getElementsByTagName('users')->item(0)->appendChild($newUser);
							$doc->preserveWhiteSpace = false;
							$doc->formatOutput = true;
							saveDocAtomic($doc, $baseXml);
							// creates user xml
							$userDoc = new DOMDocument('1.0', 'UTF-8');
							$userXmlRoot = $userDoc->createElement("user");
							$userXmlRoot = $userDoc->appendChild($userXmlRoot);
							$dataNode = $userDoc->createElement("data");
							$dataNode->setAttribute('name',$name);
							$dataNode->setAttribute('id',$freeId);
							$dataNode->setAttribute('password',$password);
							$dataNode->setAttribute('points',30);
							$dataNode->setAttribute('lastPointDate',time());
							$userXmlRoot->appendChild($dataNode);
							$followsNode = $userDoc->createElement("follows");
							$userXmlRoot->appendChild($followsNode);
							$postsNode = $userDoc->createElement("posts");
							$userXmlRoot->appendChild($postsNode);
							saveDocAtomic($userDoc, 'users/' . $freeId . '.xml');
							echo "registration done";
						} else {
							if ($typedPassword=="password") echo "choose another password please<br/>";
							if ($name=="user name") echo "choose another user name please<br/>";
						}
					} else {
						if (strlen($name)>=40) echo "choose a shorter name please<br/>";
						if (strlen($name)<=2) echo "choose a longer name please<br/>";
					}	
				}else{
					echo "choose a longer password please";
				}
			} else {
				echo "don't use exotic characters in usernames please";
			}
		} else {
			echo "user with this name already exists";
		}
	} else {
		echo "enter username, not email";
	}
	
?>