<?php

	include 'functions.php';

	$baseXml = 'users/global.xml';
	$doc = new DOMDocument();
	$doc->Load($baseXml);

	$pointsSpent = 0;
	$name = "";
	$typedPassword = "";
	$statusUpdate = "";
	$token = "";
	if (isset($_POST['name'])) $name=$_POST['name'];
	if (isset($_POST['password'])) $typedPassword=$_POST['password'];
	if (isset($_POST['token'])) $token=$_POST['token'];
	if (isset($_POST['statusUpdate'])) $statusUpdate=$_POST['statusUpdate'];
	if (isset($_POST['pointsSpent'])) $pointsSpent=intval($_POST['pointsSpent']);

	$statusUpdate = htmlspecialchars($statusUpdate, ENT_XML1, 'UTF-8');

	$dateWritten = time();
	$delay = 100;
	for ($i=0 ; $i<$pointsSpent ; $i++) $delay /= 2;
	// delay is often a fraction of a year, so we can't use strtotime here
	$dateToDisplay = $dateWritten + (int)round($delay * 365.25 * 24 * 60 * 60);

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
	
	if ($userFound) {
		if ($goodPass) {
			$userXml = 'users/' . $currentUserId . '.xml';
			if (!is_writable($userXml)) {
				echo json_encode(array('error'=>"the server cannot write the user files, fix the permissions of users/*.xml"));
				exit;
			}
			$userDoc = new DOMDocument();
			$userDoc->Load($userXml);
			$points = grantDailyPoints($userDoc);
			if ($pointsSpent >= 1) {
				if ($points >= $pointsSpent) {
					if (strlen($statusUpdate)<700) {
						$updatedPoints = $points - $pointsSpent;
						$userDoc->getElementsByTagName('data')->item(0)->setAttribute("points", $updatedPoints);
						$posts =  $userDoc->getElementsByTagName('posts')->item(0);
						$thisPost = $userDoc->createElement("post");
						$thisPost->setAttribute("message",$statusUpdate);
						$thisPost->setAttribute("date",$dateWritten);
						$thisPost->setAttribute("pointsSpent",$pointsSpent);
						$thisPost->setAttribute("displayDate",$dateToDisplay);
						$posts->appendChild($thisPost);
						$userDoc->save($userXml);
						$data = array('points'=>$updatedPoints);
						echo json_encode($data);
					} else {
						$userDoc->save($userXml);
						echo json_encode(array('points'=>$points,'error'=>"write a shorter status please"));
					}
				} else {
					$userDoc->save($userXml);
					echo json_encode(array('points'=>$points,'error'=>"not enough points"));
				}
			} else {
				$userDoc->save($userXml);
				echo json_encode(array('points'=>$points,'error'=>"posting costs at least one point"));
			}
		} else {
			echo json_encode(array('error'=>"wrong password"));
		}
	} else {
		echo json_encode(array('error'=>"sign up first"));
	}

?>